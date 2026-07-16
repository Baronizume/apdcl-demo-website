<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    CONSUMER DETAILS
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
    SEARCH & FILTER
=========================================*/

$search = "";

$status = "";

$month = "";

$where = "WHERE consumer_no='$consumer_no'";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= " AND bill_no LIKE '%$search%'";

}

if(isset($_GET['status']) && $_GET['status']!=""){

    $status = mysqli_real_escape_string($conn,$_GET['status']);

    $where .= " AND status='$status'";

}

if(isset($_GET['month']) && $_GET['month']!=""){

    $month = mysqli_real_escape_string($conn,$_GET['month']);

    $where .= " AND month='$month'";

}

/*=========================================
    BILL LIST
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
$where
ORDER BY bill_date DESC
");

/*=========================================
    DASHBOARD SUMMARY
=========================================*/

$totalBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
"));

$paidBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Paid'
"));

$pendingBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
AND status!='Paid'
"));

$totalAmountQuery = mysqli_query($conn,"
SELECT IFNULL(SUM(total_bill),0) AS total
FROM bills
WHERE consumer_no='$consumer_no'
");

$totalAmount = mysqli_fetch_assoc($totalAmountQuery)['total'];

/*=========================================
    MONTH DROPDOWN
=========================================*/

$monthQuery = mysqli_query($conn,"
SELECT DISTINCT month
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY bill_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Bill History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0d6efd;
}

.logo{
    width:55px;
    height:55px;
    margin-right:12px;
}

.summary-card{
    border:none;
    border-radius:18px;
    color:#fff;
    transition:.3s;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,.12);
}

.summary-card:hover{
    transform:translateY(-6px);
}

.summary-card .card-body{
    padding:25px;
}

.summary-card i{
    font-size:45px;
    opacity:.30;
}

.filter-card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.table-card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container">

<a class="navbar-brand d-flex align-items-center" href="dashboard.php">

<img src="../assets/images/logo-circle.png" class="logo">

<div>

<strong>APDCL Consumer Portal</strong>

<br>

<small>Bill History</small>

</div>

</a>

<div class="text-white">

<?= htmlspecialchars($user['name']) ?>

</div>

</div>

</nav>

<div class="container mt-4">

<!-- SUMMARY -->

<div class="row g-4 mb-4">

<div class="col-lg-3 col-md-6">

<div class="card summary-card bg-primary">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Total Bills</h6>

<h2><?= $totalBills ?></h2>

</div>

<i class="bi bi-receipt-cutoff"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card summary-card bg-success">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Paid Bills</h6>

<h2><?= $paidBills ?></h2>

</div>

<i class="bi bi-check-circle-fill"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card summary-card bg-danger">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Pending Bills</h6>

<h2><?= $pendingBills ?></h2>

</div>

<i class="bi bi-clock-history"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card summary-card bg-dark">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Total Amount</h6>

<h4>₹ <?= number_format($totalAmount,2) ?></h4>

</div>

<i class="bi bi-cash-stack"></i>

</div>

</div>

</div>

</div>

<!-- SEARCH & FILTER -->

<div class="card filter-card mb-4">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="bi bi-search"></i>

Search & Filter Bills

</h5>

</div>

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-4">

<input
type="text"
name="search"
value="<?= htmlspecialchars($search) ?>"
class="form-control"
placeholder="Search Bill Number">

</div>

<div class="col-lg-3">

<select
name="status"
class="form-select">

<option value="">All Status</option>

<option value="Paid" <?=($status=="Paid")?"selected":""?>>

Paid

</option>

<option value="Pending" <?=($status=="Pending")?"selected":""?>>

Pending

</option>

<option value="Unpaid" <?=($status=="Unpaid")?"selected":""?>>

Unpaid

</option>

</select>

</div>

<div class="col-lg-3">

<select
name="month"
class="form-select">

<option value="">

All Months

</option>

<?php while($m=mysqli_fetch_assoc($monthQuery)){ ?>

<option
value="<?= $m['month'] ?>"
<?=($month==$m['month'])?"selected":""?>>

<?= $m['month'] ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-lg-2 d-grid">

<button
class="btn btn-primary">

<i class="bi bi-funnel-fill"></i>

Filter

</button>

</div>

</div>

</form>

</div>

</div>

<!-- BILL TABLE STARTS -->

<div class="card table-card">

<div class="card-header bg-success text-white">

<h5 class="mb-0">

<i class="bi bi-table"></i>

Bill Records

</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>#</th>

<th>Bill No</th>

<th>Month</th>

<th>Total Bill</th>

<th>Status</th>

<th>Due Date</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($billQuery)>0){

$sl=1;

while($bill=mysqli_fetch_assoc($billQuery)){

?>

<tr>

<td><?= $sl++ ?></td>

<td>

<strong>

<?= htmlspecialchars($bill['bill_no']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($bill['month']) ?>

</td>

<td>

₹ <?= number_format($bill['total_bill'],2) ?>

</td>

<td>

<?php

if($bill['status']=="Paid"){

echo "<span class='badge bg-success'>Paid</span>";

}
elseif($bill['status']=="Pending"){

echo "<span class='badge bg-warning text-dark'>Pending</span>";

}
else{

echo "<span class='badge bg-danger'>Unpaid</span>";

}

?>

</td>

<td>

<?= date("d M Y",strtotime($bill['due_date'])) ?>

</td>

<td>

<a
href="view_bill.php?id=<?= $bill['id'] ?>"
class="btn btn-sm btn-primary">

<i class="bi bi-eye-fill"></i>

View

</a>

<a
href="print_bill.php?id=<?= $bill['id'] ?>"
target="_blank"
class="btn btn-sm btn-success">

<i class="bi bi-printer-fill"></i>

Print

</a>

<a
href="download_bill.php?id=<?= $bill['id'] ?>"
class="btn btn-sm btn-danger">

<i class="bi bi-file-earmark-pdf-fill"></i>

PDF

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center py-5">

<img
src="../assets/images/no-data.png"
style="width:120px;"
class="mb-3">

<h5 class="text-muted">

No Bill Records Found

</h5>

<p class="text-muted">

No bills matched your search criteria.

</p>

<a
href="bill_history.php"
class="btn btn-primary">

<i class="bi bi-arrow-clockwise"></i>

Refresh

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- =========================================
        FOOTER
========================================= -->

<footer class="mt-5 bg-dark text-white py-4">

<div class="container text-center">

<h5 class="mb-2">

⚡ APDCL Consumer Portal

</h5>

<p class="mb-1">

Assam Power Distribution Company Limited

</p>

<small>

Bill Management System

</small>

<br><br>

<a href="dashboard.php" class="btn btn-primary">

<i class="bi bi-house-fill"></i>

Back to Dashboard

</a>

</div>

</footer>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    TABLE SEARCH ANIMATION
=========================================*/

document.querySelectorAll(".table tbody tr").forEach(function(row){

row.addEventListener("mouseover",function(){

this.style.transition=".2s";

this.style.background="#eef5ff";

});

row.addEventListener("mouseout",function(){

this.style.background="";

});

});

/*=========================================
    CONFIRM PDF DOWNLOAD
=========================================*/

document.querySelectorAll("a[href*='download_bill']").forEach(function(btn){

btn.addEventListener("click",function(){

return confirm("Download this bill as PDF?");

});

});

/*=========================================
    CONFIRM PRINT
=========================================*/

document.querySelectorAll("a[href*='print_bill']").forEach(function(btn){

btn.addEventListener("click",function(){

return confirm("Open printable bill?");

});

});

</script>

<style>

/*=========================================
    BUTTON EFFECT
=========================================*/

.btn{

transition:.3s;

}

.btn:hover{

transform:translateY(-2px);

}

/*=========================================
    PRINT STYLE
=========================================*/

@media print{

.navbar,
footer,
.btn,
.filter-card{

display:none !important;

}

.card{

box-shadow:none !important;

border:1px solid #ddd !important;

}

body{

background:#fff;

}

}

</style>

</body>

</html>