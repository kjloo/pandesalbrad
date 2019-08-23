<?php

session_start();

if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id']) && isset($_POST['setAccountInfo']) && !empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['email'])) {

    include "sqlConn.inc";

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $userid = $_SESSION['u_id'];

    // Create update statement
    $sql = "UPDATE users SET Firstname = ?, Lastname = ?, Email = ? WHERE UserID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$fname, $lname, $email, $userid]);
        // Error Check?
    }

    // Close connection
    $conn = null;
    header("Location: ../index.html");

} else {
    header("Location: ../index.html?account=error");
    exit();
}


?>