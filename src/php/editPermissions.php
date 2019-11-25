<?php

session_start();

include "imageUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['permissions']) && !empty($_POST['userID']) && !empty($_POST['roleID'])) {

        include "sqlConn.inc";
        $userID = $_POST['userID'];
        $roleID = $_POST['roleID'];

        // Update user permissions into database
        // Create SQL Query
        $sql = "UPDATE users SET RoleID = ? WHERE UserID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$roleID, $userID]);
            // Error Check?
        }
        $conn = null;
        header("Location: ../editusers.html?action=success&message=Operation Successful.");
    }
} else {
    header("Location: ../editusers.html?action=fail&message=Insufficient Permissions.");
    exit();
}

?>