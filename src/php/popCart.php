<?php

session_start();

if (isset($_POST['popCart']) && !empty($_POST['productID'])) {

    $productID = $_POST['productID'];
    $quantity = $_SESSION['u_cart'][$productID];

    if (isset($_SESSION['u_id'])) {
        // Delete from database
        include "sqlConn.inc";
        $userID = $_SESSION['u_id'];

        // Create SQL Query
        $sql = "DELETE FROM carts WHERE UserID = ? AND ProductID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$userID, $productID]);
            // Error Check?
        }
    }

    // Delete product from cookie
    unset($_SESSION['u_cart'][$productID]);
    $data = array();
    $data['Quantity'] = $quantity;

    // Close Connection
    $conn = null;
    echo json_encode($data);

} else {
    exit();
}

?>