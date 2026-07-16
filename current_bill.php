<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    Consumer Details
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    Latest Bill
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1
");

$bill = mysqli_fetch_assoc($billQuery);

if(!$bill){
    die("
    <div style='
    width:500px;
    margin:100px auto;
    text-align:center;
    font-family:Segoe UI;
    '>

    <h2>No Bill Available</h2>

    <p>
    No electricity bill has been generated yet.
    </p>

    <a href='dashboard.php'
    style='
    background:#0d6efd;
    color:#fff;
    padding:12px 30px;
    text-decoration:none;
    border-radius:8px;
    '>

    Back to Dashboard

    </a>

    </div>
    ");
}

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>

Current Electricity Bill

</title>

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

body{

background:#edf3fb;

font-family:'Segoe UI',sans-serif;

}

.navbar{

background:#0056b3;

}

.bill-card{

background:#fff;

border-radius:20px;

padding:35px;

box-shadow:0 10px 30px rgba(0,0,0,.10);

}

.bill-title{

font-size:28px;

font-weight:700;

color:#0056b3;

}

.section-title{

font-size:18px;

font-weight:700;

color:#0d6efd;

margin-bottom:15px;

}

.table td{

padding:10px;

}

.amount{

font-size:30px;

font-weight:bold;

color:#198754;

}

.status-paid{

background:#198754;

color:#fff;

padding:8px 20px;

border-radius:30px;

}

.status-pending{

background:#dc3545;

color:#fff;

padding:8px 20px;

border-radius:30px;

}

.action-btn{

padding:12px 25px;

border-radius:10px;

}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container">

<a href="dashboard.php" class="navbar-brand">

⚡ APDCL Consumer Portal

</a>

<div class="text-white">

<?= htmlspecialchars($user['name']) ?>

</div>

</div>

</nav>

<div class="container mt-5">

<div class="bill-card">

<div class="row">

<div class="col-md-8">

<h2 class="bill-title">

Electricity Bill

</h2>

<p>

Assam Power Distribution Company Limited

</p>

</div>

<div class="col-md-4 text-end">

<?php

if($bill['status']=="Paid")

{

echo "<span class='status-paid'>PAID</span>";

}

else

{

echo "<span class='status-pending'>UNPAID</span>";

}

?>

</div>

</div>

<hr>

<!-- =========================================
        CONSUMER & BILL DETAILS
========================================= -->

<div class="row mt-4">

    <!-- Consumer Details -->

    <div class="col-lg-6">

        <h5 class="section-title">

            <i class="bi bi-person-fill"></i>

            Consumer Details

        </h5>

        <table class="table table-bordered">

            <tr>
                <th width="40%">Consumer No</th>
                <td><?= htmlspecialchars($bill['consumer_no']) ?></td>
            </tr>

            <tr>
                <th>Consumer Name</th>
                <td><?= htmlspecialchars($bill['consumer_name']) ?></td>
            </tr>

            <tr>
                <th>Father Name</th>
                <td><?= htmlspecialchars($bill['father_name']) ?></td>
            </tr>

            <tr>
                <th>Mobile</th>
                <td><?= htmlspecialchars($bill['mobile']) ?></td>
            </tr>

            <tr>
                <th>Address</th>
                <td><?= nl2br(htmlspecialchars($bill['address'])) ?></td>
            </tr>

            <tr>
                <th>Category</th>
                <td><?= htmlspecialchars($bill['category']) ?></td>
            </tr>

            <tr>
                <th>Meter No</th>
                <td><?= htmlspecialchars($bill['meter_no']) ?></td>
            </tr>

            <tr>
                <th>Zone</th>
                <td><?= htmlspecialchars($bill['zone']) ?></td>
            </tr>

            <tr>
                <th>Circle</th>
                <td><?= htmlspecialchars($bill['circle']) ?></td>
            </tr>

            <tr>
                <th>Sub-Division</th>
                <td><?= htmlspecialchars($bill['sub_division']) ?></td>
            </tr>

        </table>

    </div>

    <!-- Bill Information -->

    <div class="col-lg-6">

        <h5 class="section-title">

            <i class="bi bi-receipt-cutoff"></i>

            Bill Information

        </h5>

        <table class="table table-bordered">

            <tr>
                <th width="40%">Bill No</th>
                <td><?= htmlspecialchars($bill['bill_no']) ?></td>
            </tr>

            <tr>
                <th>Billing Month</th>
                <td><?= htmlspecialchars($bill['month']) ?></td>
            </tr>

            <tr>
                <th>Bill Date</th>
                <td><?= date("d-m-Y",strtotime($bill['bill_date'])) ?></td>
            </tr>

            <tr>
                <th>Due Date</th>
                <td><?= date("d-m-Y",strtotime($bill['due_date'])) ?></td>
            </tr>

            <tr>
                <th>Billing Period</th>
                <td>

                    <?= date("d-m-Y",strtotime($bill['bill_period_from'])) ?>

                    -

                    <?= date("d-m-Y",strtotime($bill['bill_period_to'])) ?>

                </td>

            </tr>

            <tr>
                <th>Billing Days</th>
                <td><?= $bill['billing_days'] ?></td>
            </tr>

            <tr>
                <th>Supply Type</th>
                <td><?= htmlspecialchars($bill['supply_type']) ?></td>
            </tr>

            <tr>
                <th>Supply Voltage</th>
                <td><?= htmlspecialchars($bill['supply_voltage']) ?></td>
            </tr>

            <tr>
                <th>Tariff Category</th>
                <td><?= htmlspecialchars($bill['tariff_category']) ?></td>
            </tr>

            <tr>
                <th>Meter Status</th>
                <td><?= htmlspecialchars($bill['meter_status']) ?></td>
            </tr>

        </table>

    </div>

</div>

<!-- =========================================
        METER READING
========================================= -->

<div class="mt-5">

    <h5 class="section-title">

        <i class="bi bi-speedometer2"></i>

        Meter Reading Details

    </h5>

    <table class="table table-striped table-bordered">

        <thead class="table-primary">

            <tr>

                <th>Previous Reading</th>

                <th>Current Reading</th>

                <th>Units Consumed</th>

                <th>MF</th>

                <th>Power Factor</th>

                <th>Recorded Demand</th>

                <th>Maximum Demand</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td><?= $bill['previous_reading'] ?></td>

                <td><?= $bill['current_reading'] ?></td>

                <td><?= $bill['units'] ?></td>

                <td><?= $bill['mf'] ?></td>

                <td><?= $bill['power_factor'] ?> %</td>

                <td><?= $bill['recorded_demand'] ?></td>

                <td><?= $bill['maximum_demand'] ?></td>

            </tr>

        </tbody>

    </table>

</div>

<!-- =========================================
        BILL CHARGES
========================================= -->

<div class="mt-5">

    <h5 class="section-title">

        <i class="bi bi-cash-stack"></i>

        Bill Charges

    </h5>

    <table class="table table-bordered table-hover">

        <thead class="table-success">

            <tr>

                <th>Charge Description</th>

                <th width="220">Amount (₹)</th>

            </tr>

        </thead>

        <tbody>

            <tr>
                <td>Energy Charge</td>
                <td><?= number_format($bill['energy_charge'],2) ?></td>
            </tr>

            <tr>
                <td>Fixed Charge</td>
                <td><?= number_format($bill['fixed_charge'],2) ?></td>
            </tr>

            <tr>
                <td>FPPPA Charge</td>
                <td><?= number_format($bill['fpppa_charge'],2) ?></td>
            </tr>

            <tr>
                <td>Electricity Duty</td>
                <td><?= number_format($bill['electricity_duty'],2) ?></td>
            </tr>

            <tr>
                <td>Government Subsidy</td>
                <td class="text-success">
                    - ₹ <?= number_format($bill['government_subsidy'],2) ?>
                </td>
            </tr>

            <tr>
                <td>Tariff Subsidy</td>
                <td class="text-success">
                    - ₹ <?= number_format($bill['tariff_subsidy'],2) ?>
                </td>
            </tr>

            <tr>
                <td>Solar Rebate</td>
                <td class="text-success">
                    - ₹ <?= number_format($bill['solar_rebate'],2) ?>
                </td>
            </tr>

            <tr>
                <td>Outstanding Amount</td>
                <td><?= number_format($bill['outstanding_amount'],2) ?></td>
            </tr>

            <tr>
                <td>Adjustment Amount</td>
                <td><?= number_format($bill['adjustment_amount'],2) ?></td>
            </tr>

            <tr>
                <td>Current Surcharge</td>
                <td><?= number_format($bill['current_surcharge'],2) ?></td>
            </tr>

            <tr class="table-warning">

                <th>Total Bill</th>

                <th class="text-danger fs-4">

                    ₹ <?= number_format($bill['total_bill'],2) ?>

                </th>

            </tr>

        </tbody>

    </table>

</div>

<!-- =========================================
        AMOUNT IN WORDS
========================================= -->

<div class="alert alert-primary mt-4">

    <h6>

        <i class="bi bi-chat-square-text-fill"></i>

        Amount in Words

    </h6>

    <strong>

        <?= htmlspecialchars($bill['amount_in_words']) ?>

    </strong>

</div>

<!-- =========================================
        ACTION BUTTONS
========================================= -->

<div class="text-center mt-5 mb-4">

    <a href="dashboard.php"
       class="btn btn-secondary btn-lg me-2">

        <i class="bi bi-house-door-fill"></i>

        Dashboard

    </a>

    <button
        onclick="window.print();"
        class="btn btn-primary btn-lg me-2">

        <i class="bi bi-printer-fill"></i>

        Print Bill

    </button>

    <?php if($bill['status']!="Paid"){ ?>

        <a href="pay_bill.php?bill=<?= $bill['id'] ?>"
           class="btn btn-success btn-lg">

            <i class="bi bi-credit-card-fill"></i>

            Pay Now

        </a>

    <?php }else{ ?>

        <button
            class="btn btn-outline-success btn-lg"
            disabled>

            <i class="bi bi-check-circle-fill"></i>

            Bill Paid

        </button>

    <?php } ?>

</div>

</div>

</body>

</html>