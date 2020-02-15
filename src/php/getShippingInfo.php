<?php

session_start();

$data = null;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    include "sqlConn.inc";

    // Create SQL Query
    $sql = "SELECT AddressID, Address, City, s.StateID, Zipcode
            FROM addresses AS a
            INNER JOIN users AS u ON a.UserID = u.UserID
            INNER JOIN states AS s ON a.StateID = s.StateID
            WHERE u.UserID = ?";
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

echo json_encode($data, JSON_NUMERIC_CHECK);

?>