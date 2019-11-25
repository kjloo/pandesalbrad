<?php

session_start();

include "imageUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['productID']) && !empty($_POST['pname']) && !empty($_POST['iname']) && !empty($_POST['price']) && !empty($_POST['category'])) {

        include "sqlConn.inc";
        $productID = $_POST['productID'];
        $pname = $_POST['pname'];
        $iname = $_POST['iname'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        // Need to get old image name to check if it changed
        $oldiname = get_image_name_from_db($productID);

        // Check if file data was uploaded and update.
        if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['size'] > 0) {
            // Delete current Image
            delete_image($oldiname);
            // Upload new Image
            $errors = upload_image($iname);
            if(empty($errors) != true) {
                header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        } else if ($iname != $oldiname) {
            $errors = move_image($oldiname, $iname);
          
            if(empty($errors) != true) {
                header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        }

        // Update product info in database
        // Create SQL Query
        $sql = "UPDATE products SET Price = ?, Image = ?, Name = ?, CollectionID = ? WHERE ProductID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$price, $iname, $pname, $category, $productID]);
            // Error Check?
        }
        $conn = null;
        header("Location: ../upload.html?upload=success&message=File Update Successful.");
    } else if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['pname']) && !empty($_POST['iname']) && !empty($_POST['price']) && !empty($_POST['category'])) {

        include "sqlConn.inc";
        $pname = $_POST['pname'];
        $iname = $_POST['iname'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        // Copy file to server product directory
        $errors = upload_image($iname);
          
        if(empty($errors) != true) {
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
        header("Location: ../upload.html?upload=fail&message=Incorrect Parameters.");
        exit();
    }
} else {
    header("Location: ../upload.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>