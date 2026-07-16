<?php
session_start();

include("../db.php");

/*=========================================================
    CHECK OTP VERIFICATION
=========================================================*/

if (
    !isset($_SESSION['otp_verified']) ||
    $_SESSION['otp_verified'] !== true ||
    !isset($_SESSION['reset_consumer'])
) {
    header("Location: forgot_password.php");
    exit();
}

$consumer_no = $_SESSION['reset_consumer'];

$success = "";
$error = "";

/*=========================================================
    RESET PASSWORD
=========================================================*/

if (isset($_POST['reset'])) {

    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    if (empty($password) || empty($confirm)) {

        $error = "Please fill all fields.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password != $confirm) {

        $error = "Passwords do not match.";

    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn,"
            UPDATE users
            SET password=?
            WHERE consumer_no=?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $hash,
            $consumer_no
        );

        if(mysqli_stmt_execute($stmt)){

            session_unset();
            session_destroy();

            session_start();
            $_SESSION['success'] = "Password reset successfully. Please login.";

            header("Location: login.php");
            exit();

        }else{

            $error = "Unable to reset password.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Reset Password | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{

    background:#eef4ff;

    font-family:'Segoe UI',sans-serif;

}

.reset-box{

    max-width:520px;

    margin:70px auto;

}

.card{

    border:none;

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,.12);

}

.card-header{

    background:linear-gradient(135deg,#0d6efd,#1565c0);

    color:#fff;

    text-align:center;

    padding:25px;

}

.form-control{

    height:50px;

    border-radius:12px;

}

.btn{

    border-radius:12px;

    height:50px;

    font-weight:600;

}

.logo{

    width:90px;

    margin-bottom:15px;

}

</style>

</head>

<body>

<div class="container">

<div class="reset-box">

<div class="card">

<div class="card-header">

<img src="../assets/images/logo-circle.png" class="logo" alt="APDCL">

<h3>

Reset Password

</h3>

<p class="mb-0">

Create a new password for your APDCL Consumer account.

</p>

</div>

<div class="card-body p-4">

<?php if($success!=""){ ?>

<div class="alert alert-success">

<?= $success ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label fw-bold">

New Password

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-lock-fill"></i>

</span>

<input
type="password"
name="password"
id="password"
class="form-control"
required>

</div>

</div>

<div class="mb-4">

<label class="form-label fw-bold">

Confirm Password

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-shield-lock-fill"></i>

</span>

<input
type="password"
name="confirm_password"
id="confirm_password"
class="form-control"
required>

</div>

</div>

<div class="d-grid">

<button
type="submit"
name="reset"
class="btn btn-primary">

<i class="bi bi-check-circle-fill"></i>

Reset Password

</button>

</div>

</form>

<hr>

<div class="text-center">

<a href="login.php" class="text-decoration-none">

<i class="bi bi-arrow-left-circle"></i>

Back to Login

</a>

</div>

</div>

</div>

</div>

</div>

<script>

const password=document.getElementById("password");
const confirm=document.getElementById("confirm_password");

confirm.addEventListener("keyup",function(){

    if(password.value!==confirm.value){

        confirm.style.borderColor="red";

    }else{

        confirm.style.borderColor="green";

    }

});

</script>

</body>

</html>