<?php

include "paypal.inc";

session_start();

if (isset($_POST['processOrder']) && !empty($_POST['orderID'])) {
    $data = array();
    $data['Processed'] = False;

    $orderID = $_POST['orderID'];
    $total = $_SESSION['u_total'];
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
        if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id']) && !empty($_POST['addressID'])) {
            $userID = $_SESSION['u_id'];
            $addressID = $_POST['addressID'];
            $sql = "INSERT INTO orders(UserID, OrderID, StatusID, OrderDate, AddressID, Total) VALUES(?, ?, (SELECT StatusID FROM statuses WHERE Status = 'ordered'), CURDATE(), ?, ?)";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$userID, $orderID, $addressID, $total]);
                // Error Checking?
            }
        } else if (!empty($_POST['address']) && !empty($_POST['city']) && !empty($_POST['state']) && !empty($_POST['zipcode'])) {
            $address = $_POST['address'];
            $city = $_POST['city'];
            $state = $_POST['state'];
            $zipcode = $_POST['zipcode'];
            $sql = "INSERT INTO orders(OrderID, StatusID, OrderDate, Total, Address, City, State, Zipcode) VALUES(?, (SELECT StatusID FROM statuses WHERE Status = 'ordered'), CURDATE(), ?, ?, ?, ?, ?)";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$orderID, $total, $address, $city, $state, $zipcode]);
                // Error Checking?
            }
        }

        // Successfully inserted into database
        // Update data base to reflect order
        // Create an array where a table row is every three elements
        $cart = $_SESSION['u_cart'];
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
    }
    // Return if process succeeded
    echo json_encode($data);

    // Display Receipt Page
    // header("Location: ../receipt.html");
} else {
    exit();
}

?>