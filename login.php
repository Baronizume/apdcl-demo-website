<?php
session_start();
include("../db.php");

$error = "";

if(isset($_POST['login'])){

    $consumer_no = mysqli_real_escape_string($conn, trim($_POST['consumer_no']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $query = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE consumer_no='$consumer_no'
        AND password='$password'
        LIMIT 1
    ");

    if(mysqli_num_rows($query)==1){

        $user = mysqli_fetch_assoc($query);

        $_SESSION['consumer'] = $user['consumer_no'];
        $_SESSION['consumer_name'] = $user['name'];

        header("Location: dashboard.php");
        exit();

    }else{

        $error = "Invalid Consumer Number or Password.";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL Consumer Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{

    margin:0;
    padding:0;
    height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    font-family:'Segoe UI',sans-serif;

    background:linear-gradient(135deg,#0d47a1,#1565c0,#42a5f5);

}

.login-card{

    width:430px;

    background:#fff;

    border:none;

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 20px 45px rgba(0,0,0,.25);

}

.login-header{

    text-align:center;

    padding:30px;

    background:#f8fbff;

}

.login-header img{

    width:90px;

    height:90px;

    border-radius:50%;

    background:#fff;

    padding:6px;

    border:2px solid #0d47a1;

}

.login-header h3{

    color:#0d47a1;

    margin-top:15px;

    font-weight:700;

}

.login-header p{

    color:#666;

}

.form-control{

    height:48px;

}

.input-group-text{

    background:#0d47a1;

    color:#fff;

}

.btn-login{

    height:50px;

    font-weight:600;

}

.footer{

    text-align:center;

    color:#888;

    font-size:13px;

    margin-top:20px;

}

a{

    text-decoration:none;

}

</style>

</head>

<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-lg-5 col-md-7">

            <div class="card login-card">

                <!-- Header -->

                <div class="login-header">

                    <img src="../assets/images/logo-circle.png"
                         alt="APDCL Logo">

                    <h3>APDCL</h3>

                    <p>Assam Power Distribution Company Limited</p>

                    <h5 class="text-primary mt-3">

                        Consumer Login

                    </h5>

                </div>

                <div class="card-body p-4">

                    <?php if($error!=""){ ?>

                        <div class="alert alert-danger">

                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <?= $error; ?>

                        </div>

                    <?php } ?>

                    <form method="POST">

                        <!-- Consumer Number -->

                        <div class="mb-3">

                            <label class="form-label">

                                Consumer Number

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-person-badge-fill"></i>

                                </span>

                                <input
                                    type="text"
                                    name="consumer_no"
                                    class="form-control"
                                    placeholder="Enter Consumer Number"
                                    required>

                            </div>

                        </div>

                        <!-- Password -->

                        <div class="mb-3">

                            <label class="form-label">

                                Password

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-lock-fill"></i>

                                </span>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Enter Password"
                                    required>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword()">

                                    <i class="bi bi-eye-fill"
                                       id="eyeIcon"></i>

                                </button>

                            </div>

                        </div>

                        <!-- Remember + Forgot -->

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember">

                                <label class="form-check-label"
                                       for="remember">

                                    Remember Me

                                </label>

                            </div>

                            <a href="forgot_password.php">

                                Forgot Password?

                            </a>

                        </div>

                        <!-- Login Button -->

                        <div class="d-grid">

                            <button
                                type="submit"
                                name="login"
                                class="btn btn-primary btn-login">

                                <i class="bi bi-box-arrow-in-right"></i>

                                Login

                            </button>

                        </div>

                    </form>

                    <hr>

                    <div class="text-center">

                        <a href="../index.php">

                            <i class="bi bi-arrow-left-circle"></i>

                            Back to Home

                        </a>

                    </div>

                    <div class="footer">

                        © <?= date("Y"); ?>

                        APDCL Consumer Portal

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ===========================
   Show / Hide Password
=========================== */

function togglePassword(){

    const password = document.getElementById("password");

    const eye = document.getElementById("eyeIcon");

    if(password.type === "password"){

        password.type = "text";

        eye.classList.remove("bi-eye-fill");

        eye.classList.add("bi-eye-slash-fill");

    }else{

        password.type = "password";

        eye.classList.remove("bi-eye-slash-fill");

        eye.classList.add("bi-eye-fill");

    }

}

/* ===========================
   Login Card Animation
=========================== */

document.addEventListener("DOMContentLoaded", function(){

    const card = document.querySelector(".login-card");

    card.style.opacity = "0";

    card.style.transform = "translateY(40px)";

    setTimeout(function(){

        card.style.transition = "all .6s ease";

        card.style.opacity = "1";

        card.style.transform = "translateY(0)";

    },100);

});

</script>

</body>

</html>
