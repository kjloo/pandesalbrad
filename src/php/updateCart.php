<?php

session_start();

if (isset($_POST['updateCart']) && !empty($_POST['itemID']) && !empty($_POST['quantity'])) {

    $itemID = $_POST['itemID'];
    $nquantity = intval($_POST['quantity']);
    $oquantity = $_SESSION['u_cart'][$itemID];

    if (isset($_SESSION['u_id'])) {
        // Update database
        include "sqlConn.inc";
        $userID = $_SESSION['u_id'];

        // Create SQL Query
        $sql = "UPDATE carts SET Quantity = ? WHERE UserID = ? AND ItemID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$nquantity, $userID, $itemID]);
            // Error Check?
        }
        // Close Connection
        $conn = null;
    }

    // Update quantity of product on cookie
    $_SESSION['u_cart'][$itemID] = $nquantity;
    $data = array();
    $data['QuantityDiff'] = intval($nquantity - $oquantity);

    echo json_encode($data);

} else {
    exit();
}

?>