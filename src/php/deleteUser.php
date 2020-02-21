<?php

session_start();

include "imageUtils.inc";
require_once "adminUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE') {
        include "sqlConn.inc";
        $parameters = array();
        // Create SQL Query
        $sqlArr = ["DELETE FROM users"];
        if(!empty($_SERVER['PATH_INFO'])) {
            // Get DELETE Request
            $userID = $_SERVER['PATH_INFO'];

            array_push($sqlArr, "WHERE UserID = ?");
            array_push($parameters, $userID);
        } else {
            array_push($sqlArr, "WHERE Activated = False AND SignupDate < ADDDATE(NOW(), INTERVAL -1 MONTH)");
        }
        $sql = join(" ", $sqlArr);
        if($stmt = $conn->prepare($sql)) {
            // DELETE users from table
            $stmt->execute($parameters);
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