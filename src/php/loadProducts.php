<?php

include "sqlConn.inc";

$collection = $_GET["collection"];

$parameters = array();
// Create SQL Query
if (!empty($collection)) {
    $sql = "SELECT * FROM products WHERE CollectionID = ?";
    array_push($parameters, $collection);
} else {
    $sql = "SELECT * FROM products";
}

$data = array();
if($stmt = $conn->prepare($sql)) {
    $stmt->execute($parameters);

    // output data of each row
    foreach ($stmt as $row) {
        $data[] = $row;
    }
}
// Close Connection
$conn = null;

echo json_encode($data);

?>