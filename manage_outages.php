<?php

session_start();

include("../db.php");


/*=====================================================
    ADMIN LOGIN CHECK
=====================================================*/

if(!isset($_SESSION['admin_id'])){

    header("Location: login.php");
    exit();

}


/*=====================================================
    OUTAGE COUNTS
=====================================================*/


$totalOutages = 0;
$activeOutages = 0;
$restoredOutages = 0;


$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
");

if($result){

    $row = mysqli_fetch_assoc($result);

    $totalOutages = $row['total'];

}



$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
WHERE status='Active'
");


if($result){

    $row = mysqli_fetch_assoc($result);

    $activeOutages = $row['total'];

}



$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
WHERE status='Restored'
");


if($result){

    $row = mysqli_fetch_assoc($result);

    $restoredOutages = $row['total'];

}



/*=====================================================
    SEARCH
=====================================================*/


$search="";


if(isset($_GET['search'])){

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );

}



$sql="
SELECT *
FROM outages
WHERE 
zone LIKE '%$search%'
OR circle LIKE '%$search%'
OR feeder_name LIKE '%$search%'
OR transformer LIKE '%$search%'
OR sub_division LIKE '%$search%'

ORDER BY id DESC
";


$outages = mysqli_query($conn,$sql);


?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">


<title>
Manage Outages - APDCL
</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>


body{

background:#f4f7fb;

font-family:'Segoe UI',sans-serif;

}


.main{

margin-left:260px;

padding:30px;

}


.card{

border:none;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}


.status-active{

background:#dc3545;

}


.status-restored{

background:#198754;

}


.table{

background:#fff;

border-radius:15px;

overflow:hidden;

}

/* ===============================
   APDCL HEADER
================================ */


.page-header{

background:white;

padding:20px 25px;

border-radius:18px;

display:flex;

align-items:center;

gap:20px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}



.apdcl-logo{

width:80px;

height:80px;

object-fit:contain;

}



.page-header h2{

margin:0;

font-size:28px;

font-weight:700;

color:#004aad;

}



.page-header p{

margin:5px 0 0;

color:#777;

font-size:15px;

}



/* ADD BUTTON */

.btn-success{

border-radius:30px;

padding:10px 22px;

font-weight:600;

}



/* SEARCH BUTTON */

.btn-primary{

border-radius:30px;

}



/* TABLE */

.table thead th{

vertical-align:middle;

text-align:center;

}


.table tbody td{

vertical-align:middle;

}




</style>


</head>


<body>



<div class="main">


<!-- PAGE HEADER -->

<div class="page-header mb-4">

<img src="../assets/images/logo-circle.png"
class="apdcl-logo">


<div>

<h2>
<i class="bi bi-lightning-charge-fill text-warning"></i>
Manage Outages
</h2>

<p>
APDCL Admin Panel - Power Outage Monitoring System
</p>

</div>


</div>



<!--==================================================
SUMMARY CARDS
===================================================-->

<div class="row g-4 mb-4">


<div class="col-md-4">

<div class="card p-4">

<h6 class="text-muted">

Total Outages

</h6>

<h2>

<?= $totalOutages ?>

</h2>


</div>

</div>




<div class="col-md-4">

<div class="card p-4">

<h6 class="text-muted">

Active Outages

</h6>


<h2 class="text-danger">

<?= $activeOutages ?>

</h2>


</div>

</div>




<div class="col-md-4">

<div class="card p-4">


<h6 class="text-muted">

Restored Outages

</h6>


<h2 class="text-success">

<?= $restoredOutages ?>

</h2>


</div>

</div>



</div>




<!--==================================================
SEARCH
===================================================-->


<div class="card p-4 mb-4">


<form method="GET">


<div class="input-group">


<input type="text"
name="search"
class="form-control"
placeholder="Search Zone, Circle, Feeder..."
value="<?= htmlspecialchars($search) ?>">


<button class="btn btn-primary">

<i class="bi bi-search"></i>

Search

</button>


<a href="manage_outages.php"
class="btn btn-secondary">

Reset

</a>


</div>


</form>


</div>





<!--==================================================
OUTAGE TABLE
===================================================-->

<div class="card p-4">


<div class="d-flex justify-content-between mb-3">


<h5>

Outage List

</h5>


<a href="add_outage.php"
class="btn btn-success">

<i class="bi bi-plus-circle"></i>

Add Outage

</a>


</div>



<div class="table-responsive">


<table class="table table-hover">


<thead class="table-dark">

<tr>

<th>ID</th>

<th>Location</th>

<th>Feeder</th>

<th>Consumers</th>

<th>Start</th>

<th>Status</th>

<th>Action</th>


</tr>

</thead>


<tbody>


<?php while($row=mysqli_fetch_assoc($outages)){ ?>


<tr>


<td>

<?= $row['id'] ?>

</td>


<td>

<?= htmlspecialchars($row['zone']) ?>

<br>

<small>

<?= htmlspecialchars($row['circle']) ?>

</small>


</td>



<td>

<?= htmlspecialchars($row['feeder_name']) ?>


</td>



<td>

<?= $row['consumers_affected'] ?>

</td>



<td>

<?= date(
"d M Y h:i A",
strtotime($row['start_time'])
) ?>


</td>




<td>


<?php if($row['status']=="Active"){ ?>

<span class="badge bg-danger">

Active

</span>


<?php }else{ ?>


<span class="badge bg-success">

Restored

</span>


<?php } ?>


</td>




<td>


<a href="outage_details.php?id=<?= $row['id'] ?>"
class="btn btn-info btn-sm">

<i class="bi bi-eye"></i>

</a>



<a href="edit_outage.php?id=<?= $row['id'] ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil"></i>

</a>



<a href="delete_outage.php?id=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this outage?')">

<i class="bi bi-trash"></i>

</a>


</td>



</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>


</div>


</body>

</html>