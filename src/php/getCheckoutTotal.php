<?php

session_start();
include "cartUtils.inc";

$data = getCheckoutTotal();

echo json_encode($data, JSON_NUMERIC_CHECK);

?>