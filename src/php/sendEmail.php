<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['send']) && !empty($_POST['subject']) && !empty($_POST['email']) && !empty($_POST['message'])) {

    include "email.inc";
    $subject = $_POST['subject'];
    $msg = $_POST['message'];
    $email = $_POST['email'];
    $errors = send_email($subject, $msg, $email);
    if(empty($errors) != true) {
        header("Location: ../email.html?message=" . join(",", $errors));
        exit();
    }
    header("Location: ../email.html?message=Email Sent");
}

?>