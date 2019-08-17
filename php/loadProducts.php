<?php

include "sqlConn.inc";

$collection = mysqli_real_escape_string($conn, $_GET["collection"]);

// Create SQL Query
if (!empty($collection)) {
    $sql = "SELECT * FROM products WHERE CollectionID = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
	    mysqli_stmt_bind_param($stmt, "s", $collection);

	    mysqli_stmt_execute($stmt);

	    $result = mysqli_stmt_get_result($stmt);
	
	    mysqli_stmt_close($stmt);
	}
} else {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
}

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