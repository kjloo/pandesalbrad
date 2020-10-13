<?php

include "sqlConn.inc";

// Create SQL Query
$sql = "SELECT * FROM formats AS f
        LEFT JOIN backgrounds AS b on f.backgroundID = b.backgroundID";
$result = $conn->query($sql);

$data = array();

// Get Result
// output data of each row
while ($row = $result->fetch()) {
    $data[] = $row;
}

// Close Connection
$conn = null;

echo json_encode($data, JSON_NUMERIC_CHECK);

?>