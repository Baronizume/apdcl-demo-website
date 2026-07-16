<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/* Logged In Admin */

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn, "
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($adminQuery);

/* ============================
   AUTO GENERATE CONSUMER NUMBER
   ============================ */

$result = mysqli_query($conn, "
SELECT MAX(CAST(SUBSTRING(consumer_no,4) AS UNSIGNED)) AS last_no
FROM users
");

$row = mysqli_fetch_assoc($result);

if ($row['last_no'] != NULL) {

    $next_no = $row['last_no'] + 1;

} else {

    $next_no = 1;

}

$consumer_no = "089" . str_pad($next_no, 10, "0", STR_PAD_LEFT);

/* ============================
   SAVE CONSUMER
   ============================ */

if(isset($_POST['save'])){

    $consumer_no = mysqli_real_escape_string($conn, $_POST['consumer_no']);
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $email       = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile      = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address     = mysqli_real_escape_string($conn, $_POST['address']);
    $pincode     = mysqli_real_escape_string($conn, $_POST['pincode']);

    $meter_no    = mysqli_real_escape_string($conn, $_POST['meter_no']);
    $meter_type  = mysqli_real_escape_string($conn, $_POST['meter_type']);

    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $tariff      = mysqli_real_escape_string($conn, $_POST['tariff']);

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn,"
    SELECT id
    FROM users
    WHERE consumer_no='$consumer_no'
    ");

    if(mysqli_num_rows($check)>0){

        echo "<script>
        alert('Consumer Number already exists!');
        window.location='add_consumer.php';
        </script>";
        exit();

    }

    $insert = mysqli_query($conn,"
    INSERT INTO users
    (
        consumer_no,
        name,
        email,
        mobile,
        address,
        pincode,
        meter_no,
        meter_type,
        category,
        tariff,
        password
    )
    VALUES
    (
        '$consumer_no',
        '$name',
        '$email',
        '$mobile',
        '$address',
        '$pincode',
        '$meter_no',
        '$meter_type',
        '$category',
        '$tariff',
        '$password'
    )
    ");

    if($insert){

        echo "<script>
        alert('Consumer Added Successfully');
        window.location='add_consumer.php';
        </script>";

    }else{

        die("Database Error: " . mysqli_error($conn));

    }

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Add Consumer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>

body{

background:#eef3f9;

font-family:'Segoe UI',sans-serif;

}

/* Navbar */

.navbar{

background:#0d47a1;

padding:15px 25px;

box-shadow:0 5px 20px rgba(0,0,0,.2);

}

.navbar-brand{

display:flex;

align-items:center;

color:#fff!important;

font-weight:bold;

font-size:24px;

}

.navbar-brand img{

width:60px;

margin-right:15px;

}

.nav-title{

line-height:1.3;

}

.nav-title small{

display:block;

font-size:14px;

font-weight:400;

}

.profile-avatar{

width:45px;

height:45px;

background:white;

color:#0d47a1;

border-radius:50%;

display:flex;

align-items:center;

justify-content:center;

font-size:22px;

margin-right:10px;

}

.profile-btn{

color:white;

text-decoration:none;

font-weight:600;

}

.main{

padding:30px;

}

.card{

border:none;

border-radius:18px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.section-title{

background:#0d47a1;

color:white;

padding:15px 20px;

font-size:22px;

font-weight:bold;

border-radius:18px 18px 0 0;

}

.form-label{

font-weight:600;

}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">

<div class="container-fluid">

<a class="navbar-brand d-flex align-items-center" href="dashboard.php">

<img src="../assets/images/logo-circle.png" class="me-3">

<div>

<h4 class="mb-0 fw-bold">APDCL</h4>

<small class="text-light opacity-75">
SDE Administration Portal
</small>

</div>

</a>

<div class="ms-auto d-flex align-items-center">

<div class="text-end me-3">

<div class="fw-bold text-white">

<?= htmlspecialchars($admin['name']); ?>

</div>

<small class="text-light opacity-75">

<?= date("d M Y | h:i A"); ?>

</small>

</div>

<div class="dropdown">

<a class="nav-link dropdown-toggle text-white"
href="#"
role="button"
data-bs-toggle="dropdown">

<div class="profile-circle">

<i class="bi bi-person-fill"></i>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end shadow">

<li class="dropdown-header">

<?= htmlspecialchars($admin['username']); ?>

</li>

<li><hr></li>

<li>

<a class="dropdown-item"
href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

</li>

<li>

<a class="dropdown-item"
href="profile.php">

<i class="bi bi-person-circle"></i>

My Profile

</a>

</li>

<li>

<a class="dropdown-item"
href="settings.php">

<i class="bi bi-gear"></i>

Settings

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

</div>

</nav>

<div class="container-fluid main">

<form method="POST">

<div class="card">

<div class="section-title">

<i class="bi bi-person-plus-fill"></i>

Consumer Information

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">

Consumer Number

</label>

<input
type="text"
name="consumer_no"
class="form-control"
value="<?= $consumer_no; ?>"
readonly>

</div>

<div class="col-md-8 mb-3">

<label class="form-label">

Consumer Name

</label>

<input
type="text"
name="name"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Email

</label>

<input
type="email"
name="email"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Mobile

</label>

<input
type="text"
name="mobile"
class="form-control"
required>

</div>

<div class="col-md-8 mb-3">

<label class="form-label">

Address

</label>

<textarea
name="address"
rows="3"
class="form-control"
required></textarea>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

PIN Code

</label>

<input
type="text"
name="pincode"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Meter Number

</label>

<div class="mb-3">
    <label>Meter Number</label>
    <input type="text" name="meter_no" class="form-control">
</div>

<div class="mb-3">

    <label class="form-label">

        Meter Type

    </label>

    <select name="meter_type" class="form-select" required>

        <option value="Postpaid">Postpaid</option>

        <option value="Smart Prepaid">Smart Prepaid</option>

    </select>

</div>


<input
type="text"
name="meter_no"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Meter Type

</label>

<select
name="meter_type"
class="form-select">

<option>Single Phase</option>

<option>Three Phase</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Password

</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Consumer Category

</label>

<select
name="category"
class="form-select">

<option>Domestic</option>

<option>Commercial</option>

<option>Industrial</option>

<option>Agriculture</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Tariff

</label>

<select
name="tariff"
class="form-select">

<option>LT Domestic</option>

<option>LT Commercial</option>

<option>HT Industrial</option>

</select>

</div>

<!-- ===========================================
     CONNECTION & BILLING DETAILS
============================================ -->

</div>
</div>

<br>

<div class="card">

<div class="section-title">

<i class="bi bi-lightning-charge-fill"></i>

Connection & Billing Details

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">

Division

</label>

<input
type="text"
name="division"
class="form-control"
placeholder="Example : Guwahati East">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Sub Division

</label>

<input
type="text"
name="subdivision"
class="form-control"
placeholder="Example : Six Mile">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Sanction Load (kW)

</label>

<input
type="number"
step="0.01"
name="sanction_load"
class="form-control"
value="1">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Connected Load (kW)

</label>

<input
type="number"
step="0.01"
name="connected_load"
class="form-control"
value="1">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Meter Status

</label>

<select
name="meter_status"
class="form-select">

<option value="Active">Active</option>

<option value="Disconnected">Disconnected</option>

<option value="Temporary">Temporary</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Supply Type

</label>

<select
name="supply_type"
class="form-select">

<option>LT</option>

<option>HT</option>

</select>

</div>

</div>

<hr>

<h4 class="text-primary mb-4">

<i class="bi bi-receipt"></i>

Billing Information

</h4>

<div class="row">

<div class="col-md-3 mb-3">

<label class="form-label">

Bill Month

</label>

<input
type="month"
name="bill_month"
class="form-control"
value="<?= date('Y-m'); ?>">

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Bill Date

</label>

<input
type="date"
name="bill_date"
class="form-control"
value="<?= date('Y-m-d'); ?>">

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Due Date

</label>

<input
type="date"
name="due_date"
class="form-control"
value="<?= date('Y-m-d',strtotime('+15 days')); ?>">

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Bill Number

</label>

<input
type="text"
name="bill_no"
class="form-control"
placeholder="Auto / Optional">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Previous Reading

</label>

<input
type="number"
id="previous_reading"
name="previous_reading"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Current Reading

</label>

<input
type="number"
id="current_reading"
name="current_reading"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Units Consumed

</label>

<input
type="number"
id="units"
name="units"
class="form-control"
readonly>

</div>

</div>

<hr>

<h4 class="text-success mb-4">

<i class="bi bi-cash-stack"></i>

Billing Summary

</h4>

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">

Current Demand

</label>

<input
type="number"
step="0.01"
name="current_demand"
id="current_demand"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Outstanding Amount

</label>

<input
type="number"
step="0.01"
name="outstanding"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Adjustment Amount

</label>

<input
type="number"
step="0.01"
name="adjustment"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Government Subsidy

</label>

<input
type="number"
step="0.01"
name="government_subsidy"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Solar Rebate

</label>

<input
type="number"
step="0.01"
name="solar_rebate"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Net Bill Amount

</label>

<input
type="number"
step="0.01"
id="net_bill"
name="net_bill"
class="form-control"
readonly>

</div>

</div>

<!-- ================= BILL CHARGES ================= -->

<hr>

<h4 class="text-primary mb-4">
    <i class="bi bi-cash-coin"></i>
    Energy Charge Details
</h4>

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-primary">

<tr>

<th>Description</th>

<th width="120">Units</th>

<th width="120">Rate (₹)</th>

<th width="150">Amount (₹)</th>

</tr>

</thead>

<tbody>

<tr>

<td>Energy Charge (0 - 300 Units)</td>

<td>
<input type="number" class="form-control" id="u1" value="300">
</td>

<td>
<input type="number" class="form-control" id="r1" value="7.74">
</td>

<td>
<input type="text" class="form-control bg-light" id="a1" readonly>
</td>

</tr>

<tr>

<td>Energy Charge (301 - 500 Units)</td>

<td>
<input type="number" class="form-control" id="u2" value="200">
</td>

<td>
<input type="number" class="form-control" id="r2" value="7.74">
</td>

<td>
<input type="text" class="form-control bg-light" id="a2" readonly>
</td>

</tr>

<tr>

<td>Energy Charge (Above 500 Units)</td>

<td>
<input type="number" class="form-control" id="u3" value="490">
</td>

<td>
<input type="number" class="form-control" id="r3" value="7.44">
</td>

<td>
<input type="text" class="form-control bg-light" id="a3" readonly>
</td>

</tr>

<tr class="table-success">

<th colspan="3">
Total Energy Charge
</th>

<th>

<input type="text"
class="form-control fw-bold bg-light"
id="energy_total"
readonly>

</th>

</tr>

</tbody>

</table>

</div>

<hr>

<h4 class="text-primary mb-4">

Other Charges

</h4>

<div class="row">

<div class="col-md-4 mb-3">

<label>Demand / Fixed Charge</label>

<input type="number"
class="form-control"
id="fixed_charge"
value="420">

</div>

<div class="col-md-4 mb-3">

<label>FPPPA Charge</label>

<input type="number"
class="form-control"
id="fpppa"
value="177.78">

</div>

<div class="col-md-4 mb-3">

<label>Electricity Duty</label>

<input type="number"
class="form-control"
id="duty"
value="383.13">

</div>

<div class="col-md-4 mb-3">

<label>Tariff Subsidy</label>

<input type="number"
class="form-control"
id="tariff"
value="455">

</div>

<div class="col-md-4 mb-3">

<label>Area Principal</label>

<input type="number"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label>Area Surcharge</label>

<input type="number"
class="form-control"
value="0">

</div>

<div class="col-md-4 mb-3">

<label>Current Surcharge</label>

<input type="number"
class="form-control"
value="0">

</div>

</div>

<hr>

<h4 class="text-success">

Bill Summary

</h4>

<table class="table table-bordered">

<tr>

<th width="40%">
Current Demand
</th>

<td>
<input type="text"
id="currentDemand"
class="form-control bg-light"
readonly>
</td>

</tr>

<tr>

<th>
Payable Before Due Date
</th>

<td>
<input type="text"
id="beforeDue"
class="form-control bg-light"
readonly>
</td>

</tr>

<tr>

<th>
Payable After Due Date
</th>

<td>
<input type="text"
id="afterDue"
class="form-control bg-light"
readonly>
</td>

</tr>

</table>

<div class="text-center mt-5">

<button type="submit"
name="save"
class="btn btn-success btn-lg px-5">

<i class="bi bi-check-circle-fill"></i>

Save Consumer

</button>

<a href="javascript:history.back()" class="btn btn-secondary btn-lg">
    <i class="bi bi-arrow-left-circle"></i> Back
</a>

</div>

</form>

</div>

<!-- ===========================================
        LIVE BILL PREVIEW
=========================================== -->

<hr class="mt-5">

<div class="card border-primary shadow-lg">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-receipt-cutoff"></i>

APDCL Bill Preview

</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th width="45%">Consumer No</th>

<td>

<input
type="text"
class="form-control border-0 bg-white fw-bold"
id="previewConsumer"
readonly>

</td>

</tr>

<tr>

<th>Consumer Name</th>

<td>

<input
type="text"
class="form-control border-0 bg-white"
id="previewName"
readonly>

</td>

</tr>

<tr>

<th>Bill Month</th>

<td>

<input
type="text"
class="form-control border-0 bg-white"
id="previewMonth"
readonly>

</td>

</tr>

<tr>

<th>Bill Date</th>

<td>

<input
type="text"
class="form-control border-0 bg-white"
id="previewDate"
readonly>

</td>

</tr>

<tr>

<th>Due Date</th>

<td>

<input
type="text"
class="form-control border-0 bg-white"
id="previewDue"
readonly>

</td>

</tr>

<tr>

<th>Units</th>

<td>

<input
type="text"
class="form-control border-0 bg-white"
id="previewUnits"
readonly>

</td>

</tr>

</table>

</div>

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th>Energy Charge</th>

<td class="text-end">

₹ <span id="previewEnergy">0.00</span>

</td>

</tr>

<tr>

<th>Fixed Charge</th>

<td class="text-end">

₹ <span id="previewFixed">0.00</span>

</td>

</tr>

<tr>

<th>Electricity Duty</th>

<td class="text-end">

₹ <span id="previewDuty">0.00</span>

</td>

</tr>

<tr>

<th>FPPPA</th>

<td class="text-end">

₹ <span id="previewFpppa">0.00</span>

</td>

</tr>

<tr>

<th>Tariff Subsidy</th>

<td class="text-end text-success">

- ₹ <span id="previewSubsidy">0.00</span>

</td>

</tr>

<tr class="table-success">

<th>Total Bill</th>

<th class="text-end">

₹ <span id="previewTotal">0.00</span>

</th>

</tr>

</table>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-12">

<h5 class="text-primary">

Amount in Words

</h5>

<div class="alert alert-light border">

<strong id="amountWords">

Zero Rupees Only

</strong>

</div>

</div>

</div>

</div>

</div>


<script>

function calculateBill(){

let a1=u1.value*r1.value;
let a2=u2.value*r2.value;
let a3=u3.value*r3.value;

document.getElementById("a1").value=a1.toFixed(2);
document.getElementById("a2").value=a2.toFixed(2);
document.getElementById("a3").value=a3.toFixed(2);

let energy=a1+a2+a3;

document.getElementById("energy_total").value=energy.toFixed(2);

let total=
energy+
parseFloat(fixed_charge.value)+
parseFloat(fpppa.value)+
parseFloat(duty.value)-
parseFloat(tariff.value);

document.getElementById("currentDemand").value=total.toFixed(2);

document.getElementById("beforeDue").value=total.toFixed(2);

document.getElementById("afterDue").value=total.toFixed(2);

}

document.querySelectorAll("input").forEach(function(i){

i.addEventListener("input",calculateBill);

});

calculateBill();

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

<script>

// =============================
// Meter Reading Calculation
// =============================

function calculateUnits(){

let previous=parseFloat(document.getElementById("previous_reading").value)||0;

let current=parseFloat(document.getElementById("current_reading").value)||0;

let units=current-previous;

if(units<0){

units=0;

}

document.getElementById("units").value=units;

calculateEnergy();

}

// =============================
// Energy Charge Calculation
// =============================

function calculateEnergy(){

let units=parseFloat(document.getElementById("units").value)||0;

let slab1=0;
let slab2=0;
let slab3=0;

let rate1=7.74;
let rate2=7.74;
let rate3=7.44;

if(units<=300){

slab1=units;

}else if(units<=500){

slab1=300;
slab2=units-300;

}else{

slab1=300;
slab2=200;
slab3=units-500;

}

document.getElementById("u1").value=slab1;
document.getElementById("u2").value=slab2;
document.getElementById("u3").value=slab3;

let amount1=slab1*rate1;
let amount2=slab2*rate2;
let amount3=slab3*rate3;

document.getElementById("a1").value=amount1.toFixed(2);
document.getElementById("a2").value=amount2.toFixed(2);
document.getElementById("a3").value=amount3.toFixed(2);

let totalEnergy=amount1+amount2+amount3;

document.getElementById("energy_total").value=totalEnergy.toFixed(2);

calculateBill();

}

// =============================
// Bill Calculation
// =============================

function calculateBill(){

let energy=parseFloat(document.getElementById("energy_total").value)||0;

let fixed=parseFloat(document.getElementById("fixed_charge").value)||0;

let fpppa=parseFloat(document.getElementById("fpppa").value)||0;

let duty=parseFloat(document.getElementById("duty").value)||0;

let subsidy=parseFloat(document.getElementById("tariff").value)||0;

let govt=parseFloat(document.querySelector('[name="government_subsidy"]').value)||0;

let solar=parseFloat(document.querySelector('[name="solar_rebate"]').value)||0;

let outstanding=parseFloat(document.querySelector('[name="outstanding"]').value)||0;

let adjustment=parseFloat(document.querySelector('[name="adjustment"]').value)||0;

let total=
energy+
fixed+
fpppa+
duty+
outstanding+
adjustment-
subsidy-
govt-
solar;

if(total<0){

total=0;

}

document.getElementById("currentDemand").value=total.toFixed(2);

document.getElementById("beforeDue").value=total.toFixed(2);

document.getElementById("afterDue").value=total.toFixed(2);

document.getElementById("net_bill").value=total.toFixed(2);

}

// =============================
// Live Calculation
// =============================

document.querySelectorAll("input").forEach(function(item){

item.addEventListener("keyup",calculateUnits);

item.addEventListener("change",calculateUnits);

});

calculateUnits();

// Bill Preview

document.getElementById("previewConsumer").value =
document.querySelector("[name='consumer_no']").value;

document.getElementById("previewName").value =
document.querySelector("[name='name']").value;

document.getElementById("previewMonth").value =
document.querySelector("[name='bill_month']").value;

document.getElementById("previewDate").value =
document.querySelector("[name='bill_date']").value;

document.getElementById("previewDue").value =
document.querySelector("[name='due_date']").value;

document.getElementById("previewUnits").value =
document.getElementById("units").value;

document.getElementById("previewEnergy").innerHTML =
energy.toFixed(2);

document.getElementById("previewFixed").innerHTML =
fixed.toFixed(2);

document.getElementById("previewDuty").innerHTML =
duty.toFixed(2);

document.getElementById("previewFpppa").innerHTML =
fpppa.toFixed(2);

document.getElementById("previewSubsidy").innerHTML =
subsidy.toFixed(2);

document.getElementById("previewTotal").innerHTML =
total.toFixed(2);

function numberToWords(num){

num=Math.round(num);

if(num==0) return "Zero Rupees Only";

const ones=[
"",
"One","Two","Three","Four","Five","Six","Seven","Eight","Nine",
"Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen",
"Sixteen","Seventeen","Eighteen","Nineteen"
];

const tens=[
"",
"",
"Twenty","Thirty","Forty","Fifty",
"Sixty","Seventy","Eighty","Ninety"
];

function convert(n){

if(n<20)
return ones[n];

if(n<100)
return tens[Math.floor(n/10)]+" "+ones[n%10];

if(n<1000)
return ones[Math.floor(n/100)]+" Hundred "+convert(n%100);

if(n<100000)
return convert(Math.floor(n/1000))+" Thousand "+convert(n%1000);

if(n<10000000)
return convert(Math.floor(n/100000))+" Lakh "+convert(n%100000);

return convert(Math.floor(n/10000000))+" Crore "+convert(n%10000000);

}

return convert(num)+" Rupees Only";

}
</script>

<div class="d-flex justify-content-center gap-3 mt-4">

<button type="button"
class="btn btn-primary btn-lg"
onclick="window.print()">

<i class="bi bi-printer-fill"></i>

Print Bill

</button>

<button
type="submit"
name="save"
class="btn btn-success btn-lg">

<i class="bi bi-check-circle-fill"></i>

Save Consumer

</button>

<button
type="button"
class="btn btn-warning btn-lg">

<i class="bi bi-lightning-charge-fill"></i>

Generate Bill

</button>


</div>

</form>

<footer class="mt-5 text-center">

<hr>

<h5 class="text-primary">

Assam Power Distribution Company Limited

</h5>

<p class="text-muted">

SDE Administration Portal

</p>

<small>

© <?= date("Y"); ?> APDCL | Internship Project

</small>

</footer>

</html>
