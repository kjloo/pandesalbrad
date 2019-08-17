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

    if (!empty($_POST['userID'])) {
        include "sqlConn.inc";
        $userID = mysqli_real_escape_string($conn, $_POST['userID']);
        $productID = mysqli_real_escape_string($conn, $_POST['productID']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
	    // Create SQL Query
	    // First see if item already in cart
    	$sql = "SELECT Quantity FROM carts WHERE UserID = ? AND ProductID = ?";
    	if($stmt = mysqli_prepare($conn, $sql)) {
    	    mysqli_stmt_bind_param($stmt, "ii", $userID, $productID);

    		mysqli_stmt_execute($stmt);

    		$result = mysqli_stmt_get_result($stmt);
    		
    		mysqli_stmt_close($stmt);

            $exists = ($result->num_rows > 0);
        }

    	// If row already exists, create UPDATE. Else create INSERT
    	if ($exists) {
    		$sql = "UPDATE carts SET Quantity = ? WHERE UserID = ? AND ProductID = ?";
    	} else {
            $sql = "INSERT INTO carts (Quantity, UserID, ProductID) VALUES (?, ?, ?)";
        }
    	
    	if($stmt = mysqli_prepare($conn, $sql)) {
    		mysqli_stmt_bind_param($stmt, "iii",$quantity, $userID, $productID);

    		mysqli_stmt_execute($stmt);

    		$result = mysqli_stmt_get_result($stmt);
    		
    		mysqli_stmt_close($stmt);
    	}
    	// Successfully inserted into database
    }
} else {
    exit();
}

?>