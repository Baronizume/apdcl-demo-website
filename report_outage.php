<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    GET LOGGED-IN CONSUMER
=========================================*/

$consumer_no = $_SESSION['consumer'];

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($userQuery)==0){
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    CONSUMER DETAILS
=========================================*/

$name          = $user['name'];
$email         = $user['email'];
$mobile        = $user['mobile'];
$address       = $user['address'];

$district      = $user['district'] ?? "";
$zone          = $user['zone'] ?? "";
$circle        = $user['circle'] ?? "";
$sub_division  = $user['sub_division'] ?? "";

/*=========================================
    GENERATE COMPLAINT ID
=========================================*/

$year = date("Y");

$result = mysqli_query($conn,"
SELECT id
FROM complaints
ORDER BY id DESC
LIMIT 1
");

if(mysqli_num_rows($result)>0){

    $row = mysqli_fetch_assoc($result);

    $nextId = $row['id'] + 1;

}else{

    $nextId = 1;

}

$complaint_id = "CMP".$year.str_pad($nextId,5,"0",STR_PAD_LEFT);

/*=========================================
    DEFAULT VALUES
=========================================*/

$category     = "";
$description  = "";
$latitude     = "";
$longitude    = "";

$success = "";
$error   = "";
/*=========================================
    SUBMIT COMPLAINT
=========================================*/

if(isset($_POST['submit_complaint']))
{

    $category    = mysqli_real_escape_string($conn,$_POST['category']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $address     = mysqli_real_escape_string($conn,$_POST['address']);

    $latitude  = !empty($_POST['latitude']) ? $_POST['latitude'] : NULL;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : NULL;

    /*=====================================
        PHOTO UPLOAD
    =====================================*/

    $photo = "";

    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){

        $uploadDir = "../uploads/complaints/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0777,true);
        }

        $extension = strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));

        $allowed = array("jpg","jpeg","png","webp");

        if(in_array($extension,$allowed)){

            $photo = time()."_".basename($_FILES['photo']['name']);

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $uploadDir.$photo
            );

        }else{

            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        }

    }

    /*=====================================
        SAVE COMPLAINT
    =====================================*/

    if(empty($error)){

        $insert = mysqli_query($conn,"
        INSERT INTO complaints
        (
            complaint_id,
            consumer_no,
            name,
            mobile,
            category,
            description,
            address,
            district,
            zone,
            circle,
            sub_division,
            latitude,
            longitude,
            photo,
            status
        )
        VALUES
        (
            '$complaint_id',
            '$consumer_no',
            '$name',
            '$mobile',
            '$category',
            '$description',
            '$address',
            '$district',
            '$zone',
            '$circle',
            '$sub_division',
            ".($latitude===NULL?"NULL":"'".$latitude."'").",
            ".($longitude===NULL?"NULL":"'".$longitude."'").",
            '$photo',
            'Pending'
        )
        ");

        if($insert){

            $success = "Complaint submitted successfully. Complaint ID : ".$complaint_id;

            /* Generate next Complaint ID */

            $nextId++;

            $complaint_id = "CMP".date("Y").str_pad($nextId,5,"0",STR_PAD_LEFT);

            $category = "";
            $description = "";
            $latitude = "";
            $longitude = "";

        }else{

            $error = mysqli_error($conn);

        }

    }

}

/*=========================================
    MY PREVIOUS COMPLAINTS
=========================================*/

$myComplaints = mysqli_query($conn,"
SELECT
    complaint_id,
    category,
    description,
    status,
    created_at
FROM complaints
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
");

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>APDCL Consumer Outage Reporting</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{

background:#eef4fb;

}

/*=========================
NAVBAR
=========================*/

.navbar{

background:linear-gradient(90deg,#0b4ea2,#1565c0,#1976d2);

padding:12px 25px;

box-shadow:0 4px 15px rgba(0,0,0,.15);

}

.navbar-brand{

font-size:24px;

font-weight:700;

color:#fff !important;

display:flex;

align-items:center;

gap:15px;

}

.navbar-brand img{

width:60px;

height:60px;

background:#fff;

border-radius:50%;

padding:5px;

}

/*=========================
PAGE
=========================*/

.page-content{

padding:35px;

}

/*=========================
MAIN CARD
=========================*/

.main-card{

background:#fff;

border:none;

border-radius:22px;

overflow:hidden;

box-shadow:0 10px 30px rgba(0,0,0,.08);

}

/*=========================
HEADER
=========================*/

.page-header{

background:linear-gradient(135deg,#0b4ea2,#1e88e5);

color:#fff;

padding:35px;

text-align:center;

}

.page-header h2{

font-weight:700;

margin-bottom:10px;

}

.page-header p{

margin:0;

opacity:.95;

}

/*=========================
CONSUMER CARD
=========================*/

.consumer-card{

background:#f8fbff;

border:1px solid #d8e8ff;

border-radius:18px;

padding:25px;

margin-bottom:30px;

}

.consumer-card h5{

color:#0b4ea2;

font-weight:700;

margin-bottom:20px;

}

.consumer-item{

padding:15px;

background:#fff;

border-radius:12px;

box-shadow:0 3px 10px rgba(0,0,0,.05);

height:100%;

}

.consumer-item small{

display:block;

color:#777;

font-size:13px;

margin-bottom:5px;

}

.consumer-item strong{

font-size:16px;

color:#222;

}

/*=========================
FORM
=========================*/

.form-control,

.form-select{

border-radius:12px;

min-height:50px;

}

textarea.form-control{

min-height:130px;

}

label{

font-weight:600;

margin-bottom:8px;

color:#0b4ea2;

}

/*=========================
BUTTON
=========================*/

.btn-primary{

background:#1565c0;

border:none;

border-radius:12px;

padding:12px 28px;

}

.btn-secondary{

border-radius:12px;

padding:12px 28px;

}

/*=========================
ALERT
=========================*/

.alert{

border-radius:12px;

}

/*=========================
SECTION TITLE
=========================*/

.section-title{

font-size:22px;

font-weight:700;

color:#0b4ea2;

margin-bottom:25px;

}

/*=========================
RESPONSIVE
=========================*/

@media(max-width:768px){

.page-content{

padding:15px;

}

.page-header{

padding:25px;

}

.navbar-brand{

font-size:18px;

}

}

</style>

</head>

<body>

<!--=====================================
NAVBAR
======================================-->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<a class="navbar-brand" href="#">

<img src="../assets/images/logo-circle.png">

<div>

<div>APDCL Consumer Portal</div>

<small style="font-size:13px;font-weight:400;">

Outage Reporting System

</small>

</div>

</a>

</div>

</nav>

<div class="page-content">

<div class="container">

<div class="main-card">

<!--=====================================
HEADER
======================================-->

<div class="page-header">

<h2>

<i class="bi bi-lightning-charge-fill"></i>

Electricity Outage Reporting

</h2>

<p>

Report power outages directly to APDCL and track their resolution.

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

<!--=====================================
CONSUMER INFORMATION
======================================-->

<div class="consumer-card">

<h5>

<i class="bi bi-person-badge-fill"></i>

Consumer Information

</h5>

<div class="row g-3">

<div class="col-lg-4">

<div class="consumer-item">

<small>Consumer Number</small>

<strong><?= htmlspecialchars($consumer_no) ?></strong>

</div>

</div>

<div class="col-lg-4">

<div class="consumer-item">

<small>Consumer Name</small>

<strong><?= htmlspecialchars($name) ?></strong>

</div>

</div>

<div class="col-lg-4">

<div class="consumer-item">

<small>Mobile Number</small>

<strong><?= htmlspecialchars($mobile) ?></strong>

</div>

</div>

<div class="col-lg-3">

<div class="consumer-item">

<small>District</small>

<strong><?= htmlspecialchars($district) ?></strong>

</div>

</div>

<div class="col-lg-3">

<div class="consumer-item">

<small>Zone</small>

<strong><?= htmlspecialchars($zone) ?></strong>

</div>

</div>

<div class="col-lg-3">

<div class="consumer-item">

<small>Circle</small>

<strong><?= htmlspecialchars($circle) ?></strong>

</div>

</div>

<div class="col-lg-3">

<div class="consumer-item">

<small>Sub-Division</small>

<strong><?= htmlspecialchars($sub_division) ?></strong>

</div>

</div>

</div>

</div>

<h4 class="section-title">

<i class="bi bi-pencil-square"></i>

Register New Outage

</h4>

<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-4">

<label>

Complaint ID

</label>

<input
type="text"
class="form-control"
value="<?= $complaint_id ?>"
readonly>

</div>

<div class="col-md-6 mb-4">

<label>

Outage Type

</label>

<select
name="category"
class="form-select"
required>

<option value="">Select Outage Type</option>

<option>Complete Power Failure</option>

<option>Partial Power Failure</option>

<option>Transformer Failure</option>

<option>11 KV Line Fault</option>

<option>33 KV Line Fault</option>

<option>Feeder Shutdown</option>

<option>Fuse Call</option>

<option>Tree Touching Line</option>

<option>Broken Electric Pole</option>

<option>Snapped Conductor</option>

<option>Scheduled Maintenance</option>

<option>Other</option>

</select>

</div>

<div class="col-md-6 mb-4">

<label>

Priority

</label>

<select
name="priority"
class="form-select">

<option value="Low">Low</option>

<option value="Medium" selected>Medium</option>

<option value="High">High</option>

</select>

</div>

<div class="col-md-6 mb-4">

<label>

Upload Photo

</label>

<input
type="file"
name="photo"
accept=".jpg,.jpeg,.png,.webp"
class="form-control">

<div class="form-text">

Upload transformer, pole, wire or outage image.

</div>

</div>

<div class="col-12 mb-4">

<label>

Complaint Description

</label>

<textarea
name="description"
class="form-control"
required><?= htmlspecialchars($description) ?></textarea>

</div>

<div class="col-12 mb-4">

<label>

Outage Location / Address

</label>

<textarea
name="address"
class="form-control"
required><?= htmlspecialchars($address) ?></textarea>

</div>

<div class="col-md-5 mb-4">

<label>

Latitude

</label>

<input
type="text"
id="latitude"
name="latitude"
value="<?= htmlspecialchars($latitude) ?>"
class="form-control"
readonly>

</div>

<div class="col-md-5 mb-4">

<label>

Longitude

</label>

<input
type="text"
id="longitude"
name="longitude"
value="<?= htmlspecialchars($longitude) ?>"
class="form-control"
readonly>

</div>

<div class="col-md-2 mb-4 d-flex align-items-end">

<button
type="button"
class="btn btn-success w-100"
onclick="getLocation()">

<i class="bi bi-geo-alt-fill"></i>

GPS

</button>

</div>

<div class="col-12 text-center mt-4">

<button
type="submit"
name="submit_complaint"
class="btn btn-primary btn-lg">

<i class="bi bi-send-fill"></i>

Submit Report

</button>

<a
href="dashboard.php"
class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-arrow-left-circle"></i>

Back

</a>

</div>

</div>

</form>

<hr class="my-5">

<div class="card shadow border-0 rounded-4">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-clock-history"></i>

My Previous Complaints

</h4>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
    <th>Complaint ID</th>
    <th>Category</th>
    <th>Description</th>
    <th>Actions</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

</thead>

<tbody>

<?php

if(mysqli_num_rows($myComplaints)>0)
{

while($row=mysqli_fetch_assoc($myComplaints))
{

?>

<tr>

<td>

<strong>

<?= htmlspecialchars($row['complaint_id']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($row['category']) ?>

</td>

<td>

<?= htmlspecialchars(substr($row['description'],0,60)) ?>

...

</td>

<td>

<?php if($row['status']=="Pending"){ ?>

<a href="edit_complaint.php?id=<?= urlencode($row['complaint_id']) ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>
Edit

</a>

<?php } ?>

<a href="track_complaint.php?id=<?= urlencode($row['complaint_id']) ?>"
class="btn btn-info btn-sm">

<i class="bi bi-search"></i>
Track

</a>

</td>

<td>

<?php

switch($row['status'])
{

case "Pending":

echo "<span class='badge bg-danger'>Pending</span>";

break;

case "In Progress":

echo "<span class='badge bg-warning text-dark'>In Progress</span>";

break;

case "Resolved":

echo "<span class='badge bg-success'>Resolved</span>";

break;

default:

echo "<span class='badge bg-secondary'>Unknown</span>";

}

?>

</td>


<td>

<?= date("d M Y",strtotime($row['created_at'])) ?>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="5" class="text-center text-muted">

No complaints submitted yet.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

<script>

/*=========================================
        GET CURRENT LOCATION
=========================================*/

function getLocation()
{

    if(navigator.geolocation)
    {

        navigator.geolocation.getCurrentPosition(

        function(position)
        {

            document.getElementById("latitude").value =
            position.coords.latitude.toFixed(6);

            document.getElementById("longitude").value =
            position.coords.longitude.toFixed(6);

            alert("Current location captured successfully.");

        },

        function(error)
        {

            alert("Unable to fetch your location.\nPlease enable GPS/location permission.");

        });

    }
    else
    {

        alert("Your browser does not support Geolocation.");

    }

}

</script>

</html>

</body