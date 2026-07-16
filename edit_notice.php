<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

if(!isset($_GET['id'])){
    header("Location: notices.php");
    exit();
}

$id = $_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM notices WHERE id='$id'");
$notice = mysqli_fetch_assoc($result);

if(isset($_POST['update'])){

    $title = $_POST['title'];
    $message = $_POST['message'];
    $date = $_POST['notice_date'];

    mysqli_query($conn,"UPDATE notices SET
        title='$title',
        message='$message',
        notice_date='$date'
        WHERE id='$id'");

    header("Location: notices.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Notice</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>Edit Notice</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Title</label>

<input type="text"
name="title"
class="form-control"
value="<?php echo $notice['title']; ?>"
required>

</div>

<div class="mb-3">

<label>Message</label>

<textarea
name="message"
class="form-control"
rows="5"
required><?php echo $notice['message']; ?></textarea>

</div>

<div class="mb-3">

<label>Date</label>

<input
type="date"
name="notice_date"
class="form-control"
value="<?php echo $notice['notice_date']; ?>"
required>

</div>

<button class="btn btn-success" name="update">
Update Notice
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