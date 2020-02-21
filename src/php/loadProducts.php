<?php

include "sqlConn.inc";

$collection = isset($_GET["collection"]) ? $_GET["collection"] : null;
$product = isset($_GET["product"]) ? $_GET["product"] : null;
$name = isset($_GET["name"]) ? $_GET["name"] : null;

echo $product;

$parameters = array();
// Create SQL Query
if (!empty($product)) {
    $sql = "SELECT * FROM products WHERE ProductID = ?";
    array_push($parameters, $product);
} else if (!empty($collection)) {
    $sql = "SELECT * FROM products WHERE CollectionID = ?";
    array_push($parameters, $collection);
} else if (!empty($name)) {
    $sql = "SELECT * FROM products WHERE Name LIKE ?";
    array_push($parameters, "%$name%");
} else {
    $sql = "SELECT * FROM products";
}

$data = array();
if($stmt = $conn->prepare($sql)) {
    $stmt->execute($parameters);

    // output data of each row
    if ($stmt->rowCount() > 0) {
        foreach ($stmt as $row) {
            $data[] = $row;
        }
    }
}
// Close Connection
$conn = null;

echo json_encode($data);

?>