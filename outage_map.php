<?php
session_start();
include("../db.php");

/*=========================================
    DEVELOPMENT MODE
=========================================*/

$adminName = $_SESSION['name'] ?? "Super Admin";
$role      = $_SESSION['role'] ?? "Super Admin";

/*=========================================
    DASHBOARD COUNTS
=========================================*/

$totalActive = 0;
$totalRestored = 0;
$totalConsumers = 0;
$totalToday = 0;

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM outages WHERE status='Active'");
if($q){
    $totalActive = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM outages WHERE status='Restored'");
if($q){
    $totalRestored = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"
SELECT IFNULL(SUM(consumers_affected),0) total
FROM outages
WHERE status='Active'
");
if($q){
    $totalConsumers = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE DATE(created_at)=CURDATE()
");
if($q){
    $totalToday = mysqli_fetch_assoc($q)['total'];
}

/*=========================================
    SEARCH
=========================================*/

$search = "";

if(isset($_GET['search']))
{
    $search = trim($_GET['search']);
}

/*=========================================
    PAGINATION
=========================================*/

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1)
{
    $page = 1;
}

$start = ($page-1)*$limit;

/*=========================================
    TOTAL RECORDS
=========================================*/

if($search=="")
{

$countQuery=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
");

}
else
{

$s=mysqli_real_escape_string($conn,$search);

$countQuery=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE
zone LIKE '%$s%'
OR circle LIKE '%$s%'
OR sub_division LIKE '%$s%'
OR feeder_name LIKE '%$s%'
OR status LIKE '%$s%'
");

}

$totalRecords=mysqli_fetch_assoc($countQuery)['total'];

$totalPages=ceil($totalRecords/$limit);

/*=========================================
    LOAD OUTAGES FOR MAP
=========================================*/

$outageQuery = mysqli_query($conn,"
SELECT
    id,
    zone,
    circle,
    sub_division,
    feeder_name,
    transformer,
    latitude,
    longitude,
    consumers_affected,
    outage_reason,
    start_time,
    estimated_restore,
    status
FROM outages
ORDER BY id DESC
");

$outages = [];

while($row = mysqli_fetch_assoc($outageQuery))
{
    $outages[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Outage Management Map</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.page-header{
    background:linear-gradient(90deg,#0b4ea2,#1976d2);
    color:#fff;
    padding:20px 30px;
    border-radius:18px;
    margin-bottom:25px;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
}

.page-header h2{
    margin:0;
    font-weight:700;
}

.card-stat{
    border:none;
    border-radius:18px;
    transition:.35s;
    color:#fff;
}

.card-stat:hover{
    transform:translateY(-8px);
    box-shadow:0 15px 30px rgba(0,0,0,.15);
}

.bg-active{
    background:linear-gradient(135deg,#dc3545,#ff6b6b);
}

.bg-restored{
    background:linear-gradient(135deg,#198754,#36c36b);
}

.bg-consumers{
    background:linear-gradient(135deg,#0d6efd,#4da3ff);
}

.bg-today{
    background:linear-gradient(135deg,#fd7e14,#ffb347);
}

.card-stat i{
    font-size:45px;
    opacity:.9;
}

.card-stat h2{
    font-weight:700;
}

.toolbar{
    background:#fff;
    padding:18px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    margin-bottom:20px;
}

#map{
    height:650px;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.12);
    background:#ddd;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

<div class="page-header d-flex justify-content-between align-items-center">

<div>

<h2>

<i class="bi bi-lightning-charge-fill"></i>

APDCL Outage Management Map

</h2>

<small>

Monitor power outages across Zones, Circles & Sub-Divisions

</small>

</div>

<div class="text-end">

<strong>

<?= htmlspecialchars($adminName) ?>

</strong>

<br>

<?= htmlspecialchars($role) ?>

</div>

</div>

<div class="row g-4 mb-4">

<div class="col-lg-3 col-md-6">

<div class="card card-stat bg-active">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Active Outages</h6>

<h2><?= $totalActive ?></h2>

</div>

<i class="bi bi-lightning-fill"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card card-stat bg-restored">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Power Restored</h6>

<h2><?= $totalRestored ?></h2>

</div>

<i class="bi bi-check-circle-fill"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card card-stat bg-consumers">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Consumers Affected</h6>

<h2><?= number_format($totalConsumers) ?></h2>

</div>

<i class="bi bi-people-fill"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="card card-stat bg-today">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h6>Today's Reports</h6>

<h2><?= $totalToday ?></h2>

</div>

<i class="bi bi-calendar-event-fill"></i>

</div>

</div>

</div>

</div>

</div>

<div class="toolbar">

<div class="row align-items-center">

<div class="col-md-8">

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-search"></i>

</span>

<input
type="text"
class="form-control"
placeholder="Search Zone, Circle, Sub-Division or Feeder">

</div>

</div>

<div class="col-md-4 text-end">

<button class="btn btn-danger btn-lg">

<i class="bi bi-plus-circle-fill"></i>

Report New Outage

</button>

</div>

</div>

</div>

<!-- =========================================
        OUTAGE MAP
========================================= -->

<div class="card shadow border-0 rounded-4">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="bi bi-geo-alt-fill"></i>

            Live Outage Map

        </h5>

    </div>

    <div class="card-body p-2">

        <div id="map"></div>

    </div>

</div>

</div>

<a href="delete_outage.php?id=<?= $row['id']; ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to delete this outage?');">

    <i class="fa fa-trash"></i>

</a>
<div class="container-fluid mt-4">

<div class="row g-3">

<?php

$totalOutages=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total FROM outages
"))['total'];

$activeOutages=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Active'
"))['total'];

$restoredOutages=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Restored'
"))['total'];

$totalConsumers=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(consumers_affected) total
FROM outages
"))['total'];

?>

<div class="col-lg-3">

<div class="card border-0 shadow bg-primary text-white">

<div class="card-body">

<h6>Total Outages</h6>

<h2><?= $totalOutages ?></h2>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card border-0 shadow bg-danger text-white">

<div class="card-body">

<h6>Active Outages</h6>

<h2><?= $activeOutages ?></h2>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card border-0 shadow bg-success text-white">

<div class="card-body">

<h6>Restored</h6>

<h2><?= $restoredOutages ?></h2>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="card border-0 shadow bg-warning">

<div class="card-body">

<h6>Consumers Affected</h6>

<h2><?= number_format($totalConsumers) ?></h2>

</div>

</div>

</div>

</div>

<div class="card shadow mt-4">

<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

<h5 class="mb-0">

Outage Management

</h5>

<a href="add_outage.php" class="btn btn-success">

<i class="fa fa-plus"></i>

Add Outage

</a>

</div>

<div class="card-body">

<form method="GET">

<div class="row mb-3">

<div class="col-md-5">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Zone / Circle / Feeder">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

Search

</button>

</div>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th>ID</th>

<th>Zone</th>

<th>Circle</th>

<th>Sub Division</th>

<th>Feeder</th>

<th>Consumers</th>

<th>Status</th>

<th width="150">

Action

</th>

</tr>

</thead>

<tbody>

<?php

if(!empty($outages))
{

foreach($outages as $row)
{

?>


<tr>

<td><?= $row['id'] ?></td>

<td><?= htmlspecialchars($row['zone']) ?></td>

<td><?= htmlspecialchars($row['circle']) ?></td>

<td><?= htmlspecialchars($row['sub_division']) ?></td>

<td><?= htmlspecialchars($row['feeder_name']) ?></td>

<td><?= number_format($row['consumers_affected']) ?></td>

<td>

<?php
if($row['status']=="Active")
{
?>
<span class="badge bg-danger">
Active
</span>
<?php
}
else
{
?>
<span class="badge bg-success">
Restored
</span>
<?php
}
?>

</td>

<td>

<a
href="edit_outage.php?id=<?= $row['id'] ?>"
class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>

</a>

<a
href="delete_outage.php?id=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this outage?')">

<i class="fa fa-trash"></i>

</a>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="8" class="text-center text-danger">

No outage records found.

</td>

</tr>

<?php

}

?>

</tbody>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

/*=========================================
        LEAFLET MAP
=========================================*/

// Assam Center
var map = L.map('map').setView([26.1445, 91.7362], 10);
const outages = <?= json_encode($outages); ?>;
// OpenStreetMap
L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
    maxZoom:19,
    attribution:'© OpenStreetMap Contributors'
}).addTo(map);


/*=========================================
        ICONS
=========================================*/

var redIcon = L.icon({

    iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',

    shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',

    iconSize:[25,41],

    iconAnchor:[12,41],

    popupAnchor:[1,-34]

});

var greenIcon = L.icon({

    iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',

    shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',

    iconSize:[25,41],

    iconAnchor:[12,41],

    popupAnchor:[1,-34]

});


/*=========================================
        DYNAMIC MARKERS FROM DATABASE
=========================================*/

<?php

$outages = mysqli_query($conn,"
SELECT *
FROM outages
ORDER BY id DESC
");

while($row = mysqli_fetch_assoc($outages))
{

?>

var markerIcon =
"<?php echo $row['status']; ?>"=="Active"
?
redIcon
:
greenIcon;

L.marker(
[
<?php echo $row['latitude']; ?>,
<?php echo $row['longitude']; ?>
],
{
icon:markerIcon
})
.addTo(map)
.bindPopup(

"<div style='min-width:220px'>"+

"<h6><b><?php echo addslashes($row['sub_division']); ?></b></h6>"+

"<b>Zone :</b> <?php echo addslashes($row['zone']); ?><br>"+

"<b>Circle :</b> <?php echo addslashes($row['circle']); ?><br>"+

"<b>Feeder :</b> <?php echo addslashes($row['feeder_name']); ?><br>"+

"<b>Transformer :</b> <?php echo addslashes($row['transformer']); ?><br>"+

"<b>Consumers :</b> <?php echo $row['consumers_affected']; ?><br>"+

"<b>Reason :</b> <?php echo addslashes($row['outage_reason']); ?><br>"+

"<b>Start :</b> <?php echo $row['start_time']; ?><br>"+

"<b>Estimated Restore :</b> <?php echo $row['estimated_restore']; ?><br>"+

"<b>Status :</b> <span style='color:<?php echo ($row['status']=="Active")?"red":"green"; ?>'><b><?php echo $row['status']; ?></b></span>"+

"</div>"

);

<?php

}

?>

</script>
</body>
</html>