<?php

include "sqlConn.inc";

$collection = isset($_GET["collection"]) ? $_GET["collection"] : null;
$product = isset($_GET["product"]) ? $_GET["product"] : null;
$name = isset($_GET["name"]) ? $_GET["name"] : null;

$parameters = array();
// Create SQL Query
// Base Quersy
$sql_arr = ["SELECT p.ProductID, p.Name, p.Image, p.Available, p.CollectionID, pc.CategoryID FROM products AS p 
        INNER JOIN product_categories AS pc ON p.ProductID = pc.ProductID"];
if (!empty($product)) {
    array_push($sql_arr, "WHERE p.ProductID = ?");
    array_push($parameters, $product);
} else if (!empty($collection)) {
    array_push($sql_arr, "WHERE p.CollectionID = ?");
    array_push($parameters, $collection);
} else if (!empty($name)) {
    array_push($sql_arr, "WHERE p.Name LIKE ?");
    array_push($parameters, "%$name%");
}

$products = array();
$sql = join(" ", $sql_arr);
if($stmt = $conn->prepare($sql)) {
    $stmt->execute($parameters);
    // output data of each row
    if ($stmt->rowCount() > 0) {
        foreach ($stmt as $row) {
            // Analyze row
            $productID = $row["ProductID"];
            if (array_key_exists($productID, $products)) {
                // Add to categories list
                $categoryID = $row["CategoryID"];
                array_push($products[$productID]["Categories"], $categoryID);
            } else {
                // New product
                $product = array();
                $product["ProductID"] = $row["ProductID"];
                $product["Name"] = $row["Name"];
                $product["Image"] = $row["Image"];
                $product["Available"] = $row["Available"];
                $product["CollectionID"] = $row["CollectionID"];
                $product["Categories"] = [$row["CategoryID"]];
                $products[$productID] = $product;
            }
        }
    }
}
// Close Connection
$conn = null;

// Filter unavailable products
require_once "adminUtils.inc";

if (!is_user_admin()) {
    $products = array_filter($products, function($product) {
        return $product["Available"];
    });
}

echo json_encode(array_values($products));

?>