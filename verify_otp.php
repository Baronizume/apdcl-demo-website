<?php
session_start();

include("../db.php");

/*=========================================
    CHECK RESET SESSION
=========================================*/

if (
    !isset($_SESSION['reset_consumer']) ||
    !isset($_SESSION['reset_email']) ||
    !isset($_SESSION['reset_otp']) ||
    !isset($_SESSION['otp_expiry'])
) {

    header("Location: forgot_password.php");
    exit();

}

/*=========================================
    VARIABLES
=========================================*/

$error = "";
$success = "";

$consumer_no = $_SESSION['reset_consumer'];
$email       = $_SESSION['reset_email'];

/*=========================================
    DEMO OTP
=========================================*/

$demo_otp = $_SESSION['reset_otp'];

/*=========================================
    OTP EXPIRED
=========================================*/

if (time() > $_SESSION['otp_expiry']) {

    session_unset();
    session_destroy();

    header("Location: forgot_password.php?expired=1");
    exit();

}

/*=========================================
    VERIFY OTP
=========================================*/

if (isset($_POST['verify'])) {

    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : "";

    if ($otp === "") {

        $error = "Please enter the OTP.";

    } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {

        $error = "OTP must be exactly 6 digits.";

    } elseif ((string)$otp === (string)$_SESSION['reset_otp']) {

        $_SESSION['otp_verified'] = true;

        header("Location: reset_password.php");
        exit();

    } else {

        $error = "Invalid OTP. Please try again.";

    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verify OTP | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    background:linear-gradient(135deg,#0d47a1,#1976d2,#42a5f5);

    font-family:'Segoe UI',sans-serif;

}

.verify-card{

    width:100%;

    max-width:520px;

    border:none;

    border-radius:20px;

    overflow:hidden;

    background:#fff;

    box-shadow:0 20px 40px rgba(0,0,0,.25);

}

.verify-header{

    background:#0d47a1;

    color:#fff;

    text-align:center;

    padding:30px;

}

.verify-header img{

    width:85px;

    margin-bottom:15px;

}

.verify-header h3{

    font-weight:700;

    margin-bottom:5px;

}

.verify-header p{

    margin-bottom:0;

    opacity:.9;

}

.verify-body{

    padding:35px;

}

.info-box{

    background:#eef5ff;

    border-left:5px solid #1976d2;

    padding:15px;

    border-radius:10px;

    margin-bottom:25px;

}

.form-label{

    font-weight:600;

}

.form-control{

    height:55px;

    border-radius:12px;

    font-size:18px;

    text-align:center;

    letter-spacing:6px;

}

.btn-primary{

    height:52px;

    border-radius:12px;

    font-weight:600;

}

.back-link{

    text-decoration:none;

    font-weight:600;

}

.footer{

    text-align:center;

    color:#666;

    margin-top:20px;

    font-size:14px;

}

.alert{

    border-radius:12px;

}

</style>

</head>

<body>

<div class="verify-card">

<div class="verify-header">

<img
src="../assets/images/logo-circle.png"
alt="APDCL Logo">

<h3>

APDCL Consumer Portal

</h3>

<p>

OTP Verification

</p>

</div>

<div class="verify-body">

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

<div class="info-box">

<div class="fw-bold mb-2">

<i class="bi bi-shield-lock-fill"></i>

Verify Your Identity

</div>

<p class="mb-1">

Consumer Number:

<strong>

<?= htmlspecialchars($consumer_no) ?>

</strong>

</p>

<p class="mb-0">

OTP has been sent to:

<strong>

<?= htmlspecialchars($email) ?>

</strong>

</p>

</div>

<!-- Demo OTP -->

<div class="alert alert-success text-center mt-3">

    <h5>

        <i class="bi bi-shield-lock-fill"></i>

        Demo OTP

    </h5>

    <h1 class="fw-bold text-danger">

        <?= htmlspecialchars($demo_otp) ?>

    </h1>

    <small class="text-muted">

        Demo Mode: Enter this OTP below.

    </small>

</div>

<form method="POST" autocomplete="off">

    <!-- OTP -->

    <div class="mb-4">

        <label class="form-label">

            <i class="bi bi-key-fill"></i>

            Enter 6-Digit OTP

        </label>

        <input
        type="text"
        name="otp"
        id="otp"
        class="form-control"
        maxlength="6"
        minlength="6"
        pattern="[0-9]{6}"
        placeholder="000000"
        required>

        <div class="form-text">

            Please enter the 6-digit OTP sent to your registered email.

        </div>

    </div>

    <!-- Verify Button -->

    <div class="d-grid mb-3">

        <button
        type="submit"
        name="verify"
        class="btn btn-primary">

            <i class="bi bi-shield-check"></i>

            Verify OTP

        </button>

    </div>

</form>

<!-- Resend OTP -->

<div class="d-grid mb-3">

    <a
    href="forgot_password.php"
    class="btn btn-outline-warning">

        <i class="bi bi-arrow-repeat"></i>

        Resend OTP

    </a>

</div>

<!-- Back -->

<div class="text-center mt-4">

    <a
    href="login.php"
    class="back-link">

        <i class="bi bi-arrow-left-circle-fill"></i>

        Back to Login

    </a>

</div>

<div class="footer">

    © <?= date("Y"); ?> APDCL Consumer Portal

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* Allow only numbers */

document.getElementById("otp").addEventListener("input", function () {
    this.value = this.value.replace(/\D/g, "");
});

</script>

</body>

</html>