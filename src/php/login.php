<?php

session_start();

if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {

    include "sqlConn.inc";

    $username = $_POST['username'];
    $password = $_POST['password'];
    // Create SQL Query
    $sql = "SELECT * FROM users WHERE Username = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$username]);

        // Get Result
        if ($stmt->rowCount() == 1) {
            // output data of each row
            $row = $stmt->fetch();
            // Verify password
            $hashVerify = password_verify($password, $row['Password']);
            if ($hashVerify == true) {
                include "session.inc";
                $userID = $row['UserID'];
                $_SESSION['u_fname'] = $row['Firstname'];
                $_SESSION['u_lname'] = $row['Lastname'];
                $_SESSION['u_id'] = $userID;
                $_SESSION['u_name'] = $row['Username'];
                $_SESSION['u_email'] = $row['Email'];
                $_SESSION['u_role'] = $row['RoleID'];

                // Add current cart to database
                $isCart = isset($_SESSION['u_cart']) && !empty($_SESSION['u_cart']);
                if ($isCart) {
                    $cart = $_SESSION['u_cart'];
                    $ids_arr = str_repeat('(?,?,?),', count($cart) - 1) . '(?,?,?)';
                    $productsArr = array();
                    foreach ($cart as $key => $value) {
                        $productID = $key;
                        $quantity = $value;
                        array_push($productsArr, $userID, $productID, $quantity);
                    }
                }

                // Query for shopping cart
                $sql = "SELECT ProductID, Quantity FROM carts WHERE UserID = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$_SESSION['u_id']]);

                    foreach($stmt as $row) {
                        $_SESSION['u_cart'][$row['ProductID']] = $row['Quantity'];
                    }
                }

                if ($isCart) {
                    // Now insert
                    $sql = "INSERT INTO carts(UserID, ProductID, Quantity) VALUES {$ids_arr}";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->execute($productsArr);
                        // Error Check?
                    }
                }

                regenerate_session_id();

                session_validate();

                header("Location: ../index.html?login=success");
            } else {
                header("Location: ../index.html?login=incorrect");
            }
        } else {
            header("Location: ../index.html?login=fail");
        }
    }
    // Close Connection
    $conn = null;

} else {
    header("Location: ../index.html?login=error");
    exit();
}


?>