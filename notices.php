<?php
session_start();

if(!isset($_SESSION['consumer'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

$query=mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Notices</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f7fb;
}

.card{
margin-top:20px;
box-shadow:0 5px 15px rgba(0,0,0,.15);
border:none;
}

</style>

</head>

<body>

<div class="container">

<h2 class="mt-4 mb-4">

📢 APDCL Notices

</h2>

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<div class="card">

<div class="card-header bg-primary text-white">

<?= htmlspecialchars($row['title']); ?>

</div>

<div class="card-body">

<p>

<?= nl2br(htmlspecialchars($row['message'])); ?>

</p>

<small class="text-muted">

<?= $row['created_at']; ?>

</small>

</div>

</div>

<?php } ?>

<br>

<a href="dashboard.php" class="btn btn-secondary">

← Dashboard

</a>

</div>

</body>
</html>