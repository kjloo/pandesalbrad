<?php

include "sqlConn.inc";
include "slideUtils.inc";

$data = load_slides();
// Close Connection
$conn = null;

echo json_encode($data);

?>