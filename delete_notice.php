<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

if(isset($_GET['id'])){

    $id=$_GET['id'];

    mysqli_query($conn,"DELETE FROM notices WHERE id='$id'");
}

header("Location: notices.php");
exit();