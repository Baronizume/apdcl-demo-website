<?php
session_start();
include("../db.php");

if(!isset($_SESSION['consumer_no'])){
    header("Location:login.php");
    exit();
}

$consumer_no=$_SESSION['consumer_no'];

if(!isset($_GET['id'])){
    die("Invalid Request");
}

$id=intval($_GET['id']);

$paymentQuery=mysqli_query($conn,"
SELECT *
FROM payments
WHERE id='$id'
AND consumer_no='$consumer_no'
");

$payment=mysqli_fetch_assoc($paymentQuery);

if(!$payment){
    die("Receipt Not Found");
}

$userQuery=mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
");

$user=mysqli_fetch_assoc($userQuery);

$billQuery=mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='".$payment['bill_id']."'
");

$bill=mysqli_fetch_assoc($billQuery);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Payment Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{

background:#f4f7fb;

font-family:Arial;

}

.receipt{

width:850px;

margin:30px auto;

background:white;

padding:35px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.15);

}

.logo{

width:80px;

}

.header{

text-align:center;

margin-bottom:20px;

}

.table td{

padding:10px;

}

.paid{

font-size:40px;

font-weight:bold;

color:green;

border:4px solid green;

padding:10px 30px;

display:inline-block;

transform:rotate(-15deg);

margin-top:20px;

}

@media print{

.btn{

display:none;

}

body{

background:white;

}

.receipt{

box-shadow:none;

width:100%;

margin:0;

}

}

</style>

</head>

<body>

<div class="receipt">

<div class="header">

<img src="../assets/images/apdcl-logo.png" class="logo">

<h2 class="mt-2 text-primary">

Assam Power Distribution Company Limited

</h2>

<h4>Payment Receipt</h4>

<hr>

</div>

<div class="row">

<div class="col-md-6">

<table class="table table-borderless">

<tr>

<th>Receipt No</th>

<td>RCPT<?= str_pad($payment['id'],6,"0",STR_PAD_LEFT); ?></td>

</tr>

<tr>

<th>Consumer No</th>

<td><?= htmlspecialchars($payment['consumer_no']); ?></td>

</tr>

<tr>

<th>Name</th>

<td><?= htmlspecialchars($user['name']); ?></td>

</tr>

<tr>

<th>Bill ID</th>

<td><?= $payment['bill_id']; ?></td>

</tr>

</table>

</div>

<div class="col-md-6">

<table class="table table-borderless">

<tr>

<th>Payment Date</th>

<td><?= $payment['payment_date']; ?></td>

</tr>

<tr>

<th>Payment Mode</th>

<td><?= htmlspecialchars($payment['payment_mode']); ?></td>

</tr>

<tr>

<th>Transaction ID</th>

<td><?= htmlspecialchars($payment['transaction_id']); ?></td>

</tr>

<tr>

<th>Billing Month</th>

<td><?= htmlspecialchars($bill['month']); ?></td>

</tr>

</table>

</div>

</div>

<hr>

<h4 class="text-primary">

Payment Details

</h4>

<table class="table table-bordered">

<tr>

<th>Description</th>

<th>Amount</th>

</tr>

<tr>

<td>Electricity Bill Payment</td>

<td>₹<?= number_format($payment['amount'],2); ?></td>

</tr>

<tr class="table-success">

<th>Total Paid</th>

<th>

₹<?= number_format($payment['amount'],2); ?>

</th>

</tr>

</table>

<div class="text-center">

<div class="paid">

PAID

</div>

</div>

<div class="text-center mt-4">

<button onclick="window.print()" class="btn btn-primary">

<i class="fas fa-print"></i>

Print Receipt

</button>

<a href="payment_history.php" class="btn btn-success">

<i class="fas fa-list"></i>

Payment History

</a>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fas fa-home"></i>

Dashboard

</a>

</div>

</div>

</body>

</html>