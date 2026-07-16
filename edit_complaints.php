<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Complaint ID.");
}

$complaint_id = mysqli_real_escape_string($conn, $_GET['id']);

/*=========================================
    FETCH COMPLAINT
=========================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM complaints
WHERE complaint_id=?
AND consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"ss",$complaint_id,$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Complaint not found.");
}

$complaint = mysqli_fetch_assoc($result);

/*=========================================
    ALLOW EDIT ONLY IF PENDING
=========================================*/

if($complaint['status']!="Pending"){
    die("Only pending complaints can be edited.");
}

/*=========================================
    LOAD CONSUMER DETAILS
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

$name = $user['name'];
$mobile = $user['mobile'];

/*=========================================
    DEFAULT VALUES
=========================================*/

$category = $complaint['category'];
$description = $complaint['description'];
$address = $complaint['address'];
$latitude = $complaint['latitude'];
$longitude = $complaint['longitude'];
$photo = $complaint['photo'];

$success="";
$error="";

/*=========================================
    UPDATE COMPLAINT
=========================================*/

if(isset($_POST['update'])){

    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $address = trim($_POST['address']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    /* Photo Upload */

    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){

        $uploadDir="../uploads/complaints/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0777,true);
        }

        $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));

        if(in_array($ext,['jpg','jpeg','png','webp'])){

            $newPhoto=time()."_".$_FILES['photo']['name'];

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $uploadDir.$newPhoto
            );

            if(!empty($photo) && file_exists($uploadDir.$photo)){
                unlink($uploadDir.$photo);
            }

            $photo=$newPhoto;

        }else{

            $error="Only JPG, JPEG, PNG and WEBP files are allowed.";

        }

    }

    if(empty($error)){

        $update=mysqli_prepare($conn,"
        UPDATE complaints
        SET
            category=?,
            description=?,
            address=?,
            latitude=?,
            longitude=?,
            photo=?
        WHERE complaint_id=?
        ");

        mysqli_stmt_bind_param(
            $update,
            "sssssss",
            $category,
            $description,
            $address,
            $latitude,
            $longitude,
            $photo,
            $complaint_id
        );

        if(mysqli_stmt_execute($update)){

            $_SESSION['success']="Complaint updated successfully.";

            header("Location: complaint.php");

            exit();

        }else{

            $error=mysqli_error($conn);

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Complaint | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef4fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:linear-gradient(90deg,#0b4ea2,#1565c0,#1e88e5);
    box-shadow:0 4px 15px rgba(0,0,0,.15);
}

.navbar-brand{
    color:#fff!important;
    font-weight:700;
    font-size:22px;
}

.main-card{
    margin-top:35px;
    background:#fff;
    border-radius:20px;
    border:none;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header-main{
    background:linear-gradient(135deg,#0b4ea2,#1976d2);
    color:#fff;
    padding:30px;
}

.info-card{
    background:#f8fbff;
    border:1px solid #d8e8ff;
    border-radius:15px;
    padding:20px;
    margin-bottom:25px;
}

.info-item{
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,.05);
    height:100%;
}

.info-item small{
    color:#666;
    display:block;
}

.info-item strong{
    font-size:16px;
}

.form-control,
.form-select{
    min-height:50px;
    border-radius:12px;
}

textarea.form-control{
    min-height:130px;
}

.btn{
    border-radius:12px;
}

.preview{
    width:220px;
    border-radius:15px;
    border:3px solid #ddd;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg">

<div class="container">

<a href="dashboard.php" class="navbar-brand text-decoration-none">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Portal

</a>

</div>

</nav>

<div class="container">

<div class="main-card">

<div class="card-header-main">

<h2>

<i class="bi bi-pencil-square"></i>

Edit Complaint

</h2>

<p class="mb-0">

Update your complaint before it is processed.

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

<div class="info-card">

<h5 class="mb-4">

<i class="bi bi-person-circle"></i>

Consumer Information

</h5>

<div class="row g-3">

<div class="col-md-4">

<div class="info-item">

<small>Consumer Number</small>

<strong><?= htmlspecialchars($consumer_no) ?></strong>

</div>

</div>

<div class="col-md-4">

<div class="info-item">

<small>Name</small>

<strong><?= htmlspecialchars($name) ?></strong>

</div>

</div>

<div class="col-md-4">

<div class="info-item">

<small>Mobile</small>

<strong><?= htmlspecialchars($mobile) ?></strong>

</div>

</div>

</div>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-4">

<label class="form-label">

Complaint ID

</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($complaint_id) ?>"
readonly>

</div>

<div class="col-md-6 mb-4">

<label class="form-label">

Outage Type

</label>

<select
name="category"
class="form-select"
required>

<?php

$options=[
"Complete Power Failure",
"Partial Power Failure",
"Transformer Failure",
"11 KV Line Fault",
"33 KV Line Fault",
"Feeder Shutdown",
"Fuse Call",
"Tree Touching Line",
"Broken Electric Pole",
"Snapped Conductor",
"Scheduled Maintenance",
"Other"
];

foreach($options as $option){

?>

<option value="<?= $option ?>" <?= $category==$option?"selected":"" ?>>

<?= $option ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-12 mb-4">

<label class="form-label">

Complaint Description

</label>

<textarea
name="description"
class="form-control"
required><?= htmlspecialchars($description) ?></textarea>

</div>

<div class="col-12 mb-4">

<label class="form-label">

Address

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
class="form-control"
value="<?= htmlspecialchars($latitude) ?>"
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
class="form-control"
value="<?= htmlspecialchars($longitude) ?>"
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

<div class="col-md-6 mb-4">

<label>

Current Photo

</label>

<br>

<?php if(!empty($photo)){ ?>

<img
src="../uploads/complaints/<?= htmlspecialchars($photo) ?>"
class="preview img-fluid">

<?php }else{ ?>

<div class="text-muted">

No image uploaded.

</div>

<?php } ?>

</div>

<div class="col-md-6 mb-4">

<label>

Replace Photo

</label>

<input
type="file"
name="photo"
accept=".jpg,.jpeg,.png,.webp"
class="form-control">

<div class="form-text">

Leave empty to keep the existing image.

</div>

</div>

<div class="col-12 text-center mt-4">

<button
type="submit"
name="update"
class="btn btn-primary btn-lg">

<i class="bi bi-save"></i>

Update Complaint

</button>

<a
href="complaint.php"
class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-arrow-left-circle"></i>

Cancel

</a>

</div>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

        },

        function(error)
        {

            switch(error.code)
            {

                case error.PERMISSION_DENIED:

                    alert("Location permission denied.");
                    break;

                case error.POSITION_UNAVAILABLE:

                    alert("Location information is unavailable.");
                    break;

                case error.TIMEOUT:

                    alert("Location request timed out.");
                    break;

                default:

                    alert("Unable to fetch current location.");

            }

        },

        {

            enableHighAccuracy:true,
            timeout:10000,
            maximumAge:0

        });

    }
    else
    {

        alert("Geolocation is not supported by your browser.");

    }

}

/*=========================================
    IMAGE PREVIEW
=========================================*/

document.querySelector("input[name='photo']").addEventListener("change",function(e){

    const file=e.target.files[0];

    if(!file) return;

    const reader=new FileReader();

    reader.onload=function(event){

        let img=document.querySelector(".preview");

        if(!img){

            img=document.createElement("img");

            img.className="preview img-fluid";

            e.target.parentNode.insertAdjacentElement("beforebegin",img);

        }

        img.src=event.target.result;

    };

    reader.readAsDataURL(file);

});

/*=========================================
    FORM VALIDATION
=========================================*/

document.querySelector("form").addEventListener("submit",function(e){

    let category=document.querySelector("select[name='category']").value.trim();

    let description=document.querySelector("textarea[name='description']").value.trim();

    let address=document.querySelector("textarea[name='address']").value.trim();

    if(category=="")
    {

        alert("Please select an outage type.");

        e.preventDefault();

        return;

    }

    if(description.length<10)
    {

        alert("Complaint description must contain at least 10 characters.");

        e.preventDefault();

        return;

    }

    if(address.length<5)
    {

        alert("Please enter a valid address.");

        e.preventDefault();

        return;

    }

});

</script>

</body>

</html>