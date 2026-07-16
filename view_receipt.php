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

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);

/*=========================================
    FETCH PAYMENT DETAILS
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
    FETCH CONSUMER DETAILS
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    FETCH BILL DETAILS
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='".$payment['bill_id']."'
LIMIT 1
");

$bill = mysqli_fetch_assoc($billQuery);

/*=========================================
    GENERATE RECEIPT NUMBER
=========================================*/

$receiptNo = "APDCL-REC-".str_pad($payment['id'],6,"0",STR_PAD_LEFT);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Payment Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.receipt-card{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.12);
    overflow:hidden;
}

.receipt-header{
    background:#0056b3;
    color:#fff;
    padding:25px;
}

.logo{
    width:75px;
    height:75px;
}

.company-title{
    font-size:28px;
    font-weight:bold;
}

.company-sub{
    font-size:14px;
    opacity:.9;
}

.section-title{
    font-size:18px;
    font-weight:600;
    color:#0056b3;
    border-bottom:2px solid #0d6efd;
    padding-bottom:8px;
    margin-bottom:15px;
}

.table td{
    padding:10px;
}

.badge-paid{
    background:#198754;
    color:#fff;
    padding:8px 18px;
    border-radius:20px;
    font-size:15px;
}

</style>

</head>

<body>

<div class="container">

<div class="receipt-card">

<!-- ================= HEADER ================= -->

<div class="receipt-header">

<div class="row align-items-center">

<div class="col-md-2 text-center">

<img src="../assets/images/logo-circle.png"
     class="logo">

</div>

<div class="col-md-7">

<div class="company-title">

APDCL

</div>

<div class="company-sub">

Assam Power Distribution Company Limited

</div>

<div class="company-sub">

Official Electricity Bill Payment Receipt

</div>

</div>

<div class="col-md-3 text-end">

<h5>

Receipt

</h5>

<h4>

<?= $receiptNo ?>

</h4>

</div>

</div>

</div>

<!-- ================= BODY ================= -->

<div class="p-4">

<div class="row">

<!-- Consumer Details -->

<div class="col-md-6">

<div class="section-title">

Consumer Details

</div>

<table class="table table-borderless">

<tr>

<td><strong>Consumer No</strong></td>

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

<td><strong>Email</strong></td>

<td><?= htmlspecialchars($user['email']) ?></td>

</tr>

<tr>

<td><strong>Address</strong></td>

<td><?= nl2br(htmlspecialchars($user['address'])) ?></td>

</tr>

</table>

</div>

<!-- Payment Details -->

<div class="col-md-6">

<div class="section-title">

Payment Details

</div>

<table class="table table-borderless">

<tr>

<td><strong>Transaction ID</strong></td>

<td><?= htmlspecialchars($payment['transaction_id']) ?></td>

</tr>

<tr>

<td><strong>Payment Date</strong></td>

<td>

<?= date("d M Y",strtotime($payment['payment_date'])) ?>

</td>

</tr>

<tr>

<td><strong>Payment Time</strong></td>

<td>

<?= date("h:i A",strtotime($payment['payment_date'])) ?>

</td>

</tr>

<tr>

<td><strong>Payment Method</strong></td>

<td>

<?= htmlspecialchars($payment['payment_method']) ?>

</td>

</tr>

<tr>

<td><strong>Status</strong></td>

<td>

<span class="badge-paid">

SUCCESS

</span>

</td>

</tr>

</table>

</div>

</div>

<hr class="my-4">

<!-- ==========================================
        BILL DETAILS
========================================== -->

<div class="section-title">

    Bill Details

</div>

<div class="table-responsive">

<table class="table table-bordered">

    <thead class="table-primary">

        <tr>

            <th>Bill No</th>

            <th>Billing Month</th>

            <th>Due Date</th>

            <th>Total Bill</th>

        </tr>

    </thead>

    <tbody>

        <tr>

            <td>

                <?= htmlspecialchars($bill['bill_no']) ?>

            </td>

            <td>

                <?= htmlspecialchars($bill['month']) ?>

            </td>

            <td>

                <?= date("d M Y",strtotime($bill['due_date'])) ?>

            </td>

            <td class="fw-bold text-success">

                ₹ <?= number_format($bill['total_bill'],2) ?>

            </td>

        </tr>

    </tbody>

</table>

</div>

<!-- ==========================================
        PAYMENT SUMMARY
========================================== -->

<div class="row mt-4">

    <div class="col-md-6">

        <div class="alert alert-success">

            <h5>

                <i class="bi bi-check-circle-fill"></i>

                Payment Successful

            </h5>

            <p class="mb-0">

                Your electricity bill has been paid successfully.

                Thank you for using the APDCL Consumer Portal.

            </p>

        </div>

    </div>

    <div class="col-md-6">

        <table class="table table-bordered">

            <tr>

                <th>Total Bill</th>

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

            <tr class="table-success">

                <th>Balance</th>

                <td class="text-end fw-bold">

                    ₹ 0.00

                </td>

            </tr>

        </table>

    </div>

</div>

<hr class="my-4">

<!-- ==========================================
        ACTION BUTTONS
========================================== -->

<div class="text-center mb-4">

    <a href="print_receipt.php?id=<?= $payment['id'] ?>"
       target="_blank"
       class="btn btn-dark btn-lg">

        <i class="bi bi-printer-fill"></i>

        Print Receipt

    </a>

    <a href="download_receipt.php?id=<?= $payment['id'] ?>"
       class="btn btn-success btn-lg ms-2">

        <i class="bi bi-download"></i>

        Download PDF

    </a>

    <a href="payment_history.php"
       class="btn btn-primary btn-lg ms-2">

        <i class="bi bi-arrow-left-circle"></i>

        Back

    </a>

</div>

</div>

<!-- ==========================================
        FOOTER
========================================== -->

<div class="bg-light border-top p-4">

    <div class="row">

        <div class="col-md-8">

            <h5 class="text-primary">

                Thank You!

            </h5>

            <p class="mb-1">

                Thank you for making your electricity bill payment through the
                <strong>APDCL Consumer Portal</strong>.

            </p>

            <p class="mb-0 text-muted">

                Please keep this receipt for your future reference.

            </p>

        </div>

        <div class="col-md-4 text-md-end mt-3 mt-md-0">

            <h6 class="text-success">

                Payment Status

            </h6>

            <span class="badge bg-success fs-6 px-3 py-2">

                SUCCESS

            </span>

        </div>

    </div>

</div>

</div>

</div>

<footer class="text-center mt-4 mb-4">

    <small class="text-muted">

        © <?= date("Y") ?> Assam Power Distribution Company Limited (APDCL)

        <br>

        Consumer Self Service Portal

    </small>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    PRINT SHORTCUT (Ctrl + P)
=========================================*/

document.addEventListener("keydown", function(e){

    if(e.ctrlKey && e.key === "p"){

        e.preventDefault();

        window.print();

    }

});

</script>

</body>

</html>