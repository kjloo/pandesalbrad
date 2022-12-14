<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_user_admin() {
    return isset($_SESSION['u_id']) && isset($_SESSION['u_isAdmin']) && $_SESSION['u_isAdmin'];
}

function query_admin() {
    $isAdmin = False;
    if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
        include "sqlConn.inc";

        // Create SQL Query
        $sql = "SELECT r.Role FROM users AS u INNER JOIN roles AS r ON u.RoleID = r.RoleID WHERE UserID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute([$_SESSION['u_id']]);
            // Get Result
            // output data of each row
            if ($stmt->rowCount() == 1) {
                $data = $stmt->fetch();
            }
        }
        // Analyze and return result
        $isAdmin = ($data['Role'] == 'Admin');
        $_SESSION['u_isAdmin'] = $isAdmin;
    }
    return $isAdmin;

}

?>