<?php

$data = array();
if (!empty($_GET["product"]) && !empty($_GET["format"])) {

    include "sqlConn.inc";

    $product = $_GET["product"];
    $format = $_GET["format"];

    // Create SQL Query
    $sql = "SELECT ItemID, Price FROM items WHERE ProductID = ? AND FormatID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$product, $format]);

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