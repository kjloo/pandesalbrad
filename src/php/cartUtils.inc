<?php

function getCheckoutTotal() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // Create SQL Query
    $cart = array_keys($_SESSION['u_cart']);
    $data = array();
    // Check if cart is empty
    if (count($cart)) {
        include "sqlConn.inc";
        // Create replacement string
        $ids_arr = str_repeat('?,', count($cart) - 1) . '?';
        $sql = "SELECT i.ItemID, i.Price AS Price, i.FormatID, s.Cost AS Shipping, s.Bundle
                FROM items as i
                INNER JOIN formats AS f ON i.FormatID = f.FormatID
                INNER JOIN shipping AS s ON f.ShippingID = s.ShippingID
                WHERE i.ItemID in ({$ids_arr})";

        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($cart);

            // Get Result
            if ($stmt->rowCount() > 0) {
                $grandtotal = 0;
                $shippingTotal = 0;

                // keep track of shipping items
                $bundle = array();
                // output data of each row
                foreach ($stmt as $row) {
                    $id = $row['ItemID'];
                    $price = $row['Price'];
                    $bundleSize = $row['Bundle'];
                    $shipping = $row['Shipping'];

                    // need to calculate shipping costs
                    $formatID = $row['FormatID'];
                    if (!array_key_exists($formatID, $bundle)) {
                        $bundle[$formatID] = array();
                        $bundle[$formatID]['count'] = 0;
                        $bundle[$formatID]['size'] = $bundleSize;
                        $bundle[$formatID]['shipping'] = $shipping;
                    }
                    $quantity = $_SESSION['u_cart'][$id];
                    $bundle[$formatID]['count'] += $quantity;

                    $total = money_format('%.2n', ($price * $quantity));
                    $grandtotal += $total;
                }
                // calculate shipping cost against number of items
                foreach ($bundle as $key => $value) {
                    $bundleCount = $value['count'];
                    $bundleSize = $value['size'];
                    $shipping = $value['shipping'];
                    // next to check if there is a remainder
                    $nextBundle = ($bundleCount % $bundleSize) > 0 ? 1 : 0;
                    $shippingTotal += ($shipping * (intdiv($bundleCount, $bundleSize) + $nextBundle));
                }

                // Final Row
                $grandtotal = money_format('%.2n', $grandtotal);
                $shippingTotal = money_format('%.2n', $shippingTotal);
                $data['Total'] = $grandtotal;
                $data['Shipping'] = $shippingTotal;

                // Save grand total in session
                $_SESSION['u_total'] = $grandtotal;
            }
        }

        // Close Connection
        $conn = null;
    }
    return $data;
}

function getTaxRate($stateID) {
    include "sqlConn.inc";
    $sql = "SELECT Tax FROM states WHERE StateID = ?";

    $data = array();
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$stateID]);

        if ($stmt->rowCount() == 1) {
            // output data of each row
            $data = $stmt->fetch();
            return $data['Tax'];
        }
    }
    return 0;
}

function getGrandTotal($stateID) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Calculate checkout total
    $cart = getCheckoutTotal();
    $tax = getTaxRate($stateID);
    $total = round(($cart['Total'] + $cart['Shipping']) * (1 + ($tax / 100)), 2);

    // Save grand total in session
    $_SESSION['u_total'] = $total;

    return $total;
}

?>