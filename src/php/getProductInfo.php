<?php

session_start();

$data = array();
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET" && !empty($_SERVER['PATH_INFO'])) {
    include "sqlConn.inc";

    $productID = $_SERVER['PATH_INFO'];

    // Create SQL Query
    $sql = "SELECT p.ProductID, i.Price, p.Image, p.Available, p.Name, p.CollectionID, i.FormatID FROM products AS p INNER JOIN items AS i ON p.ProductID = i.ProductID WHERE p.ProductID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute([$productID]);
        // Get Result
        // output data of each row
        if ($stmt->rowCount() > 0) {
            foreach ($stmt as $row) {
                $data[] = $row;
            }
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data, JSON_NUMERIC_CHECK);

?>