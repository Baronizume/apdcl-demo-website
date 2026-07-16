<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    LOGGED IN CONSUMER
=========================================*/

$consumer_no = $_SESSION['consumer'];

/*=========================================
    VALIDATE COMPLAINT ID
=========================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Complaint ID.");
}

$id = (int)$_GET['id'];

/*=========================================
    FETCH COMPLAINT
=========================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM complaint
WHERE id=?
AND consumer_no=?
LIMIT 1
");

if (!$stmt) {
    die("SQL Error : ".mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $id,
    $consumer_no
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Complaint not found or access denied.");
}

$complaint = mysqli_fetch_assoc($result);

/*=========================================
    STATUS BADGE
=========================================*/

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

/*=========================================
    PHOTO
=========================================*/

$photo="";

if(
    !empty($complaint['photo']) &&
    file_exists("../uploads/complaint/".$complaint['photo'])
){
    $photo="../uploads/complaint/".$complaint['photo'];
}

/*=========================================
    GOOGLE MAP LINK
=========================================*/

$mapLink="";

if(
    !empty($complaint['latitude']) &&
    !empty($complaint['longitude'])
){
    $mapLink="https://www.google.com/maps?q="
        .$complaint['latitude'].","
        .$complaint['longitude'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Complaint | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef3fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0d6efd;
}

.page-content{
    padding:30px;
}

.card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,.12);
}

.card-header{
    font-weight:600;
}

.form-control,
textarea{
    background:#f8f9fa;
    border-radius:10px;
}

.badge{
    font-size:15px;
    padding:8px 20px;
    border-radius:25px;
}

img{
    border-radius:10px;
}

.btn{
    border-radius:10px;
}

@media print{

.navbar,
.btn{
display:none!important;
}

body{
background:#fff;
}

.card{
box-shadow:none;
border:1px solid #ddd;
}

}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">

<div class="container">

<a class="navbar-brand fw-bold" href="#">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Portal

</a>

</div>

</nav>

<div class="container page-content">

<div class="card mb-4">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-2 text-center">

<img src="../assets/images/logo-circle.png"
width="90">

</div>

<div class="col-md-10">

<h2 class="text-primary fw-bold">

Assam Power Distribution Company Limited

</h2>

<h5 class="text-secondary">

Complaint Details

</h5>

<p class="text-muted mb-0">

Complaint Tracking Information

</p>

</div>

</div>

</div>

</div>


<!-- Complaint Summary -->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

<i class="bi bi-info-circle-fill"></i>

Complaint Summary

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label class="fw-bold">

Complaint ID

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['complaint_id']) ?>">

</div>

<div class="col-md-4 mb-3">

<label class="fw-bold">

Status

</label>

<br>

<span class="badge bg-<?= $badge ?>">

<?= htmlspecialchars($complaint['status']) ?>

</span>

</div>

<div class="col-md-4 mb-3">

<label class="fw-bold">

Registered On

</label>

<input
class="form-control"
readonly
value="<?= date("d M Y h:i A",strtotime($complaint['created_at'])) ?>">

</div>

</div>

</div>

</div>



<!-- Consumer Information -->

<div class="card mb-4">

<div class="card-header bg-success text-white">

<i class="bi bi-person-fill"></i>

Consumer Information

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label class="fw-bold">

Consumer Number

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['consumer_no']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="fw-bold">

Consumer Name

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['name']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="fw-bold">

Mobile Number

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['mobile']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="fw-bold">

Email Address

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['email']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="fw-bold">

Complaint Category

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['category']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="fw-bold">

Priority

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['priority']) ?>">

</div>

<div class="col-md-12 mb-3">

<label class="fw-bold">

Subject

</label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars($complaint['subject']) ?>">

</div>

<div class="col-md-12 mb-3">

<label class="fw-bold">

Complaint Description

</label>

<textarea
class="form-control"
rows="5"
readonly><?= htmlspecialchars($complaint['description']) ?></textarea>

</div>

<div class="col-md-12 mb-3">

<label class="fw-bold">

Address

</label>

<textarea
class="form-control"
rows="3"
readonly><?= htmlspecialchars($complaint['address']) ?></textarea>

</div>
        </div>
    </div>
</div>

<!-- ACTION BUTTONS -->

<div class="text-center mt-4 mb-5">

    <button
        onclick="window.print();"
        class="btn btn-success btn-lg">

        <i class="bi bi-printer-fill"></i>
        Print Complaint

    </button>

    <a
        href="complaint.php"
        class="btn btn-secondary btn-lg ms-2">

        <i class="bi bi-arrow-left-circle-fill"></i>
        Back

    </a>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Auto Print (optional)
// window.print();

document.addEventListener("DOMContentLoaded",function(){

    const img=document.querySelector("img");

    if(img){

        img.addEventListener("click",function(){

            window.open(this.src,"_blank");

        });

    }

});

</script>

</body>
</html>