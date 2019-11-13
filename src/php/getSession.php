<?php
    session_start();

    $data = array();
    $data["Username"] = null;
    $data["UserID"] = null;
    $data["Cart"] = array();
    $data["Total"] = 0.00;
    if (isset($_SESSION['u_id']) && isset($_SESSION['u_name'])) {
        $data["UserID"] = $_SESSION['u_id'];
        $data["Username"] = $_SESSION['u_name'];
        $data["Firstname"] = $_SESSION['u_fname'];
    }
    if (isset($_SESSION['u_cart'])) {
        $data["Cart"] = $_SESSION['u_cart'];
    }
    if (isset($_SESSION['u_total'])) {
        $data["Total"] = $_SESSION['u_total'];
    }
    echo json_encode($data);
?>