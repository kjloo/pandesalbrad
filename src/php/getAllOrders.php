<?php

session_start();

$data = array();
include "imageUtils.inc";
if (is_user_admin()) {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT o.OrderID, OrderDate, s.Status, pr.Image, pr.Name, p.Quantity, Total FROM orders AS o INNER JOIN packages AS p ON o.OrderID = p.OrderID INNER JOIN products AS pr ON p.ProductID = pr.ProductID INNER JOIN statuses AS s ON o.StatusID = s.StatusID";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        // Get Result
        // output data of each row
        if ($stmt->rowCount() > 0) {
            foreach ($stmt as $row) {
                $orderID = $row['OrderID'];
                if (!array_key_exists($orderID, $data)) {
                    $data[$orderID] = array(
                        'OrderID' => $orderID,
                        'OrderDate' => $row['OrderDate'],
                        'Status' => $row['Status'],
                        'Total' => $row['Total'],
                        'Products' => array()
                    );
                }
                $product = array(
                    'Image' => $row['Image'],
                    'Name' => $row['Name'],
                    'Quantity' => $row['Quantity']
                );
                array_push($data[$orderID]['Products'], $product);
            }
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data);

?>