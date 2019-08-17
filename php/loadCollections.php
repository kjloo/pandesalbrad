<?php

include "sqlConn.inc";

// Create SQL Query
$sql = "SELECT * FROM collections";
$result = $conn->query($sql);

$data = array();

// Get Result
if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

?>