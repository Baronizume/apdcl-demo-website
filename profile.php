<?php
session_start();
include("../db.php");

/*=========================================
    LOGIN CHECK
=========================================*/

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

$consumer_no = $_SESSION['consumer'];

/*=========================================
    GET CONSUMER DETAILS
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

if(!$user){
    die("Consumer not found.");
}

/*=========================================
    PROFILE PHOTO
=========================================*/

$profilePhoto = "../assets/images/default-user.png";

if(!empty($user['photo']) && file_exists("../uploads/".$user['photo']))
{
    $profilePhoto = "../uploads/".$user['photo'];
}

/*=========================================
    UPDATE PROFILE
=========================================*/

$success = "";
$error = "";

if(isset($_POST['update_profile']))
{
    // Get form values
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile  = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Keep old photo by default
    $photoName = $user['photo'];

    // Upload new photo
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0)
    {
        $uploadDir = "../uploads/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0777,true);
        }

        $photoName = time() . "_" . basename($_FILES['photo']['name']);

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            $uploadDir . $photoName
        );
    }

    // Update profile
    $update = mysqli_query($conn,"
        UPDATE users SET
            name='$name',
            email='$email',
            mobile='$mobile',
            address='$address',
            photo='$photoName'
        WHERE consumer_no='$consumer_no'
    ");

    if($update)
    {
        $success = "Profile updated successfully.";

        $userQuery = mysqli_query($conn,"
            SELECT *
            FROM users
            WHERE consumer_no='$consumer_no'
        ");

        $user = mysqli_fetch_assoc($userQuery);

        if(!empty($user['photo']) && file_exists("../uploads/".$user['photo']))
        {
            $profilePhoto = "../uploads/".$user['photo'];
        }
        else
        {
            $profilePhoto = "../uploads/default.png";
        }
    }
    else
    {
        $error = mysqli_error($conn);
    }
}

/*=========================================
    CHANGE PASSWORD
=========================================*/

$passwordMsg="";

if(isset($_POST['change_password']))
{

    $current=$_POST['current_password'];

    $new=$_POST['new_password'];

    $confirm=$_POST['confirm_password'];

    if($new!=$confirm){

        $passwordMsg="New passwords do not match.";

    }
    elseif(!password_verify($current,$user['password'])){

        $passwordMsg="Current password is incorrect.";

    }
    else{

        $hash=password_hash($new,PASSWORD_DEFAULT);

        mysqli_query($conn,"
        UPDATE users
        SET password='$hash'
        WHERE consumer_no='$consumer_no'
        ");

        $passwordMsg="Password changed successfully.";

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

My Profile | APDCL Consumer Portal

</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

</head>

<body style="background:#eef3f9;">

<!-- ===========================
NAVBAR
=========================== -->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">

<div class="container">

<a class="navbar-brand fw-bold" href="dashboard.php">

⚡ APDCL Consumer Portal

</a>

<div class="ms-auto">

<a href="dashboard.php"
class="btn btn-light btn-sm">

<i class="bi bi-house-door-fill"></i>

Dashboard

</a>

</div>

</div>

</nav>

<!-- ===========================
PAGE HEADER
=========================== -->

<div class="container mt-4">

<div class="card border-0 shadow-lg">

<div class="card-body text-center bg-primary text-white rounded">

<h2>

<i class="bi bi-person-circle"></i>

My Profile

</h2>

<p class="mb-0">

Manage your personal information and electricity connection details.

</p>

</div>

</div>

</div>

<!-- ===========================
PROFILE SECTION
=========================== -->

<div class="container mt-4">

<div class="row">

<!-- LEFT SIDE -->

<div class="col-lg-4">

<div class="card shadow border-0">

<div class="card-body text-center">

<img
src="<?= $profilePhoto ?>"
id="preview"
class="rounded-circle border border-4 border-primary shadow"
width="180"
height="180"
style="object-fit:cover;">

<h3 class="mt-3">

<?= htmlspecialchars($user['name']) ?>

</h3>

<p class="text-muted">

Consumer No

<br>

<b><?= $user['consumer_no'] ?></b>

</p>

<span class="badge bg-success">

Active Consumer

</span>

<hr>

<form
method="POST"
enctype="multipart/form-data">

<label class="btn btn-outline-primary w-100">

<i class="bi bi-camera-fill"></i>

Choose Profile Photo

<input
type="file"
name="photo"
id="photo"
accept=".jpg,.jpeg,.png,.webp"
hidden>

</label>

</div>

</div>

<!-- ACCOUNT INFO -->

<div class="card shadow border-0 mt-4">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

<i class="bi bi-info-circle-fill"></i>

Account Information

</h5>

</div>

<div class="card-body">

<p>

<b>Consumer Number</b>

<br>

<?= $user['consumer_no'] ?>

</p>

<p>

<b>Meter Number</b>

<br>

<?= $user['meter_no'] ?>

</p>

<p>

<b>Category</b>

<br>

<?= $user['category'] ?>

</p>

<p>

<b>Status</b>

<br>

<span class="badge bg-success">

Active

</span>

</p>

</div>

</div>

</div>

<!-- RIGHT SIDE -->

<div class="col-lg-8">

<div class="card shadow border-0">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

<i class="bi bi-pencil-square"></i>

Edit Personal Information

</h4>

</div>

<div class="card-body">

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

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="name"
class="form-control"
value="<?= htmlspecialchars($user['name']) ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Email Address

</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($user['email']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Mobile Number

</label>

<input
type="text"
name="mobile"
class="form-control"
value="<?= htmlspecialchars($user['mobile']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Consumer Number

</label>

<input
type="text"
class="form-control"
value="<?= $user['consumer_no'] ?>"
readonly>

</div>

<div class="col-12 mb-3">

<label class="form-label">

Address

</label>

<textarea
name="address"
rows="4"
class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>

</div>

<!-- ===========================
ELECTRICITY CONNECTION DETAILS
=========================== -->

<hr class="my-4">

<h4 class="text-primary mb-4">

<i class="bi bi-lightning-charge-fill"></i>

Electricity Connection Details

</h4>

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Meter Number

</label>

<input
type="text"
class="form-control"
value="<?= $user['meter_no'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Category

</label>

<input
type="text"
class="form-control"
value="<?= $user['category'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Zone

</label>

<input
type="text"
class="form-control"
value="<?= $user['zone'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Circle

</label>

<input
type="text"
class="form-control"
value="<?= $user['circle'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Sub Division

</label>

<input
type="text"
class="form-control"
value="<?= $user['sub_division'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

DTR Number

</label>

<input
type="text"
class="form-control"
value="<?= $user['dtr_no'] ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Pole Number

</label>

<input
type="text"
class="form-control"
value="<?= $user['pole_no'] ?>"
readonly>

</div>

</div>

<!-- ===========================
CHANGE PASSWORD
=========================== -->

<hr class="my-4">

<h4 class="text-danger mb-4">

<i class="bi bi-shield-lock-fill"></i>

Change Password

</h4>

<?php if($passwordMsg!=""){ ?>

<div class="alert alert-info">

<?= $passwordMsg ?>

</div>

<?php } ?>

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">

Current Password

</label>

<input
type="password"
name="current_password"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

New Password

</label>

<input
type="password"
name="new_password"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Confirm Password

</label>

<input
type="password"
name="confirm_password"
class="form-control">

</div>

</div>

<!-- ===========================
BUTTONS
=========================== -->

<hr>

<div class="d-flex flex-wrap gap-3">

<button
type="submit"
name="update_profile"
class="btn btn-primary btn-lg">

<i class="bi bi-save-fill"></i>

Save Profile

</button>

<button
type="submit"
name="change_password"
class="btn btn-danger btn-lg">

<i class="bi bi-key-fill"></i>

Change Password

</button>

<button type="submit" name="update_profile" class="btn btn-primary">
    Update Profile
</button>

<button
type="reset"
class="btn btn-warning btn-lg">

<i class="bi bi-arrow-counterclockwise"></i>

Reset

</button>

<a
href="dashboard.php"
class="btn btn-success btn-lg">

<i class="bi bi-house-fill"></i>

Back to Dashboard

</a>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<!-- ===========================
FOOTER
=========================== -->

<footer class="bg-primary text-white mt-5 py-4">

<div class="container text-center">

<h5 class="fw-bold">

⚡ APDCL Consumer Portal

</h5>

<p class="mb-1">

Assam Power Distribution Company Limited

</p>

<small>

© <?= date('Y') ?> APDCL Consumer Portal. All Rights Reserved.

</small>

</div>

</footer>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===========================
IMAGE PREVIEW
=========================== -->

<script>

document.getElementById("photo").addEventListener("change",function(e){

const file=e.target.files[0];

if(file){

document.getElementById("preview").src=URL.createObjectURL(file);

}

});

</script>

<!-- ===========================
CUSTOM CSS
=========================== -->

<style>

body{

background:#eef3f9;

font-family:'Segoe UI',sans-serif;

}

/* Navbar */

.navbar{

box-shadow:0 5px 15px rgba(0,0,0,.15);

}

/* Cards */

.card{

border:none;

border-radius:18px;

transition:.35s;

overflow:hidden;

}

.card:hover{

transform:translateY(-5px);

box-shadow:0 18px 35px rgba(0,0,0,.15);

}

/* Profile Photo */

#preview{

width:180px;

height:180px;

object-fit:cover;

transition:.4s;

}

#preview:hover{

transform:scale(1.05);

}

/* Form */

.form-control{

border-radius:10px;

padding:12px;

}

.form-control:focus{

border-color:#0d6efd;

box-shadow:0 0 10px rgba(13,110,253,.25);

}

/* Buttons */

.btn{

border-radius:10px;

padding:10px 20px;

transition:.3s;

}

.btn:hover{

transform:translateY(-2px);

}

/* Header */

.card-header{

font-weight:600;

font-size:18px;

}

/* Footer */

footer{

box-shadow:0 -4px 15px rgba(0,0,0,.15);

}

/* Responsive */

@media(max-width:768px){

#preview{

width:130px;

height:130px;

}

h2{

font-size:28px;

}

.btn{

width:100%;

margin-bottom:10px;

}

}

</style>

</body>

</html>