<?php

session_start();

if (isset($_POST['pushCart']) && !empty($_POST['productID']) && !empty($_POST['quantity'])) {
    // Update cookie
    $productID = intval($_POST['productID']);
    $quantity = intval($_POST['quantity']);
    if (is_int($productID) && is_int($quantity)) {
        if (array_key_exists($productID, $_SESSION['u_cart'])) {
            $_SESSION['u_cart'][$productID] += $quantity;
        } else {
            $_SESSION['u_cart'][$productID] = $quantity;
        }
    } else {
        exit();
    }

    if (isset($_SESSION['u_id'])) {
        include "sqlConn.inc";
        $userID = $_SESSION['u_id'];
        $productID = $_POST['productID'];
	    // Create SQL Query
	    // First see if item already in cart
    	$sql = "SELECT Quantity FROM carts WHERE UserID = ? AND ProductID = ?";
    	if($stmt = $conn->prepare($sql)) {
    	    $stmt->execute([$userID, $productID]);

            $exists = ($stmt->rowCount() > 0);
            if ($exists) {
                // Get current quantity
                $row = $stmt->fetch();
                $quantity += $row['Quantity'];
            }
        }

    	// If row already exists, create UPDATE. Else create INSERT
    	if ($exists) {
    		$sql = "UPDATE carts SET Quantity = ? WHERE UserID = ? AND ProductID = ?";
    	} else {
            $sql = "INSERT INTO carts (Quantity, UserID, ProductID) VALUES (?, ?, ?)";
        }
    	
    	if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$quantity, $userID, $productID]);
            // Error check?
    	}
    	// Successfully inserted into database
        // Close connection
        $conn = null;
    }
} else {
    exit();
}

?>