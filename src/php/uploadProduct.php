<?php

session_start();

if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['pname']) && !empty($_POST['iname']) && !empty($_POST['price']) && !empty($_POST['category'])) {

    if (isset($_SESSION['u_id']) && isset($_SESSION['u_isAdmin']) && $_SESSION['u_isAdmin']) {

        include "sqlConn.inc";
        $pname = $_POST['pname'];
        $iname = $_POST['iname'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        // Copy file to server product directory
        $target_dir = dirname(dirname(__FILE__)) . "/images/";
        $target_file = $target_dir . $iname;
        $errors = array();

        if (file_exists($target_file)) {
            $errors[] = "File already exists.";
        }

        $file_size =$_FILES['uploadedImage']['size'];
        $file_tmp =$_FILES['uploadedImage']['tmp_name'];
        $file_ext=strtolower(end(explode('.',$_FILES['uploadedImage']['name'])));
          
        $extensions= array("jpeg", "jpg", "png");
          
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
                echo "Could not upload image.";
                header("Location: ../upload.html?upload=fail&message=" . $uploadStatus);
                exit();
            }
        } else {
            //print_r($errors);
            header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        // Create SQL Query
        // First see if item already in cart
        $sql = "INSERT INTO products (Price, Image, Name, CollectionID) VALUES (?, ?, ?, ?)";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$price, $iname, $pname, $category]);
            // Error Check?
        }

        $conn = null;
        header("Location: ../upload.html?upload=success&message=File Upload Successful.");
    } else {
        header("Location: ../upload.html?upload=fail&message=Insufficient Permissions.");
        exit();
    }
} else {
    header("Location: ../upload.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>