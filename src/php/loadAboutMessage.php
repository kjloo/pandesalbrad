<?php

$data = null;
include "sqlConn.inc";

// Create SQL Query
$sql = "SELECT Message FROM about";

if($stmt = $conn->prepare($sql)) {
    $stmt->execute();

    // output data of each row
    if ($stmt->rowCount() == 1) {
        $data = $stmt->fetch();
    } else {
        exit();
    }
}
// Close Connection
$conn = null;

echo json_encode($data);

?>