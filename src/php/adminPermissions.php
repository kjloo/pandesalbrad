<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function redirect_user() {
    $data['href'] = 'index.html';
    echo json_encode($data);
    exit();
}

$isAdmin = False;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    require_once "adminUtils.inc";

    $isAdmin = query_admin();
}

// Redirect if not admin
if (!$isAdmin) {
    redirect_user();
}

?>