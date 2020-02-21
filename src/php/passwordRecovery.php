<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['recovery']) && !empty($_POST['email'])) {
    $email = $_POST['email'];
    include "sqlConn.inc";
    // Create SQL Query
    $sql = "SELECT Activated FROM users WHERE Email = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$email]);
        // Get Result
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            $activated = $row['Activated'];
            if ($activated) {
                $random_hash_token = bin2hex(openssl_random_pseudo_bytes(16));
                // Send email recovery
                include "email.inc";
                $subject = "PandesalBrad Forgot Password";
                $msg = "Click the link below to reset your password:\r\n";
                $msg .= "http://chingloo.zapto.org:9090/reset.html?token=" . $random_hash_token . "\r\n";
                $email = $_POST['email'];
                $errors = send_email($subject, $msg, $email);
                if(empty($errors) != true) {
                    header("Location: ../recovery.html?status=fail&message=" . join(",", $errors));
                    exit();
                } else {
                    // Create SQL Query
                    $sql = "UPDATE users SET Token = ? WHERE email = ?";

                    if($stmt = $conn->prepare($sql)) {
                        $stmt->execute([$random_hash_token, $email]);
                        // Error check?
                    }
                }
            } else {
                header("Location: ../recovery.html?status=fail&message=Account Not Activated");
                exit();
            }
        } else {
            // Email not found
            header("Location: ../recovery.html?status=fail&message=Email Not Registered");
            exit();
        }
    }

    header("Location: ../recovery.html?status=success&message=Recovery Link Sent");
}

?>