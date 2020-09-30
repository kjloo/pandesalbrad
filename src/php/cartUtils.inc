<?php

function getCart() {
    // Get cart and update user session and database

    // Grab session cart
    $cart = array_keys($_SESSION['u_cart']);

    // Verify all products in cart are available
    include "sqlConn.inc";
    if (count($cart)) {
        // Create replacement string
        $ids_arr = str_repeat('?,', count($cart) - 1) . '?';
        $sql = "SELECT i.ItemID
                FROM items as i
                INNER JOIN products AS p ON i.ProductID = p.ProductID
                WHERE i.ItemID in ({$ids_arr})
                AND p.Available";

        $data = array();
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($cart);

            foreach($stmt as $row) {
                $data[] = $row["ItemID"];
            }
        }

        // Check if cart item is in data
        if (!empty($data)) {
            $_SESSION['u_cart'] = array_filter($_SESSION['u_cart'], function($item) use($data) {
                return in_array($item, $data);
            }, ARRAY_FILTER_USE_KEY);
        }
    }

    // Update database if user
    if (isset($_SESSION['u_id'])) {
        // Query for shopping cart
        $sql = "DELETE c FROM carts AS c
                INNER JOIN items AS i ON c.ItemID = i.ItemID
                INNER JOIN products AS p on i.ProductID = p.ProductID
                WHERE UserID = ?
                AND p.Available = 0";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute([$_SESSION['u_id']]);
            // Error Check?
        }
    }
    $conn = null;

    return $_SESSION['u_cart'];
}

function getCheckoutTotal() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // Create SQL Query
    $cart = array_keys(getCart());
    $data = array();
    // Check if cart is empty
    if (count($cart)) {
        include "sqlConn.inc";
        // Create replacement string
        $ids_arr = str_repeat('?,', count($cart) - 1) . '?';
        $sql = "SELECT i.ItemID, i.Price AS Price, i.FormatID, f.Freebie AS Freebie, s.Cost AS Shipping, s.Bundle
                FROM items as i
                INNER JOIN formats AS f ON i.FormatID = f.FormatID
                INNER JOIN shipping AS s ON f.ShippingID = s.ShippingID
                INNER JOIN products AS p ON i.ProductID = p.ProductID
                WHERE i.ItemID in ({$ids_arr})
                AND p.Available";

        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($cart);

            // Get Result
            if ($stmt->rowCount() > 0) {
                $grandtotal = 0;
                $shippingTotal = 0;
                $freebieDiscount = 0;

                // keep track of shipping items
                $bundle = array();
                // output data of each row
                foreach ($stmt as $row) {
                    $id = $row['ItemID'];
                    $price = $row['Price'];
                    $bundleSize = $row['Bundle'];
                    $freebie = $row['Freebie'];
                    $shipping = $row['Shipping'];

                    // need to calculate shipping costs
                    $formatID = $row['FormatID'];
                    if (!array_key_exists($formatID, $bundle)) {
                        $bundle[$formatID] = array();
                        $bundle[$formatID]['count'] = 0;
                        $bundle[$formatID]['shippingSize'] = $bundleSize;
                        $bundle[$formatID]['freebieSize'] = $freebie;
                        $bundle[$formatID]['price'] = array();
                        $bundle[$formatID]['shipping'] = $shipping;
                    }

                    $quantity = $_SESSION['u_cart'][$id];
                    for ($i = 0; $i < $quantity; $i++) {
                        array_push($bundle[$formatID]['price'], $price);
                    }
                    $bundle[$formatID]['count'] += $quantity;

                    $total = money_format('%.2n', ($price * $quantity));
                    $grandtotal += $total;
                }
                // calculate shipping cost against number of items
                foreach ($bundle as $key => $value) {
                    $bundleCount = $value['count'];
                    $bundleSize = $value['shippingSize'];
                    $freebieSize = $value['freebieSize'];
                    // free item is taken from the lowest price first
                    $priceList = $value['price'];
                    sort($priceList);
                    $shipping = $value['shipping'];

                    // next to check if there is a remainder
                    $nextBundle = ($bundleCount % $bundleSize) > 0 ? 1 : 0;
                    $shippingTotal += ($shipping * (intdiv($bundleCount, $bundleSize) + $nextBundle));
                    if (!empty($freebieSize)) {  
                        // calculate freebie discount
                        // logic for buy X get N (BOGO)
                        $freeItems = intdiv($bundleCount, $freebieSize);
                        for ($i = 0; $i < $freeItems; $i++) {
                            $freebieDiscount += array_shift($priceList);
                        }
                    }
                }

                // Final Row
                $grandtotal = money_format('%.2n', $grandtotal);
                $shippingTotal = money_format('%.2n', $shippingTotal);
                $freebieDiscount = money_format('%.2n', $freebieDiscount);
                $data['Total'] = $grandtotal;
                $data['Shipping'] = $shippingTotal;
                // Call it Bulk in the UI
                $data['Bulk'] = $freebieDiscount;

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

function isCouponUsed($code, $address, $city, $stateID, $zipcode) {
    if (!empty($code)) {
        include "sqlConn.inc";
        $sql = "SELECT OrderID 
                FROM orders 
                WHERE Coupon = ?
                AND Address = ?
                AND City = ?
                AND StateID = ?
                AND Zipcode = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$code, $address, $city, $stateID, $zipcode]);

            // output data of each row
            if ($stmt->rowCount() > 0) {
                return True;
            }
        }
    }
    return False;
}

function getDiscountRate($code) {
    if (!empty($code)) {
        include "sqlConn.inc";
        $sql = "SELECT Discount FROM coupons WHERE Code = ? and Active = TRUE";

        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$code]);

            // output data of each row
            if ($stmt->rowCount() == 1) {
                $data = $stmt->fetch();
                return $data['Discount'];
            }
        }
    }
    return 0;
}

function getGrandTotal($stateID, $code) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Calculate checkout total
    $discountRate = getDiscountRate($code);
    $cart = getCheckoutTotal();
    // Make bulk discount calculation here
    $runningTotal = $cart['Total'] - $cart['Bulk'];
    $shipping = $cart['Shipping'];
    $discount = round($runningTotal * ($discountRate / 100), 2);
    $tax = getTaxRate($stateID);
    $total = round(($runningTotal - $discount + $shipping) * (1 + ($tax / 100)), 2);

    // Save grand total in session
    $_SESSION['u_total'] = $total;

    return $total;
}

?>