<?php
    session_start();

    $data = array();
    $data["Username"] = null;
    $data["UserID"] = null;
    $data["Cart"] = array();
    $data["Total"] = 0.00;
    $data["Darkmode"] = True;
    if (isset($_SESSION['u_id']) && isset($_SESSION['u_name'])) {
        $data["UserID"] = $_SESSION['u_id'];
        $data["Username"] = $_SESSION['u_name'];
        $data["Firstname"] = $_SESSION['u_fname'];
    }
    if (isset($_SESSION['u_cart'])) {
        $data["Cart"] = $_SESSION['u_cart'];
    } else {
        $_SESSION['u_cart'] = array();
    }
    if (isset($_SESSION['u_total'])) {
        $data["Total"] = $_SESSION['u_total'];
    }
    if (isset($_SESSION['u_darkmode'])) {
        $data["Darkmode"] = $_SESSION['u_darkmode'];
    } else {
        $_SESSION['u_darkmode'] = True;
    }
    echo json_encode($data, JSON_NUMERIC_CHECK);
?>