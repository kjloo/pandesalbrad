<?php

session_start();

function return_error_code() {
    http_response_code(404);
    exit();
}

include "imageUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE' && !empty($_SERVER['PATH_INFO'])) {
        // Get DELETE Request
        include "sqlConn.inc";

        $userID = $_SERVER['PATH_INFO'];

        // Create SQL Query
        // Delete user from table
        $sql = "DELETE FROM users WHERE UserID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$userID]);
            // Error Check?
        }

        $conn = null;
        $data = array();
        $data['UserID'] = $userID;
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>