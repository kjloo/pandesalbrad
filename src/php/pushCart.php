<?php

session_start();

if (isset($_POST['pushCart']) && !empty($_POST['itemID']) && !empty($_POST['quantity'])) {
    // Update cookie
    $itemID = intval($_POST['itemID']);
    $quantity = intval($_POST['quantity']);
    if (is_int($itemID) && is_int($quantity)) {
        if (array_key_exists($itemID, $_SESSION['u_cart'])) {
            $_SESSION['u_cart'][$itemID] += $quantity;
        } else {
            $_SESSION['u_cart'][$itemID] = $quantity;
        }
    } else {
        exit();
    }

    if (isset($_SESSION['u_id'])) {
        include "sqlConn.inc";
        $userID = $_SESSION['u_id'];
        $itemID = $_POST['itemID'];
	    // Create SQL Query
	    // First see if item already in cart
    	$sql = "SELECT Quantity FROM carts WHERE UserID = ? AND ItemID = ?";
    	if($stmt = $conn->prepare($sql)) {
    	    $stmt->execute([$userID, $itemID]);

            $exists = ($stmt->rowCount() > 0);
            if ($exists) {
                // Get current quantity
                $row = $stmt->fetch();
                $quantity += $row['Quantity'];
            }
        }

    	// If row already exists, create UPDATE. Else create INSERT
    	if ($exists) {
    		$sql = "UPDATE carts SET Quantity = ? WHERE UserID = ? AND ItemID = ?";
    	} else {
            $sql = "INSERT INTO carts (Quantity, UserID, ItemID) VALUES (?, ?, ?)";
        }
    	
    	if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$quantity, $userID, $itemID]);
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