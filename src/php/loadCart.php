<?php

session_start();

include "sqlConn.inc";

// Create SQL Query
$cart = array_keys($_SESSION['u_cart']);
// Create replacement string
$ids_arr = str_repeat('?,', count($cart) - 1) . '?';
$sql = "SELECT Name, Price, ProductID FROM products WHERE ProductID in ({$ids_arr})";
$data = array();

if($stmt = $conn->prepare($sql)) {

    $stmt->execute($cart);

    // Get Result
    if ($stmt->rowCount() > 0) {
        $grandtotal = 0;
        // output data of each row
        foreach ($stmt as $row) {
            $id = $row['ProductID'];
            $price = $row['Price'];
            $quantity = $_SESSION['u_cart'][$id];
            $total = money_format('%.2n', ($price * $quantity));
            $row['Quantity'] = $quantity;
            $row['Total'] = $total;
            $data[] = $row;
            $grandtotal += $total;
        }
        // Final Row
        $final_row = array();
        $final_row['Name'] = "Sub Total";
        $final_row['ProductID'] = Null;
        $grandtotal = money_format('%.2n', $grandtotal);
        $final_row['Total'] = $grandtotal;
        $data[] = $final_row;

        // Save grand total in session
        $_SESSION['u_total'] = $grandtotal;
    }
}

// Close Connection
$conn = null;

echo json_encode($data);

?>