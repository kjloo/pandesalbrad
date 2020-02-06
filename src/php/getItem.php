<?php

$data = array();
if (!empty($_GET["product"]) && !empty($_GET["format"])) {

    include "sqlConn.inc";

    $product = $_GET["product"];
    $format = $_GET["format"];
    $choice = $_GET["choice"];

    $parameters = array();
    // Create SQL Query
    if (!empty($choice)) {
        $sql = "SELECT ItemID, Price FROM items WHERE ProductID = ? AND FormatID = ? AND ChoiceID = ?";
        array_push($parameters, $product, $format, $choice);
    } else {
        $sql = "SELECT ItemID, Price FROM items WHERE ProductID = ? AND FormatID = ?";
        array_push($parameters, $product, $format);
    }

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute($parameters);

        // Should return one item
        if ($stmt->rowCount() == 1) {
            $data = $stmt->fetch();
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data);

?>