<?php

require __DIR__ . '/vendor/autoload.php';
include "paypal.inc";

use PayPalCheckoutSdk\Orders\OrdersGetRequest;

session_start();

if (isset($_POST['processOrder']) && !empty($_POST['orderID']) && !empty($_POST['addressID'])) {
    $orderID = $_POST['orderID'];
    $addressID = $_POST['addressID'];
    $total = $_SESSION['u_total'];
    // Call PayPal to get transaction details
    $client = PayPalClient::client();
    $response = $client->execute(new OrdersGetRequest($orderID));

    // Compare total
    if ($total != $response->result->purchase_units[0]->amount->value) {
        echo "Error";
        exit();
    } else {
        if (isset($_SESSION['u_id'])) {
            include "sqlConn.inc";
            $userID = $_SESSION['u_id'];
            // Create SQL Query
            // First see if item already in cart
            $sql = "INSERT INTO orders(UserID, OrderID, StatusID, OrderDate, AddressID, Total) VALUES(?, ?, 0, CURDATE(), ?, ?)";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$userID, $orderID, $addressID, $total]);
                // Error Checking?
            }

            // Successfully inserted into database
            // Update data base to reflect order
            $sql = "INSERT INTO packages(OrderID, ProductID, Quantity) SELECT '{$orderID}', ProductID, Quantity FROM carts WHERE UserID = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$userID]);
                // Error Checking?
            }
            // Empty Cart
            /*$sql = "DELETE FROM carts WHERE UserID = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$userID]);
                // Error Checking?
            }*/

            // Close connection
            $conn = null;
        }
    }
    echo json_encode($response->result);

    // Display Receipt Page
    //aheader("Location: ../receipt.html");
} else {
    exit();
}

?>