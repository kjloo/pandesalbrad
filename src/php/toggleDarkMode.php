<?php

session_start();

$_SESSION['u_darkmode'] = !$_SESSION['u_darkmode'];

if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {

    include "sqlConn.inc";

    $userid = $_SESSION['u_id'];

    // Create update statement
    $sql = "UPDATE users SET Darkmode = !Darkmode WHERE UserID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$userid]);
        // Error Check?
    }

    // Close connection
    $conn = null;
    header("Location: ../index.html");

}

?>