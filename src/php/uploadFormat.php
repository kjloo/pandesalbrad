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

        // Begin Transcation
        $conn->beginTransaction();
        // Update format table
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
            $stmt->closeCursor();

            if (empty($_POST['formatID'])) {         
                // Store inserted id as the new formatID
                $formatID = $conn->lastInsertId();
            }
        }

        // Update background table
        if (!empty($_POST['background']) && isset($_POST['scale']) && isset($_POST['xPos']) && isset($_POST['yPos'])) {
            $background = $_POST['background'];
            $scale = $_POST['scale'];
            $xPos = $_POST['xPos'];
            $yPos = $_POST['yPos'];

            $parameters = array($background, $scale, $xPos, $yPos);

            if (!empty($_POST['backgroundID'])) {
                $backgroundID = $_POST['backgroundID'];
                $sql = "UPDATE backgrounds SET Background = ?, Scale = ?, X = ?, Y = ? WHERE BackgroundID = ?";
                array_push($parameters, $backgroundID);
            } else {
                $sql = "INSERT INTO backgrounds(Background, Scale, X, Y) VALUES(?, ?, ?, ?)";
            }

            if($stmt = $conn->prepare($sql)) {
                $stmt->execute($parameters);
                // Error Check?
                $stmt->closeCursor();

                if (empty($_POST['backgroundID'])) {
                    // Store inserted id as the new backgroundID
                    $backgroundID = $conn->lastInsertId();

                    // Insert new ID into format table
                    if ($backgroundID) {
                        $sql = "UPDATE formats SET BackgroundID = ? WHERE FormatID = ?";
                        if($stmt = $conn->prepare($sql)) {
                            $stmt->execute([$backgroundID, $formatID]);
                            // Error Check?
                            $stmt->closeCursor();
                        }
                    }
                }

            }
        }

        $conn->commit();
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