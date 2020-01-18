<?php

session_start();

include "imageUtils.inc";
include "slideUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE' && !empty($_SERVER['PATH_INFO'])) {
        // Get DELETE Request
        include "sqlConn.inc";

        $slideID = $_SERVER['PATH_INFO'];
        $iname = get_slide_image_name_from_db($slideID);
        if ($iname == null) {
            return_error_code();
        }

        // Delete image from server image directory
        $deleted = delete_image($iname);

        if ($deleted) {
            // Create SQL Query
            // Delete product from table
            delete_slide($slideID);
        }

        // Run reorder util
        reorder_slides();

        $conn = null;
        $data = array();
        $data['SlideID'] = $slideID;
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>