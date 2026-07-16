<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$bills = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
");

if(!$bills){
    die("Query Error: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>My Bills</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fb;
}

.navbar{
    background:#0d6efd;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.10);
}

.badge{
    font-size:14px;
    padding:8px 12px;
}

.table th{
    background:#0d6efd;
    color:#fff;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container-fluid">

<span class="navbar-brand">

⚡ APDCL Consumer Portal

</span>

<a href="dashboard.php" class="btn btn-light">

Dashboard

</a>

</div>

</nav>

<div class="container mt-4">

<div class="card">

<div class="card-header bg-primary text-white">

<h3 class="mb-0">

<i class="bi bi-receipt-cutoff"></i>

My Electricity Bills

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>

<th>ID</th>
<th>Month</th>
<th>Units</th>
<th>Bill Amount</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php
if(mysqli_num_rows($bills)>0){

while($bill=mysqli_fetch_assoc($bills)){
?>

<tr>

<td><?= $bill['id']; ?></td>

<td><?= htmlspecialchars($bill['month']); ?></td>

<td><?= $bill['units']; ?> Units</td>

<td>₹ <?= number_format($bill['total_bill'],2); ?></td>

<td>

<?php

if($bill['status']=="Paid"){

echo "<span class='badge bg-success'>Paid</span>";

}else{

echo "<span class='badge bg-danger'>Unpaid</span>";

}

?>

</td>

<td>

<?php if($bill['status']=="Unpaid"){ ?>

<a href="pay_bill.php?id=<?= $bill['id']; ?>"
class="btn btn-success btn-sm">

<i class="bi bi-credit-card"></i>

Pay

</a>

<?php } ?>

<a href="print_bill.php?id=<?= $bill['id']; ?>"
class="btn btn-primary btn-sm"
target="_blank">

<i class="bi bi-printer"></i>

Print

</a>

</td>

</tr>

<?php
}

}else{
?>

<tr>

<td colspan="6" class="text-center text-muted">

No Bills Found

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="mt-3">

<a href="dashboard.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

</div>

</div>

</body>

</html>