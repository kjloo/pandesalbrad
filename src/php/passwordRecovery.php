<?php

session_start();

if (isset($_POST['recovery']) && !empty($_POST['email'])) {

    $random_hash_token = bin2hex(openssl_random_pseudo_bytes(16));
    // Send email recovery
    include "email.inc";
    $subject = "PandesalBrad Forgot Password";
    $msg = "Click the link below to reset your password:\r\n";
    $msg .= "http://chingloo.zapto.org:9090/reset.html?token=" . $random_hash_token . "\r\n";
    $email = $_POST['email'];
    if (!send_email($subject, $msg, $email)) {
        // Email could not send
        header("Location: ../recovery.html?message=Error sending email");
        exit();
    } else {
        include "sqlConn.inc";
        // Create SQL Query
        $sql = "UPDATE users SET Token = ? WHERE email = ?";

        if($stmt = $conn->prepare($sql)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt->execute([$random_hash_token, $email]);
            // Error check?
        }
    }
    header("Location: ../recovery.html?message=Recovery Link Sent");
}

?>