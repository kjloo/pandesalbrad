<?php

include "paypal.inc";

function error_exit($message) {
    header("Location: ../checkout.html?checkout=fail&message=" . $message);
    exit();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['processOrder']) && !empty($_POST['orderID']) && !empty($_POST['address']) && !empty($_POST['city']) && !empty($_POST['stateID']) && !empty($_POST['zipcode']) && !empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['email'])) {
    include "cartUtils.inc";
    $data = array();
    $data['Processed'] = False;

    $orderID = $_POST['orderID'];

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $stateID = $_POST['stateID'];
    $zipcode = $_POST['zipcode'];

    // Get coupon code. Might be empty. If empty leave as null
    $coupon = !empty($_POST['coupon']) ? $_POST['coupon'] : null;
    // Validate coupon not used
    $coupon = !isCouponUsed($coupon, $address, $city, $stateID, $zipcode) ? $coupon : null;

    $total = getGrandTotal($stateID, $coupon);

    // Call PayPal to get transaction details
    $client = PayPalClient::client();
    $response = PayPalClient::order($client, $orderID);

    // Compare total
    if ($total != $response->result->purchase_units[0]->amount->value) {
        $data['Processed'] = False;
    } else {
        include "sqlConn.inc";
        // Create SQL Query
        // Check if user has account
        $isUserAccount = isset($_SESSION['u_id']) && !empty($_SESSION['u_id']);

        $parameters = array();
        // Begin Transcation
        $conn->beginTransaction();
        if ($isUserAccount) {
            $userID = $_SESSION['u_id'];
            if (empty($_POST['addressID'])) {
                $sql = "INSERT INTO addresses (Address, City, StateID, Zipcode, UserID) VALUES (?, ?, ?, ?, ?)";
                $input = [$address, $city, $stateID, $zipcode, $userID];
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->execute($input);
                    // Error Checking?
                    $stmt->closeCursor();
                }
            }
        }
        $userID = $isUserAccount ? $_SESSION['u_id'] : NULL;
        # Process Order into Table
        $sql = "INSERT INTO orders(OrderID, StatusID, OrderDate, Total, UserID, Firstname, Lastname, Email, Address, City, StateID, Zipcode, Coupon) VALUES(?, (SELECT StatusID FROM statuses WHERE Status = 'Ordered'), CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        array_push($parameters, $orderID, $total, $userID, $fname, $lname, $email, $address, $city, $stateID, $zipcode, $coupon);

        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute($parameters);
            // Error Checking?
            $stmt->closeCursor();
        }
        $conn->commit();

        // Successfully inserted into database
        // Update database to reflect order
        // Create an array where a table row is every three elements
        $cart = getCart();
        $ids_arr = str_repeat('(?,?,?),', count($cart) - 1) . '(?,?,?)';
        $productsArr = array();
        foreach ($cart as $key => $value) {
            $itemID = $key;
            $quantity = $value;
            array_push($productsArr, $orderID, $itemID, $quantity);
        }
        $sql = "INSERT INTO packages(OrderID, ItemID, Quantity) VALUES {$ids_arr}";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($productsArr);
            // Error Checking?
        }
        // Send confirmation email
        include "email.inc";
        $subject = "PandesalBrad Order Confirmation";
        $msg = "Thank you for your order.\r\n";
        $msg .= "Order Summary:\r\n";
        $msg .= "Total: " . $total . "\r\n";
        $msg .= "Order ID: " . $orderID . "\r\n";
        $errors = send_email($subject, $msg, $email);
        if(empty($errors) != true) {
            error_exit(join(",", $errors));
        }
        // Empty Cart
        $_SESSION['u_cart'] = array();
        // Delete from database if user session
        if (isset($_SESSION['u_id'])) {
            $userID = $_SESSION['u_id'];
            $sql = "DELETE FROM carts WHERE UserID = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$userID]);
                // Error Checking?
            }
        }

        // Close connection
        $conn = null;

        // Processing of order succeeded
        $data['Processed'] = True;
        $data["OrderID"] = $orderID;
    }
    // Return if process succeeded
    echo json_encode($data);

    // Display Receipt Page
    // header("Location: ../receipt.html");
} else {
    error_exit("Invalid Information!");
}

?>