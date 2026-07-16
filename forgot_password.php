<?php
session_start();

include("../db.php");

/*=========================================
    ALREADY LOGGED IN
=========================================*/

if (isset($_SESSION['consumer'])) {
    header("Location: dashboard.php");
    exit();
}

/*=========================================
    VARIABLES
=========================================*/

$error = "";
$success = "";

/*=========================================
    SUBMIT FORM
=========================================*/

if (isset($_POST['submit'])) {

    $consumer_no = trim($_POST['consumer_no']);
    $email = trim($_POST['email']);

    if (empty($consumer_no) || empty($email)) {

        $error = "Please enter Consumer Number and Email Address.";

    } else {

        $stmt = mysqli_prepare($conn,"
            SELECT *
            FROM users
            WHERE consumer_no=?
            AND email=?
            LIMIT 1
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $consumer_no,
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            /*=====================================
                GENERATE OTP
            =====================================*/

            $otp = rand(100000,999999);

            $_SESSION['reset_consumer'] = $consumer_no;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

            /*
            -------------------------------------------------
            NEXT PART:
            Send OTP using PHPMailer
            -------------------------------------------------
            */

            header("Location: verify_otp.php");
            exit();

        } else {

            $error = "Consumer Number or Email Address is incorrect.";

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    margin:0;
    padding:0;
    background:linear-gradient(135deg,#0d47a1,#1976d2,#42a5f5);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:'Segoe UI',sans-serif;
}

.card{
    width:100%;
    max-width:500px;
    border:none;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 15px 35px rgba(0,0,0,.25);
}

.card-header{
    background:#0d47a1;
    color:#fff;
    text-align:center;
    padding:25px;
}

.card-header img{
    width:80px;
    margin-bottom:10px;
}

.card-header h3{
    margin:0;
    font-weight:700;
}

.card-header p{
    margin-top:5px;
    margin-bottom:0;
    opacity:.9;
}

.card-body{
    padding:35px;
    background:#fff;
}

.form-label{
    font-weight:600;
}

.form-control{
    height:50px;
    border-radius:10px;
}

.btn-primary{
    height:50px;
    border-radius:10px;
    font-weight:600;
}

.back-link{
    text-decoration:none;
    font-weight:600;
}

.footer-text{
    text-align:center;
    margin-top:20px;
    color:#777;
    font-size:14px;
}

</style>

</head>

<body>

<div class="card">

<div class="card-header">

<img src="../assets/images/logo-circle.png" alt="APDCL Logo">

<h3>APDCL Consumer Portal</h3>

<p>Forgot Password</p>

</div>

<div class="card-body">

<?php if(!empty($error)){ ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-triangle-fill"></i>

<?= htmlspecialchars($error) ?>

</div>

<?php } ?>

<?php if(!empty($success)){ ?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill"></i>

<?= htmlspecialchars($success) ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">

<i class="bi bi-person-badge-fill"></i>

Consumer Number

</label>

<input
type="text"
name="consumer_no"
class="form-control"
placeholder="Enter Consumer Number"
required>

</div>

<div class="mb-4">

<label class="form-label">

<i class="bi bi-envelope-fill"></i>

Registered Email Address

</label>

<input
type="email"
name="email"
class="form-control"
placeholder="Enter Registered Email"
required>

</div>

<div class="d-grid">

<button
type="submit"
name="submit"
class="btn btn-primary">

<i class="bi bi-send-fill"></i>

Send OTP

</button>

</div>

</form>

<div class="text-center mt-4">

<a href="login.php" class="back-link">

<i class="bi bi-arrow-left-circle-fill"></i>

Back to Login

</a>

</div>

<div class="footer-text">

© <?= date("Y") ?> APDCL Consumer Portal

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>