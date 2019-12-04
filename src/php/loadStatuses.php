<?php

include "sqlConn.inc";

// Create SQL Query
$sql = "SELECT * FROM statuses";

$data = array();
if($stmt = $conn->prepare($sql)) {
    $stmt->execute();

    // output data of each row
    if ($stmt->rowCount() > 0) {
        foreach ($stmt as $row) {
            $data[] = $row;
        }
    }
}
// Close Connection
$conn = null;

echo json_encode($data);

?>