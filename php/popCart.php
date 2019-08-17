<?php

session_start();

if (isset($_POST['popCart']) && !empty($_POST['userID']) && !empty($_POST['productID'])) {

    include "sqlConn.inc";

    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $productID = mysqli_real_escape_string($conn, $_POST['productID']);

    $sql = "SELECT Quantity FROM carts WHERE UserID = ? AND ProductID = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $userID, $productID);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        
        mysqli_stmt_close($stmt);

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $quantity = $data['Quantity'];
        }
    }

    // Create SQL Query
    $sql = "DELETE FROM carts WHERE UserID = ? AND ProductID = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $userID, $productID);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        
        mysqli_stmt_close($stmt);
    }

    // Successfully inserted into database
    $_SESSION['u_cart'] -= $quantity;
    $data = array();
    $data['Quantity'] = $quantity;
    echo json_encode($data);

} else {
    exit();
}

?>