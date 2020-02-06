<?php

$data = array();
if (!empty($_GET["product"]) && !empty($_GET["format"])) {

    include "sqlConn.inc";

    $product = $_GET["product"];
    $format = $_GET["format"];

    // Create SQL Query
    $sql = "SELECT c.Name, c.ChoiceID FROM items AS i
        INNER JOIN choices AS c ON i.ChoiceID = c.ChoiceID
        WHERE i.ProductID = ? AND i.FormatID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$product, $format]);

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

echo json_encode($data);

?>
