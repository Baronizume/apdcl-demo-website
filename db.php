<?php
// Database Connection

$servername = "localhost";
$username = "root";
$password = "";
$database = "apdcl_demo";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>