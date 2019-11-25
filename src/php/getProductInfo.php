<?php

session_start();

$data = null;
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET" && !empty($_SERVER['PATH_INFO'])) {
    include "sqlConn.inc";

    $productID = $_SERVER['PATH_INFO'];

    // Create SQL Query
    $sql = "SELECT ProductID, Price, Image, Name, CollectionID FROM products WHERE ProductID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute([$productID]);
        // Get Result
        // output data of each row
        if ($stmt->rowCount() == 1) {
            $data = $stmt->fetch();
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data, JSON_NUMERIC_CHECK);

?>