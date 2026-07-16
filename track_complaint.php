<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$success = "";
$error   = "";

/*=========================================================
    GET COMPLAINT ID
=========================================================*/

if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id = (int)$_GET['id'];

} else {

    $latest = mysqli_query($conn,"
        SELECT id
        FROM complaint
        WHERE consumer_no='$consumer_no'
        ORDER BY id DESC
        LIMIT 1
    ");

    if (!$latest || mysqli_num_rows($latest) == 0) {
        die("No complaint found.");
    }

    $row = mysqli_fetch_assoc($latest);

    $id = $row['id'];
}

/*=========================================================
    LOAD CONSUMER
=========================================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$userResult = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($userResult)==0){
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userResult);

$name           = $user['name'] ?? "";
$email          = $user['email'] ?? "";
$mobile         = $user['mobile'] ?? "";
$address        = $user['address'] ?? "";
$district       = $user['district'] ?? "";
$zone           = $user['zone'] ?? "";
$circle         = $user['circle'] ?? "";
$sub_division   = $user['sub_division'] ?? "";

/*=========================================================
    LOAD COMPLAINT
=========================================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM complaint
WHERE id=?
AND consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"is",$id,$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Complaint not found.");
}

$complaint = mysqli_fetch_assoc($result);

/*=========================================================
    EDIT PERMISSION
=========================================================*/

$editable = in_array(
    $complaint['status'],
    ["Pending","Assigned"]
);

/*=========================================================
    UPDATE COMPLAINT
=========================================================*/

if(isset($_POST['update']) && $editable){

    $category    = trim($_POST['category']);
    $priority    = trim($_POST['priority']);
    $subject     = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $address     = trim($_POST['address']);
    $latitude    = trim($_POST['latitude']);
    $longitude   = trim($_POST['longitude']);

    $photo = $complaint['photo'];

    /* PHOTO UPLOAD */

    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){

        $folder="../uploads/complaint/";

        if(!is_dir($folder)){
            mkdir($folder,0777,true);
        }

        $extension=strtolower(pathinfo(
            $_FILES['photo']['name'],
            PATHINFO_EXTENSION
        ));

        $allowed=["jpg","jpeg","png","webp"];

        if(in_array($extension,$allowed)){

            $photo=uniqid("CMP_").".".$extension;

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $folder.$photo
            );

        }

    }

    $update=mysqli_prepare($conn,"
    UPDATE complaint
    SET
        category=?,
        priority=?,
        subject=?,
        description=?,
        address=?,
        latitude=?,
        longitude=?,
        photo=?
    WHERE id=?
    ");

    mysqli_stmt_bind_param(
        $update,
        "ssssssssi",
        $category,
        $priority,
        $subject,
        $description,
        $address,
        $latitude,
        $longitude,
        $photo,
        $id
    );

    if(mysqli_stmt_execute($update)){

        $_SESSION['success']="Complaint updated successfully.";

        header("Location: track_complaint.php?id=".$id);

        exit();

    }else{

        $error=mysqli_error($conn);

    }

}

/*=========================================================
    SUCCESS MESSAGE
=========================================================*/

if(isset($_SESSION['success'])){

    $success=$_SESSION['success'];

    unset($_SESSION['success']);

}

/*=========================================================
    STATUS BADGE
=========================================================*/

switch($complaint['status']){

    case "Pending":
        $badge="warning";
        break;

    case "Assigned":
        $badge="info";
        break;

    case "In Progress":
        $badge="primary";
        break;

    case "Resolved":
        $badge="success";
        break;

    case "Rejected":
        $badge="danger";
        break;

    default:
        $badge="secondary";
}

/*=========================================================
    PHOTO
=========================================================*/

$photoPath="../assets/images/no-image.png";

if(
    !empty($complaint['photo']) &&
    file_exists("../uploads/complaint/".$complaint['photo'])
){
    $photoPath="../uploads/complaint/".$complaint['photo'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Track Complaint | APDCL Consumer Portal</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef3fb;
    font-family:'Segoe UI',sans-serif;
}

/*==========================
NAVBAR
==========================*/

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1e88e5);
    box-shadow:0 5px 18px rgba(0,0,0,.15);
}

.navbar-brand{
    color:#fff !important;
    font-size:24px;
    font-weight:700;
}

.navbar .btn{
    border-radius:10px;
}

/*==========================
HEADER
==========================*/

.header-card{
    background:linear-gradient(135deg,#1565c0,#42a5f5);
    color:#fff;
    border:none;
    border-radius:20px;
    padding:35px;
    margin-top:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.header-card h2{
    font-weight:700;
    margin-bottom:10px;
}

.header-card p{
    opacity:.95;
}

/*==========================
CARD
==========================*/

.main-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.main-card .card-header{
    font-size:18px;
    font-weight:700;
}

/*==========================
FORM
==========================*/

.form-label{
    font-weight:600;
}

.form-control,
.form-select{
    border-radius:12px;
    min-height:48px;
}

textarea.form-control{
    min-height:130px;
}

/*==========================
STATUS BADGE
==========================*/

.badge{
    padding:10px 20px;
    font-size:14px;
    border-radius:40px;
}

/*==========================
BUTTONS
==========================*/

.btn{
    border-radius:10px;
}

.btn-lg{
    padding:12px 24px;
}

/*==========================
TABLE
==========================*/

.table th{
    width:40%;
    font-weight:600;
}

.table td{
    color:#444;
}

/*==========================
PHOTO
==========================*/

.preview{
    width:100%;
    max-height:320px;
    object-fit:cover;
    border-radius:15px;
    border:2px solid #dee2e6;
}

/*==========================
MAP
==========================*/

#map{
    width:100%;
    height:420px;
    border-radius:15px;
    border:2px solid #dee2e6;
}

/*==========================
TIMELINE
==========================*/

.timeline{
    position:relative;
    padding-left:45px;
}

.timeline::before{
    content:"";
    position:absolute;
    left:15px;
    top:0;
    width:4px;
    height:100%;
    background:#d9d9d9;
}

.timeline-item{
    position:relative;
    margin-bottom:35px;
}

.timeline-dot{
    position:absolute;
    left:-38px;
    width:26px;
    height:26px;
    border-radius:50%;
    background:#0d6efd;
    border:4px solid #fff;
    box-shadow:0 0 0 2px #0d6efd;
}

.timeline-content{
    background:#fff;
    border-radius:15px;
    padding:18px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.timeline-content h5{
    color:#0d47a1;
    font-weight:700;
    margin-bottom:8px;
}

.timeline-content p{
    margin-bottom:5px;
}

/*==========================
SUCCESS
==========================*/

.alert{
    border-radius:12px;
}

/*==========================
RESPONSIVE
==========================*/

@media(max-width:768px){

    .header-card{
        text-align:center;
        padding:25px;
    }

    #map{
        height:300px;
    }

}

@media print{

    .navbar,
    .btn{
        display:none !important;
    }

    body{
        background:#fff;
    }

}

</style>

</head>

<body>

<!-- ==========================
NAVBAR
========================== -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="dashboard.php">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Portal

</a>

<div>

<a href="complaint.php" class="btn btn-light me-2">

<i class="bi bi-arrow-left-circle-fill"></i>

Back

</a>

<button
type="button"
onclick="window.print();"
class="btn btn-warning">

<i class="bi bi-printer-fill"></i>

Print

</button>

</div>

</div>

</nav>

<div class="container">

<?php if($success!=""){ ?>

<div class="alert alert-success mt-4">

<i class="bi bi-check-circle-fill"></i>

<?= htmlspecialchars($success) ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger mt-4">

<i class="bi bi-exclamation-triangle-fill"></i>

<?= htmlspecialchars($error) ?>

</div>

<?php } ?>

<div class="header-card">

<div class="row align-items-center">

<div class="col-lg-8">

<h2>

<i class="bi bi-geo-alt-fill"></i>

Track & Edit Complaint

</h2>

<p>

Track your complaint status, update complaint details, upload a new photo, and modify the complaint location using the interactive map.

</p>

</div>

<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

<span class="badge bg-<?= $badge ?>">

<?= htmlspecialchars($complaint['status']) ?>

</span>

<h5 class="mt-3">

Complaint ID

</h5>

<h4>

<?= htmlspecialchars($complaint['complaint_id']) ?>

</h4>

</div>

</div>

</div>

<!-- ==========================================
EDIT COMPLAINT FORM
========================================== -->

<form method="POST" enctype="multipart/form-data">

<div class="row mt-4">

<!-- LEFT COLUMN -->

<div class="col-lg-7">

<div class="card main-card mb-4">

<div class="card-header bg-primary text-white">

<i class="bi bi-pencil-square"></i>

Edit Complaint Details

</div>

<div class="card-body">

<div class="row">

<!-- Consumer Number -->

<div class="col-md-6 mb-3">

<label class="form-label">Consumer Number</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($consumer_no) ?>"
readonly>

</div>

<!-- Consumer Name -->

<div class="col-md-6 mb-3">

<label class="form-label">Consumer Name</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($name) ?>"
readonly>

</div>

<!-- Category -->

<div class="col-md-6 mb-3">

<label class="form-label">Complaint Category</label>

<select
name="category"
class="form-select"
<?= $editable ? "" : "disabled" ?>
required>

<?php

$categories=[
"Power Failure",
"Low Voltage",
"High Voltage",
"Transformer Fault",
"Meter Problem",
"Billing Issue",
"Pole Damage",
"Wire Damage",
"Street Light",
"Other"
];

foreach($categories as $cat){

?>

<option
value="<?= $cat ?>"
<?= ($complaint['category']==$cat) ? "selected" : "" ?>>

<?= $cat ?>

</option>

<?php } ?>

</select>

</div>

<!-- Priority -->

<div class="col-md-6 mb-3">

<label class="form-label">Priority</label>

<select
name="priority"
class="form-select"
<?= $editable ? "" : "disabled" ?>>

<option value="Low"
<?= ($complaint['priority']=="Low")?"selected":"" ?>>

Low

</option>

<option value="Medium"
<?= ($complaint['priority']=="Medium")?"selected":"" ?>>

Medium

</option>

<option value="High"
<?= ($complaint['priority']=="High")?"selected":"" ?>>

High

</option>

</select>

</div>

<!-- Subject -->

<div class="col-12 mb-3">

<label class="form-label">Subject</label>

<input
type="text"
name="subject"
class="form-control"
value="<?= htmlspecialchars($complaint['subject']) ?>"
<?= $editable ? "" : "readonly" ?>
required>

</div>

<!-- Description -->

<div class="col-12 mb-3">

<label class="form-label">Description</label>

<textarea
name="description"
class="form-control"
rows="5"
<?= $editable ? "" : "readonly" ?>
required><?= htmlspecialchars($complaint['description']) ?></textarea>

</div>

<!-- Address -->

<div class="col-12 mb-3">

<label class="form-label">Complaint Address</label>

<textarea
name="address"
class="form-control"
rows="3"
<?= $editable ? "" : "readonly" ?>
required><?= htmlspecialchars($complaint['address']) ?></textarea>

</div>

<!-- Upload Photo -->

<div class="col-12">

<label class="form-label">

Replace Complaint Photo

</label>

<input
type="file"
name="photo"
id="photo"
class="form-control"
accept=".jpg,.jpeg,.png,.webp"
<?= $editable ? "" : "disabled" ?>>

</div>

</div>

</div>

</div>

</div>

<!-- ==========================================
RIGHT COLUMN
========================================== -->

<div class="col-lg-5">

<!-- STATUS CARD -->

<div class="card main-card mb-4">

<div class="card-header bg-success text-white">

<i class="bi bi-info-circle-fill"></i>

Complaint Status

</div>

<div class="card-body">

<div class="text-center mb-3">

<span class="badge bg-<?= $badge ?> fs-6 px-4 py-3">

<?= htmlspecialchars($complaint['status']) ?>

</span>

</div>

<table class="table table-borderless">

<tr>

<th>Complaint ID</th>

<td><?= htmlspecialchars($complaint['complaint_id']) ?></td>

</tr>

<tr>

<th>Submitted</th>

<td><?= date("d M Y h:i A",strtotime($complaint['created_at'])) ?></td>

</tr>

<tr>

<th>Assigned To</th>

<td>

<?= !empty($complaint['assigned_to']) ? htmlspecialchars($complaint['assigned_to']) : "Not Assigned"; ?>

</td>

</tr>

<tr>

<th>Remarks</th>

<td>

<?= !empty($complaint['remarks']) ? nl2br(htmlspecialchars($complaint['remarks'])) : "No Remarks"; ?>

</td>

</tr>

</table>

</div>

</div>

<!-- PHOTO -->

<div class="card main-card mb-4">

<div class="card-header bg-warning">

<i class="bi bi-image-fill"></i>

Complaint Photo

</div>

<div class="card-body text-center">

<img
src="<?= $photoPath ?>"
id="previewImage"
class="preview">

</div>

</div>

<!-- MAP -->

<div class="card main-card mb-4">

<div class="card-header bg-info text-white">

<i class="bi bi-geo-alt-fill"></i>

Complaint Location

</div>

<div class="card-body">

<div id="map"></div>

<div class="row mt-3">

<div class="col-md-6 mb-3">

<label class="form-label">

Latitude

</label>

<input
type="text"
name="latitude"
id="latitude"
class="form-control"
value="<?= htmlspecialchars($complaint['latitude']) ?>"
<?= $editable ? "" : "readonly" ?>>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Longitude

</label>

<input
type="text"
name="longitude"
id="longitude"
class="form-control"
value="<?= htmlspecialchars($complaint['longitude']) ?>"
<?= $editable ? "" : "readonly" ?>>

</div>

</div>

<?php if($editable){ ?>

<button
type="button"
class="btn btn-success w-100"
onclick="getLocation()">

<i class="bi bi-crosshair"></i>

Detect Current Location

</button>

<?php } ?>

</div>

</div>

<!-- ACTION BUTTONS -->

<div class="card main-card">

<div class="card-body text-center">

<?php if($editable){ ?>

<button
type="submit"
name="update"
class="btn btn-primary btn-lg">

<i class="bi bi-save-fill"></i>

Update Complaint

</button>

<?php } ?>

<a
href="complaint.php"
class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-arrow-left-circle-fill"></i>

Back

</a>

</div>

</div>

</div>

</div>

</form>

<!-- ==========================================
COMPLAINT TRACKING TIMELINE
========================================== -->

<div class="card main-card mt-4 mb-4">

<div class="card-header bg-dark text-white">

<h4 class="mb-0">

<i class="bi bi-diagram-3-fill"></i>

Complaint Tracking Timeline

</h4>

</div>

<div class="card-body">

<div class="timeline">

<!-- Submitted -->

<div class="timeline-item">

<div class="timeline-dot bg-success"></div>

<div class="timeline-content">

<h5>

<i class="bi bi-check-circle-fill text-success"></i>

Complaint Submitted

</h5>

<p>

Your complaint has been successfully registered.

</p>

<small class="text-muted">

<?= date("d M Y h:i A",strtotime($complaint['created_at'])) ?>

</small>

</div>

</div>

<!-- Assigned -->

<div class="timeline-item">

<div class="timeline-dot <?= ($complaint['status']!="Pending") ? 'bg-primary' : 'bg-secondary'; ?>"></div>

<div class="timeline-content">

<h5>

<i class="bi bi-person-check-fill"></i>

Assigned to Officer

</h5>

<p>

<?php

if($complaint['status']=="Pending"){

    echo "Waiting for assignment.";

}else{

    echo "Assigned to <strong>".htmlspecialchars($complaint['assigned_to'] ?: "Maintenance Officer")."</strong>.";

}

?>

</p>

</div>

</div>

<!-- Progress -->

<div class="timeline-item">

<div class="timeline-dot

<?=

($complaint['status']=="In Progress" || $complaint['status']=="Resolved")

?

'bg-warning'

:

'bg-secondary'

?>

"></div>

<div class="timeline-content">

<h5>

<i class="bi bi-tools"></i>

Work In Progress

</h5>

<p>

<?php

switch($complaint['status']){

case "Pending":

echo "Work has not started yet.";

break;

case "Assigned":

echo "Waiting for maintenance team.";

break;

case "In Progress":

echo "Maintenance team is repairing the issue.";

break;

case "Resolved":

echo "Repair work completed successfully.";

break;

default:

echo "No update.";

}

?>

</p>

</div>

</div>

<!-- Resolved -->

<div class="timeline-item">

<div class="timeline-dot

<?=

($complaint['status']=="Resolved")

?

'bg-success'

:

'bg-secondary'

?>

"></div>

<div class="timeline-content">

<h5>

<i class="bi bi-patch-check-fill"></i>

Complaint Resolved

</h5>

<p>

<?php

if($complaint['status']=="Resolved"){

    echo "Your complaint has been resolved successfully.";

}else{

    echo "Waiting for resolution.";

}

?>

</p>

<?php if($complaint['status']=="Resolved"){ ?>

<small class="text-success">

Last Updated :

<?= date("d M Y h:i A",strtotime($complaint['updated_at'])) ?>

</small>

<?php } ?>

</div>

</div>

</div>

</div>

</div>

<!-- ==========================================
OFFICER REMARKS
========================================== -->

<div class="card main-card mb-4">

<div class="card-header bg-warning">

<h5 class="mb-0">

<i class="bi bi-chat-left-text-fill"></i>

Officer Remarks

</h5>

</div>

<div class="card-body">

<?php if(!empty($complaint['remarks'])){ ?>

<div class="alert alert-warning mb-0">

<?= nl2br(htmlspecialchars($complaint['remarks'])) ?>

</div>

<?php }else{ ?>

<div class="alert alert-secondary mb-0">

<i class="bi bi-info-circle-fill"></i>

No remarks available yet.

</div>

<?php } ?>

</div>

</div>

<!-- ==========================================
LEAFLET MAP + JAVASCRIPT
========================================== -->

<script>

let defaultLat = parseFloat(document.getElementById("latitude").value);
let defaultLng = parseFloat(document.getElementById("longitude").value);

if(isNaN(defaultLat)) defaultLat = 26.1445;
if(isNaN(defaultLng)) defaultLng = 91.7362;

/*=========================
INITIALIZE MAP
=========================*/

var map = L.map('map').setView([defaultLat, defaultLng], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{

    maxZoom:19,

    attribution:'© OpenStreetMap'

}).addTo(map);

/*=========================
DRAGGABLE MARKER
=========================*/

var marker = L.marker([defaultLat, defaultLng],{

    draggable:true

}).addTo(map);

marker.on("dragend",function(){

    var p = marker.getLatLng();

    document.getElementById("latitude").value = p.lat.toFixed(6);

    document.getElementById("longitude").value = p.lng.toFixed(6);

});

/*=========================
CLICK MAP TO CHANGE LOCATION
=========================*/

map.on("click",function(e){

    marker.setLatLng(e.latlng);

    document.getElementById("latitude").value =
    e.latlng.lat.toFixed(6);

    document.getElementById("longitude").value =
    e.latlng.lng.toFixed(6);

});

/*=========================
DETECT CURRENT LOCATION
=========================*/

function getLocation(){

    if(!navigator.geolocation){

        alert("Geolocation is not supported.");

        return;

    }

    navigator.geolocation.getCurrentPosition(

        function(position){

            var lat = position.coords.latitude;
            var lng = position.coords.longitude;

            document.getElementById("latitude").value = lat.toFixed(6);
            document.getElementById("longitude").value = lng.toFixed(6);

            marker.setLatLng([lat,lng]);

            map.setView([lat,lng],17);

        },

        function(){

            alert("Unable to fetch your location.");

        }

    );

}

/*=========================
UPDATE MARKER IF LAT/LNG CHANGED
=========================*/

document.getElementById("latitude").addEventListener("change",updateMarker);
document.getElementById("longitude").addEventListener("change",updateMarker);

function updateMarker(){

    let lat = parseFloat(document.getElementById("latitude").value);
    let lng = parseFloat(document.getElementById("longitude").value);

    if(!isNaN(lat) && !isNaN(lng)){

        marker.setLatLng([lat,lng]);

        map.setView([lat,lng],15);

    }

}

/*=========================
PHOTO PREVIEW
=========================*/

const photoInput = document.getElementById("photo");

if(photoInput){

photoInput.addEventListener("change",function(e){

    if(e.target.files.length){

        document.getElementById("previewImage").src =
        URL.createObjectURL(e.target.files[0]);

    }

});

}

</script>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>