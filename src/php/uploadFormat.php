<?php

session_start();
require_once "adminUtils.inc";

if (is_user_admin()) {
    if (isset($_POST['save']) && !empty($_POST['name']) && isset($_POST['freebie']) && !empty($_POST['defaultPrice']) &&
        !empty($_POST['method'])) {
        include "sqlConn.inc";
        $name = $_POST['name'];
        $freebie = $_POST['freebie'];
        $defaultPrice = $_POST['defaultPrice'];
        $shippingID = $_POST['method'];

        $parameters = array($name, $freebie, $defaultPrice, $shippingID);

        if (!empty($_POST['formatID'])) {
            // Update
            $formatID = $_POST['formatID'];
            $sql = "UPDATE formats SET Name = ?, Freebie = ?, DefaultPrice = ?, ShippingID = ? WHERE FormatID = ?";
            array_push($parameters, $formatID);
        } else {
            // Insert
            $sql = "INSERT INTO formats(Name, Freebie, DefaultPrice, ShippingID) VALUES(?, ?, ?, ?)";
        }

        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($parameters);
            // Error Check?
        }

        // Close Connection
        $conn = null;
        // Success
        header("Location: ../admin/editformats.html?status=success&message=Changes Saved.");
        exit();
    } else {
        header("Location: ../admin/editformats.html?status=fail&message=Invalid Data!");
        exit();
    }
} else {
    header("Location: ../admin/editformats.html?status=fail&message=Insufficient Permissions!");
    exit();
}

?>