<?php

session_start();

$data = array();
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    include "sqlConn.inc";

    $userID = $_SESSION['u_id'];
    $orderID = $_GET["orderID"];
    $statusID = $_GET["statusID"];
    $addAnd = False;

    // Create SQL Query
    $parameters = array();
    $sql = "SELECT o.OrderID, OrderDate, s.Status, o.StatusID, pr.Image, pr.Name, p.Quantity, Total FROM orders AS o INNER JOIN packages AS p ON o.OrderID = p.OrderID INNER JOIN products AS pr ON p.ProductID = pr.ProductID INNER JOIN statuses AS s ON o.StatusID = s.StatusID";
    include "imageUtils.inc";
    if (!is_user_admin()) {
        $sql .= " WHERE UserID = ?";
        $addAnd = True;
        array_push($parameters, $userID);
    }
    if (isset($statusID)) {
        if ($addAnd) {
            $sql .= " AND";
        }
        $sql .= " WHERE s.StatusID = ?";
        $addAnd = True;
        array_push($parameters, $statusID);
    }
    if (isset($orderID)) {
        if ($addAnd) {
            $sql .= " AND";
        }
        $sql .= " WHERE o.OrderID LIKE ?";
        array_push($parameters, "%$orderID%");
    }

    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute($parameters);
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
                        'StatusID' => $row['StatusID'],
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