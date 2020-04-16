<?php

include "categoryUtils.inc";

// Create SQL Query
$data = load_categories();

// Close Connection from sql
$conn = null;

echo json_encode($data);

?>