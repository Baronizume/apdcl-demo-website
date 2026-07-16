<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================================
    LOGGED-IN CONSUMER
=========================================================*/

$consumer_no = $_SESSION['consumer'];

$success = "";
$error = "";

/*=========================================================
    LOAD CONSUMER DETAILS
=========================================================*/

$user = mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");

if(!$user){
    die("User Query Error : ".mysqli_error($conn));
}

mysqli_stmt_bind_param($user,"s",$consumer_no);
mysqli_stmt_execute($user);

$userResult = mysqli_stmt_get_result($user);

if(mysqli_num_rows($userResult)==0){
    die("Consumer not found.");
}

$consumer = mysqli_fetch_assoc($userResult);

/*=========================================================
    DEFAULT VALUES
=========================================================*/

$category   = "";
$priority   = "Medium";
$subject    = "";
$message    = "";
$latitude   = "";
$longitude  = "";
$photo      = "";

$complaint_id = "CMP".date("YmdHis").rand(100,999);

/*=========================================================
    SUBMIT NEW COMPLAINT
=========================================================*/

if(isset($_POST['submit'])){

    $category  = trim($_POST['category']);
    $priority  = trim($_POST['priority']);
    $subject   = trim($_POST['subject']);
    $message   = trim($_POST['message']);
    $latitude  = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    /*=====================================
        VALIDATION
    =====================================*/

    if(
        empty($category) ||
        empty($priority) ||
        empty($subject) ||
        empty($message)
    ){

        $error = "Please fill all required fields.";

    }else{

        /*=====================================
            PHOTO UPLOAD
        =====================================*/

        $photo = "";

        if(
            isset($_FILES['photo']) &&
            $_FILES['photo']['error']==0
        ){

            $uploadDir = "../uploads/complaint/";

            if(!is_dir($uploadDir)){
                mkdir($uploadDir,0777,true);
            }

            $extension = strtolower(pathinfo(
                $_FILES['photo']['name'],
                PATHINFO_EXTENSION
            ));

            $allowed = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if(in_array($extension,$allowed)){

                $photo = uniqid().".".$extension;

                move_uploaded_file(
                    $_FILES['photo']['tmp_name'],
                    $uploadDir.$photo
                );

            }

        }

        /*=====================================
            INSERT COMPLAINT
        =====================================*/

        $status = "Pending";

        $insert = mysqli_prepare($conn,"
        INSERT INTO complaint
        (
            complaint_id,
            consumer_no,
            name,
            mobile,
            email,
            category,
            subject,
            description,
            address,
            status,
            priority,
            latitude,
            longitude,
            photo,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,
            NOW()
        )
        ");

        if(!$insert){
            die("Insert Error : ".mysqli_error($conn));
        }

        mysqli_stmt_bind_param(

            $insert,

            "ssssssssssssss",

            $complaint_id,
            $consumer_no,
            $consumer['name'],
            $consumer['mobile'],
            $consumer['email'],
            $category,
            $subject,
            $message,
            $consumer['address'],
            $status,
            $priority,
            $latitude,
            $longitude,
            $photo

        );

        if(mysqli_stmt_execute($insert)){

            $_SESSION['success'] =
            "Complaint submitted successfully.";

            header("Location: complaint.php");

            exit();

        }else{

            $error = mysqli_stmt_error($insert);

        }

    }

}

/*=========================================================
    SUCCESS MESSAGE
=========================================================*/

if(isset($_SESSION['success'])){

    $success = $_SESSION['success'];

    unset($_SESSION['success']);

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>New Complaint | APDCL Consumer Portal</title>

<link rel="icon" href="../assets/images/logo-circle.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{

    background:#eef4fb;

}

/*=====================================
    NAVBAR
=====================================*/

.navbar{

    background:linear-gradient(90deg,#0d47a1,#1565c0);

    box-shadow:0 8px 20px rgba(0,0,0,.15);

    padding:14px 0;

}

.navbar-brand{

    color:#fff!important;

    font-size:24px;

    font-weight:700;

    display:flex;

    align-items:center;

}

.navbar-brand img{

    width:45px;

    height:45px;

    margin-right:12px;

}

.navbar-brand small{

    display:block;

    font-size:13px;

    font-weight:400;

    color:#e3f2fd;

}

/*=====================================
    NAVIGATION BUTTONS
=====================================*/

.nav-btn{

    color:#fff!important;

    border:1px solid rgba(255,255,255,.25);

    padding:9px 18px;

    border-radius:10px;

    margin-left:10px;

    transition:.3s;

    text-decoration:none;

}

.nav-btn:hover{

    background:#fff;

    color:#1565c0!important;

}

.logout-btn{

    background:#dc3545;

    border:none;

}

.logout-btn:hover{

    background:#bb2d3b!important;

    color:#fff!important;

}

/*=====================================
    PAGE HEADER
=====================================*/

.page-header{

    background:linear-gradient(135deg,#1565c0,#0d47a1);

    color:#fff;

    border-radius:18px;

    padding:35px;

    margin-top:30px;

    margin-bottom:25px;

    box-shadow:0 10px 25px rgba(0,0,0,.12);

}

.page-header h2{

    font-weight:700;

    margin-bottom:10px;

}

.page-header p{

    opacity:.9;

    margin:0;

}

.complaint-id{

    background:#fff;

    color:#1565c0;

    padding:15px 20px;

    border-radius:12px;

    text-align:center;

    font-weight:700;

    box-shadow:0 5px 15px rgba(0,0,0,.15);

}

/*=====================================
    CARDS
=====================================*/

.main-card{

    background:#fff;

    border:none;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

    margin-bottom:25px;

}

.main-card .card-header{

    background:#1565c0;

    color:#fff;

    font-size:19px;

    font-weight:600;

    padding:18px 25px;

}

/*=====================================
    ALERTS
=====================================*/

.alert{

    border-radius:12px;

}

/*=====================================
    FORM
=====================================*/

.form-label{

    font-weight:600;

    margin-bottom:8px;

}

.form-control,
.form-select{

    height:50px;

    border-radius:10px;

}

textarea.form-control{

    height:auto;

    resize:none;

}

.form-control:focus,
.form-select:focus{

    border-color:#1565c0;

    box-shadow:0 0 0 .2rem rgba(21,101,192,.15);

}

/*=====================================
    BUTTONS
=====================================*/

.btn{

    border-radius:10px;

    font-weight:600;

    padding:10px 22px;

}

.btn-primary{

    background:#1565c0;

    border:none;

}

.btn-primary:hover{

    background:#0d47a1;

}

/*=====================================
    PROFILE CARD
=====================================*/

.profile-card{

    background:#fff;

    border-radius:18px;

    padding:25px;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.profile-card h5{

    color:#1565c0;

    font-weight:700;

    margin-bottom:20px;

}

.profile-card p{

    margin-bottom:12px;

}

.profile-card i{

    color:#1565c0;

    width:22px;

}

/*=====================================
    IMAGE PREVIEW
=====================================*/

#preview{

    display:none;

    width:100%;

    max-height:260px;

    object-fit:cover;

    border-radius:12px;

    margin-top:15px;

    border:2px solid #ddd;

}

/*=====================================
    RESPONSIVE
=====================================*/

@media(max-width:768px){

.navbar-brand{

    font-size:18px;

}

.nav-btn{

    margin-top:10px;

    margin-left:0;

    display:block;

}

.page-header{

    text-align:center;

}

.complaint-id{

    margin-top:20px;

}

}

</style>

</head>

<body>

<!-- =====================================
        NAVBAR
====================================== -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="dashboard.php">

<img src="../assets/images/logo-circle.png" alt="APDCL Logo">

<div>

APDCL Consumer Portal

<small>Complaint Management System</small>

</div>

</a>

<div class="ms-auto">

<a href="dashboard.php" class="nav-btn">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

<a href="complaint.php" class="nav-btn">

<i class="bi bi-chat-left-text-fill"></i>

My Complaints

</a>

<a href="../logout.php" class="nav-btn logout-btn">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

</div>

</nav>

<div class="container">

<?php if(!empty($success)){ ?>

<div class="alert alert-success alert-dismissible fade show mt-4">

<i class="bi bi-check-circle-fill"></i>

<?= htmlspecialchars($success) ?>

<button
type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php if(!empty($error)){ ?>

<div class="alert alert-danger alert-dismissible fade show mt-4">

<i class="bi bi-exclamation-triangle-fill"></i>

<?= htmlspecialchars($error) ?>

<button
type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<!-- =====================================
        PAGE HEADER
====================================== -->

<div class="page-header">

<div class="row align-items-center">

<div class="col-lg-8">

<h2>

<i class="bi bi-pencil-square"></i>

Submit New Complaint

</h2>

<p>

Register your electricity-related complaint with APDCL. Our support team will review and process your request as soon as possible.

</p>

</div>

<div class="col-lg-4 text-lg-end">

<div class="complaint-id">

Complaint ID

<br>

<strong>

<?= htmlspecialchars($complaint_id) ?>

</strong>

</div>

</div>

</div>

</div>

<div class="row">

<!-- =====================================
        CONSUMER DETAILS
====================================== -->

<div class="col-lg-4 mb-4">

<div class="profile-card">

<h5>

<i class="bi bi-person-circle"></i>

Consumer Information

</h5>

<hr>

<p>

<i class="bi bi-person-fill"></i>

<strong>Name :</strong><br>

<?= htmlspecialchars($consumer['name']) ?>

</p>

<p>

<i class="bi bi-credit-card-2-front-fill"></i>

<strong>Consumer No :</strong><br>

<?= htmlspecialchars($consumer['consumer_no']) ?>

</p>

<p>

<i class="bi bi-telephone-fill"></i>

<strong>Mobile :</strong><br>

<?= htmlspecialchars($consumer['mobile']) ?>

</p>

<p>

<i class="bi bi-envelope-fill"></i>

<strong>Email :</strong><br>

<?= htmlspecialchars($consumer['email']) ?>

</p>

<p>

<i class="bi bi-geo-alt-fill"></i>

<strong>Address :</strong><br>

<?= nl2br(htmlspecialchars($consumer['address'])) ?>

</p>

<p>

<i class="bi bi-shield-check"></i>

<strong>Status :</strong>

<span class="badge bg-success">

Verified Consumer

</span>

</p>

</div>

</div>

<!-- =====================================
        COMPLAINT FORM
====================================== -->

<div class="col-lg-8">

<div class="card main-card">

<div class="card-header">

<i class="bi bi-file-earmark-plus-fill"></i>

Complaint Information

</div>

<div class="card-body">

<form
method="POST"
enctype="multipart/form-data">

<div class="row g-4">

<!-- Complaint Category -->

<div class="col-md-6">

<label class="form-label">

Complaint Category

<span class="text-danger">*</span>

</label>

<select
name="category"
class="form-select"
required>

<option value="">Select Category</option>

<option value="Power Failure" <?=($category=="Power Failure")?"selected":"";?>>

Power Failure

</option>

<option value="Low Voltage" <?=($category=="Low Voltage")?"selected":"";?>>

Low Voltage

</option>

<option value="High Voltage" <?=($category=="High Voltage")?"selected":"";?>>

High Voltage

</option>

<option value="Meter Problem" <?=($category=="Meter Problem")?"selected":"";?>>

Meter Problem

</option>

<option value="Transformer Fault" <?=($category=="Transformer Fault")?"selected":"";?>>

Transformer Fault

</option>

<option value="Line Break" <?=($category=="Line Break")?"selected":"";?>>

Line Break

</option>

<option value="Billing Issue" <?=($category=="Billing Issue")?"selected":"";?>>

Billing Issue

</option>

<option value="Street Light" <?=($category=="Street Light")?"selected":"";?>>

Street Light

</option>

<option value="Others" <?=($category=="Others")?"selected":"";?>>

Others

</option>

</select>

</div>

<!-- Priority -->

<div class="col-md-6">

<label class="form-label">

Priority

<span class="text-danger">*</span>

</label>

<select
name="priority"
class="form-select"
required>

<option value="Low" <?=($priority=="Low")?"selected":"";?>>

Low

</option>

<option value="Medium" <?=($priority=="Medium")?"selected":"";?>>

Medium

</option>

<option value="High" <?=($priority=="High")?"selected":"";?>>

High

</option>

</select>

</div>

<!-- Subject -->

<div class="col-12">

<label class="form-label">

Complaint Subject

<span class="text-danger">*</span>

</label>

<input
type="text"
name="subject"
class="form-control"
value="<?= htmlspecialchars($subject) ?>"
placeholder="Enter complaint subject"
required>

</div>

<!-- Complaint Form -->
<div class="col-lg-8">

    <div class="card shadow-lg border-0 rounded-4">

        <div class="card-header bg-primary text-white py-3">

            <h4 class="mb-0">
                <i class="bi bi-pencil-square"></i>
                Register New Complaint
            </h4>

        </div>

        <div class="card-body p-4">

            <?php if(!empty($success)){ ?>

                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= $success ?>
                </div>

            <?php } ?>

            <?php if(!empty($error)){ ?>

                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= $error ?>
                </div>

            <?php } ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="row">

                    <!-- Complaint ID -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Complaint ID
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= $complaint_id ?>"
                            readonly>

                    </div>

                    <!-- Consumer Number -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Consumer Number
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($consumer['consumer_no']) ?>"
                            readonly>

                    </div>

                    <!-- Consumer Name -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Consumer Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($consumer['name']) ?>"
                            readonly>

                    </div>

                    <!-- Mobile -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Mobile Number
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($consumer['mobile']) ?>"
                            readonly>

                    </div>

                    <!-- Email -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?= htmlspecialchars($consumer['email']) ?>"
                            readonly>

                    </div>

                    <!-- Complaint Category -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Complaint Category
                        </label>

                        <select
                            name="category"
                            class="form-select"
                            required>

                            <option value="">Select Category</option>

                            <option>Power Failure</option>
                            <option>Low Voltage</option>
                            <option>High Voltage</option>
                            <option>Meter Problem</option>
                            <option>Billing Issue</option>
                            <option>Transformer Fault</option>
                            <option>Street Light</option>
                            <option>Line Broken</option>
                            <option>Others</option>

                        </select>

                    </div>

                    <!-- Priority -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Priority
                        </label>

                        <select
                            name="priority"
                            class="form-select"
                            required>

                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>

                        </select>

                    </div>

                    <!-- Subject -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Subject
                        </label>

                        <input
                            type="text"
                            name="subject"
                            class="form-control"
                            placeholder="Enter Complaint Subject"
                            required>

                    </div>

                    <!-- Description -->

                    <div class="col-12 mb-3">

                        <label class="form-label fw-bold">
                            Complaint Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="5"
                            placeholder="Describe your complaint..."
                            required></textarea>

                    </div>

                    <!-- Address -->

                    <div class="col-12 mb-3">

                        <label class="form-label fw-bold">
                            Complaint Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="Enter Complaint Address"><?= htmlspecialchars($consumer['address']) ?></textarea>

                    </div>

                    <!-- Latitude -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Latitude
                        </label>

                        <input
                            type="text"
                            id="latitude"
                            name="latitude"
                            class="form-control"
                            readonly>

                    </div>

                    <!-- Longitude -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Longitude
                        </label>

                        <input
                            type="text"
                            id="longitude"
                            name="longitude"
                            class="form-control"
                            readonly>

                    </div>

                    <!-- Detect Location -->

                    <div class="col-12 mb-3">

                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            onclick="getLocation()">

                            <i class="bi bi-geo-alt-fill"></i>

                            Detect My Location

                        </button>

                    </div>

                    <!-- Upload -->

                    <div class="col-12 mb-4">

                        <label class="form-label fw-bold">
                            Upload Complaint Photo
                        </label>

                        <input
                            type="file"
                            name="photo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp">

                    </div>

                    <!-- Buttons -->

                    <div class="col-12 text-end">

                        <a
                            href="complaint.php"
                            class="btn btn-secondary">

                            <i class="bi bi-arrow-left"></i>

                            Back

                        </a>

                        <button
                            type="reset"
                            class="btn btn-warning">

                            <i class="bi bi-arrow-clockwise"></i>

                            Reset

                        </button>

                        <button
                            type="submit"
                            name="submit"
                            class="btn btn-success">

                            <i class="bi bi-send-fill"></i>

                            Submit Complaint

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- Footer -->

<footer class="bg-dark text-white text-center py-3 mt-5">

    <div class="container">

        <small>

            © <?= date("Y") ?> Assam Power Distribution Company Limited (APDCL)

            <br>

            Consumer Complaint Management System

        </small>

    </div>

</footer>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    IMAGE PREVIEW
=========================================*/

document.querySelector("input[name='photo']").addEventListener("change", function(e){

    if(e.target.files.length==0) return;

    let old=document.getElementById("previewImage");

    if(old){
        old.remove();
    }

    let img=document.createElement("img");

    img.id="previewImage";

    img.src=URL.createObjectURL(e.target.files[0]);

    img.className="img-thumbnail mt-3";

    img.style.maxWidth="250px";

    this.parentNode.appendChild(img);

});

/*=========================================
    GET CURRENT LOCATION
=========================================*/

function getLocation(){

    if(!navigator.geolocation){

        alert("Geolocation is not supported by your browser.");

        return;

    }

    navigator.geolocation.getCurrentPosition(

        function(position){

            document.getElementById("latitude").value =
            position.coords.latitude;

            document.getElementById("longitude").value =
            position.coords.longitude;

            alert("Location detected successfully.");

        },

        function(){

            alert("Unable to detect your location.");

        }

    );

}

/*=========================================
    FORM VALIDATION
=========================================*/

document.querySelector("form").addEventListener("submit",function(e){

    let category=document.querySelector("[name='category']").value.trim();

    let subject=document.querySelector("[name='subject']").value.trim();

    let description=document.querySelector("[name='description']").value.trim();

    if(category==""){

        alert("Please select a complaint category.");

        e.preventDefault();

        return;

    }

    if(subject.length<5){

        alert("Subject must contain at least 5 characters.");

        e.preventDefault();

        return;

    }

    if(description.length<10){

        alert("Complaint description must contain at least 10 characters.");

        e.preventDefault();

        return;

    }

});

/*=========================================
    DISABLE DOUBLE SUBMIT
=========================================*/

document.querySelector("form").addEventListener("submit",function(){

    let btn=document.querySelector("button[name='submit']");

    btn.disabled=true;

    btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Submitting...';

});

</script>

</body>

</html>