<?php

session_start();

include "imageUtils.inc";
require_once "adminUtils.inc";

if (is_user_admin()) {
    if (isset($_POST['status']) && !empty($_POST['orderID']) && !empty($_POST['statusID'])) {

        include "sqlConn.inc";
        $orderID = $_POST['orderID'];
        $statusID = $_POST['statusID'];

        // Update user permissions into database
        // Create SQL Query
        $sql = "UPDATE orders SET statusID = ? WHERE orderID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$statusID, $orderID]);
            // Error Check?
        }
        $conn = null;
        header("Location: ../orders.html?action=success&message=Operation Successful.");
    }
} else {
    header("Location: ../orders.html?action=fail&message=Insufficient Permissions.");
    exit();
}

?>