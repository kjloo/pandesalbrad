<?php

session_start();
require_once "adminUtils.inc";

$data = array();
$rc = False;
if (is_user_admin()) {
    if (isset($_POST['pushCoupons']) && !empty($_POST['code']) && isset($_POST['discount'])) {

        $code = $_POST['code'];
        $discount = floatval($_POST['discount']);

        include "sqlConn.inc";

        // Create SQL Query
        $parameters = [$code, $discount, 1];
        $sql = "INSERT INTO coupons VALUES(?, ?, ?)";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($parameters);
            // Error Check?
        }
        // Close Connection
        $conn = null;
        // Success
        $rc = True;
    }
}
if ($rc) {
    $data['Message'] = "Successfully Added Coupon";
} else {
    $data['Message'] = "Failed To Add Coupon";
}
echo json_encode($data);

?>