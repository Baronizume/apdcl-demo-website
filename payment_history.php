<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    FETCH CONSUMER DETAILS
=========================================*/

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
    PAYMENT STATISTICS
=========================================*/

// Total Paid Bills
$totalPaidQuery = mysqli_query($conn,"
SELECT COUNT(id) AS total_paid
FROM payments
WHERE consumer_no='$consumer_no'
AND payment_status='Success'
");

if (!$totalPaidQuery) {
    die("SQL Error: " . mysqli_error($conn));
}

$totalPaid = mysqli_fetch_assoc($totalPaidQuery)['total_paid'];

// Total Paid Amount
$totalAmountQuery = mysqli_query($conn,"
SELECT SUM(amount) AS total_amount
FROM payments
WHERE consumer_no='$consumer_no'
AND payment_status='Success'
");

$totalAmount = mysqli_fetch_assoc($totalAmountQuery)['total_amount'] ?? 0;

// Last Payment
$lastPaymentQuery = mysqli_query($conn,"
SELECT *
FROM payments
WHERE consumer_no='$consumer_no'
ORDER BY payment_date DESC
LIMIT 1
");

$lastPayment = mysqli_fetch_assoc($lastPaymentQuery);

/*=========================================
    SEARCH
=========================================*/

$search = "";

$where = "WHERE p.consumer_no='$consumer_no'";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= " AND (

        p.transaction_id LIKE '%$search%'

        OR

        b.bill_no LIKE '%$search%'

        OR

        p.payment_method LIKE '%$search%'

    )";

}

/*=========================================
    PAYMENT HISTORY
=========================================*/

$paymentQuery = mysqli_query($conn,"
SELECT

p.*,

b.bill_no,

b.month,

b.due_date,

b.total_bill

FROM payments p

LEFT JOIN bills b
ON p.bill_id=b.id

$where

ORDER BY p.payment_date DESC
");

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Payment History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef4fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0056b3;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.brand{
    color:#fff;
    font-size:24px;
    font-weight:bold;
}

.summary-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    color:#fff;
    transition:.3s;
    cursor:pointer;
}

.summary-card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(0,0,0,.20);
}

.summary-card .card-body{
    padding:25px;
}

.summary-card h2{
    font-size:34px;
    font-weight:bold;
}

.summary-card i{
    font-size:48px;
    opacity:.25;
    position:absolute;
    right:20px;
    bottom:15px;
}

.profile-card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 20px rgba(0,0,0,.10);
}

.profile-card img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #0d6efd;
}

.search-box{
    border-radius:15px;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container">

<span class="brand">

APDCL Consumer Portal

</span>

<div>

<a href="dashboard.php" class="btn btn-light">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

</div>

</div>

</nav>

<div class="container py-4">

<!-- ===============================
PROFILE
================================ -->

<div class="card profile-card mb-4">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-2 text-center">

<?php

$photo="../assets/images/default-user.png";

if(!empty($user['photo']) && file_exists("../uploads/".$user['photo'])){

    $photo="../uploads/".$user['photo'];

}

?>

<img src="<?= $photo ?>">

</div>

<div class="col-md-10">

<h3>

<?= htmlspecialchars($user['name']) ?>

</h3>

<p class="mb-1">

<strong>Consumer No :</strong>

<?= htmlspecialchars($user['consumer_no']) ?>

</p>

<p class="mb-1">

<strong>Mobile :</strong>

<?= htmlspecialchars($user['mobile']) ?>

</p>

<p class="mb-0">

<strong>Email :</strong>

<?= htmlspecialchars($user['email']) ?>

</p>

</div>

</div>

</div>

</div>

<!-- ===============================
SUMMARY CARDS
================================ -->

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="card summary-card bg-success position-relative">

<div class="card-body">

<h6>Total Payments</h6>

<h2><?= $totalPaid ?></h2>

<small>Successful Transactions</small>

<i class="bi bi-check-circle-fill"></i>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card summary-card bg-primary position-relative">

<div class="card-body">

<h6>Total Paid Amount</h6>

<h2>

₹ <?= number_format($totalAmount,2) ?>

</h2>

<small>Lifetime Payments</small>

<i class="bi bi-cash-stack"></i>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card summary-card bg-warning position-relative">

<div class="card-body">

<h6>Last Payment</h6>

<h5>

<?php

if($lastPayment){

echo date("d M Y",strtotime($lastPayment['payment_date']));

}else{

echo "No Payment";

}

?>

</h5>

<small>Latest Transaction</small>

<i class="bi bi-calendar-check-fill"></i>

</div>

</div>

</div>

</div>

<!-- ===============================
SEARCH
================================ -->

<div class="card border-0 shadow mb-4">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input

type="text"

name="search"

class="form-control form-control-lg search-box"

placeholder="Search by Bill Number, Transaction ID or Payment Method"

value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-2 d-grid">

<button class="btn btn-primary btn-lg">

<i class="bi bi-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

</div>

<!-- ==========================================
        PAYMENT HISTORY TABLE
========================================== -->

<div class="card border-0 shadow-lg">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="bi bi-clock-history"></i>

            Payment History

        </h5>

    </div>

    <div class="card-body">

<?php if(mysqli_num_rows($paymentQuery)>0){ ?>

<div class="table-responsive">

<table class="table table-hover table-bordered align-middle">

<thead class="table-dark">

<tr>

<th>#</th>

<th>Bill No</th>

<th>Billing Month</th>

<th>Amount</th>

<th>Payment Method</th>

<th>Transaction ID</th>

<th>Payment Date</th>

<th>Status</th>

<th width="220">Action</th>

</tr>

</thead>

<tbody>

<?php

$sl=1;

while($row=mysqli_fetch_assoc($paymentQuery)){

?>

<tr>

<td><?= $sl++ ?></td>

<td>

<strong>

<?= htmlspecialchars($row['bill_no']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($row['month']) ?>

</td>

<td>

<strong class="text-success">

₹ <?= number_format($row['amount'],2) ?>

</strong>

</td>

<td>

<span class="badge bg-primary">

<?= htmlspecialchars($row['payment_method']) ?>

</span>

</td>

<td>

<small>

<?= htmlspecialchars($row['transaction_id']) ?>

</small>

</td>

<td>

<?= date("d M Y h:i A",strtotime($row['payment_date'])) ?>

</td>

<td>

<?php

if($row['payment_status']=="Success"){

echo '<span class="badge bg-success">Success</span>';

}else{

echo '<span class="badge bg-danger">'.$row['payment_status'].'</span>';

}

?>

</td>

<td>

<a href="view_receipt.php?id=<?= $row['id'] ?>"

class="btn btn-sm btn-primary">

<i class="bi bi-eye-fill"></i>

View

</a>

<a href="download_receipt.php?id=<?= $row['id'] ?>"

class="btn btn-sm btn-success">

<i class="bi bi-download"></i>

PDF

</a>

<button

onclick="window.open('print_receipt.php?id=<?= $row['id'] ?>','_blank')"

class="btn btn-sm btn-dark">

<i class="bi bi-printer-fill"></i>

Print

</button>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php }else{ ?>

<div class="text-center py-5">

<i class="bi bi-receipt display-1 text-secondary"></i>

<h3 class="mt-3">

No Payment Records Found

</h3>

<p class="text-muted">

You haven't made any electricity bill payments yet.

</p>

<a href="pay_bill.php"

class="btn btn-success">

<i class="bi bi-credit-card-fill"></i>

Pay Bill

</a>

</div>

<?php } ?>

    </div>

</div>

<!-- ==========================================
        FOOTER
========================================== -->

<footer class="mt-5">

    <div class="card border-0 shadow">

        <div class="card-body text-center">

            <h5 class="text-primary">

                <i class="bi bi-lightning-charge-fill"></i>

                Assam Power Distribution Company Limited

            </h5>

            <p class="mb-2">

                APDCL Consumer Portal • Payment History

            </p>

            <small class="text-muted">

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*==============================
    Table Search Highlight
==============================*/

const searchBox = document.querySelector('input[name="search"]');

if(searchBox){

searchBox.addEventListener('focus',function(){

this.classList.add('border-primary');

});

searchBox.addEventListener('blur',function(){

this.classList.remove('border-primary');

});

}

/*==============================
    Card Animation
==============================*/

document.querySelectorAll('.summary-card').forEach(function(card){

card.addEventListener('mouseenter',function(){

this.style.transform="translateY(-8px)";

});

card.addEventListener('mouseleave',function(){

this.style.transform="translateY(0px)";

});

});

/*==============================
    Tooltip
==============================*/

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

tooltipTriggerList.map(function (tooltipTriggerEl) {

return new bootstrap.Tooltip(tooltipTriggerEl);

});

</script>

</body>

</html>