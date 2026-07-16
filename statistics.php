<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

// Summary
$consumers = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users"));
$bills = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bills"));
$payments = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM payments"));
$complaints = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM complaints"));

$revenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(amount) AS total
FROM payments
"));

$totalRevenue = $revenue['total'] ?? 0;

$paidBills = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM bills
WHERE status='Paid'
"));

$unpaidBills = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM bills
WHERE status='Unpaid'
"));

$pending = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
WHERE status='Pending'
"));

$resolved = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
WHERE status='Resolved'
"));
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Statistics Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#f4f7fb;
}

.main-content{
margin-left:250px;
padding:30px;
}

.card{
border:none;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.15);
margin-bottom:25px;
}

.card-header{
font-weight:bold;
}

</style>

</head>

<body>

<?php include("sidebar.php"); ?>

<div class="main-content">

<h2 class="mb-4">
📊 Statistics Dashboard
</h2>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header bg-primary text-white">
Consumer Statistics
</div>

<div class="card-body">

<canvas id="consumerChart"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card">

<div class="card-header bg-success text-white">
Revenue Statistics
</div>

<div class="card-body">

<canvas id="revenueChart"></canvas>

</div>

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header bg-warning">
Monthly Bills
</div>

<div class="card-body">

<canvas id="billChart"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card">

<div class="card-header bg-danger text-white">
Complaint Status
</div>

<div class="card-body">

<canvas id="complaintChart"></canvas>

</div>

</div>

</div>

</div>

</div>

<script>

// Consumer Statistics
new Chart(document.getElementById("consumerChart"),{
type:"bar",
data:{
labels:["Consumers","Bills","Payments","Complaints"],
datasets:[{
label:"System Statistics",
data:[
<?= $consumers ?>,
<?= $bills ?>,
<?= $payments ?>,
<?= $complaints ?>
]
}]
}
});

// Revenue Statistics
new Chart(document.getElementById("revenueChart"),{
type:"line",
data:{
labels:["Revenue"],
datasets:[{
label:"Total Revenue",
data:[<?= $totalRevenue ?>],
fill:false,
tension:0.4
}]
}
});

// Bill Status
new Chart(document.getElementById("billChart"),{
type:"bar",
data:{
labels:["Paid","Unpaid"],
datasets:[{
label:"Bills",
data:[
<?= $paidBills ?>,
<?= $unpaidBills ?>
]
}]
}
});

// Complaint Status
new Chart(document.getElementById("complaintChart"),{
type:"pie",
data:{
labels:["Pending","Resolved"],
datasets:[{
data:[
<?= $pending ?>,
<?= $resolved ?>
]
}]
}
});

</script>

</body>
</html>