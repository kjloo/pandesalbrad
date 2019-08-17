<?php

session_start();

if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {

    include "sqlConn.inc";

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    // Create SQL Query
    $sql = "SELECT * FROM users WHERE Username = ?";

    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        mysqli_stmt_close($stmt);

        // Get Result
        if ($result->num_rows == 1) {
            // output data of each row
            $row = $result->fetch_assoc();
            // Verify password
            $hashVerify = password_verify($password, $row['Password']);
            if ($hashVerify == true) {
                $_SESSION['u_fname'] = $row['Firstname'];
                $_SESSION['u_lname'] = $row['Lastname'];
                $_SESSION['u_id'] = $row['UserID'];
                $_SESSION['u_name'] = $row['Username'];
                $_SESSION['u_email'] = $row['Email'];
                $_SESSION['u_role'] = $row['RoleID'];

                // Query for shopping cart
                $sql = "SELECT ProductID, Quantity FROM carts WHERE UserID = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['u_id']);

                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);

                    mysqli_stmt_close($stmt);
                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            $_SESSION['u_cart'][$row['ProductID']] = $row['Quantity'];
                        }
                    }
                }

                header("Location: ../index.html?login=success");
            } else {
                header("Location: ../index.html?login=incorrect");
            }
        } else {
            header("Location: ../index.html?login=fail");
            exit();
        }
    }

} else {
    header("Location: ../index.html?login=error");
    exit();
}


?>