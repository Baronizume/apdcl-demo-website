<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* Logged In Admin */

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($adminQuery);

/* Tariff */

$rate = 7.50;
$fixed_charge = 150;

$message = "";

/* ============================
   GET BILL ID
============================ */

if(isset($_GET['id'])){

    $id = (int)$_GET['id'];

}elseif(isset($_GET['edit'])){

    $id = (int)$_GET['edit'];

}else{

    header("Location: manage_bills.php");
    exit();

}

/* ============================
   FETCH BILL
============================ */

$result = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='$id'
");

if(!$result){
    die("Database Error : ".mysqli_error($conn));
}

if(mysqli_num_rows($result)==0){
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($result);

/* ============================
   FETCH CONSUMER DETAILS
============================ */

$consumerQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='".$bill['consumer_no']."'
");

$consumer = mysqli_fetch_assoc($consumerQuery);

/* ============================
   UPDATE BILL
============================ */

if(isset($_POST['update'])){

    $month = mysqli_real_escape_string($conn,$_POST['month']);

    $units = (int)$_POST['units'];

    $status = mysqli_real_escape_string($conn,$_POST['status']);

    $energy_charge = $units * $rate;

    $electricity_duty = $energy_charge * 0.05;

    $subsidy = $energy_charge * 0.10;

    $total_bill =
        $energy_charge +
        $fixed_charge +
        $electricity_duty -
        $subsidy;

    $update = mysqli_query($conn,"
    UPDATE bills SET

        month='$month',
        units='$units',
        energy_charge='$energy_charge',
        fixed_charge='$fixed_charge',
        electricity_duty='$electricity_duty',
        subsidy='$subsidy',
        total_bill='$total_bill',
        status='$status'

    WHERE id='$id'
    ");

    if($update){

        header("Location: manage_bills.php?updated=1");
        exit();

    }else{

        $message="
        <div class='alert alert-danger'>
        Unable to update bill.
        </div>";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Edit Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fc;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}

/* ================= NAVBAR ================= */

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    height:75px;
    padding:0 25px;
    box-shadow:0 4px 15px rgba(0,0,0,.25);
}

.navbar-brand{
    color:#fff!important;
    font-size:24px;
    font-weight:700;
    display:flex;
    align-items:center;
}

.navbar-brand img{
    width:58px;
    height:58px;
    border-radius:50%;
    background:#fff;
    padding:4px;
    margin-right:15px;
}

.navbar-brand small{
    display:block;
    font-size:12px;
    color:#dbeafe;
}

.dropdown-menu{
    border:none;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
}

/* ================= SIDEBAR ================= */

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
    color:#fff;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.sidebar-logo{
    width:65px;
    height:65px;
    background:#fff;
    border-radius:50%;
    padding:5px;
    margin-bottom:10px;
}

.sidebar-header h5{
    margin:0;
    font-weight:700;
}

.sidebar-header small{
    color:#ddd;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    color:#fff;
    text-decoration:none;
    padding:15px 22px;
    transition:.3s;
    border-left:4px solid transparent;
}

.sidebar a i{
    font-size:19px;
    width:24px;
}

.sidebar a:hover{
    background:#1565c0;
    border-left:4px solid #ffc107;
}

.sidebar a.active{
    background:#1976d2;
    border-left:4px solid #ffc107;
}

.content{
    margin-left:280px;
    padding:30px;
}

.card{
    border:none;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.10);
}

.card-header{
    background:linear-gradient(90deg,#0d47a1,#1565c0);
    color:#fff;
    font-size:22px;
    font-weight:600;
}

.form-control,
.form-select{
    height:48px;
    border-radius:10px;
}

.btn{
    border-radius:10px;
    font-weight:600;
}

footer{
    margin-left:280px;
    padding:20px;
    color:#666;
}

</style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg navbar-dark">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="/apdcl-demo/assets/images/logo-circle.png">

<div>

APDCL

<small>Electricity Billing Management System</small>

</div>

</a>

<div class="ms-auto dropdown">

<a
class="text-white text-decoration-none dropdown-toggle"
href="#"
data-bs-toggle="dropdown">

<i class="bi bi-person-circle fs-3"></i>

<?= htmlspecialchars($admin['name']); ?>

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

<!-- ================= SIDEBAR ================= -->

<div class="sidebar">

<div class="sidebar-header">

<img src="/apdcl-demo/assets/images/logo-circle.png"
class="sidebar-logo">

<h5>Admin Panel</h5>

<small><?= htmlspecialchars($admin['username']); ?></small>

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

<i class="bi bi-lightning-charge"></i>

<span>Generate Bill</span>

</a>

<a href="manage_bills.php" class="active">

<i class="bi bi-receipt"></i>

<span>Manage Bills</span>

</a>

<a href="complaints.php">

<i class="bi bi-chat-left-text"></i>

<span>Complaints</span>

</a>

<a href="reports.php">

<i class="bi bi-bar-chart"></i>

<span>Reports</span>

</a>

<a href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

<span>Logout</span>

</a>

</div>

<!-- ================= PAGE CONTENT ================= -->

<div class="content">

<?php echo $message; ?>

<div class="card">

<div class="card-header">

<i class="bi bi-pencil-square"></i>

Edit Electricity Bill

</div>

<div class="card-body">

<form method="POST">

<div class="row">

    <!-- Consumer Information -->

    <div class="col-lg-6">

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">

                <i class="bi bi-person-vcard-fill"></i>

                Consumer Information

            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label class="form-label">Consumer Number</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($bill['consumer_no']); ?>"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Consumer Name</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($consumer['name'] ?? ''); ?>"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Mobile Number</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($consumer['mobile'] ?? ''); ?>"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Meter Number</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($consumer['meter_no'] ?? ''); ?>"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Category</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($consumer['category'] ?? ''); ?>"
                        readonly>

                </div>

            </div>

        </div>

    </div>

    <!-- Bill Details -->

    <div class="col-lg-6">

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-success text-white">

                <i class="bi bi-lightning-charge-fill"></i>

                Bill Details

            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label class="form-label">Billing Month</label>

                    <input
                        type="month"
                        name="month"
                        class="form-control"
                        value="<?= $bill['month']; ?>"
                        required>

                </div>

                <div class="mb-3">

                    <label class="form-label">Units Consumed</label>

                    <input
                        type="number"
                        name="units"
                        id="units"
                        class="form-control"
                        value="<?= $bill['units']; ?>"
                        required>

                </div>

                <div class="mb-3">

                    <label class="form-label">Tariff Rate</label>

                    <input
                        type="text"
                        class="form-control"
                        value="₹<?= number_format($rate,2); ?> / Unit"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Fixed Charge</label>

                    <input
                        type="text"
                        id="fixed_charge"
                        name="fixed_charge"
                        class="form-control"
                        value="<?= number_format($fixed_charge,2); ?>"
                        readonly>

                </div>

                <div class="mb-3">

                    <label class="form-label">Status</label>

                    <select
                        name="status"
                        class="form-select">

                        <option value="Pending"
                        <?= ($bill['status']=="Pending") ? "selected" : ""; ?>>

                        Pending

                        </option>

                        <option value="Paid"
                        <?= ($bill['status']=="Paid") ? "selected" : ""; ?>>

                        Paid

                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Bill Calculation -->

<div class="card shadow-sm mb-4">

<div class="card-header bg-warning text-dark">

<i class="bi bi-calculator-fill"></i>

Bill Calculation

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3 mb-3">

<label>Energy Charge</label>

<input
type="text"
id="energy_charge"
name="energy_charge"
class="form-control"
value="<?= number_format($bill['energy_charge'],2); ?>"
readonly>

</div>

<div class="col-md-3 mb-3">

<label>Electricity Duty (5%)</label>

<input
type="text"
id="electricity_duty"
name="electricity_duty"
class="form-control"
value="<?= number_format($bill['electricity_duty'],2); ?>"
readonly>

</div>

<div class="col-md-3 mb-3">

<label>Subsidy (10%)</label>

<input
type="text"
id="subsidy"
name="subsidy"
class="form-control"
value="<?= number_format($bill['subsidy'],2); ?>"
readonly>

</div>

<div class="col-md-3 mb-3">

<label>Total Bill</label>

<input
type="text"
id="total_bill"
name="total_bill"
class="form-control fw-bold text-danger"
value="<?= number_format($bill['total_bill'],2); ?>"
readonly>

</div>

</div>

</div>

</div>

<div class="text-center mt-4">

<button
type="submit"
name="update"
class="btn btn-success btn-lg">

<i class="bi bi-check-circle-fill"></i>

Update Bill

</button>

<a
href="manage_bills.php"
class="btn btn-secondary btn-lg">

<i class="bi bi-arrow-left-circle"></i>

Back

</a>

</div>

</form>

</div>

</div>

</div>

<script>

function calculateBill(){

    let units = parseFloat(document.getElementById("units").value) || 0;

    let rate = <?= $rate ?>;
    let fixed = <?= $fixed_charge ?>;

    let energy = units * rate;

    let duty = energy * 0.05;

    let subsidy = energy * 0.10;

    let total = energy + fixed + duty - subsidy;

    document.getElementById("energy_charge").value =
        energy.toFixed(2);

    document.getElementById("electricity_duty").value =
        duty.toFixed(2);

    document.getElementById("subsidy").value =
        subsidy.toFixed(2);

    document.getElementById("total_bill").value =
        total.toFixed(2);

}

document.getElementById("units").addEventListener("keyup", calculateBill);
document.getElementById("units").addEventListener("change", calculateBill);

window.onload = function(){

    calculateBill();
    updateClock();

};

/* ===========================
   LIVE CLOCK
=========================== */

function updateClock(){

    let now = new Date();

    let time = now.toLocaleTimeString();

    let clock = document.getElementById("clock");

    if(clock){
        clock.innerHTML = time;
    }

}

setInterval(updateClock,1000);

</script>

<footer class="text-center mt-5 mb-3 text-muted">

<hr>

<p>

© <?= date("Y"); ?> APDCL - Assam Power Distribution Company Limited

</p>

<p>

Electricity Billing Management System | Admin Panel

</p>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>