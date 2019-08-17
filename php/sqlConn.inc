<?php

$servername = "localhost";
$username = "Brad";
$password = "(B)r4dley!";
$dbname = "BradStoreDB";

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>