<?php

include "sqlConn.inc";

$data = array();
$data['Discount'] = 0;
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET' && !empty($_GET['code']) && !empty($_GET['address']) && !empty($_GET['city']) && !empty($_GET['stateID']) && !empty($_GET['zipcode'])) {
    include "cartUtils.inc";
    $code = $_GET['code'];
    $address = $_GET['address'];
    $city = $_GET['city'];
    $stateID = $_GET['stateID'];
    $zipcode = $_GET['zipcode'];
    if (isCouponUsed($code, $address, $city, $stateID, $zipcode)) {
        $data['Message'] = "Coupon Code Already Used.";
    } else {
        $data['Discount'] = getDiscountRate($code);
        if ($data['Discount'] > 0) {
            $data['Message'] = "Coupon Code Applied.";
        } else {
            $data['Message'] = "Invalid Coupon Code.";
        }
    }

    // Close Connection from utils call
    $conn = null;
}

echo json_encode($data, JSON_NUMERIC_CHECK);

?>