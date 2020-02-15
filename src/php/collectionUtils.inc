<?php

session_start();

function load_collections() {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT * FROM collections ORDER BY CollectionIndex";
    $result = $conn->query($sql);

    $data = array();

    // Get Result
    // output data of each row
    while ($row = $result->fetch()) {
        $data[] = $row;
    }
    return $data;
}

function order_collections($collections) {
    include "sqlConn.inc";
    // Iterate through table and order numerically
    $errors = array();
    $collectionIndex = 0;
    for ($i = 0; $i < count($collections); $i++) {
        $row = $collections[$i];
        $collectionID = $row["CollectionID"];
        try {
            if (!isset($collectionID)) {
                // This is a new entry
                $sql = "INSERT INTO collections(Name, Image, CollectionIndex) VALUES(?,?,?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$row["Name"], $row["Image"], $collectionIndex]);
                }
            } else {
                // Update table with data
                $sql = "UPDATE collections SET Name = ?, Image = ?, CollectionIndex = ? WHERE CollectionID = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$row["Name"], $row["Image"], $collectionIndex, $collectionID]);
                    // Error Check?
                }
            }
        } catch(PDOException $Exception) {
            $errors[] = "Database Upload Issues!";
        }
        $collectionIndex++;
    }
    return $errors;
}

function reorder_collections() {
    order_collections(load_collections());
}

function delete_collection($collectionID) {
    include "sqlConn.inc";
    $sql = "DELETE FROM collections WHERE CollectionID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$collectionID]);
        // Error Check?
    }
}

function insert_collection($collection) {
    include "sqlConn.inc";
    $data = load_collections();
    $collectionIndex = $collection["CollectionIndex"];
    $collectionID = $collection["CollectionID"];
    $index = count($data);
    $found = False;
    $remove = -1;
    for ($i = 0; $i < count($data); $i++) {
        if (!$found && ($collectionIndex <= $data[$i]["CollectionIndex"])) {
            // Insert Collection Here
            $index = $i;
            $found = True;
        }
        if (isset($collectionID) && ($collectionID == $data[$i]["CollectionID"])) {
            // Must delete existing row
            $remove = $i;
        }
    }
    if ($remove >= 0) {
        unset($data[$remove]);
        $data = array_values($data);
    }
    if ($index >= count($data)) {
        array_push($data, $collection);
    } else {
        $data = array_merge(array_slice($data, 0, $index), array($collection), array_slice($data, $index));
    }
    order_collections($data);
}

?>