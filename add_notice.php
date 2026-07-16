<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

if(isset($_POST['save'])){

$title=$_POST['title'];
$message=$_POST['message'];
$date=$_POST['notice_date'];

mysqli_query($conn,"INSERT INTO notices(title,message,notice_date)
VALUES('$title','$message','$date')");

header("Location: notices.php");
exit();

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Notice</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Add Notice</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Title</label>

<input type="text" name="title" class="form-control" required>

</div>

<div class="mb-3">

<label>Notice</label>

<textarea name="message" class="form-control" rows="5" required></textarea>

</div>

<div class="mb-3">

<label>Date</label>

<input type="date" name="notice_date" class="form-control" required>

</div>

<button class="btn btn-success" name="save">
Save Notice
</button>

<a href="notices.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</div>

</body>

</html>