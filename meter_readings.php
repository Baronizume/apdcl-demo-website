<?php
session_start();

/*=========================================
    ADMIN LOGIN CHECK
=========================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    DELETE METER READING
=========================================*/

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    mysqli_query($conn,"
        DELETE FROM meter_readings
        WHERE id='$id'
    ");

    $_SESSION['success'] = "Meter reading deleted successfully.";

    header("Location: meter_readings.php");
    exit();
}

/*=========================================
    DASHBOARD STATISTICS
=========================================*/

// Total Readings
$totalReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM meter_readings
"))['total'];

// Today's Readings
$todayReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM meter_readings
WHERE DATE(reading_date)=CURDATE()
"))['total'];

// This Month Readings
$monthReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM meter_readings
WHERE MONTH(reading_date)=MONTH(CURDATE())
AND YEAR(reading_date)=YEAR(CURDATE())
"))['total'];

// Total Units Recorded
$totalUnits = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(current_reading-previous_reading),0) units
FROM meter_readings
"))['units'];

/*=========================================
    SEARCH + FILTER
=========================================*/

$where = "WHERE 1=1";

if (!empty($_GET['search'])) {

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= "

    AND
    (
        mr.consumer_no LIKE '%$search%'

        OR

        mr.meter_no LIKE '%$search%'

        OR

        u.name LIKE '%$search%'
    )

    ";

}

if (!empty($_GET['month'])) {

    $month = (int)$_GET['month'];

    $where .= " AND MONTH(mr.reading_date)='$month'";

}

if (!empty($_GET['year'])) {

    $year = (int)$_GET['year'];

    $where .= " AND YEAR(mr.reading_date)='$year'";

}

/*=========================================
    FETCH METER READINGS
=========================================*/

$query = mysqli_query($conn,"

SELECT

mr.*,

u.name

FROM meter_readings mr

LEFT JOIN users u

ON mr.consumer_no=u.consumer_no

$where

ORDER BY mr.id DESC

");

if(!$query){

    die(mysqli_error($conn));

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Meter Readings | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
    font-family:"Segoe UI",sans-serif;
}

.container-fluid{
    padding:30px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.stat-card{
    color:#fff;
    min-height:130px;
}

.stat-card h2{
    font-size:34px;
    font-weight:700;
}

.stat-icon{
    font-size:35px;
    opacity:.8;
}

.table th{
    background:#0d6efd;
    color:#fff;
    text-align:center;
}

.table td{
    vertical-align:middle;
}

.form-control,
.form-select{
    border-radius:10px;
    min-height:45px;
}

.btn{
    border-radius:10px;
}

.page-title{
    font-weight:700;
    color:#1d3557;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="page-title">

<i class="fa fa-gauge-high text-primary"></i>

Meter Readings Management

</h2>

<p class="text-muted mb-0">

Manage consumer meter readings and electricity units.

</p>

</div>

<a href="add_meter_reading.php"
class="btn btn-success">

<i class="fa fa-plus"></i>

Add Reading

</a>

</div>

<?php
if(isset($_SESSION['success'])){
?>

<div class="alert alert-success">

<?= $_SESSION['success']; ?>

</div>

<?php
unset($_SESSION['success']);
}
?>

<!-- Dashboard Cards -->

<div class="row g-4 mb-4">

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-primary">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Total Readings</h6>

<h2><?= $totalReadings ?></h2>

</div>

<i class="fa fa-database stat-icon"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-success">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Today's Readings</h6>

<h2><?= $todayReadings ?></h2>

</div>

<i class="fa fa-calendar-day stat-icon"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-warning text-dark">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>This Month</h6>

<h2><?= $monthReadings ?></h2>

</div>

<i class="fa fa-calendar stat-icon"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-danger">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<h6>Total Units</h6>

<h2><?= number_format($totalUnits) ?></h2>

</div>

<i class="fa fa-bolt stat-icon"></i>

</div>

</div>

</div>

</div>

<!-- Search Filter -->

<div class="card mb-4">

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-4">

<input
type="text"
name="search"
class="form-control"
placeholder="Consumer No / Meter No / Name"
value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

</div>

<div class="col-lg-2">

<select
name="month"
class="form-select">

<option value="">Month</option>

<?php
for($m=1;$m<=12;$m++){
?>

<option
value="<?= $m ?>"
<?= (($_GET['month'] ?? '')==$m) ? "selected" : "" ?>>

<?= date("F",mktime(0,0,0,$m,1)); ?>

</option>

<?php
}
?>

</select>

</div>

<div class="col-lg-2">

<select
name="year"
class="form-select">

<option value="">Year</option>

<?php
for($y=date("Y");$y>=2023;$y--){
?>

<option
value="<?= $y ?>"
<?= (($_GET['year'] ?? '')==$y) ? "selected" : "" ?>>

<?= $y ?>

</option>

<?php
}
?>

</select>

</div>

<div class="col-lg-2">

<button class="btn btn-primary w-100">

<i class="fa fa-search"></i>

Search

</button>

</div>

<div class="col-lg-2">

<a
href="meter_readings.php"
class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>

<!--=========================================
        METER READINGS TABLE
==========================================-->

<div class="card">

<div class="card-header bg-dark text-white">

<div class="d-flex justify-content-between align-items-center">

<h5 class="mb-0">

<i class="fa fa-table"></i>

Meter Readings

</h5>

<span class="badge bg-primary">

<?= mysqli_num_rows($query) ?>

Records

</span>

</div>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>

<th>ID</th>

<th>Consumer</th>

<th>Meter No</th>

<th>Reading Date</th>

<th>Previous</th>

<th>Current</th>

<th>Units</th>

<th width="170">

Action

</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

$units =
$row['current_reading'] -
$row['previous_reading'];

?>

<tr>

<td>

<?= $row['id']; ?>

</td>

<td>

<strong>

<?= htmlspecialchars($row['consumer_no']); ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($row['name']); ?>

</small>

</td>

<td>

<?= htmlspecialchars($row['meter_no']); ?>

</td>

<td>

<?= date("d M Y",strtotime($row['reading_date'])); ?>

<br>

<small class="text-muted">

<?= date("h:i A",strtotime($row['reading_date'])); ?>

</small>

</td>

<td>

<?= number_format($row['previous_reading']); ?>

</td>

<td>

<?= number_format($row['current_reading']); ?>

</td>

<td>

<span class="badge bg-success">

<?= number_format($units); ?>

Units

</span>

</td>

<td>

<a
href="edit_meter_reading.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>

</a>

<a
href="?delete=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this reading?')">

<i class="fa fa-trash"></i>

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8"
class="text-center text-danger py-4">

<i class="fa fa-folder-open fa-2x mb-2"></i>

<br>

No meter readings found.

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

<br>

<a
href="dashboard.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>