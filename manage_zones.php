<?php
session_start();
include("../db.php");

/*=========================================
LOGIN CHECK
=========================================*/

if(
    !isset($_SESSION['logged_in']) ||
    $_SESSION['role']!="Super Admin"
){
    header("Location: login.php");
    exit();
}

/*=========================================
LOGGED IN USER
=========================================*/

$adminId   = $_SESSION['admin_id'];
$adminName = $_SESSION['name'];

/*=========================================
MESSAGE
=========================================*/

$message="";

/*=========================================
SEARCH
=========================================*/

$search="";

if(isset($_GET['search']))
{
    $search=trim($_GET['search']);
}

/*=========================================
PAGINATION
=========================================*/

$limit=10;

$page=isset($_GET['page'])?(int)$_GET['page']:1;

if($page<1)
{
    $page=1;
}

$start=($page-1)*$limit;

/*=========================================
SEARCH CONDITION
=========================================*/

$where="";

if($search!="")
{
    $search=mysqli_real_escape_string($conn,$search);

    $where="
    WHERE
    zone_name LIKE '%$search%'
    OR status LIKE '%$search%'
    ";
}

/*=========================================
TOTAL RECORDS
=========================================*/

$count=mysqli_query(
$conn,
"
SELECT COUNT(*) total
FROM zones
$where
"
);

$row=mysqli_fetch_assoc($count);

$totalRecords=$row['total'];

$totalPages=ceil($totalRecords/$limit);

/*=========================================
FETCH ZONES
=========================================*/

$zones=mysqli_query(
$conn,
"
SELECT *
FROM zones
$where
ORDER BY zone_name ASC
LIMIT $start,$limit
"
);

/*=========================================
DEFAULT VALUES
=========================================*/

$id=0;

$zone_name="";

$status="Active";

$edit=false;

/*=========================================
ADD ZONE
=========================================*/

if(isset($_POST['save']))
{

    $zone_name = trim($_POST['zone_name']);

    $status = trim($_POST['status']);

    if(empty($zone_name))
    {

        $message='
        <div class="alert alert-danger">

        Please enter Zone Name.

        </div>';

    }
    else
    {

        $zone_name=mysqli_real_escape_string($conn,$zone_name);

        $check=mysqli_query(
        $conn,
        "SELECT id
        FROM zones
        WHERE zone_name='$zone_name'
        LIMIT 1");

        if(mysqli_num_rows($check)>0)
        {

            $message='
            <div class="alert alert-danger">

            Zone already exists.

            </div>';

        }
        else
        {

            mysqli_query(
            $conn,
            "INSERT INTO zones
            (
                zone_name,
                status
            )
            VALUES
            (
                '$zone_name',
                '$status'
            )");

            $message='
            <div class="alert alert-success">

            Zone Added Successfully.

            </div>';

            $zone_name="";

            $status="Active";

        }

    }

}

/*=========================================
DELETE ZONE
=========================================*/

if(isset($_GET['delete']))
{

    $deleteId=(int)$_GET['delete'];

    mysqli_query(
    $conn,
    "DELETE FROM zones
    WHERE id='$deleteId'");

    header("Location: manage_zones.php");

    exit();

}

/*=========================================
EDIT ZONE
=========================================*/

if(isset($_GET['edit']))
{

    $editId=(int)$_GET['edit'];

    $result=mysqli_query(
    $conn,
    "SELECT *
    FROM zones
    WHERE id='$editId'
    LIMIT 1");

    if(mysqli_num_rows($result)>0)
    {

        $edit=true;

        $row=mysqli_fetch_assoc($result);

        $id=$row['id'];

        $zone_name=$row['zone_name'];

        $status=$row['status'];

    }

}

/*=========================================
UPDATE ZONE
=========================================*/

if(isset($_POST['update']))
{

    $id=(int)$_POST['id'];

    $zone_name=trim($_POST['zone_name']);

    $status=trim($_POST['status']);

    mysqli_query(
    $conn,
    "UPDATE zones SET

    zone_name='$zone_name',

    status='$status'

    WHERE id='$id'");

    header("Location: manage_zones.php");

    exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Manage Zones |
APDCL Super Admin Portal

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<link
rel="stylesheet"
href="../assets/css/admin.css">

</head>

<style>

/*=========================================
    GENERAL
=========================================*/

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/*=========================================
    NAVBAR
=========================================*/

.navbar{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:75px;
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    box-shadow:0 5px 15px rgba(0,0,0,.15);
    z-index:1000;
}

.logo{
    width:45px;
    height:45px;
    object-fit:contain;
    background:#fff;
    border-radius:50%;
    padding:3px;
    margin-right:12px;
}

.brand-title{
    color:#fff;
    font-size:24px;
    font-weight:bold;
    margin:0;
}

.brand-sub{
    color:#dbeafe;
    font-size:13px;
}

/*=========================================
    SIDEBAR
=========================================*/

.sidebar{
    position:fixed;
    top:75px;
    left:0;
    width:250px;
    height:calc(100vh - 75px);
    background:#083b8a;
    overflow-y:auto;
    padding-top:15px;
}

.sidebar a{
    display:flex;
    align-items:center;
    padding:15px 22px;
    color:#fff;
    text-decoration:none;
    font-size:15px;
    transition:.3s;
    border-left:4px solid transparent;
}

.sidebar a i{
    width:28px;
    font-size:18px;
    margin-right:12px;
}

.sidebar a:hover{
    background:#1976d2;
    padding-left:28px;
    border-left:4px solid #ffc107;
}

.sidebar a.active{
    background:#1565c0;
    border-left:4px solid #ffc107;
}

/*=========================================
    MAIN CONTENT
=========================================*/

.main-content{
    margin-left:270px;
    margin-top:95px;
    padding:30px;
}

/*=========================================
    CARDS
=========================================*/

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.card-header{
    border-radius:15px 15px 0 0 !important;
}

/*=========================================
    TABLE
=========================================*/

.table th{
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
}

/*=========================================
    FOOTER
=========================================*/

footer{
    margin-top:40px;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

/*=========================================
    RESPONSIVE
=========================================*/

@media(max-width:992px){

    .sidebar{
        width:220px;
    }

    .main-content{
        margin-left:220px;
    }

}

@media(max-width:768px){

    .sidebar{
        position:relative;
        width:100%;
        height:auto;
        top:0;
    }

    .main-content{
        margin-left:0;
        margin-top:20px;
    }

}

</style>

<body>

<!--=========================================
NAVBAR
==========================================-->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
class="logo"
alt="APDCL Logo"
style="width:45px; height:45px; object-fit:contain;">

<div>

<h4 class="brand-title">

APDCL Super Admin Portal

</h4>

<div class="brand-sub">

Assam Power Distribution Company Limited

</div>

</div>

</div>

<div class="text-white text-end">

<strong>

<?= htmlspecialchars($adminName) ?>

</strong>

<br>

<small>

Super Administrator

</small>

</div>

</div>

</nav>

<!--=========================================
SIDEBAR
==========================================-->

<div class="sidebar">

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

<a
href="manage_zones.php"
class="active">

<i class="bi bi-globe-central-south-asia"></i>

Manage Zones

</a>

<a href="manage_circles.php">

<i class="bi bi-diagram-3-fill"></i>

Manage Circles

</a>

<a href="manage_subdivisions.php">

<i class="bi bi-building"></i>

Manage Sub-Divisions

</a>

<a href="manage_admins.php">

<i class="bi bi-person-badge"></i>

Manage Admins

</a>

<a href="manage_consumers.php">

<i class="bi bi-people-fill"></i>

Consumers

</a>

<a href="manage_bills.php">

<i class="bi bi-receipt-cutoff"></i>

Bills

</a>

<a href="complaints.php">

<i class="bi bi-chat-left-text-fill"></i>

Complaints

</a>

<hr>

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

<!--=========================================
MAIN CONTENT
==========================================-->

<div class="main-content">

<div class="card shadow mb-4">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-globe-central-south-asia"></i>

Manage Zones

</h4>

</div>

<div class="card-body">

<?= $message ?>

<form method="POST">

<input
type="hidden"
name="id"
value="<?= $id ?>">

<div class="row">

<div class="col-md-8 mb-3">

<label class="form-label">

Zone Name

</label>

<input
type="text"
name="zone_name"
class="form-control"
required
placeholder="Enter Zone Name"
value="<?= htmlspecialchars($zone_name) ?>">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option
value="Active"
<?= ($status=="Active")?"selected":""; ?>>

Active

</option>

<option
value="Inactive"
<?= ($status=="Inactive")?"selected":""; ?>>

Inactive

</option>

</select>

</div>

</div>

<div class="mt-3">

<?php if($edit){ ?>

<button
type="submit"
name="update"
class="btn btn-success">

<i class="bi bi-pencil-square"></i>

Update Zone

</button>

<a
href="manage_zones.php"
class="btn btn-secondary">

Cancel

</a>

<?php } else { ?>

<button
type="submit"
name="save"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Add Zone

</button>

<button
type="reset"
class="btn btn-warning">

Reset

</button>

<?php } ?>

</div>

</form>

</div>

</div>

<!--=========================================
    ZONE LIST
==========================================-->

<div class="card">

<div class="card-header bg-dark text-white">

<div class="row align-items-center">

<div class="col-md-6">

<h4 class="mb-0">

<i class="bi bi-list-ul"></i>

Zone List

</h4>

</div>

<div class="col-md-6">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Zone..."
value="<?= htmlspecialchars($search) ?>">

<button
class="btn btn-primary"
type="submit">

<i class="bi bi-search"></i>

</button>

</div>

</form>

</div>

</div>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-primary">

<tr>

<th width="80">ID</th>

<th>Zone Name</th>

<th width="120">Status</th>

<th width="170">Action</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($zones)>0)
{

while($row=mysqli_fetch_assoc($zones))
{

?>

<tr>

<td>

<?= $row['id'] ?>

</td>

<td>

<?= htmlspecialchars($row['zone_name']) ?>

</td>

<td>

<?php

if($row['status']=="Active")
{

echo '<span class="badge bg-success">

Active

</span>';

}
else
{

echo '<span class="badge bg-danger">

Inactive

</span>';

}

?>

</td>

<td>

<a
href="?edit=<?= $row['id'] ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

</a>

<a
href="?delete=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this Zone?')">

<i class="bi bi-trash"></i>

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

<td colspan="4" class="text-center">

No Zone Found

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

<!--=========================================
    PAGINATION
==========================================-->

<?php

if($totalPages>1)
{

?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php

for($i=1;$i<=$totalPages;$i++)
{

?>

<li class="page-item <?= ($page==$i)?'active':''; ?>">

<a
class="page-link"
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">

<?= $i ?>

</a>

</li>

<?php

}

?>

</ul>

</nav>

<?php

}

?>

<!--=========================================
    FOOTER
==========================================-->

<footer class="mt-5">

<div class="row">

<div class="col-md-6">

<strong>

APDCL Super Admin Portal

</strong>

<br>

<small>

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

<strong>

Logged in as

</strong>

<br>

<?= htmlspecialchars($adminName) ?>

<br>

<small>

Super Administrator

</small>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-6">

© <?= date("Y") ?>

APDCL

All Rights Reserved

</div>

<div class="col-md-6 text-end">

<span
id="liveClock"
class="fw-bold text-primary">

</span>

</div>

</div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function updateClock()
{

let now = new Date();

let options = {

weekday:'short',

day:'2-digit',

month:'short',

year:'numeric'

};

let date = now.toLocaleDateString('en-IN',options);

let time = now.toLocaleTimeString();

document.getElementById("liveClock").innerHTML =
date + " | " + time;

}

updateClock();

setInterval(updateClock,1000);

document.querySelectorAll(".card").forEach(function(card){

card.addEventListener("mouseenter",function(){

this.style.transform="translateY(-5px)";

this.style.transition=".3s";

});

card.addEventListener("mouseleave",function(){

this.style.transform="translateY(0px)";

});

});

document.querySelectorAll("tbody tr").forEach(function(row){

row.addEventListener("mouseenter",function(){

this.style.background="#eef6ff";

});

row.addEventListener("mouseleave",function(){

this.style.background="";

});

});

</script>

</body>

</html>