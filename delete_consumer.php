<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (!isset($_GET['id'])) {
    die("Invalid Consumer ID");
}

$id = intval($_GET['id']);

// Check if consumer exists
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

if (mysqli_num_rows($result) == 0) {
    die("Consumer not found.");
}

// Delete consumer
mysqli_query($conn, "DELETE FROM users WHERE id=$id");

// Redirect back
header("Location: consumers.php");
exit();
?>