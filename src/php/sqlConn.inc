<?php

$servername = "localhost";
$username = "Brad";
$password = "(B)r4dley!";
$dbname = "BradStoreDB";

// Create Connection
$dsn = "mysql:host=$servername;dbname=$dbname";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_STRINGIFY_FETCHES => false,
    PDO::ATTR_EMULATE_PREPARES => false
];
$conn = new PDO($dsn, $username, $password, $options);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>