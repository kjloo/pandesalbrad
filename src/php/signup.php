<?php

session_start();

if (isset($_POST['signup']) && !empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['password2']) && !empty($_POST['email'])) {

    include "sqlConn.inc";

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $email = $_POST['email'];
    $role = "Customer";

    include "passwordUtils.inc";
    // Compare passwords and return error if they do not match
    $errors = validate_password($password, $password2);
    if (empty($errors) != true) {
        header("Location: ../signup.html?signup=fail&message=" . join(",", $errors));
        exit();
    }

    // Check if username is taken
    $sql = "SELECT Username FROM users WHERE Username = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$username]);
        // Get Result
        if ($stmt->rowCount() > 0) {
            // Username already exists, return error
            header("Location: ../signup.html?signup=fail&message=Username is taken");
            exit();
        }
    }

    // Check if email is taken
    $sql = "SELECT Email FROM users WHERE Email = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$email]);
        // Get Result
        if ($stmt->rowCount() > 0) {
            // Username already exists, return error
            header("Location: ../signup.html?signup=fail&message=Email is already in use");
            exit();
        } else {
            // Create validation token
            $random_hash_token = bin2hex(openssl_random_pseudo_bytes(16));
            // Send email validation
            include "email.inc";
            $subject = "PandesalBrad Email Validation";
            $msg = "Click the link below to complete account creation:\r\n";
            $msg .= "https://pandesalbradart.com/activate.html?token=" . $random_hash_token . "\r\n";
            $errors = send_email($subject, $msg, $email);
            if(empty($errors) != true) {
                header("Location: ../signup.html?signup=fail&message=" . join(",", $errors));
                exit();
            } else {
                // Create SQL Query
                $sql = "INSERT INTO users (Firstname, Lastname, Username, Password, Email, SignupDate, Activated, Token, RoleID) SELECT ?, ?, ?, ?, ?, CURDATE(), FALSE, ?, RoleID FROM roles WHERE Role = '$role'";

                if($stmt = $conn->prepare($sql)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt->execute([$fname, $lname, $username, $hash, $email, $random_hash_token]);
                    // Error check?
                }
                header("Location: ../index.html?signup=success");
            }
        }
    }

    // Close connection
    $conn = null;

} else {
	header("Location: ../signup.html?signup=error&message=Not All Fields Filled In");
	exit();
}


?>