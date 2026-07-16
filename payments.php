<?php
session_start();
include("../db.php");

/*=========================================
    LOGIN CHECK
=========================================*/

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

$consumer_no = $_SESSION['consumer'];

/*=========================================
    GET CONSUMER DETAILS
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

$name   = $user['name'];
$mobile = $user['mobile'];
$email  = $user['email'];

/*=========================================
    LOAD PENDING BILLS
=========================================*/

$pendingBills = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
AND payment_mode='Pending'
ORDER BY bill_date DESC
");

/*=========================================
    GENERATE TRANSACTION ID
=========================================*/

$result = mysqli_query($conn,"
SELECT id
FROM payments
ORDER BY id DESC
LIMIT 1
");

if(mysqli_num_rows($result)>0){

    $row = mysqli_fetch_assoc($result);

    $nextPaymentId = $row['id'] + 1;

}else{

    $nextPaymentId = 1;

}

$transaction_id =
"APDCL-".
date("Ymd").
"-".
str_pad($nextPaymentId,6,"0",STR_PAD_LEFT);

/*=========================================
    DEFAULT VARIABLES
=========================================*/

$success = "";
$error   = "";

/*=========================================
    PROCESS PAYMENT
=========================================*/

if(isset($_POST['pay_bill']))
{

    $bill_id = (int)$_POST['bill_id'];

    $payment_method = mysqli_real_escape_string(
        $conn,
        $_POST['payment_method']
    );

    /*=====================================
        GET BILL DETAILS
    =====================================*/

    $billQuery = mysqli_query($conn,"
    SELECT *
    FROM bills
    WHERE id='$bill_id'
    AND consumer_no='$consumer_no'
    LIMIT 1
    ");

    if(mysqli_num_rows($billQuery)==0){

        $error = "Invalid bill selected.";

    }else{

        $bill = mysqli_fetch_assoc($billQuery);

        $amount = $bill['total_bill'];

        mysqli_begin_transaction($conn);

        try{

            /*=================================
                INSERT PAYMENT
            =================================*/

            mysqli_query($conn,"
            INSERT INTO payments
            (
                transaction_id,
                bill_id,
                consumer_no,
                amount,
                payment_method,
                payment_date,
                payment_status
            )
            VALUES
            (
                '$transaction_id',
                '$bill_id',
                '$consumer_no',
                '$amount',
                '$payment_method',
                NOW(),
                'Paid'
            )
            ");

            /*=================================
                UPDATE BILL
            =================================*/

            mysqli_query($conn,"
            UPDATE bills
            SET
                payment_mode='$payment_method'
            WHERE id='$bill_id'
            ");

            mysqli_commit($conn);

            $success =
            "Payment Successful.<br>
            Transaction ID :
            <strong>$transaction_id</strong>";

            /*=================================
                GENERATE NEXT TRANSACTION ID
            =================================*/

            $nextPaymentId++;

            $transaction_id =
            "APDCL-".
            date("Ymd").
            "-".
            str_pad($nextPaymentId,6,"0",STR_PAD_LEFT);

        }catch(Exception $e){

            mysqli_rollback($conn);

            $error = "Payment failed.";

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>

Pay Electricity Bill | APDCL Consumer Portal

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

.page{
    padding:30px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.summary-card{
    transition:.3s;
}

.summary-card:hover{
    transform:translateY(-5px);
}

.table th{
    background:#0d6efd;
    color:#fff;
}

.badge-pending{
    background:#dc3545;
}

.header-logo{
    width:75px;
    height:75px;
}

</style>

</head>

<body>

<div class="page">

<div class="container-fluid">

<!-- APDCL Header -->

<div class="card mb-4">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-1 text-center">

<img
src="../assets/images/logo-circle.png"
class="header-logo">

</div>

<div class="col-md-8">

<h2 class="fw-bold text-primary mb-1">

Assam Power Distribution Company Limited

</h2>

<p class="text-muted mb-0">

Electricity Bill Payment Portal

</p>

</div>

<div class="col-md-3 text-end">

<a href="dashboard.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left-circle"></i>

Dashboard

</a>

</div>

</div>

</div>

</div>

<!-- Consumer Card -->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="bi bi-person-circle"></i>

Consumer Information

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">

<strong>Consumer No</strong>

<br>

<?= htmlspecialchars($consumer_no) ?>

</div>

<div class="col-md-3">

<strong>Name</strong>

<br>

<?= htmlspecialchars($name) ?>

</div>

<div class="col-md-3">

<strong>Mobile</strong>

<br>

<?= htmlspecialchars($mobile) ?>

</div>

<div class="col-md-3">

<strong>Email</strong>

<br>

<?= htmlspecialchars($email) ?>

</div>

</div>

</div>

</div>

<!-- Success / Error -->

<?php if($success!=""){ ?>

<div class="alert alert-success">

<?= $success ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php } ?>

<!-- Summary Cards -->

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="card summary-card bg-primary text-white">

<div class="card-body text-center">

<i class="bi bi-receipt-cutoff display-5"></i>

<h6 class="mt-3">

Pending Bills

</h6>

<h2>

<?= mysqli_num_rows($pendingBills) ?>

</h2>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card summary-card bg-success text-white">

<div class="card-body text-center">

<i class="bi bi-credit-card display-5"></i>

<h6 class="mt-3">

Transaction ID

</h6>

<h5>

<?= $transaction_id ?>

</h5>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card summary-card bg-warning">

<div class="card-body text-center">

<i class="bi bi-wallet2 display-5"></i>

<h6 class="mt-3">

Payment Status

</h6>

<h4>

Pending

</h4>

</div>

</div>

</div>

</div>

<!-- Pending Bills -->

<div class="card">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

<i class="bi bi-lightning-charge-fill"></i>

Pending Bills

</h5>

</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>

<th>Bill No</th>

<th>Month</th>

<th>Due Date</th>

<th>Units</th>

<th>Amount</th>

<th>Status</th>

<th width="160">

Action

</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($pendingBills)>0){

mysqli_data_seek($pendingBills,0);

while($bill=mysqli_fetch_assoc($pendingBills)){

?>

<tr>

<td><?= htmlspecialchars($bill['bill_no']) ?></td>

<td><?= htmlspecialchars($bill['bill_month']) ?></td>

<td><?= date("d M Y",strtotime($bill['due_date'])) ?></td>

<td><?= number_format($bill['units']) ?></td>

<td>

<strong class="text-success">

₹<?= number_format($bill['total_bill'],2) ?>

</strong>

</td>

<td>

<span class="badge bg-danger">

Pending

</span>

</td>

<td>

<form method="POST">

<input
type="hidden"
name="bill_id"
value="<?= $bill['id'] ?>">

<select
name="payment_method"
class="form-select mb-2"
required>

<option value="">Select Method</option>

<option value="Cash">💵 Cash</option>

<option value="UPI">📱 UPI</option>

<option value="Debit Card">💳 Debit Card</option>

<option value="Credit Card">💳 Credit Card</option>

<option value="Net Banking">🏦 Net Banking</option>

<option value="Mobile Banking">📲 Mobile Banking</option>

<option value="Cheque">🧾 Cheque</option>

<option value="APDCL Online Portal">
🌐 APDCL Online Portal
</option>

</select>

<button
type="submit"
name="pay_bill"
class="btn btn-success btn-sm w-100">

<i class="bi bi-credit-card-fill"></i>

Pay Now

</button>

</form>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center text-danger">

No Pending Bills Found

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>