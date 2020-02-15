<?php

session_start();

include "imageUtils.inc";
include "collectionUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['collectionID']) && !empty($_POST['cname']) && !empty($_POST['iname']) && isset($_POST['collectionIndex'])) {

        include "sqlConn.inc";
        $collectionID = $_POST['collectionID'];
        $cname = $_POST['cname'];
        $iname = $_POST['iname'];
        $collectionIndex = $_POST['collectionIndex'];

        // Need to get old image name to check if it changed
        $oldiname = get_collection_image_name_from_db($collectionID);

        // Check if file data was uploaded and update.
        if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['size'] > 0) {
            // Delete current Image
            delete_image($oldiname);
            // Upload new Image
            $errors = upload_image($iname);
            if(empty($errors) != true) {
                header("Location: ../editcollections.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        } else if ($iname != $oldiname) {
            $errors = move_image($oldiname, $iname);
          
            if(empty($errors) != true) {
                header("Location: ../editcollections.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        }

        $collection = array();
        $collection["CollectionID"] = $collectionID;
        $collection["Name"] = $cname;
        $collection["Image"] = $iname;
        $collection["CollectionIndex"] = $collectionIndex;
        $errors = insert_collection($collection);

        if (empty($errors) != true) {
            header("Location: ../editcollections.html?upload=fail&message=" . join(",", $errors));
            exit();
        }
        $conn = null;
        header("Location: ../editcollections.html?upload=success&message=Slide Update Successful.");
    } else if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['cname']) && !empty($_POST['iname']) && isset($_POST['collectionIndex'])) {

        include "sqlConn.inc";
        $cname = $_POST['cname'];
        $iname = $_POST['iname'];
        $collectionIndex = $_POST['collectionIndex'];

        // Copy file to server product directory
        $errors = upload_image($iname);
          
        if (empty($errors) != true) {
            header("Location: ../bannder.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        $collection = array();
        $collection["Name"] = $cname;
        $collection["Image"] = $iname;
        $collection["CollectionIndex"] = $collectionIndex;
        $errors = insert_collection($collection);

        if (empty($errors) != true) {
            header("Location: ../editcollections.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        $conn = null;
        header("Location: ../editcollections.html?upload=success&message=Slide Upload Successful.");
    } else {
        header("Location: ../editcollections.html?upload=fail&message=Incorrect Parameters.");
        exit();
    }
} else {
    header("Location: ../editcollections.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>