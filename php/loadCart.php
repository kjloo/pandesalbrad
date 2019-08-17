<?php

session_start();

include "sqlConn.inc";

// Create SQL Query
//$sql = "SELECT p.Name, p.ProductID, SUM(p.Price) AS Price, SUM(c.Quantity) AS Quantity FROM carts c INNER JOIN products p ON c.ProductID = p.ProductID WHERE c.UserID = ? GROUP BY p.Name WITH ROLLUP";
// 
$cart = array_keys($_SESSION['u_cart']);
// Create replacement string
$ids_arr = str_repeat('?,', count($cart) - 1) . '?';
$data_type = str_repeat("i", count($cart));
$sql = "SELECT Name, Price, ProductID FROM products WHERE ProductID in ({$ids_arr})";
$data = array();

if($stmt = mysqli_prepare($conn, $sql)) {

   call_user_func_array(mysqli_stmt_bind_param, array_merge(array($stmt, $data_type), $cart));

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);

    // Get Result
    if ($result->num_rows > 0) {
        $grandtotal = 0;
        // output data of each row
        while ($row = $result->fetch_assoc()) {
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
        $final_row['Total'] = money_format('%.2n', $grandtotal);
        $data[] = $final_row;
    }
}

echo json_encode($data);

?>