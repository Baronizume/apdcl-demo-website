<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    mysqli_query($conn, "DELETE FROM complaints WHERE id=$id");
}

header("Location: complaints.php");
exit();
?>