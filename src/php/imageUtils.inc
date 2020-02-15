<?php

session_start();

function is_user_admin() {
    return isset($_SESSION['u_id']) && isset($_SESSION['u_isAdmin']) && $_SESSION['u_isAdmin'];
}

function return_error_code() {
    http_response_code(404);
    exit();
}

function get_image_name_from_db($productID) {
    // Retrieve image name
    include "sqlConn.inc";
    $iname = null;
    $sql = "SELECT Image FROM products WHERE ProductID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$productID]);

        if ($stmt->rowCount() == 1) {
            // output data
            $row = $stmt->fetch();
            $iname = $row['Image'];
        }
    }
    return $iname;
}

function get_slide_image_name_from_db($slideID) {
    // Retrieve image name
    include "sqlConn.inc";
    $iname = null;
    $sql = "SELECT Image FROM slides WHERE SlideID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$slideID]);

        if ($stmt->rowCount() == 1) {
            // output data
            $row = $stmt->fetch();
            $iname = $row['Image'];
        }
    }
    return $iname;
}

function get_collection_image_name_from_db($collectionID) {
    // Retrieve image name
    include "sqlConn.inc";
    $iname = null;
    $sql = "SELECT Image FROM collections WHERE CollectionID = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$collectionID]);

        if ($stmt->rowCount() == 1) {
            // output data
            $row = $stmt->fetch();
            $iname = $row['Image'];
        }
    }
    return $iname;
}

function get_images_dir() {
    return dirname(dirname(__FILE__)) . "/images/";
}

function get_image_path($iname) {
    $target_dir = get_images_dir();
    $target_file = $target_dir . $iname;
    return $target_file;
}

function move_image($src, $dst) {
    $source_file = get_image_path($src);
    $target_file = get_image_path($dst);
    $errors = array();
    if (file_exists($target_file)) {
        $errors[] = "File already exists.";
    } else {
        if (!rename($source_file, $target_file)) {
            $errors[] = "Could not rename file " . $source_file .  " to " . $target_file;
        }
    }
    return $errors;
}

function delete_image($iname) {
    // Delete image from server product directory
    $target_file = get_image_path($iname);

    $deleted = False;
    if (file_exists($target_file)) {
        $deleted = unlink($target_file);
    }
    return $deleted;
}

function upload_image($iname) {
    // Copy file to server product directory
    $target_file = get_image_path($iname);
    $errors = array();

    if (file_exists($target_file)) {
        $errors[] = "File already exists.";
    }

    $file_size = $_FILES['uploadedImage']['size'];
    $file_tmp = $_FILES['uploadedImage']['tmp_name'];
    $file_ext = strtolower(end(explode('.',$_FILES['uploadedImage']['name'])));
      
    $extensions = array("jpeg", "jpg", "png");
      
    if(in_array($file_ext, $extensions) === false) {
        $errors[] = "File Type not allowed, please choose a JPEG or PNG file.";
    }
      
    if($file_size > 2097152){
        $errors[] = "File size must be less than 2 MB.";
    }
      
    if(empty($errors) == true){
        $uploadStatus = move_uploaded_file($file_tmp, $target_file);
        if ($uploadStatus) {
            echo "Success File Upload Successful.";
        } else {
            $errors[] = $uploadStatus;
        }
    }
    
    return $errors;
}

?>