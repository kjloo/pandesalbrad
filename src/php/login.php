<?php

session_start();

if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {

    include "sqlConn.inc";
    include "cartUtils.inc";

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
                // Check if account has been activated
                if (!$row['Activated']) {
                    header("Location: ../login.html?login=fail&message=Account not yet activated. Please activate account using link provided in email.");
                    exit();
                }
                include "session.inc";
                $userID = $row['UserID'];
                $_SESSION['u_fname'] = $row['Firstname'];
                $_SESSION['u_lname'] = $row['Lastname'];
                $_SESSION['u_id'] = $userID;
                $_SESSION['u_name'] = $row['Username'];
                $_SESSION['u_email'] = $row['Email'];

                $cart = getCart();
                // Add current cart to database
                $isCart = isset($cart) && !empty($cart);
                if ($isCart) {
                    $ids_arr = str_repeat('(?,?,?),', count($cart) - 1) . '(?,?,?)';
                    $itemsArr = array();
                    foreach ($cart as $key => $value) {
                        $itemID = $key;
                        $quantity = $value;
                        array_push($itemsArr, $userID, $itemID, $quantity);
                    }
                }

                // Query for shopping cart
                $sql = "SELECT c.ItemID, Quantity FROM carts AS c
                        INNER JOIN items AS i ON c.ItemID = i.ItemID
                        INNER JOIN products as p ON i.ProductID = p.ProductID
                        WHERE UserID = ?
                        AND p.Available";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$_SESSION['u_id']]);

                    foreach($stmt as $row) {
                        $_SESSION['u_cart'][$row['ItemID']] = $row['Quantity'];
                    }
                }

                if ($isCart) {
                    // Now insert
                    $sql = "INSERT INTO carts(UserID, ItemID, Quantity) VALUES {$ids_arr}";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->execute($itemsArr);
                        // Error Check?
                    }
                }

                regenerate_session_id();

                session_validate();

                header("Location: ../index.html?login=success");
            } else {
                header("Location: ../login.html?login=fail&message=Incorrect Username or Password.");
            }
        } else {
            header("Location: ../login.html?login=fail&message=Incorrect Username Or Password.");
        }
    }
    // Close Connection
    $conn = null;

} else {
    header("Location: ../login.html?login=fail&message=Unexpected Error.");
    exit();
}


?>