<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$success = "";
$error = "";

/*=========================================
    CONSUMER DETAILS
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
    LATEST UNPAID BILL
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Unpaid'
ORDER BY bill_date DESC
LIMIT 1
");

if(mysqli_num_rows($billQuery)>0){

    $bill = mysqli_fetch_assoc($billQuery);

}else{

    $bill = null;

}

/*=========================================
    PAYMENT PROCESS
=========================================*/

if(isset($_POST['pay_bill']) && $bill){

    $payment_method = mysqli_real_escape_string(
        $conn,
        $_POST['payment_method']
    );

    $transaction_id = "TXN".date("YmdHis").rand(1000,9999);

    $amount = $bill['total_bill'];

    $bill_id = $bill['id'];

    mysqli_begin_transaction($conn);

    try{

        /*--------------------------
            SAVE PAYMENT
        ---------------------------*/

        $insertPayment = mysqli_query($conn,"
        INSERT INTO payments(

            consumer_no,
            bill_id,
            amount,
            payment_method,
            transaction_id,
            payment_status,
            payment_date

        )VALUES(

            '$consumer_no',
            '$bill_id',
            '$amount',
            '$payment_method',
            '$transaction_id',
            'Success',
            NOW()

        )
        ");

        if(!$insertPayment){
            throw new Exception(mysqli_error($conn));
        }

        /*--------------------------
            UPDATE BILL
        ---------------------------*/

        $updateBill = mysqli_query($conn,"
        UPDATE bills
        SET

            status='Paid',
            payment_status='Paid',
            payment_mode='$payment_method',
            payment_date=NOW()

        WHERE id='$bill_id'
        ");

        if(!$updateBill){
            throw new Exception(mysqli_error($conn));
        }

        mysqli_commit($conn);

        $success = "Payment completed successfully.";

        /* Reload Bill */

        $billQuery = mysqli_query($conn,"
        SELECT *
        FROM bills
        WHERE id='$bill_id'
        ");

        $bill = mysqli_fetch_assoc($billQuery);

    }catch(Exception $e){

        mysqli_rollback($conn);

        $error = $e->getMessage();

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pay Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef4fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0056b3;
    box-shadow:0 4px 12px rgba(0,0,0,.15);
}

.brand{
    color:#fff;
    font-size:24px;
    font-weight:700;
}

.payment-card{
    border:none;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}

.card-header{
    font-size:20px;
    font-weight:bold;
}

.summary-box{
    background:#f8f9fa;
    border-radius:15px;
    padding:20px;
}

.summary-box table td{
    padding:10px;
}

.amount-box{
    background:#198754;
    color:#fff;
    border-radius:15px;
    padding:20px;
    text-align:center;
}

.amount-box h1{
    font-size:42px;
    font-weight:bold;
}

.badge-status{
    font-size:15px;
    padding:8px 18px;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container">

<span class="brand">

⚡ APDCL Consumer Portal

</span>

<a href="dashboard.php" class="btn btn-light">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

</div>

</nav>

<div class="container py-5">

<?php if($success!=""){ ?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill"></i>

<?= $success ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<i class="bi bi-x-circle-fill"></i>

<?= $error ?>

</div>

<?php } ?>

<?php if($bill){ ?>

<div class="card payment-card">

<div class="card-header bg-primary text-white">

<i class="bi bi-credit-card-fill"></i>

Electricity Bill Payment

</div>

<div class="card-body">

<div class="row">

<div class="col-lg-7">

<div class="summary-box">

<h4 class="mb-4">

Consumer Details

</h4>

<table class="table">

<tr>

<td width="35%"><strong>Consumer No</strong></td>

<td><?= htmlspecialchars($user['consumer_no']) ?></td>

</tr>

<tr>

<td><strong>Name</strong></td>

<td><?= htmlspecialchars($user['name']) ?></td>

</tr>

<tr>

<td><strong>Mobile</strong></td>

<td><?= htmlspecialchars($user['mobile']) ?></td>

</tr>

<tr>

<td><strong>Meter No</strong></td>

<td><?= htmlspecialchars($user['meter_no']) ?></td>

</tr>

<tr>

<td><strong>Billing Month</strong></td>

<td><?= htmlspecialchars($bill['month']) ?></td>

</tr>

<tr>

<td><strong>Due Date</strong></td>

<td>

<?= date("d M Y",strtotime($bill['due_date'])) ?>

</td>

</tr>

<tr>

<td><strong>Status</strong></td>

<td>

<span class="badge bg-danger badge-status">

<?= htmlspecialchars($bill['status']) ?>

</span>

</td>

</tr>

</table>

</div>

</div>

<div class="col-lg-5">

<div class="amount-box">

<h6>Total Payable</h6>

<h1>

₹ <?= number_format($bill['total_bill'],2) ?>

</h1>

<p>

Please pay before the due date to avoid late payment charges.

</p>

</div>

<br>

<div class="card border-success">

<div class="card-body text-center">

<h5>

<i class="bi bi-shield-check"></i>

Secure Payment

</h5>

<p class="mb-0">

Your payment is processed through a secure payment gateway.

</p>

</div>

</div>

</div>

</div>

<hr class="my-4">
<!-- ==========================================
        PAYMENT FORM
========================================== -->

<form method="POST">

<div class="row g-4">

    <!-- Payment Methods -->
    <div class="col-lg-7">

        <h4 class="mb-4">
            <i class="bi bi-wallet2 text-primary"></i>
            Select Payment Method
        </h4>

        <div class="form-check border rounded p-3 mb-3 shadow-sm">

            <input class="form-check-input"
                   type="radio"
                   name="payment_method"
                   id="upi"
                   value="UPI"
                   checked>

            <label class="form-check-label w-100" for="upi">

                <h5 class="mb-1">
                    <i class="bi bi-phone-fill text-success"></i>
                    UPI Payment
                </h5>

                <small class="text-muted">
                    Google Pay • PhonePe • Paytm • BHIM
                </small>

            </label>

        </div>

        <div class="form-check border rounded p-3 mb-3 shadow-sm">

            <input class="form-check-input"
                   type="radio"
                   name="payment_method"
                   id="card"
                   value="Debit/Credit Card">

            <label class="form-check-label w-100" for="card">

                <h5 class="mb-1">
                    <i class="bi bi-credit-card-fill text-primary"></i>
                    Debit / Credit Card
                </h5>

                <small class="text-muted">
                    Visa • MasterCard • RuPay
                </small>

            </label>

        </div>

        <div class="form-check border rounded p-3 mb-3 shadow-sm">

            <input class="form-check-input"
                   type="radio"
                   name="payment_method"
                   id="netbanking"
                   value="Net Banking">

            <label class="form-check-label w-100" for="netbanking">

                <h5 class="mb-1">
                    <i class="bi bi-bank text-warning"></i>
                    Net Banking
                </h5>

                <small class="text-muted">
                    All Major Indian Banks
                </small>

            </label>

        </div>

        <div class="form-check border rounded p-3 mb-3 shadow-sm">

            <input class="form-check-input"
                   type="radio"
                   name="payment_method"
                   id="wallet"
                   value="Wallet">

            <label class="form-check-label w-100" for="wallet">

                <h5 class="mb-1">
                    <i class="bi bi-wallet-fill text-danger"></i>
                    Mobile Wallet
                </h5>

                <small class="text-muted">
                    Paytm Wallet • Amazon Pay • Mobikwik
                </small>

            </label>

        </div>

    </div>

    <!-- QR Payment -->
    <div class="col-lg-5">

        <div class="card border-success shadow">

            <div class="card-header bg-success text-white">

                <i class="bi bi-qr-code-scan"></i>

                Scan & Pay

            </div>

            <div class="card-body text-center">

                <img src="../assets/images/qr-placeholder.png"
                     class="img-fluid mb-3"
                     style="max-width:220px;">

                <h5 class="text-success">

                    ₹ <?= number_format($bill['total_bill'],2) ?>

                </h5>

                <p class="text-muted">

                    Scan this QR code using any UPI App to make payment.

                </p>

                <div class="alert alert-info">

                    Consumer No:
                    <strong>

                        <?= htmlspecialchars($user['consumer_no']) ?>

                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>

<hr class="my-4">

<div class="text-center">

    <button type="submit"
            name="pay_bill"
            class="btn btn-success btn-lg px-5">

        <i class="bi bi-lock-fill"></i>

        Pay ₹ <?= number_format($bill['total_bill'],2) ?>

    </button>

    <a href="current_bill.php"
       class="btn btn-secondary btn-lg ms-2">

        <i class="bi bi-arrow-left-circle"></i>

        Cancel

    </a>

</div>

</form>

<?php } else { ?>

<div class="alert alert-success text-center p-5">

    <i class="bi bi-check-circle-fill display-1 text-success"></i>

    <h3 class="mt-3">

        No Pending Bills

    </h3>

    <p>

        You have already paid all your electricity bills.

    </p>

    <a href="dashboard.php"
       class="btn btn-primary">

        <i class="bi bi-house-fill"></i>

        Back to Dashboard

    </a>

</div>

<?php } ?>
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

            <p class="mb-1">

                Consumer Portal | Secure Electricity Bill Payment

            </p>

            <small class="text-muted">

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if($success!=""){ ?>

<script>

setTimeout(function(){

    window.location.href="payment_history.php";

},3000);

</script>

<?php } ?>

<script>

document.querySelectorAll(".form-check").forEach(function(card){

    card.addEventListener("click",function(){

        this.querySelector("input").checked=true;

    });

});

</script>

</body>

</html>