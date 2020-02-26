<?php

session_start();
require_once "adminUtils.inc";

$data = array();
$rc = False;
if (is_user_admin()) {
    if (isset($_POST['updateCoupon']) && !empty($_POST['code'])) {

        $code = $_POST['code'];

        include "sqlConn.inc";

        // Create SQL Query
        $parameters = array();
        $sqlArr = ["UPDATE coupons"];
        if (isset($_POST['discount'])) {
            $discount = $_POST['discount'];
            array_push($sqlArr, "SET Discount = ?");
            array_push($parameters, $discount);
        } else if (isset($_POST['active'])) {
            $active = intval($_POST['active'] === 'true' ? True : False);
            array_push($sqlArr, "SET Active = ?");
            array_push($parameters, $active);
        }
        array_push($sqlArr, "WHERE code = ?");
        array_push($parameters, $code);
        $sql = join(" ", $sqlArr);
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
    $data['Message'] = "Success";
} else {
    $data['Message'] = "Failure";
}
echo json_encode($data);

?>