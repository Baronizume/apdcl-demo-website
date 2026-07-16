<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
DELETE COMPLAINT
=========================================*/

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM complaint WHERE id='$id'");

    $_SESSION['success'] = "Complaint deleted successfully.";

    header("Location: manage_complaint.php");
    exit();
}

/*=========================================
DASHBOARD COUNTS
=========================================*/

$totalComplaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) total FROM complaint"))['total'];

$pendingComplaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) total FROM complaint WHERE status='Pending'"))['total'];

$assignedComplaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) total FROM complaint WHERE status='Assigned'"))['total'];

$progressComplaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) total FROM complaint WHERE status='In Progress'"))['total'];

$resolvedComplaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) total FROM complaint WHERE status='Resolved'"))['total'];

/*=========================================
SEARCH
=========================================*/

$search = "";

$status = "";

$where = " WHERE 1=1 ";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= "
        AND
        (
            complaint_id LIKE '%$search%'
            OR consumer_no LIKE '%$search%'
            OR name LIKE '%$search%'
            OR mobile LIKE '%$search%'
            OR category LIKE '%$search%'
        )
    ";

}

if(isset($_GET['status']) && $_GET['status']!=""){

    $status = mysqli_real_escape_string($conn,$_GET['status']);

    $where .= " AND status='$status'";

}

/*=========================================
FETCH COMPLAINTS
=========================================*/

$query = mysqli_query($conn,"
SELECT *
FROM complaint
$where
ORDER BY id DESC
");

$totalRecords = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Complaint Management | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.container-fluid{
    padding:30px;
}

.page-title{
    font-size:30px;
    font-weight:700;
    color:#0d47a1;
}

.page-subtitle{
    color:#6c757d;
    margin-bottom:30px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.stat-card{
    color:#fff;
}

.stat-card h6{
    font-size:15px;
    opacity:.9;
}

.stat-card h2{
    font-weight:bold;
}

.search-card{
    background:#fff;
}

.btn{
    border-radius:10px;
}

.form-control,
.form-select{
    border-radius:10px;
    height:46px;
}

.table{
    margin-bottom:0;
}

.table thead{
    background:#0d6efd;
    color:#fff;
}

.table th{
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
}

.badge{
    font-size:13px;
}

.consumer-box{
    line-height:1.6;
}

.consumer-box i{
    width:18px;
    color:#0d6efd;
}

.action-btn{
    width:35px;
    height:35px;
    border-radius:8px;
}

.complaint-card{
    border-radius:20px;
    overflow:hidden;
}


.table thead th{
    font-size:14px;
    text-transform:uppercase;
}


.table tbody tr:hover{

background:#f1f7ff;

transform:scale(1.01);

transition:.2s;

}


.btn-group .btn{

width:38px;
height:35px;

display:flex;
align-items:center;
justify-content:center;

}



.badge{

border-radius:20px;

font-size:12px;

}



@media(max-width:768px){

.table{

font-size:13px;

}

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<div class="page-title">
<i class="fa-solid fa-triangle-exclamation text-danger"></i>
Complaint Management
</div>

<div class="page-subtitle">
Manage, Assign and Resolve Consumer Complaints
</div>

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary">
<i class="fa fa-arrow-left"></i>
Dashboard
</a>

</div>

</div>

<?php
if(isset($_SESSION['success'])){
?>

<div class="alert alert-success alert-dismissible fade show">

<?= $_SESSION['success']; ?>

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php
unset($_SESSION['success']);
}
?>

<!-- Dashboard Cards -->

<div class="row g-4 mb-4">

<div class="col-lg-2 col-md-4">

<div class="card stat-card bg-primary">

<div class="card-body text-center">

<h6>Total</h6>

<h2><?= $totalComplaints ?></h2>

</div>

</div>

</div>

<div class="col-lg-2 col-md-4">

<div class="card stat-card bg-danger">

<div class="card-body text-center">

<h6>Pending</h6>

<h2><?= $pendingComplaints ?></h2>

</div>

</div>

</div>

<div class="col-lg-2 col-md-4">

<div class="card stat-card bg-info">

<div class="card-body text-center">

<h6>Assigned</h6>

<h2><?= $assignedComplaints ?></h2>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-warning text-dark">

<div class="card-body text-center">

<h6>In Progress</h6>

<h2><?= $progressComplaints ?></h2>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-success">

<div class="card-body text-center">

<h6>Resolved</h6>

<h2><?= $resolvedComplaints ?></h2>

</div>

</div>

</div>

</div>

<!-- Search Panel -->

<div class="card search-card mb-4">

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-5">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Complaint ID, Consumer No, Name, Mobile..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-3">

<select name="status" class="form-select">

<option value="">All Status</option>

<option value="Pending" <?=($status=="Pending")?"selected":"";?>>
Pending
</option>

<option value="Assigned" <?=($status=="Assigned")?"selected":"";?>>
Assigned
</option>

<option value="In Progress" <?=($status=="In Progress")?"selected":"";?>>
In Progress
</option>

<option value="Resolved" <?=($status=="Resolved")?"selected":"";?>>
Resolved
</option>

</select>

</div>

<div class="col-lg-2">

<button class="btn btn-primary w-100">

<i class="fa fa-search"></i>

Search

</button>

</div>

<div class="col-lg-2">

<a href="manage_complaint.php" class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>

<!-- Complaint List -->

<div class="card shadow-lg">

<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

<h5 class="mb-0">

<i class="fa-solid fa-list-check"></i>

Complaint List

</h5>

<span class="badge bg-warning text-dark">

<?= $totalRecords ?> Records

</span>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead>

<tr>

<th width="70">ID</th>

<th width="170">Complaint ID</th>

<th width="280">Consumer Details</th>

<th width="180">Category</th>

<th width="120">Status</th>

<th width="170">Assigned Officer</th>

<th width="160">Date</th>

<th width="220">Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<strong>#<?= $row['id'] ?></strong>

</td>

<td>

<strong class="text-primary">

<?= htmlspecialchars($row['complaint_id']) ?>

</strong>

</td>

<td>

<div class="consumer-box">

<div>

<i class="fa fa-user"></i>

<strong><?= htmlspecialchars($row['name']) ?></strong>

</div>

<div>

<i class="fa fa-bolt"></i>

<?= htmlspecialchars($row['consumer_no']) ?>

</div>

<div>

<i class="fa fa-phone"></i>

<?= htmlspecialchars($row['mobile']) ?>

</div>

</div>

</td>

<td>

<?= htmlspecialchars($row['category']) ?>

</td>

<td>

<?php

switch($row['status']){

case "Pending":

echo "<span class='badge bg-danger'>Pending</span>";

break;

case "Assigned":

echo "<span class='badge bg-info'>Assigned</span>";

break;

case "In Progress":

echo "<span class='badge bg-warning text-dark'>In Progress</span>";

break;

case "Resolved":

echo "<span class='badge bg-success'>Resolved</span>";

break;

default:

echo "<span class='badge bg-secondary'>".$row['status']."</span>";

}

?>

</td>

<td>

<?php

if(empty($row['assigned_admin'])){

echo "<span class='text-muted'>Not Assigned</span>";

}else{

echo htmlspecialchars($row['assigned_admin']);

}

?>

</td>

<td>

<div>

<i class="fa fa-calendar text-primary"></i>

<?= date("d M Y",strtotime($row['created_at'])) ?>

</div>

<small class="text-muted">

<?= date("h:i A",strtotime($row['created_at'])) ?>

</small>

</td>

<td>

<a

href="view_complaint.php?id=<?= $row['id'] ?>"

class="btn btn-info btn-sm action-btn"

title="View">

<i class="fa fa-eye"></i>

</a>

<a

href="edit_complaint.php?id=<?= $row['id'] ?>"

class="btn btn-warning btn-sm action-btn"

title="Edit">

<i class="fa fa-edit"></i>

</a>

<a

href="assign_complaint.php?id=<?= $row['id'] ?>"

class="btn btn-primary btn-sm action-btn"

title="Assign">

<i class="fa fa-user-check"></i>

</a>

<a

href="?delete=<?= $row['id'] ?>"

class="btn btn-danger btn-sm action-btn"

onclick="return confirm('Delete this complaint?');"

title="Delete">

<i class="fa fa-trash"></i>

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" class="text-center p-5">

<i class="fa fa-folder-open fa-3x text-secondary mb-3"></i>

<h5>No Complaints Found</h5>

<p class="text-muted">

No complaint records match your search.

</p>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<div class="card-footer bg-light d-flex justify-content-between align-items-center">

<div>

Showing

<strong><?= $totalRecords ?></strong>

Complaint(s)

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

</div>

<!-- ===========================
     PROFESSIONAL COMPLAINT LIST
=========================== -->

<div class="card shadow-lg border-0 complaint-card">

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="bi bi-list-check"></i>
            Complaint List
        </h5>

        <span class="badge bg-light text-primary">
            <?= mysqli_num_rows($query); ?> Records
        </span>

    </div>


    <div class="card-body">


        <div class="table-responsive">


        <table class="table table-hover align-middle">


        <thead class="table-primary">

        <tr>

            <th>#</th>

            <th>
                Complaint ID
            </th>

            <th>
                Consumer Details
            </th>

            <th>
                Category
            </th>

            <th>
                Status
            </th>

            <th>
                Assigned
            </th>

            <th>
                Date
            </th>

            <th>
                Action
            </th>


        </tr>

        </thead>


        <tbody>


<?php

$count=1;


while($row=mysqli_fetch_assoc($query)){


?>


<tr>


<td>

<?= $count++; ?>

</td>



<td>

<span class="fw-bold text-primary">

<?= htmlspecialchars($row['complaint_id']); ?>

</span>

</td>




<td>


<div class="fw-bold">

<?= htmlspecialchars($row['name']); ?>

</div>


<small class="text-muted">

<i class="bi bi-person-badge"></i>

<?= htmlspecialchars($row['consumer_no']); ?>

</small>


<br>


<small>

<i class="bi bi-phone"></i>

<?= htmlspecialchars($row['mobile']); ?>

</small>


</td>





<td>

<span class="badge bg-secondary">

<?= htmlspecialchars($row['category']); ?>

</span>


</td>




<td>


<?php


$status=$row['status'];


if($status=="Pending"){

echo "

<span class='badge bg-danger px-3 py-2'>

<i class='bi bi-clock'></i>
Pending

</span>";

}

elseif($status=="Assigned"){


echo "

<span class='badge bg-info px-3 py-2'>

<i class='bi bi-person-check'></i>
Assigned

</span>";

}



elseif($status=="In Progress"){


echo "

<span class='badge bg-warning text-dark px-3 py-2'>

<i class='bi bi-arrow-repeat'></i>
In Progress

</span>";

}



elseif($status=="Resolved"){


echo "

<span class='badge bg-success px-3 py-2'>

<i class='bi bi-check-circle'></i>
Resolved

</span>";

}


?>


</td>




<td>


<?php

if(!empty($row['assigned_admin'])){


echo "

<span class='text-success fw-bold'>

<i class='bi bi-person-fill'></i>

".htmlspecialchars($row['assigned_admin'])."

</span>";

}


else{


echo "

<span class='text-muted'>

<i class='bi bi-dash-circle'></i>

Not Assigned

</span>";

}


?>


</td>






<td>


<div>

<?= date("d M Y",strtotime($row['created_at'])); ?>

</div>


<small class="text-muted">

<?= date("h:i A",strtotime($row['created_at'])); ?>

</small>


</td>





<td>


<div class="btn-group">


<a href="view_complaint.php?id=<?= $row['id']; ?>"

class="btn btn-sm btn-primary"

title="View">

<i class="bi bi-eye-fill"></i>

</a>



<a href="edit_complaint.php?id=<?= $row['id']; ?>"

class="btn btn-sm btn-warning"

title="Edit">

<i class="bi bi-pencil-square"></i>

</a>




<a href="?delete=<?= $row['id']; ?>"

onclick="return confirm('Delete this complaint?');"

class="btn btn-sm btn-danger"

title="Delete">


<i class="bi bi-trash-fill"></i>


</a>



</div>


</td>



</tr>


<?php } ?>


        </tbody>


        </table>


        </div>


    </div>

</div>



<br>


<a href="dashboard.php"

class="btn btn-dark px-4">

<i class="bi bi-arrow-left"></i>

Back to Dashboard

</a>