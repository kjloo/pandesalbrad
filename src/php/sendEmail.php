<?php

session_start();

if (isset($_POST['send']) && !empty($_POST['subject']) && !empty($_POST['email']) && !empty($_POST['message'])) {

    include "email.inc";
    $subject = $_POST['subject'];
    $msg = $_POST['message'];
    $email = $_POST['email'];
    if (!send_email($subject, $msg, $email)) {
        // Email could not send
        header("Location: ../email.html?message=Error sending email");
        exit();
    }
    header("Location: ../email.html?message=Email Sent");
}

?>