<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    SEARCH
=========================================*/

$search = "";

$where = "consumer_no='$consumer_no'";

if(isset($_GET['search']) && trim($_GET['search'])!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= " AND (
        complaint_id LIKE '%$search%'
        OR category LIKE '%$search%'
        OR status LIKE '%$search%'
    )";

}

/*=========================================
    FETCH COMPLAINTS
=========================================*/

$complaints = mysqli_query($conn,"
SELECT *
FROM complaint
WHERE $where
ORDER BY created_at DESC
");

if(!$complaints){
    die("SQL Error : ".mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Complaint History | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

</head>

<style>

body{
    background:#eef3fb;
    font-family:'Segoe UI',sans-serif;
}

.page-content{
    padding:30px;
}

.card{
    border:none;
    border-radius:18px;
}

.card-header{
    border-radius:18px 18px 0 0 !important;
}

.table th{
    background:#0d47a1;
    color:#fff;
    text-align:center;
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
    text-align:center;
}

.table tbody tr:hover{
    background:#f8fbff;
}

.badge{
    font-size:14px;
    padding:8px 14px;
    border-radius:30px;
}

.btn{
    border-radius:8px;
}

.btn-sm{
    padding:6px 12px;
}

.form-control{
    border-radius:10px;
}

.card img{
    max-height:80px;
}

h3{
    font-weight:700;
}

.table-responsive{
    border-radius:0 0 18px 18px;
}

</style>

<body>

<div class="main-content">

<nav class="navbar navbar-expand-lg navbar-dark">

</nav>

<div class="page-content">

<div class="container-fluid">

<!-- Navigation Buttons -->

<div class="text-center mt-4 mb-5">

    <a href="dashboard.php" class="btn btn-primary btn-lg">
        <i class="bi bi-house-door-fill"></i>
        Back to Dashboard
    </a>

</div>


<!-- Header -->

<div class="card shadow border-0 mb-4">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-2 text-center">

<img src="../assets/images/logo-circle.png"
     width="80"
     class="img-fluid">

</div>


<div class="col-md-10">

<h3 class="text-primary fw-bold mb-1">

Assam Power Distribution Company Limited

</h3>

<h5 class="text-secondary">

Consumer Complaint History

</h5>

<p class="text-muted mb-0">

View and track all complaints submitted from your consumer account.

</p>

</div>

</div>

</div>

</div>


<!-- Search -->

<div class="card shadow-sm border-0 mb-4">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search by Complaint ID, Category or Status..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-2 d-grid">

<button class="btn btn-primary">

<i class="bi bi-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

</div>

<!-- Complaint Table -->

<div class="card shadow border-0">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="bi bi-list-check"></i>

My Complaints

</h5>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-bordered table-hover mb-0">

<thead class="table-dark">

<tr>

<th>#</th>

<th>Complaint ID</th>

<th>Category</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

$i=1;

if(mysqli_num_rows($complaints)>0){

while($row=mysqli_fetch_assoc($complaints)){

?>
<?php

switch($row['status']){

    case "Pending":
        $badge = "warning";
        break;

    case "Assigned":
        $badge = "info";
        break;

    case "In Progress":
        $badge = "primary";
        break;

    case "Resolved":
        $badge = "success";
        break;

    case "Rejected":
        $badge = "danger";
        break;

    default:
        $badge = "secondary";
}

?>

<tr>

    <td><?= $i++; ?></td>

    <td>

        <strong>

            <?= htmlspecialchars($row['complaint_id']); ?>

        </strong>

    </td>

    <td>

        <?= htmlspecialchars($row['category']); ?>

    </td>

    <td>

        <span class="badge bg-<?= $badge; ?>">

            <?= htmlspecialchars($row['status']); ?>

        </span>

    </td>

    <td>

        <?= date("d M Y",strtotime($row['created_at'])); ?>

    </td>

    <td>

        <a href="view_complaint.php?id=<?= $row['id']; ?>"
           class="btn btn-sm btn-primary">

            <i class="bi bi-eye-fill"></i>

            View

        </a>

        <a href="track_complaint.php?id=<?= $row['id']; ?>"
           class="btn btn-sm btn-success">

            <i class="bi bi-geo-alt-fill"></i>

            Track

        </a>

    </td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center py-5">

<i class="bi bi-inbox display-4 text-muted"></i>

<h5 class="mt-3">

No complaints found.

</h5>

<p class="text-muted">

You haven't submitted any complaints yet.

</p>

<a href="report_outage.php" class="btn btn-danger">

<i class="bi bi-plus-circle"></i>

Report New Complaint

</a>

<a href="track_complaint.php?id=<?= $row['id']; ?>"
   class="btn btn-sm btn-success">
    <i class="bi bi-geo-alt-fill"></i>
    Track
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>