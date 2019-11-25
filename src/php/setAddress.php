<?php

session_start();

$data = array();

if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id']) && isset($_POST['setAddress']) && !empty($_POST['address']) && !empty($_POST['city']) && !empty($_POST['state']) && !empty($_POST['zipcode'])) {

    include "sqlConn.inc";

    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $addressID = $_POST['addressID'];
    $userID = $_SESSION['u_id'];

    // Create update statement
    if (isset($addressID) && $addressID != NULL) {
        $sql = "UPDATE addresses SET Address = ?, City = ?, State = ?, Zipcode = ? WHERE AddressID = ? AND UserID = ?";
        $input = [$address, $city, $state, $zipcode, $addressID, $userID];
    } else {
        $sql = "INSERT INTO addresses (Address, City, State, Zipcode, UserID) VALUES (?, ?, ?, ?, ?)";
        $input = [$address, $city, $state, $zipcode, $userID];
    }

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute($input);
        // Error Check?
    }
    // Close connection
    $conn = null;

    $data['href'] = 'index.html';

} else {
    $data['Reload'] = 'index.html?Address=Fail';
}

echo json_encode($data);


?>