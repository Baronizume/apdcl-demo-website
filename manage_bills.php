<?php
session_start();
include("../db.php");

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/*=========================================
    Logged In Admin
=========================================*/

$admin_username = $_SESSION['admin'];

$q = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($q);

$admin_name = $admin['name'];
$role       = $admin['role'];
$zone       = $admin['zone'];
$circle     = $admin['circle'];
$subdivision= $admin['sub_division'];

/*=========================================
    Search & Filters
=========================================*/

$search = "";

$month = "";

$status = "";

$where = " WHERE 1=1 ";

/* Restrict non-super admin */

if($role!="Super Admin"){

    $where .= " AND sub_division='".mysqli_real_escape_string($conn,$subdivision)."' ";

}

/* Search */

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = trim($_GET['search']);

    $searchEsc = mysqli_real_escape_string($conn,$search);

    $where .= "
    AND
    (
        consumer_no LIKE '%$searchEsc%'
        OR consumer_name LIKE '%$searchEsc%'
        OR bill_no LIKE '%$searchEsc%'
    )
    ";

}

/* Month Filter */

if(isset($_GET['month']) && $_GET['month']!=""){

    $month = $_GET['month'];

    $monthEsc = mysqli_real_escape_string($conn,$month);

    $where .= " AND month='$monthEsc' ";

}

/* Status Filter */

if(isset($_GET['status']) && $_GET['status']!=""){

    $status = $_GET['status'];

    $statusEsc = mysqli_real_escape_string($conn,$status);

    $where .= " AND status='$statusEsc' ";

}

/*=========================================
    Statistics
=========================================*/

$totalBills = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
$where
"))['total'];

$totalPaid = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
$where
AND status='Paid'
"))['total'];

$totalPending = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
$where
AND status='Pending'
"))['total'];

$totalAmount = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT
IFNULL(SUM(total_bill),0) total
FROM bills
$where
"))['total'];

/*=========================================
    Pagination
=========================================*/

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page<1){

    $page=1;

}

$offset = ($page-1)*$limit;

$totalPages = ceil($totalBills/$limit);

/*=========================================
    Fetch Bills
=========================================*/

$bills = mysqli_query($conn,"
SELECT *
FROM bills
$where
ORDER BY id DESC
LIMIT $offset,$limit
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Bills | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

/* ================= NAVBAR ================= */

.navbar{

height:75px;

background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

box-shadow:0 5px 15px rgba(0,0,0,.20);

position:fixed;

top:0;

left:0;

right:0;

z-index:999;

}

.logo{

width:55px;

height:55px;

border-radius:50%;

background:#fff;

padding:5px;

margin-right:15px;

}

.brand-title{

font-size:22px;

font-weight:700;

color:#fff;

margin:0;

}

.brand-sub{

font-size:13px;

color:#dbeafe;

}

/* ================= SIDEBAR ================= */

.sidebar{

position:fixed;

top:75px;

left:0;

width:250px;

height:100%;

background:#083b8a;

overflow-y:auto;

padding-top:15px;

}

.sidebar a{

display:flex;

align-items:center;

padding:15px 22px;

text-decoration:none;

color:#fff;

transition:.3s;

border-left:4px solid transparent;

}

.sidebar a:hover{

background:#1565c0;

border-left:4px solid #ffc107;

padding-left:28px;

}

.sidebar a.active{

background:#1976d2;

border-left:4px solid #ffc107;

}

.sidebar i{

font-size:20px;

margin-right:12px;

}

/* ================= CONTENT ================= */

.main-content{

margin-left:260px;

margin-top:90px;

padding:30px;

}

/* ================= STAT CARDS ================= */

.stat-card{

border:none;

border-radius:18px;

box-shadow:0 10px 20px rgba(0,0,0,.10);

transition:.3s;

overflow:hidden;

}

.stat-card:hover{

transform:translateY(-5px);

}

.stat-card .card-body{

padding:25px;

}

.stat-card i{

font-size:45px;

opacity:.25;

}

/* ================= FILTER ================= */

.filter-card{

border:none;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

</style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo">

<div>

<h4 class="brand-title">

APDCL Electricity Billing System

</h4>

<div class="brand-sub">

Manage Electricity Bills

</div>

</div>

</div>

<div class="text-white text-end">

<strong><?= htmlspecialchars($admin_name); ?></strong>

<br>

<small><?= htmlspecialchars($role); ?></small>

</div>

</div>

</nav>

<!-- ================= SIDEBAR ================= -->

<div class="sidebar">

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

<a href="manage_consumers.php">

<i class="bi bi-people-fill"></i>

Consumers

</a>

<a href="generate_bill.php">

<i class="bi bi-receipt-cutoff"></i>

Generate Bill

</a>

<a href="manage_bills.php" class="active">

<i class="bi bi-journal-text"></i>

Manage Bills

</a>

<a href="complaints.php">

<i class="bi bi-chat-left-text-fill"></i>

Complaints

</a>

<a href="reports.php">

<i class="bi bi-bar-chart-fill"></i>

Reports

</a>

<a href="notices.php">

<i class="bi bi-megaphone-fill"></i>

Notices

</a>

<hr>

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

<!-- ================= CONTENT ================= -->

<div class="main-content">

<h2 class="fw-bold text-primary mb-4">

<i class="bi bi-journal-check"></i>

Manage Electricity Bills

</h2>

<!-- ================= STATISTICS ================= -->

<div class="row g-4 mb-4">

<div class="col-lg-3">

<div class="card stat-card bg-primary text-white">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Total Bills</h6>

<h2><?= $totalBills ?></h2>

</div>

<i class="bi bi-receipt-cutoff"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card stat-card bg-success text-white">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Paid Bills</h6>

<h2><?= $totalPaid ?></h2>

</div>

<i class="bi bi-check-circle-fill"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card stat-card bg-danger text-white">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Pending Bills</h6>

<h2><?= $totalPending ?></h2>

</div>

<i class="bi bi-exclamation-circle-fill"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card stat-card bg-warning text-dark">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Total Amount</h6>

<h4>₹ <?= number_format($totalAmount,2) ?></h4>

</div>

<i class="bi bi-currency-rupee"></i>

</div>

</div>

</div>

</div>

</div>

<!-- ================= SEARCH FILTER ================= -->

<div class="card filter-card mb-4">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-4">

<input

type="text"

name="search"

class="form-control"

placeholder="Search Consumer / Bill No"

value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-3">

<input

type="month"

name="month"

class="form-control"

value="<?= htmlspecialchars($month) ?>">

</div>

<div class="col-md-3">

<select name="status" class="form-select">

<option value="">All Status</option>

<option value="Paid" <?=($status=="Paid")?"selected":""?>>

Paid

</option>

<option value="Pending" <?=($status=="Pending")?"selected":""?>>

Pending

</option>

</select>

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

<!-- ================= BILLS TABLE ================= -->

<div class="card shadow border-0 rounded-4">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="bi bi-receipt-cutoff text-primary"></i>

                Electricity Bills

            </h4>

            <span class="badge bg-primary fs-6">

                <?= $totalBills ?> Bills Found

            </span>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle">

                <thead class="table-primary text-center">

                    <tr>

                        <th width="60">#</th>

                        <th>Bill No</th>

                        <th>Consumer No</th>

                        <th>Consumer Name</th>

                        <th>Meter No</th>

                        <th>Month</th>

                        <th>Units</th>

                        <th>Total Bill</th>

                        <th>Due Date</th>

                        <th>Status</th>

                        <th width="220">Action</th>

                    </tr>

                </thead>

                <tbody>

<?php

if(mysqli_num_rows($bills)>0){

$sl=$offset+1;

while($row=mysqli_fetch_assoc($bills)){

?>

<tr>

<td class="text-center">

<?= $sl++; ?>

</td>

<td>

<strong>

<?= htmlspecialchars($row['bill_no']); ?>

</strong>

</td>

<td>

<?= htmlspecialchars($row['consumer_no']); ?>

</td>

<td>

<?= htmlspecialchars($row['consumer_name']); ?>

</td>

<td>

<?= htmlspecialchars($row['meter_no']); ?>

</td>

<td>

<?= htmlspecialchars($row['month']); ?>

</td>

<td class="text-center">

<?= number_format($row['units'],2); ?>

</td>

<td>

<strong class="text-success">

₹ <?= number_format($row['total_bill'],2); ?>

</strong>

</td>

<td>

<?= htmlspecialchars($row['due_date']); ?>

</td>

<td class="text-center">

<?php

if($row['status']=="Paid"){

?>

<span class="badge bg-success">

Paid

</span>

<?php

}elseif($row['status']=="Pending"){

?>

<span class="badge bg-warning text-dark">

Pending

</span>

<?php

}else{

?>

<span class="badge bg-secondary">

<?= htmlspecialchars($row['status']); ?>

</span>

<?php } ?>

</td>

<td class="text-center">

<a

href="view_bill.php?id=<?= $row['id']; ?>"

class="btn btn-sm btn-primary"

title="View">

<i class="bi bi-eye-fill"></i>

</a>

<a

href="generate_bill.php?edit=<?= $row['id']; ?>"

class="btn btn-sm btn-warning"

title="Edit">

<i class="bi bi-pencil-fill"></i>

</a>

<a

href="print_bill.php?id=<?= $row['id']; ?>"

target="_blank"

class="btn btn-sm btn-success"

title="Print">

<i class="bi bi-printer-fill"></i>

</a>

<button

class="btn btn-sm btn-danger"

data-bs-toggle="modal"

data-bs-target="#deleteModal"

data-id="<?= $row['id']; ?>"

title="Delete">

<i class="bi bi-trash-fill"></i>

</button>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="11" class="text-center text-muted py-5">

<i class="bi bi-receipt fs-1 d-block mb-3"></i>

No Bills Found

</td>

</tr>

<?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================
        ACTION BUTTONS
========================================= -->

<div class="d-flex justify-content-between align-items-center mt-4 mb-4">

    <div>

        <a href="generate_bill.php" class="btn btn-success">

            <i class="bi bi-plus-circle-fill"></i>

            Generate New Bill

        </a>

    </div>

    <div>

        <a href="export_bills_excel.php" class="btn btn-outline-success">

            <i class="bi bi-file-earmark-excel-fill"></i>

            Export Excel

        </a>

        <a href="export_bills_pdf.php" class="btn btn-outline-danger">

            <i class="bi bi-file-earmark-pdf-fill"></i>

            Export PDF

        </a>

    </div>

</div>

<!-- =========================================
        PAGINATION
========================================= -->

<?php if($totalPages>1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php if($page>1){ ?>

<li class="page-item">

<a class="page-link"

href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&month=<?= urlencode($month) ?>&status=<?= urlencode($status) ?>">

Previous

</a>

</li>

<?php } ?>

<?php

for($i=1;$i<=$totalPages;$i++){

?>

<li class="page-item <?=($page==$i)?'active':''?>">

<a class="page-link"

href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&month=<?= urlencode($month) ?>&status=<?= urlencode($status) ?>">

<?= $i ?>

</a>

</li>

<?php

}

?>

<?php if($page<$totalPages){ ?>

<li class="page-item">

<a class="page-link"

href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&month=<?= urlencode($month) ?>&status=<?= urlencode($status) ?>">

Next

</a>

</li>

<?php } ?>

</ul>

</nav>

<?php } ?>

<!-- =========================================
        DELETE MODAL
========================================= -->

<div class="modal fade"

id="deleteModal"

tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header bg-danger text-white">

<h5 class="modal-title">

<i class="bi bi-trash-fill"></i>

Delete Bill

</h5>

<button

type="button"

class="btn-close btn-close-white"

data-bs-dismiss="modal">

</button>

</div>

<div class="modal-body">

<p class="mb-0">

Are you sure you want to delete this bill?

</p>

<p class="text-danger fw-bold mt-2">

This action cannot be undone.

</p>

</div>

<div class="modal-footer">

<button

class="btn btn-secondary"

data-bs-dismiss="modal">

Cancel

</button>

<a

href="#"

id="deleteBtn"

class="btn btn-danger">

<i class="bi bi-trash-fill"></i>

Delete

</a>

</div>

</div>

</div>

</div>

<!-- =========================================
        FOOTER
========================================= -->

<footer class="mt-5">

<div class="card border-0 shadow-sm">

<div class="card-body">

<div class="row">

<div class="col-md-6">

<strong>

APDCL Electricity Billing Management System

</strong>

<br>

<small>

© <?= date("Y"); ?>

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

<strong>

Logged in as:

</strong>

<?= htmlspecialchars($admin_name); ?>

<br>

<small>

<?= htmlspecialchars($role); ?>

</small>

</div>

</div>

</div>

</div>

</footer>

</div>

<!-- =========================================
        BOOTSTRAP JS
========================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
        LIVE CLOCK
=========================================*/

function updateClock(){

    let now = new Date();

    let options={

        weekday:'short',

        day:'2-digit',

        month:'short',

        year:'numeric'

    };

    let date = now.toLocaleDateString('en-IN',options);

    let time = now.toLocaleTimeString();

    let clock = document.getElementById("liveClock");

    if(clock){

        clock.innerHTML = date + " | " + time;

    }

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
        DELETE BILL
=========================================*/

let deleteModal = document.getElementById('deleteModal');

if(deleteModal){

    deleteModal.addEventListener('show.bs.modal',function(event){

        let button = event.relatedTarget;

        let id = button.getAttribute('data-id');

        document.getElementById("deleteBtn").href =
        "delete_bill.php?id="+id;

    });

}

/*=========================================
        TABLE HOVER EFFECT
=========================================*/

document.querySelectorAll("table tbody tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.background="#eef6ff";

        this.style.transition=".2s";

    });

    row.addEventListener("mouseleave",function(){

        this.style.background="";

    });

});

/*=========================================
        CARD ANIMATION
=========================================*/

document.querySelectorAll(".stat-card").forEach(function(card){

    card.addEventListener("mouseenter",function(){

        this.style.transform="translateY(-6px)";

        this.style.transition=".25s";

    });

    card.addEventListener("mouseleave",function(){

        this.style.transform="translateY(0px)";

    });

});

/*=========================================
        AUTO HIDE ALERTS
=========================================*/

setTimeout(function(){

    let alerts=document.querySelectorAll(".alert");

    alerts.forEach(function(alert){

        alert.style.transition="0.5s";

        alert.style.opacity="0";

        setTimeout(function(){

            alert.remove();

        },500);

    });

},4000);

/*=========================================
        AUTO REFRESH
=========================================*/

/* Refresh every 5 minutes */

setTimeout(function(){

    location.reload();

},300000);

</script>

</body>

</html>