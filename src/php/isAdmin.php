<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$rc = array();
$rc["IsAdmin"] = False;
$_SESSION['u_isAdmin'] = False;
if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
    require_once "adminUtils.inc";

    $isAdmin = query_admin();

    $rc["IsAdmin"] = $isAdmin;
}

echo json_encode($rc);

?>