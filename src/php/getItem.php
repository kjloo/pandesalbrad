<?php

$data = array();
if (!empty($_GET["product"]) && !empty($_GET["format"])) {

    include "sqlConn.inc";

    $product = $_GET["product"];
    $format = $_GET["format"];
    $choice = isset($_GET["choice"]) ? $_GET["choice"] : null;

    $parameters = array();
    // Create SQL Query
    if (!empty($choice)) {
        $sql = "SELECT i.ItemID, i.Price, b.Background, b.Scale, b.X, b.Y FROM items AS i
                INNER JOIN formats AS f ON i.FormatID = f.FormatID
                LEFT JOIN backgrounds AS b ON f.BackgroundID = b.BackgroundID
                WHERE i.ProductID = ? AND i.FormatID = ? AND i.ChoiceID = ?";
        array_push($parameters, $product, $format, $choice);
    } else {
        $sql = "SELECT i.ItemID, i.Price, b.Background, b.Scale, b.X, b.Y FROM items AS i
                INNER JOIN formats AS f ON i.FormatID = f.FormatID
                LEFT JOIN backgrounds AS b ON f.BackgroundID = b.BackgroundID
                WHERE i.ProductID = ? AND i.FormatID = ?";
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