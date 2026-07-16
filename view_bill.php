<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    CHECK BILL ID
=========================================*/

if(!isset($_GET['id']))
{
    header("Location: bill_history.php");
    exit();
}

$billId = (int)$_GET['id'];

/*=========================================
    FETCH BILL
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='$billId'
AND consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($billQuery)==0)
{
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($billQuery);

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
    BILL CALCULATIONS
=========================================*/

$previousReading = $bill['previous_reading'];
$currentReading  = $bill['current_reading'];

$units = $currentReading - $previousReading;

$energyCharge = $bill['energy_charge'];
$fixedCharge = $bill['fixed_charge'];
$electricityDuty = $bill['electricity_duty'];
$subsidy = $bill['subsidy'];

$totalBill = $bill['total_bill'];

$paymentStatus = $bill['status'];

$paymentMode = $bill['payment_mode'];

$billMonth = $bill['month'];

$billDate = $bill['bill_date'];

$dueDate = $bill['due_date'];

$billNo = $bill['bill_no'];

/*=========================================
    AMOUNT IN WORDS
=========================================*/

function numberToWords($number)
{
    $fmt = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    return ucwords($fmt->format($number));
}

$amountWords = numberToWords(round($totalBill));
?>
<!-- =========================================
        METER READING DETAILS
========================================= -->

<div class="section-title">

Meter Reading Details

</div>

<div class="p-3">

<table class="table table-bordered">

<tr class="table-light">

<th>Previous Reading</th>

<th>Current Reading</th>

<th>Units Consumed</th>

<th>MF</th>

<th>Power Factor</th>

</tr>

<tr>

<td><?= number_format($bill['previous_reading'],2) ?></td>

<td><?= number_format($bill['current_reading'],2) ?></td>

<td>

<strong class="text-primary">

<?= $units ?>

Units

</strong>

</td>

<td><?= $bill['mf'] ?></td>

<td><?= $bill['power_factor'] ?>%</td>

</tr>

</table>

</div>

<!-- =========================================
        BILL CHARGES
========================================= -->

<div class="section-title">

Bill Charges

</div>

<div class="p-3">

<table class="table table-bordered table-striped">

<thead class="table-primary">

<tr>

<th>Description</th>

<th class="text-end">Amount (₹)</th>

</tr>

</thead>

<tbody>

<tr>

<td>Energy Charge</td>

<td class="text-end">

<?= number_format($energyCharge,2) ?>

</td>

</tr>

<tr>

<td>Fixed Charge</td>

<td class="text-end">

<?= number_format($fixedCharge,2) ?>

</td>

</tr>

<tr>

<td>Electricity Duty</td>

<td class="text-end">

<?= number_format($electricityDuty,2) ?>

</td>

</tr>

<tr>

<td>FPPPA Charge</td>

<td class="text-end">

<?= number_format($bill['fpppa_charge'],2) ?>

</td>

</tr>

<tr>

<td>Outstanding Amount</td>

<td class="text-end text-danger">

<?= number_format($bill['outstanding_amount'],2) ?>

</td>

</tr>

<tr>

<td>Adjustment Amount</td>

<td class="text-end text-success">

<?= number_format($bill['adjustment_amount'],2) ?>

</td>

</tr>

<tr>

<td>Government Subsidy</td>

<td class="text-end text-success">

- <?= number_format($bill['government_subsidy'],2) ?>

</td>

</tr>

<tr>

<td>Tariff Subsidy</td>

<td class="text-end text-success">

- <?= number_format($bill['tariff_subsidy'],2) ?>

</td>

</tr>

<tr>

<td>Solar Rebate</td>

<td class="text-end text-success">

- <?= number_format($bill['solar_rebate'],2) ?>

</td>

</tr>

<tr>

<td>Other Subsidy</td>

<td class="text-end text-success">

- <?= number_format($subsidy,2) ?>

</td>

</tr>

<tr class="table-warning">

<th>Total Bill Amount</th>

<th class="text-end">

₹ <?= number_format($totalBill,2) ?>

</th>

</tr>

</tbody>

</table>

</div>

<!-- =========================================
        PAYMENT SUMMARY
========================================= -->

<div class="section-title">

Payment Summary

</div>

<div class="p-3">

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th width="50%">Pay Before Due Date</th>

<td>

₹ <?= number_format($bill['payable_before_due'],2) ?>

</td>

</tr>

<tr>

<th>Pay After Due Date</th>

<td class="text-danger">

₹ <?= number_format($bill['payable_after_due'],2) ?>

</td>

</tr>

<tr>

<th>Due Date</th>

<td>

<?= date("d M Y",strtotime($dueDate)) ?>

</td>

</tr>

<tr>

<th>Payment Status</th>

<td>

<?php

if($paymentStatus=="Paid"){

echo "<span class='badge bg-success fs-6'>PAID</span>";

}else{

echo "<span class='badge bg-danger fs-6'>UNPAID</span>";

}

?>

</td>

</tr>

</table>

</div>

<div class="col-md-6">

<div class="card border-success">

<div class="card-header bg-success text-white">

Amount in Words

</div>

<div class="card-body">

<h5 class="text-success">

<?= $amountWords ?>

Rupees Only

</h5>

<hr>

<h2 class="text-end text-primary">

₹ <?= number_format($totalBill,2) ?>

</h2>

</div>

</div>

</div>

</div>

</div>

<!-- =========================================
        IMPORTANT INFORMATION
========================================= -->

<div class="section-title">

Important Information

</div>

<div class="p-4">

<div class="alert alert-warning">

<h5>

<i class="bi bi-exclamation-triangle-fill"></i>

Consumer Instructions

</h5>

<ul class="mb-0">

<li>Pay your electricity bill before the due date to avoid late payment surcharge.</li>

<li>Always mention your Consumer Number while making payment.</li>

<li>Keep this bill safely for future reference.</li>

<li>Report electricity faults immediately through the Consumer Portal.</li>

<li>Do not touch damaged electric lines or transformers.</li>

</ul>

</div>

</div>

<!-- =========================================
        QR PAYMENT SECTION
========================================= -->

<div class="section-title">

Digital Payment

</div>

<div class="p-4">

<div class="row align-items-center">

<div class="col-md-3 text-center">

<img src="../assets/images/qr-placeholder.png"
     class="img-fluid"
     style="max-width:180px;">

</div>

<div class="col-md-9">

<h5>

Scan & Pay

</h5>

<p>

You can pay this electricity bill using any UPI application like

<strong>

PhonePe

</strong>,

<strong>

Google Pay

</strong>,

<strong>

Paytm

</strong>

or

<strong>

BHIM UPI</strong>.

</p>

<p>

Consumer Number:

<strong>

<?= htmlspecialchars($user['consumer_no']) ?>

</strong>

</p>

<p>

Bill Amount:

<strong class="text-success">

₹ <?= number_format($totalBill,2) ?>

</strong>

</p>

</div>

</div>

</div>

<!-- =========================================
        DECLARATION
========================================= -->

<div class="section-title">

Declaration

</div>

<div class="p-4">

<p>

This electricity bill has been generated electronically by

<strong>

Assam Power Distribution Company Limited (APDCL)

</strong>

and does not require a physical signature.

</p>

<p>

If you find any discrepancy in this bill, please contact your nearest APDCL office or submit a complaint through the Consumer Portal.

</p>

</div>

<!-- =========================================
        SIGNATURE
========================================= -->

<div class="row px-4 pb-4">

<div class="col-md-6">

<strong>

Generated On:

</strong>

<?= date("d M Y h:i A") ?>

</div>

<div class="col-md-6 text-end">

<br><br>

_________________________

<br>

Authorized Officer

<br>

APDCL

</div>

</div>

</div>

<!-- =========================================
        FOOTER
========================================= -->

<footer class="bg-dark text-white text-center py-4 mt-4">

<div class="container">

<h5>

⚡ APDCL Consumer Portal

</h5>

<p class="mb-1">

Assam Power Distribution Company Limited

</p>

<small>

Smart Electricity Management System

</small>

<br><br>

<a href="bill_history.php"
class="btn btn-light">

<i class="bi bi-arrow-left-circle"></i>

Back to Bill History

</a>

<button
onclick="window.print()"
class="btn btn-success">

<i class="bi bi-printer-fill"></i>

Print Bill

</button>

<a
href="dashboard.php"
class="btn btn-primary">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>

@media print{

.navbar,
footer,
.btn{

display:none!important;

}

body{

background:#fff;

}

.bill-container{

box-shadow:none!important;

margin:0;

max-width:100%;

}

.section-title{

background:#e9ecef!important;

}

}

</style>

</body>

</html>