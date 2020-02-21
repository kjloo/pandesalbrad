<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function redirect_user() {
    header("Location: ../index.html");
    exit();
}

$isAdmin = False;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    require_once "../../php/adminUtils.inc";

    $isAdmin = query_admin();
}

// Redirect if not admin
if (!$isAdmin) {
    redirect_user();
}

header("Location: ./manage.html");

?>