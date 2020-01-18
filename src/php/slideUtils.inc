<?php

session_start();

function load_slides() {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT * FROM slides ORDER BY SlideIndex";
    $result = $conn->query($sql);

    $data = array();

    // Get Result
    // output data of each row
    while ($row = $result->fetch()) {
        $data[] = $row;
    }
    return $data;
}

function order_slides($slides) {
    include "sqlConn.inc";
    // Iterate through table and order numerically
    $errors = array();
    $slideIndex = 0;
    for ($i = 0; $i < count($slides); $i++) {
        $row = $slides[$i];
        $slideID = $row["SlideID"];
        try {
            if (!isset($slideID)) {
                // This is a new entry
                $sql = "INSERT INTO slides(Name, Image, SlideIndex, Caption, Link) VALUES(?,?,?,?,?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$row["Name"], $row["Image"], $slideIndex, $row["Caption"], $row["Link"]]);
                }
            } else {
                // Update table with data
                $sql = "UPDATE slides SET Name = ?, Image = ?, SlideIndex = ?, Caption = ?, Link = ? WHERE SlideID = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$row["Name"], $row["Image"], $slideIndex, $row["Caption"], $row["Link"], $slideID]);
                    // Error Check?
                }
            }
        } catch(PDOException $Exception) {
            $errors[] = "Database Upload Issues!";
        }
        $slideIndex++;
    }
    return $errors;
}

function reorder_slides() {
    order_slides(load_slides());
}

function delete_slide($slideID) {
    include "sqlConn.inc";
    $sql = "DELETE FROM slides WHERE SlideID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$slideID]);
        // Error Check?
    }
}

function insert_slide($slide) {
    include "sqlConn.inc";
    $data = load_slides();
    $slideIndex = $slide["SlideIndex"];
    $slideID = $slide["SlideID"];
    $index = count($data);
    $found = False;
    $remove = -1;
    for ($i = 0; $i < count($data); $i++) {
        if (!$found && ($slideIndex <= $data[$i]["SlideIndex"])) {
            // Insert Slide Here
            $index = $i;
            $found = True;
        }
        if (isset($slideID) && ($slideID == $data[$i]["SlideID"])) {
            // Must delete existing row
            $remove = $i;
        }
    }
    if ($remove >= 0) {
        unset($data[$remove]);
        $data = array_values($data);
    }
    if ($index >= count($data)) {
        array_push($data, $slide);
    } else {
        $data = array_merge(array_slice($data, 0, $index), array($slide), array_slice($data, $index));
    }
    order_slides($data);
}

?>