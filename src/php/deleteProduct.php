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

        $productID = $_SERVER['PATH_INFO'];
        $iname = get_image_name_from_db($productID);
        if ($iname == null) {
            return_error_code();
        }

        // Delete image from server product directory
        $deleted = delete_image($iname);

        if ($deleted) {
            // Create SQL Query
            // Delete product from table
            $sql = "DELETE FROM products WHERE ProductID = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$productID]);
                // Error Check?
            }
        }

        $conn = null;
        $data = array();
        $data['ProductID'] = $productID;
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>