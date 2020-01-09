<?php

if (isset($_POST['reset']) && !empty($_POST['email']) && !empty($_POST['token']) && !empty($_POST['password']) && !empty($_POST['password2'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Verify password meets criteria
    include "passwordUtils.inc";
    $errors = validate_password($password, $password2);
    if (!empty($errors)) {
        header("Location: ../reset.html?status=fail&message=" . join(",", $errors));
        exit();
    }

    include "sqlConn.inc";

    // Check if token is correct
    $sql = "SELECT UserID FROM users WHERE Email = ? AND Token = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$email, $token]);

        if ($stmt->rowCount() == 1) {
            // output data of each row
            $row = $stmt->fetch();
            // Change password
            $sql = "UPDATE users SET Password = ? WHERE UserID = ?";

            if ($stmt = $conn->prepare($sql)) {
                $user_id = $row['UserID'];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt->execute([$hash, $user_id]);
                // Error check?
                header("Location: ../index.html?password=success");

                // Must now make token null
                $sql = "UPDATE users SET Token = NULL WHERE Token = ? AND UserID = ?";
                if($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$token, $user_id]);
                    // Error Check?
                }
            }
        } else {
            // Invalid reset password request
            header("Location: ../reset.html?status=fail&message=Invalid Reset Password Request");
        }

    }
    // Close connection
    $conn = null;
} else {
    header("Location: ../reset.html?status=fail&message=Invalid Inputs");
    exit();
}


?>