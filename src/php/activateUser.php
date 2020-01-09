<?php

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PUT' && !empty($_SERVER['PATH_INFO'])) {
    // Get PUT Request
    include "sqlConn.inc";

    $token = $_SERVER['PATH_INFO'];

    // Create SQL Query
    // Set user activated
    $sql = "UPDATE users SET Activated = TRUE, Token = NULL WHERE Token = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$token]);
        // Error Check?
    } else {
        echo False;
        exit();
    }

    $conn = null;
    echo True;
} else {
    echo False;
}

?>