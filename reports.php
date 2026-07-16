<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* Logged-in Admin */

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($adminQuery);

/* Dashboard Counts */

$totalConsumers = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users"));

$totalBills = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bills"));

$totalPayments = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM payments"));

$totalComplaints = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM complaints"));

/* Revenue */

$revenueQuery = mysqli_query($conn,"
SELECT SUM(amount) AS revenue
FROM payments
");

$revenueRow = mysqli_fetch_assoc($revenueQuery);

$revenue = $revenueRow['revenue'] ?? 0;

/* Reports */

$consumerReport = mysqli_query($conn,"
SELECT *
FROM users
ORDER BY id DESC
");

$billReport = mysqli_query($conn,"
SELECT *
FROM bills
ORDER BY id DESC
");

$paymentReport = mysqli_query($conn,"
SELECT *
FROM payments
ORDER BY id DESC
");

$complaintReport = mysqli_query($conn,"
SELECT *
FROM complaints
ORDER BY id DESC
");
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Reports | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

/* NAVBAR */

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    height:75px;
    box-shadow:0 5px 18px rgba(0,0,0,.2);
}

.navbar-brand{
    color:#fff!important;
    font-weight:bold;
    display:flex;
    align-items:center;
}

.navbar-brand img{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#fff;
    padding:5px;
    margin-right:15px;
}

.navbar-brand small{
    color:#dbeafe;
    font-size:12px;
}

.avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#fff;
    color:#0d47a1;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:22px;
}

/* SIDEBAR */

.sidebar{
    position:fixed;
    top:75px;
    left:0;
    width:260px;
    height:calc(100vh - 75px);
    background:#0b3b86;
    overflow-y:auto;
    box-shadow:4px 0 15px rgba(0,0,0,.15);
}

.sidebar-header{
    text-align:center;
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,.15);
    color:#fff;
}

.sidebar-logo{
    width:65px;
    height:65px;
    background:#fff;
    border-radius:50%;
    padding:5px;
    margin-bottom:10px;
}

.sidebar a{
    display:flex;
    align-items:center;
    color:#fff;
    text-decoration:none;
    padding:15px 20px;
    transition:.3s;
    border-left:4px solid transparent;
}

.sidebar a:hover{
    background:#1565c0;
    border-left:4px solid #ffc107;
}

.sidebar a.active{
    background:#1976d2;
    border-left:4px solid #ffc107;
}

.sidebar i{
    width:28px;
    font-size:20px;
}

.sidebar-divider{
    height:1px;
    background:rgba(255,255,255,.15);
    margin:15px 20px;
}

/* CONTENT */

.content{
    margin-left:270px;
    padding:30px;
}

/* CARDS */

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

.card-header{
    font-size:20px;
    font-weight:bold;
}

/* TABLE */

.table th{
    background:#0d47a1;
    color:#fff;
}

.table td{
    vertical-align:middle;
}

@media(max-width:992px){

.sidebar{
    width:70px;
}

.sidebar span,
.sidebar-header{
    display:none;
}

.content{
    margin-left:80px;
}

}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="/apdcl-demo/assets/images/logo-circle.png">

<div>

<div style="font-size:24px;">APDCL</div>

<small>Reports Management</small>

</div>

</a>

<div class="ms-auto dropdown">

<a class="text-white text-decoration-none d-flex align-items-center"
href="#"
data-bs-toggle="dropdown">

<div class="avatar">

<i class="bi bi-person-fill"></i>

</div>

<div class="ms-2">

<b><?= htmlspecialchars($admin['name']); ?></b>

<br>

<small><?= htmlspecialchars($admin['username']); ?></small>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end">

<li>

<a class="dropdown-item" href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

</li>

<li>

<a class="dropdown-item" href="profile.php">

<i class="bi bi-person-circle"></i>

Profile

</a>

</li>

<li><hr></li>

<li>

<a class="dropdown-item text-danger"
href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</li>

</ul>

</div>

</div>

</nav>

<!-- SIDEBAR -->

<div class="sidebar">

<div class="sidebar-header">

<img src="/apdcl-demo/assets/images/logo-circle.png"
class="sidebar-logo">

<h5>APDCL</h5>

<small>Admin Panel</small>

</div>

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

<span>Dashboard</span>

</a>

<a href="manage_consumer.php">

<i class="bi bi-people"></i>

<span>Manage Consumers</span>

</a>

<a href="generate_bill.php">

<i class="bi bi-receipt"></i>

<span>Generate Bill</span>

</a>

<a href="manage_bills.php">

<i class="bi bi-journal-text"></i>

<span>Manage Bills</span>

</a>

<a href="complaints.php">

<i class="bi bi-chat-left-text"></i>

<span>Complaints</span>

</a>

<a href="notices.php">

<i class="bi bi-megaphone"></i>

<span>Notices</span>

</a>

<a href="reports.php" class="active">

<i class="bi bi-bar-chart-fill"></i>

<span>Reports</span>

</a>

<div class="sidebar-divider"></div>

<a href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

<span>Logout</span>

</a>

</div>

<!-- CONTENT -->

<div class="content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

<i class="bi bi-bar-chart-fill"></i>

Reports Dashboard

</h2>

<div>

<button onclick="window.print()" class="btn btn-primary">

<i class="bi bi-printer-fill"></i>

Print Report

</button>

</div>

</div>

<!-- ================= SUMMARY CARDS ================= -->

<div class="row g-4 mb-4">

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow h-100">

            <div class="card-body d-flex align-items-center">

                <div class="bg-primary text-white rounded-circle p-3 me-3">

                    <i class="bi bi-people-fill fs-2"></i>

                </div>

                <div>

                    <h6 class="text-muted mb-1">
                        Total Consumers
                    </h6>

                    <h2 class="fw-bold text-primary mb-0">
                        <?= $totalConsumers; ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow h-100">

            <div class="card-body d-flex align-items-center">

                <div class="bg-success text-white rounded-circle p-3 me-3">

                    <i class="bi bi-receipt-cutoff fs-2"></i>

                </div>

                <div>

                    <h6 class="text-muted mb-1">
                        Total Bills
                    </h6>

                    <h2 class="fw-bold text-success mb-0">
                        <?= $totalBills; ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow h-100">

            <div class="card-body d-flex align-items-center">

                <div class="bg-info text-white rounded-circle p-3 me-3">

                    <i class="bi bi-credit-card-fill fs-2"></i>

                </div>

                <div>

                    <h6 class="text-muted mb-1">
                        Total Payments
                    </h6>

                    <h2 class="fw-bold text-info mb-0">
                        <?= $totalPayments; ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow h-100">

            <div class="card-body d-flex align-items-center">

                <div class="bg-danger text-white rounded-circle p-3 me-3">

                    <i class="bi bi-cash-coin fs-2"></i>

                </div>

                <div>

                    <h6 class="text-muted mb-1">
                        Total Revenue
                    </h6>

                    <h3 class="fw-bold text-danger mb-0">

                        ₹ <?= number_format($revenue,2); ?>

                    </h3>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= CONSUMER REPORT ================= -->

<div class="card mb-5">

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

        <span>

            <i class="bi bi-people-fill"></i>

            Consumer Report

        </span>

        <span class="badge bg-light text-dark">

            <?= $totalConsumers; ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead>

                <tr>

                    <th width="70">ID</th>

                    <th>Consumer No</th>

                    <th>Name</th>

                    <th>Mobile</th>

                    <th>Email</th>

                    <th>Category</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($consumerReport)>0){

                    while($row=mysqli_fetch_assoc($consumerReport)){

                ?>

                <tr>

                    <td><?= $row['id']; ?></td>

                    <td><?= htmlspecialchars($row['consumer_no']); ?></td>

                    <td><?= htmlspecialchars($row['name']); ?></td>

                    <td><?= htmlspecialchars($row['mobile']); ?></td>

                    <td><?= htmlspecialchars($row['email']); ?></td>

                    <td><?= htmlspecialchars($row['category'] ?? 'N/A'); ?></td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="6" class="text-center text-muted">

                        No Consumer Records Found

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= BILL REPORT ================= -->

<div class="card mb-5">

    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">

        <span>

            <i class="bi bi-receipt-cutoff"></i>

            Bill Report

        </span>

        <span class="badge bg-light text-dark">

            <?= $totalBills; ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>Consumer No</th>

                    <th>Month</th>

                    <th>Units</th>

                    <th>Total Bill</th>

                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($billReport)>0){

                    while($bill=mysqli_fetch_assoc($billReport)){

                ?>

                <tr>

                    <td><?= $bill['id']; ?></td>

                    <td><?= htmlspecialchars($bill['consumer_no']); ?></td>

                    <td><?= htmlspecialchars($bill['month']); ?></td>

                    <td><?= $bill['units']; ?></td>

                    <td>

                        ₹ <?= number_format($bill['total_bill'],2); ?>

                    </td>

                    <td>

                        <?php

                        if($bill['status']=="Paid"){

                            echo "<span class='badge bg-success'>Paid</span>";

                        }else{

                            echo "<span class='badge bg-danger'>Pending</span>";

                        }

                        ?>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="6" class="text-center text-muted">

                        No Bill Records Found

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= PAYMENT REPORT ================= -->

<div class="card mb-5">

    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">

        <span>

            <i class="bi bi-credit-card-fill"></i>

            Payment Report

        </span>

        <span class="badge bg-light text-dark">

            <?= $totalPayments; ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>Consumer No</th>

                    <th>Amount</th>

                    <th>Payment Method</th>

                    <th>Payment Date</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($paymentReport)>0){

                    while($payment=mysqli_fetch_assoc($paymentReport)){

                ?>

                <tr>

                    <td><?= $payment['id']; ?></td>

                    <td><?= htmlspecialchars($payment['consumer_no']); ?></td>

                    <td>

                        ₹ <?= number_format($payment['amount'],2); ?>

                    </td>

                    <td>
                        <?= htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?>
                    </td>

                    <td>

                        <?= htmlspecialchars($payment['payment_date']); ?>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="5" class="text-center text-muted">

                        No Payment Records Found

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= COMPLAINT REPORT ================= -->

<div class="card mb-5">

    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">

        <span>

            <i class="bi bi-chat-left-text-fill"></i>

            Complaint Report

        </span>

        <span class="badge bg-light text-dark">

            <?= $totalComplaints; ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>Consumer No</th>

                    <th>Subject</th>

                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($complaintReport)>0){

                    while($c=mysqli_fetch_assoc($complaintReport)){

                ?>

                <tr>

                    <td><?= $c['id']; ?></td>

                    <td><?= htmlspecialchars($c['consumer_no']); ?></td>

                    <td><?= htmlspecialchars($c['subject']); ?></td>

                    <td>

                        <?php

                        if($c['status']=="Resolved"){

                            echo "<span class='badge bg-success'>Resolved</span>";

                        }elseif($c['status']=="Pending"){

                            echo "<span class='badge bg-warning text-dark'>Pending</span>";

                        }else{

                            echo "<span class='badge bg-secondary'>".$c['status']."</span>";

                        }

                        ?>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="4" class="text-center text-muted">

                        No Complaint Records Found

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ACTION BUTTONS -->

<div class="text-center mb-5">

    <a href="dashboard.php" class="btn btn-secondary btn-lg">

        <i class="bi bi-house-door-fill"></i>

        Dashboard

    </a>

    <button onclick="window.print()" class="btn btn-primary btn-lg">

        <i class="bi bi-printer-fill"></i>

        Print Report

    </button>

</div>

</div>

<!-- FOOTER -->

<footer class="text-center py-4 text-muted">

<hr>

<p class="mb-1">

© <?= date("Y"); ?> APDCL - Assam Power Distribution Company Limited

</p>

<p>

Electricity Billing Management System | Reports Module

</p>

</footer>

<!-- PRINT STYLE -->

<style>

@media print{

.navbar,
.sidebar,
.btn,
footer{
    display:none!important;
}

.content{
    margin-left:0!important;
    padding:0!important;
}

.card{
    box-shadow:none!important;
    border:1px solid #ddd!important;
    page-break-inside:avoid;
}

}

</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>