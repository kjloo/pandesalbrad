<?php

session_start();

include "imageUtils.inc";
require_once "adminUtils.inc";

if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['info'])) {

        include "sqlConn.inc";
        $info = $_POST['info'];

        // about TABLE in db restricts to VARCHAR(2048)
        if (strlen($info) > 2048) {       
            header("Location: ../admin/editabout.html?status=fail&message=Message is Too Large.");
            $conn = null;
            exit();
        }

        // Update user permissions into database
        // Create SQL Query
        $sql = "UPDATE about SET Message = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$info]);
            // Error Check?
        }
        $conn = null;
        header("Location: ../admin/editabout.html?status=success&message=Operation Successful.");
    }
    else {    
        header("Location: ../admin/editabout.html?status=fail&message=Not Enough Information.");
        exit();
    }
} else {
    header("Location: ../admin/editabout.html?status=fail&message=Insufficient Permissions.");
    exit();
}

?>