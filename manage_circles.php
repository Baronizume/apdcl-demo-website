<?php
session_start();
include("../db.php");

/*=========================================
    LOGIN CHECK
=========================================*/

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['role'] != "Super Admin"
) {
    header("Location: login.php");
    exit();
}

/*=========================================
    LOGGED IN USER
=========================================*/

$adminId   = $_SESSION['admin_id'];
$adminName = $_SESSION['name'];

$message = "";

/*=========================================
    DEFAULT VALUES
=========================================*/

$id          = 0;
$zone_id     = "";
$circle_name = "";
$status      = "Active";
$edit        = false;

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

$start = ($page - 1) * $limit;

/*=========================================
    SEARCH CONDITION
=========================================*/

$where = "";

if($search!="")
{
    $search = mysqli_real_escape_string($conn,$search);

    $where = "
    WHERE
        zones.zone_name LIKE '%$search%'
        OR circles.circle_name LIKE '%$search%'
        OR circles.status LIKE '%$search%'
    ";
}

/*=========================================
    TOTAL RECORDS
=========================================*/

$countQuery = mysqli_query(
$conn,
"
SELECT COUNT(*) total

FROM circles

INNER JOIN zones
ON circles.zone_id = zones.id

$where
"
);

$countRow = mysqli_fetch_assoc($countQuery);

$totalRecords = $countRow['total'];

$totalPages = ceil($totalRecords/$limit);

/*=========================================
    FETCH CIRCLES
=========================================*/

$circles = mysqli_query(
$conn,
"
SELECT

circles.*,

zones.zone_name

FROM circles

INNER JOIN zones
ON circles.zone_id = zones.id

$where

ORDER BY
zones.zone_name,
circles.circle_name

LIMIT $start,$limit
"
);

/*=========================================
    FETCH ACTIVE ZONES
=========================================*/

$zoneList = mysqli_query(
$conn,
"
SELECT *
FROM zones
WHERE status='Active'
ORDER BY zone_name ASC
"
);

/*=========================================
    ADD CIRCLE
=========================================*/

if(isset($_POST['save']))
{

    $zone_id = (int)$_POST['zone_id'];

    $circle_name = trim($_POST['circle_name']);

    $status = trim($_POST['status']);

    if($zone_id==0 || $circle_name=="")
    {

        $message='
        <div class="alert alert-danger">

        Please fill all required fields.

        </div>';

    }
    else
    {

        $circle_name = mysqli_real_escape_string(
        $conn,
        $circle_name
        );

        $check = mysqli_query(
        $conn,
        "
        SELECT id

        FROM circles

        WHERE
        zone_id='$zone_id'

        AND circle_name='$circle_name'

        LIMIT 1
        "
        );

        if(mysqli_num_rows($check)>0)
        {

            $message='
            <div class="alert alert-danger">

            Circle already exists in this Zone.

            </div>';

        }
        else
        {

            mysqli_query(
            $conn,
            "
            INSERT INTO circles
            (
                zone_id,
                circle_name,
                status
            )
            VALUES
            (
                '$zone_id',
                '$circle_name',
                '$status'
            )
            "
            );

            $message='
            <div class="alert alert-success">

            Circle added successfully.

            </div>';

            $zone_id="";
            $circle_name="";
            $status="Active";

        }

    }

}

/*=========================================
    DELETE CIRCLE
=========================================*/

if(isset($_GET['delete']))
{

    $deleteId=(int)$_GET['delete'];

    mysqli_query(
    $conn,
    "
    DELETE FROM circles
    WHERE id='$deleteId'
    "
    );

    header("Location: manage_circles.php");
    exit();

}

/*=========================================
    EDIT CIRCLE
=========================================*/

if(isset($_GET['edit']))
{

    $editId=(int)$_GET['edit'];

    $result=mysqli_query(
    $conn,
    "
    SELECT *
    FROM circles
    WHERE id='$editId'
    LIMIT 1
    "
    );

    if(mysqli_num_rows($result)>0)
    {

        $edit=true;

        $row=mysqli_fetch_assoc($result);

        $id=$row['id'];

        $zone_id=$row['zone_id'];

        $circle_name=$row['circle_name'];

        $status=$row['status'];

    }

}

/*=========================================
    UPDATE CIRCLE
=========================================*/

if(isset($_POST['update']))
{

    $id=(int)$_POST['id'];

    $zone_id=(int)$_POST['zone_id'];

    $circle_name=mysqli_real_escape_string(
    $conn,
    trim($_POST['circle_name'])
    );

    $status=mysqli_real_escape_string(
    $conn,
    $_POST['status']
    );

    mysqli_query(
    $conn,
    "
    UPDATE circles

    SET

    zone_id='$zone_id',

    circle_name='$circle_name',

    status='$status'

    WHERE id='$id'
    "
    );

    header("Location: manage_circles.php");
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

Manage Circles |
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

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

/*=========================================
NAVBAR
=========================================*/

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.logo{
    width:45px;
    height:45px;
    object-fit:contain;
    border-radius:50%;
    background:#fff;
    padding:3px;
}

.brand-title{
    color:#fff;
    font-weight:700;
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
    height:100%;
    background:#083b8a;
    overflow-y:auto;
}

.sidebar a{
    display:block;
    color:#fff;
    text-decoration:none;
    padding:15px 20px;
    transition:.3s;
}

.sidebar a:hover{
    background:#1565c0;
}

.sidebar a.active{
    background:#1976d2;
    border-left:5px solid #ffc107;
}

/*=========================================
CONTENT
=========================================*/

.main-content{
    margin-left:260px;
    padding:30px;
    margin-top:20px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.card-header{
    border-radius:15px 15px 0 0!important;
}

</style>

</head>

<body>

<!--=========================================
NAVBAR
=========================================-->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
class="logo me-3">

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
=========================================-->

<div class="sidebar">

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

<a href="manage_zones.php">

<i class="bi bi-globe2"></i>

Manage Zones

</a>

<a
href="manage_circles.php"
class="active">

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

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

<a
href="dashboard.php"
class="btn btn-secondary mb-3">

<i class="bi bi-arrow-left-circle"></i>

Back to Dashboard

</a>

</div>

<!--=========================================
MAIN CONTENT
=========================================-->

<div class="main-content">

<div class="card mb-4">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-diagram-3-fill"></i>

<?= $edit ? "Edit Circle" : "Add New Circle"; ?>

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

<div class="col-md-6 mb-3">

<label class="form-label">

Zone

</label>

<select
name="zone_id"
class="form-select"
required>

<option value="">

-- Select Zone --

</option>

<?php

mysqli_data_seek($zoneList,0);

while($z=mysqli_fetch_assoc($zoneList))
{

?>

<option
value="<?= $z['id'] ?>"
<?= ($zone_id==$z['id'])?"selected":""; ?>>

<?= htmlspecialchars($z['zone_name']) ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Circle Name

</label>

<input
type="text"
name="circle_name"
class="form-control"
required
value="<?= htmlspecialchars($circle_name) ?>">

</div>

<div class="col-md-6 mb-3">

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

<?php if($edit){ ?>

<button
type="submit"
name="update"
class="btn btn-success">

<i class="bi bi-pencil-square"></i>

Update Circle

</button>

<a
href="manage_circles.php"
class="btn btn-secondary">

Cancel

</a>

<?php } else { ?>

<button
type="submit"
name="save"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Add Circle

</button>

<button
type="reset"
class="btn btn-warning">

Reset

</button>

<?php } ?>

</form>

</div>

</div>

<!--=========================================
    CIRCLE LIST
==========================================-->

<div class="card">

<div class="card-header bg-dark text-white">

<div class="row align-items-center">

<div class="col-md-6">

<h4 class="mb-0">

<i class="bi bi-list-ul"></i>

Circle List

</h4>

</div>

<div class="col-md-6">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Circle..."
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

<th width="70">ID</th>

<th>Zone</th>

<th>Circle Name</th>

<th width="120">Status</th>

<th width="180">Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($circles)>0)
{

while($row=mysqli_fetch_assoc($circles))
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

<?= htmlspecialchars($row['circle_name']) ?>

</td>

<td>

<?php

if($row['status']=="Active")
{

?>

<span class="badge bg-success">

Active

</span>

<?php

}
else
{

?>

<span class="badge bg-danger">

Inactive

</span>

<?php

}

?>

</td>

<td>

<a
href="?edit=<?= $row['id'] ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

Edit

</a>

<a
href="?delete=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this circle?');">

<i class="bi bi-trash"></i>

Delete

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

<td
colspan="5"
class="text-center text-muted">

No Circle Found.

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

<?php if($totalPages > 1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<!-- Previous -->

<li class="page-item <?= ($page<=1)?'disabled':''; ?>">

<a
class="page-link"
href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">

<i class="bi bi-chevron-left"></i>

</a>

</li>

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

<!-- Next -->

<li class="page-item <?= ($page>=$totalPages)?'disabled':''; ?>">

<a
class="page-link"
href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">

<i class="bi bi-chevron-right"></i>

</a>

</li>

</ul>

</nav>

<?php } ?>


<!--=========================================
    FOOTER
==========================================-->

<footer class="mt-5">

<div class="card">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-6">

<h6 class="mb-1">

APDCL Super Admin Portal

</h6>

<small class="text-muted">

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

APDCL.

All Rights Reserved.

</div>

<div class="col-md-6 text-end">

<span
id="liveClock"
class="fw-bold text-primary">

</span>

</div>

</div>

</div>

</div>

</footer>

</div>

<!--=========================================
    BOOTSTRAP
==========================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

/*=========================================
    LIVE CLOCK
=========================================*/

function updateClock()
{

    let now = new Date();

    let options = {

        weekday:'short',

        day:'2-digit',

        month:'short',

        year:'numeric'

    };

    let date = now.toLocaleDateString(
        'en-IN',
        options
    );

    let time = now.toLocaleTimeString(
        'en-IN'
    );

    document.getElementById("liveClock").innerHTML =
        date + " | " + time;

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
    CARD HOVER EFFECT
=========================================*/

document.querySelectorAll(".card").forEach(function(card){

    card.addEventListener("mouseenter",function(){

        this.style.transform="translateY(-5px)";
        this.style.transition=".3s";

    });

    card.addEventListener("mouseleave",function(){

        this.style.transform="translateY(0px)";

    });

});

/*=========================================
    TABLE ROW HOVER
=========================================*/

document.querySelectorAll("tbody tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.background="#eef6ff";

    });

    row.addEventListener("mouseleave",function(){

        this.style.background="";

    });

});

/*=========================================
    AUTO HIDE ALERT
=========================================*/

setTimeout(function(){

    let alert=document.querySelector(".alert");

    if(alert)
    {
        alert.style.transition="0.5s";
        alert.style.opacity="0";

        setTimeout(function(){

            alert.remove();

        },500);
    }

},4000);

</script>

</body>

</html>