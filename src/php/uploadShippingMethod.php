<?php

session_start();
require_once "adminUtils.inc";

if (is_user_admin()) {
    if (isset($_POST['save']) && !empty($_POST['name']) && !empty($_POST['cost']) && !empty($_POST['bundle'])) {
        include "sqlConn.inc";
        $name = $_POST['name'];
        $cost = $_POST['cost'];
        $bundle = $_POST['bundle'];

        $parameters = array($name, $cost, $bundle);

        if (!empty($_POST['shippingID'])) {
            // Update
            $shippingID = $_POST['shippingID'];
            $sql = "UPDATE shipping SET Name = ?, Cost = ?, Bundle = ? WHERE ShippingID = ?";
            array_push($parameters, $shippingID);
        } else {
            // Insert
            $sql = "INSERT INTO shipping(Name, Cost, Bundle) VALUES(?, ?, ?)";
        }

        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($parameters);
            // Error Check?
        }

        // Close Connection
        $conn = null;
        // Success
        header("Location: ../admin/editshipping.html?status=success&message=Changes Saved.");
        exit();
    } else {
        header("Location: ../admin/editshipping.html?status=fail&message=Invalid Data!");
        exit();
    }
} else {
    header("Location: ../admin/editshipping.html?status=fail&message=Insufficient Permissions!");
    exit();
}

?>