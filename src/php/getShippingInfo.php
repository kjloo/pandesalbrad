<?php

session_start();

$data = null;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT AddressID, Address, City, State, Zipcode FROM addresses AS a INNER JOIN users AS u on a.UserID = u.UserID WHERE u.UserID = ?";
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