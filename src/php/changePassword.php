<?php

session_start();

if (isset($_POST['save']) && !empty($_POST['opassword']) && !empty($_POST['npassword']) && !empty($_POST['password2'])) {
    if (isset($_SESSION['u_id'])) {
        $opassword = $_POST['opassword'];
        $npassword = $_POST['npassword'];
        $password2 = $_POST['password2'];

        $user_id = $_SESSION['u_id'];

        // Verify password meets criteria
        include "passwordUtils.inc";
        $errors = validate_password($npassword, $password2);
        if (!empty($errors)) {
            header("Location: ../password.html?message=" . join(",", $errors));
            exit();
        }

        include "sqlConn.inc";

        // Check if password is correct
        $sql = "SELECT Password FROM users WHERE UserID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$user_id]);

            if ($stmt->rowCount() == 1) {
                // output data of each row
                $row = $stmt->fetch();
                $hashVerify = password_verify($opassword, $row['Password']);
                if ($hashVerify == true) {
                    // Change password
                    $sql = "UPDATE users SET Password = ? WHERE UserID = ?";

                    if ($stmt = $conn->prepare($sql)) {
                        $hash = password_hash($npassword, PASSWORD_BCRYPT);
                        $stmt->execute([$hash, $user_id]);
                        // Error check?

                        header("Location: ../index.html?password=success");
                    }
                } else {
                    header("Location: ../password.html?message=Incorrect+Password");
                }
            } else {
                header("Location: ../index.html?user=invalid");
            }
        }
        // Close connection
        $conn = null;
    }
} else {
    header("Location: ../index.html?password=error");
    exit();
}


?>