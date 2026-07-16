<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    CHECK RECEIPT ID
=========================================*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);

/*=========================================
    FETCH PAYMENT
=========================================*/

$paymentQuery = mysqli_query($conn,"
SELECT *
FROM payments
WHERE id='$payment_id'
AND consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($paymentQuery)==0){
    die("Invalid Receipt.");
}

$payment = mysqli_fetch_assoc($paymentQuery);

/*=========================================
    FETCH CONSUMER
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    FETCH BILL
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='".$payment['bill_id']."'
LIMIT 1
");

$bill = mysqli_fetch_assoc($billQuery);

/*=========================================
    RECEIPT NUMBER
=========================================*/

$receiptNo = "APDCL-REC-".str_pad($payment['id'],6,"0",STR_PAD_LEFT);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Print Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
    font-family:'Segoe UI',sans-serif;
}

.receipt{
    max-width:900px;
    margin:30px auto;
    background:#fff;
    border:2px solid #000;
    padding:30px;
}

.logo{
    width:80px;
    height:80px;
}

.table td,
.table th{
    padding:8px;
}

.title{
    font-size:30px;
    font-weight:bold;
    color:#0d47a1;
}

.subtitle{
    font-size:15px;
}

.section-title{
    background:#0d47a1;
    color:#fff;
    padding:8px 12px;
    font-weight:bold;
    margin-top:20px;
}

@media print{

body{
    background:#fff;
}

.receipt{
    border:none;
    box-shadow:none;
    margin:0;
    max-width:100%;
}

.no-print{
    display:none;
}

}

</style>

</head>

<body>

<div class="receipt">

<!-- ================= HEADER ================= -->

<div class="row align-items-center">

<div class="col-2 text-center">

<img src="../assets/images/logo-circle.png" class="logo">

</div>

<div class="col-7">

<div class="title">

APDCL

</div>

<div class="subtitle">

Assam Power Distribution Company Limited

</div>

<div class="subtitle">

Electricity Bill Payment Receipt

</div>

</div>

<div class="col-3 text-end">

<strong>

Receipt No.

</strong>

<br>

<?= $receiptNo ?>

</div>

</div>

<hr>

<!-- ================= CONSUMER DETAILS ================= -->

<div class="section-title">

Consumer Information

</div>

<table class="table table-bordered">

<tr>

<th width="30%">Consumer No</th>

<td><?= htmlspecialchars($user['consumer_no']) ?></td>

</tr>

<tr>

<th>Name</th>

<td><?= htmlspecialchars($user['name']) ?></td>

</tr>

<tr>

<th>Mobile</th>

<td><?= htmlspecialchars($user['mobile']) ?></td>

</tr>

<tr>

<th>Email</th>

<td><?= htmlspecialchars($user['email']) ?></td>

</tr>

<tr>

<th>Address</th>

<td><?= nl2br(htmlspecialchars($user['address'])) ?></td>

</tr>

</table>

<!-- ================= PAYMENT DETAILS ================= -->

<div class="section-title">

Payment Information

</div>

<table class="table table-bordered">

<tr>

<th width="30%">Transaction ID</th>

<td><?= htmlspecialchars($payment['transaction_id']) ?></td>

</tr>

<tr>

<th>Payment Method</th>

<td><?= htmlspecialchars($payment['payment_method']) ?></td>

</tr>

<tr>

<th>Payment Date</th>

<td>

<?= date("d M Y h:i A",strtotime($payment['payment_date'])) ?>

</td>

</tr>

<tr>

<th>Status</th>

<td>

<span class="badge bg-success">

SUCCESS

</span>

</td>

</tr>

</table>

<!-- ================= BILL DETAILS ================= -->

<div class="section-title">

Bill Details

</div>

<table class="table table-bordered">

<tr>

<th width="30%">Bill Number</th>

<td><?= htmlspecialchars($bill['bill_no']) ?></td>

</tr>

<tr>

<th>Billing Month</th>

<td><?= htmlspecialchars($bill['month']) ?></td>

</tr>

<tr>

<th>Due Date</th>

<td><?= date("d M Y",strtotime($bill['due_date'])) ?></td>

</tr>

<tr>

<th>Total Bill</th>

<td class="fw-bold text-success">

₹ <?= number_format($bill['total_bill'],2) ?>

</td>

</tr>

</table>

<!-- ================= PAYMENT SUMMARY ================= -->

<div class="section-title">

Payment Summary

</div>

<table class="table table-bordered">

<tr>

<th width="30%">Bill Amount</th>

<td class="text-end">

₹ <?= number_format($bill['total_bill'],2) ?>

</td>

</tr>

<tr>

<th>Amount Paid</th>

<td class="text-end text-success fw-bold">

₹ <?= number_format($payment['amount'],2) ?>

</td>

</tr>

<tr>

<th>Balance</th>

<td class="text-end">

₹ 0.00

</td>

</tr>

<tr class="table-success">

<th>Payment Status</th>

<td class="fw-bold">

SUCCESS

</td>

</tr>

</table>

<!-- ================= THANK YOU ================= -->

<div class="alert alert-success mt-4">

<h5>

<i class="bi bi-check-circle-fill"></i>

Payment Successful

</h5>

<p class="mb-0">

Thank you for paying your electricity bill through the
<strong>APDCL Consumer Portal</strong>.
Please keep this receipt for future reference.

</p>

</div>

<!-- ================= SIGNATURE ================= -->

<div class="row mt-5">

<div class="col-6 text-center">

<hr>

Consumer Signature

</div>

<div class="col-6 text-center">

<hr>

Authorized Officer

</div>

</div>

<!-- ================= ACTION BUTTONS ================= -->

<div class="text-center mt-5 no-print">

<button onclick="window.print()"

class="btn btn-primary btn-lg">

<i class="bi bi-printer-fill"></i>

Print Receipt

</button>

<a href="payment_history.php"

class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-arrow-left-circle"></i>

Back

</a>

</div>

<!-- ================= FOOTER ================= -->

<hr class="mt-5">

<div class="text-center">

    <h5 class="text-primary">

        Assam Power Distribution Company Limited

    </h5>

    <p class="mb-1">

        Consumer Self Service Portal

    </p>

    <p class="text-muted mb-0">

        This is a computer-generated receipt and does not require a physical signature.

    </p>

    <small class="text-muted">

        Receipt Generated On :

        <?= date("d M Y h:i:s A"); ?>

    </small>

</div>

</div>

<!-- ================= BOOTSTRAP ================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*==================================
    Auto Print
==================================*/

window.onload = function(){

    window.print();

};

/*==================================
    Auto Close After Printing
==================================*/

window.onafterprint = function(){

    window.close();

};

</script>

</body>

</html>