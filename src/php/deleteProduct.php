<?php

session_start();

include "imageUtils.inc";
require_once "adminUtils.inc";

if (is_user_admin()) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'DELETE' && !empty($_SERVER['PATH_INFO'])) {
        $errors = array();
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
            try {
                if($stmt = $conn->prepare($sql)) {
                    if (!$stmt->execute([$productID])) {
                        $errors[] = $stmt->errno . " " . $stmt->error;
                    }
                }
            } catch (PDOException $e) {
                $errors[] = $e;
            }

        } else {
            $errors[] = "Could not delete image.";
        }

        $conn = null;
        $data = array();
        $data['Status'] = empty($errors);
        $data['ProductID'] = $productID;
        $data['Message'] = join(",", $errors);
        echo json_encode($data);
    } else {
        return_error_code();
    }
} else {
    return_error_code();
}

?>