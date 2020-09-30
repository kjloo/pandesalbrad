<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "adminUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE' && !empty($_SERVER['PATH_INFO'])) {
        // Get DELETE Request
        include "sqlConn.inc";

        $formatID = $_SERVER['PATH_INFO'];
        $sql = "DELETE FROM formats WHERE FormatID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$formatID]);
            // Error Check?
        }

        $conn = null;
        $data = array();
        $data['FormatID'] = $formatID;
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>