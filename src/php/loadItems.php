<?php

$data = array();
if (!empty($_GET["product"])) {

    include "sqlConn.inc";

    $product = $_GET["product"];

    // Create SQL Query
    $sql = "SELECT f.FormatID AS FormatID, f.Name AS Name FROM items AS i INNER JOIN formats AS f ON i.FormatID = f.FormatID WHERE i.ProductID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$product]);

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