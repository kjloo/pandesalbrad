<?php

include "collectionUtils.inc";

// Create SQL Query
$data = load_collections();

// Close Connection from sql
$conn = null;

echo json_encode($data);

?>