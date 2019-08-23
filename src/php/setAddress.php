<?php

session_start();

if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id']) && isset($_POST['setAddress']) && !empty($_POST['address']) && !empty($_POST['city']) && !empty($_POST['state']) && !empty($_POST['zipcode'])) {

    include "sqlConn.inc";

    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $userid = $_SESSION['u_id'];

    // Create update statement
    $sql = "UPDATE users SET Address = ?, City = ?, State = ?, Zipcode = ? WHERE UserID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$address, $city, $state, $zipcode, $userid]);
        // Error Check?
    }
    // Close connection
    $conn = null;
    header("Location: ../index.html");

} else {
    header("Location: ../index.html?signup=error");
    exit();
}


?>