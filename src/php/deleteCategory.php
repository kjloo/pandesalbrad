<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "imageUtils.inc";
require_once "adminUtils.inc";
include "categoryUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE' && !empty($_SERVER['PATH_INFO'])) {
        // Get DELETE Request
        include "sqlConn.inc";

        $categoryID = $_SERVER['PATH_INFO'];
        $iname = get_category_image_name_from_db($categoryID);
        if ($iname == null) {
            return_error_code();
        }

        // Delete image from server image directory
        $deleted = delete_image($iname);

        if ($deleted) {
            // Create SQL Query
            // Delete product from table
            delete_category($categoryID);
        }

        $conn = null;
        $data = array();
        $data['CategoryID'] = $categoryID;
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>