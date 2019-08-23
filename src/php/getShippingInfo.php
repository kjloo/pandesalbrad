<?php

session_start();

$data = null;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT Address, City, State, Zipcode FROM users WHERE UserID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute([$_SESSION['u_id']]);
        // Get Result
        // output data of each row
        if ($stmt->rowCount() == 1) {
            $data = $stmt->fetch();
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data);

?>