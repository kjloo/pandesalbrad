<?php
    session_start();

    $data = array();
    $data["Username"] = NULL;
    $data["Cart"] = NULL;
    if (isset($_SESSION['u_id']) && isset($_SESSION['u_name']) && isset($_SESSION['u_role'])) {
        $data["UserID"] = $_SESSION['u_id'];
        $data["Username"] = $_SESSION['u_name'];
        $data["RoleID"] = $_SESSION['u_role'];
        $data["Firstname"] = $_SESSION['u_fname'];
    }
    if (isset($_SESSION['u_cart'])) {
        $data["Cart"] = $_SESSION['u_cart'];
    }
    echo json_encode($data);
?>