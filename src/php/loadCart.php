<?php

session_start();

// Create SQL Query
$cart = array_keys($_SESSION['u_cart']);
$data = array();
// Check if cart is empty
if (count($cart)) {
    include "sqlConn.inc";
    // Create replacement string
    $ids_arr = str_repeat('?,', count($cart) - 1) . '?';
    $sql = "SELECT p.Name AS Name, i.Price AS Price, i.ItemID AS ItemID, p.Image AS Image, f.Name AS Format, c.Name AS Choice
            FROM items as i
            INNER JOIN products AS p ON i.ProductID = p.ProductID
            INNER JOIN formats AS f ON i.FormatID = f.FormatID
            LEFT JOIN choices AS c ON i.ChoiceID = c.ChoiceID
            WHERE i.ItemID in ({$ids_arr})";

    if($stmt = $conn->prepare($sql)) {

        $stmt->execute($cart);

        // Get Result
        if ($stmt->rowCount() > 0) {
            $grandtotal = 0;
            // output data of each row
            foreach ($stmt as $row) {
                $id = $row['ItemID'];
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
            $final_row['ItemID'] = Null;
            $final_row['Image'] = Null;
            $grandtotal = money_format('%.2n', $grandtotal);
            $final_row['Total'] = $grandtotal;
            $data[] = $final_row;

            // Save grand total in session
            $_SESSION['u_total'] = $grandtotal;
        }
    }

    // Close Connection
    $conn = null;
}

echo json_encode($data);

?>