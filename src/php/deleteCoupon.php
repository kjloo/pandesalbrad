<?php

session_start();

include "imageUtils.inc";
require_once "adminUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE') {
        include "sqlConn.inc";
        $code = $_SERVER['PATH_INFO'];
        // Create SQL Query
        $sql = "DELETE FROM coupons WHERE code = ?";
        if($stmt = $conn->prepare($sql)) {
            // DELETE users from table
            $stmt->execute([$code]);
            // Error Check?
        }

        $conn = null;
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>