<?php

session_start();

include "imageUtils.inc";
include "categoryUtils.inc";
require_once "adminUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['categoryID']) && !empty($_POST['cname']) && !empty($_POST['iname'])) {

        include "sqlConn.inc";
        $categoryID = $_POST['categoryID'];
        $cname = $_POST['cname'];
        $iname = $_POST['iname'];

        // Need to get old image name to check if it changed
        $oldiname = get_category_image_name_from_db($categoryID);

        // Check if file data was uploaded and update.
        if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['size'] > 0) {
            // Delete current Image
            delete_image($oldiname);
            // Upload new Image
            $errors = upload_image($iname);
            if(empty($errors) != true) {
                header("Location: ../admin/editcategories.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        } else if ($iname != $oldiname) {
            $errors = move_image($oldiname, $iname);
          
            if(empty($errors) != true) {
                header("Location: ../admin/editcategories.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        }

        $category = array();
        $category["CategoryID"] = $categoryID;
        $category["Name"] = $cname;
        $category["Image"] = $iname;
        $errors = insert_category($category);

        if (empty($errors) != true) {
            header("Location: ../admin/editcategories.html?upload=fail&message=" . join(",", $errors));
            exit();
        }
        $conn = null;
        header("Location: ../admin/editcategories.html?upload=success&message=Category Update Successful.");
    } else if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['cname']) && !empty($_POST['iname'])) {

        include "sqlConn.inc";
        $cname = $_POST['cname'];
        $iname = $_POST['iname'];

        // Copy file to server product directory
        $errors = upload_image($iname);
          
        if (empty($errors) != true) {
            header("Location: ../admin/editcategories.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        $category = array();
        $category["Name"] = $cname;
        $category["Image"] = $iname;
        $errors = insert_category($category);

        if (empty($errors) != true) {
            header("Location: ../admin/editcategories.html?upload=fail&message=" . join(",", $errors));
            exit();
        }
        $conn = null;
        header("Location: ../admin/editcategories.html?upload=success&message=Category Upload Successful.");
    } else {
        header("Location: ../admin/editcategories.html?upload=fail&message=Incorrect Parameters.");
        exit();
    }
} else {
    header("Location: ../admin/editcategories.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>