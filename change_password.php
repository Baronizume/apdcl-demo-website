<?php
session_start();

if(!isset($_SESSION['consumer_no'])){
    header("Location: login.php");
    exit();
}

include("db.php");

$message="";

$consumer_no=$_SESSION['consumer_no'];

if(isset($_POST['change'])){

    $old_password=$_POST['old_password'];
    $new_password=$_POST['new_password'];
    $confirm_password=$_POST['confirm_password'];

    $check=mysqli_query($conn,"SELECT * FROM users WHERE consumer_no='$consumer_no'");

    $user=mysqli_fetch_assoc($check);

    if($user['password']!=$old_password){

        $message="<div class='alert alert-danger'>Current Password is Incorrect.</div>";

    }elseif($new_password!=$confirm_password){

        $message="<div class='alert alert-warning'>New Password and Confirm Password do not match.</div>";

    }else{

        mysqli_query($conn,"UPDATE users SET password='$new_password' WHERE consumer_no='$consumer_no'");

        $message="<div class='alert alert-success'>Password Changed Successfully.</div>";

    }

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Change Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Change Password</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label>Current Password</label>

<input type="password" name="old_password" class="form-control" required>

</div>

<div class="mb-3">

<label>New Password</label>

<input type="password" name="new_password" class="form-control" required>

</div>

<div class="mb-3">

<label>Confirm Password</label>

<input type="password" name="confirm_password" class="form-control" required>

</div>

<button class="btn btn-primary" name="change">

Change Password

</button>

<a href="dashboard.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>