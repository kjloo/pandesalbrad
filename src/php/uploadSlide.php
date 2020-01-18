<?php

session_start();

include "imageUtils.inc";
include "slideUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['slideID']) && !empty($_POST['sname']) && !empty($_POST['iname']) && isset($_POST['slideIndex'])) {

        include "sqlConn.inc";
        $slideID = $_POST['slideID'];
        $sname = $_POST['sname'];
        $iname = $_POST['iname'];
        $slideIndex = $_POST['slideIndex'];
        // These are optional
        $caption = $_POST['caption'];
        $link = $_POST['link'];

        // Need to get old image name to check if it changed
        $oldiname = get_slide_image_name_from_db($slideID);

        // Check if file data was uploaded and update.
        if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['size'] > 0) {
            // Delete current Image
            delete_image($oldiname);
            // Upload new Image
            $errors = upload_image($iname);
            if(empty($errors) != true) {
                header("Location: ../banner.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        } else if ($iname != $oldiname) {
            $errors = move_image($oldiname, $iname);
          
            if(empty($errors) != true) {
                header("Location: ../banner.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        }

        $slide = array();
        $slide["SlideID"] = $slideID;
        $slide["Name"] = $sname;
        $slide["Image"] = $iname;
        $slide["SlideIndex"] = $slideIndex;
        $slide["Caption"] = $caption;
        $slide["Link"] = $link;
        $errors = insert_slide($slide);

        if (empty($errors) != true) {
            header("Location: ../banner.html?upload=fail&message=" . join(",", $errors));
            exit();
        }
        $conn = null;
        header("Location: ../banner.html?upload=success&message=Slide Update Successful.");
    } else if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['sname']) && !empty($_POST['iname']) && isset($_POST['slideIndex'])) {

        include "sqlConn.inc";
        $sname = $_POST['sname'];
        $iname = $_POST['iname'];
        $slideIndex = $_POST['slideIndex'];
        // These are optional
        $caption = $_POST['caption'];
        $link = $_POST['link'];

        // Copy file to server product directory
        $errors = upload_image($iname);
          
        if (empty($errors) != true) {
            header("Location: ../bannder.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        $slide = array();
        $slide["Name"] = $sname;
        $slide["Image"] = $iname;
        $slide["SlideIndex"] = $slideIndex;
        $slide["Caption"] = $caption;
        $slide["Link"] = $link;
        $errors = insert_slide($slide);

        if (empty($errors) != true) {
            header("Location: ../bannder.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        $conn = null;
        header("Location: ../banner.html?upload=success&message=Slide Upload Successful.");
    } else {
        header("Location: ../banner.html?upload=fail&message=Incorrect Parameters.");
        exit();
    }
} else {
    header("Location: ../banner.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>