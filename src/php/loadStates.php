<?php

include "sqlConn.inc";

// Create SQL Query
$sql = "SELECT * FROM states ORDER BY Abbreviation";
$result = $conn->query($sql);

$data = array();

// Get Result
// output data of each row
while ($row = $result->fetch()) {
    $data[] = $row;
}

// Close Connection
$conn = null;

echo json_encode($data);

?>