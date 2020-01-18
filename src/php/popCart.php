<?php

session_start();

if (isset($_POST['popCart']) && !empty($_POST['itemID'])) {

    $itemID = $_POST['itemID'];
    $quantity = $_SESSION['u_cart'][$itemID];

    if (isset($_SESSION['u_id'])) {
        // Delete from database
        include "sqlConn.inc";
        $userID = $_SESSION['u_id'];

        // Create SQL Query
        $sql = "DELETE FROM carts WHERE UserID = ? AND itemID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$userID, $itemID]);
            // Error Check?
        }
    }

    // Delete product from cookie
    unset($_SESSION['u_cart'][$itemID]);
    $data = array();
    $data['Quantity'] = $quantity;

    // Close Connection
    $conn = null;
    echo json_encode($data);

} else {
    exit();
}

?>