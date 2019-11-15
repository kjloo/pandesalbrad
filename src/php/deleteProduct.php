<?php

session_start();

function return_error_code() {
    http_response_code(404);
    exit();
}

if (isset($_SESSION['u_id']) && isset($_SESSION['u_isAdmin']) && $_SESSION['u_isAdmin']) {
    # ProductID will be stored in the path_info from nginx server
    $method = $_SERVER['REQUEST_METHOD'];
    $productID = $_SERVER['PATH_INFO'];
    if ($method === 'DELETE' && $productID) {
        // Get DELETE Request
        include "sqlConn.inc";

        // Retrieve image name
        $sql = "SELECT Image FROM products WHERE ProductID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$productID]);

            if ($stmt->rowCount() == 1) {
                // output data
                $row = $stmt->fetch();
                $iname = $row['Image'];
            } else {
                return_error_code();
            }
        } else {
            return_error_code();
        }

        // Delete image from server product directory
        $target_dir = dirname(dirname(__FILE__)) . "/images/";
        $target_file = $target_dir . $iname;

        $deleted = False;
        if (file_exists($target_file)) {
            $deleted = unlink($target_file);
        }

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