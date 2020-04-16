<?php

function load_categories() {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT * FROM categories";
    $result = $conn->query($sql);

    $data = array();

    // Get Result
    // output data of each row
    while ($row = $result->fetch()) {
        $data[] = $row;
    }

    return $data;

}

function insert_category($category) {
    include "sqlConn.inc";
    $errors = array();
    try {
        $args = [$category["Name"], $category["Image"]];
        if (isset($category["CategoryID"])) {
            // Update
            $sql = "UPDATE categories SET Name = ?, Image = ? WHERE CategoryID = ?";
            array_push($args, $category["CategoryID"]);
        } else {
            // Insert
            $sql = "INSERT INTO categories (Name, Image) VALUES (?,?)";
        }
        // Execute SQL
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute($args);
            // Error Check?
        }
    } catch(PDOException $Exception) {
        $errors[] = "Database Upload Issues!";
    }

    return $errors;
}

function delete_category($categoryID) {
    include "sqlConn.inc";
    $sql = "DELETE FROM categories WHERE CategoryID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$categoryID]);
        // Error Check?
    }
}

?>