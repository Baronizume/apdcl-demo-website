<?php
session_start();

/*====================================================
    LOGIN CHECK
====================================================*/

if (
    !isset($_SESSION['logged_in'])
)
{
    header("Location: login.php");
    exit();
}

include("../db.php");

/*====================================================
    LOGGED IN USER
====================================================*/

$adminName = $_SESSION['name'];
$role      = $_SESSION['role'];

/*====================================================
    SEARCH
====================================================*/

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string(
        $conn,
        trim($_GET['search'])
    );
}

/*====================================================
    ACTIVITY LOGS
====================================================*/

if($search!="")
{

    $logs = mysqli_query(
        $conn,
        "
        SELECT *
        FROM activity_logs
        WHERE

            admin_name LIKE '%$search%'

            OR role LIKE '%$search%'

            OR activity LIKE '%$search%'

            OR ip_address LIKE '%$search%'

        ORDER BY id DESC
        "
    );

}
else
{

    $logs = mysqli_query(
        $conn,
        "
        SELECT *
        FROM activity_logs
        ORDER BY id DESC
        LIMIT 200
        "
    );

}

/*====================================================
    DASHBOARD COUNTS
====================================================*/

$totalLogs = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT id FROM activity_logs"
    )
);

$todayLogs = mysqli_num_rows(
    mysqli_query(
        $conn,
        "
        SELECT id
        FROM activity_logs
        WHERE DATE(created_at)=CURDATE()
        "
    )
);

$totalAdmins = mysqli_num_rows(
    mysqli_query(
        $conn,
        "
        SELECT DISTINCT admin_id
        FROM activity_logs
        "
    )
);

/*====================================================
    PAGE TITLE
====================================================*/

$pageTitle = "Activity Logs";
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= $pageTitle ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:#f4f7fb;

font-family:'Segoe UI',sans-serif;

}

.page-header{

background:#0d6efd;

color:#fff;

padding:25px;

border-radius:15px;

margin-bottom:25px;

box-shadow:0 5px 15px rgba(0,0,0,.12);

}

.stat-card{

border:none;

border-radius:15px;

color:#fff;

transition:.3s;

box-shadow:0 5px 15px rgba(0,0,0,.10);

}

.stat-card:hover{

transform:translateY(-5px);

}

.card-primary{

background:#0d6efd;

}

.card-success{

background:#198754;

}

.card-warning{

background:#ffc107;

color:#000;

}

.card-icon{

font-size:40px;

opacity:.9;

}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="page-header">

<div class="d-flex justify-content-between align-items-center">

<div>

<h2>

<i class="bi bi-clock-history"></i>

Activity Logs

</h2>

<p class="mb-0">

APDCL Super Admin Portal

</p>

</div>

<div>

<a href="dashboard.php"

class="btn btn-light">

<i class="bi bi-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

</div>

<!-- =========================================
        DASHBOARD CARDS
========================================= -->

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="card stat-card card-primary">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h5>Total Logs</h5>

<h2><?= $totalLogs ?></h2>

</div>

<i class="bi bi-journal-text card-icon"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card stat-card card-success">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h5>Today's Activities</h5>

<h2><?= $todayLogs ?></h2>

</div>

<i class="bi bi-calendar-check card-icon"></i>

</div>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card stat-card card-warning">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h5>Active Admins</h5>

<h2><?= $totalAdmins ?></h2>

</div>

<i class="bi bi-people-fill card-icon"></i>

</div>

</div>

</div>

</div>

<!-- =========================================
        SEARCH & EXPORT
========================================= -->

<div class="card shadow-sm border-0 mb-4">

<div class="card-body">

<div class="row">

<div class="col-lg-8">

<form method="GET">

<div class="input-group">

<input
type="text"
class="form-control"
name="search"
placeholder="Search by Admin, Activity, Role or IP Address..."
value="<?= htmlspecialchars($search) ?>">

<button
class="btn btn-primary"
type="submit">

<i class="bi bi-search"></i>

Search

</button>

<?php if($search!=""){ ?>

<a
href="activity_logs.php"
class="btn btn-secondary">

<i class="bi bi-x-circle"></i>

Reset

</a>

<?php } ?>

</div>

</form>

</div>

<div class="col-lg-4 text-end">

<a
href="export_logs_excel.php"
class="btn btn-success">

<i class="bi bi-file-earmark-excel-fill"></i>

Excel

</a>

<a
href="export_logs_pdf.php"
class="btn btn-danger">

<i class="bi bi-file-earmark-pdf-fill"></i>

PDF

</a>

</div>

</div>

</div>

</div>

<!-- =========================================
        ACTIVITY LOG TABLE
========================================= -->

<div class="card shadow border-0">

<div class="card-header bg-dark text-white">

<div class="d-flex justify-content-between">

<h5 class="mb-0">

<i class="bi bi-list-ul"></i>

Recent Activities

</h5>

<span class="badge bg-warning text-dark">

<?= mysqli_num_rows($logs) ?>

Records

</span>

</div>

</div>

<div class="card-body">

<div class="table-responsive">

<table
class="table table-hover table-bordered align-middle">

<thead class="table-primary">

<tr>

<th width="70">ID</th>

<th width="180">Admin</th>

<th width="120">Role</th>

<th>Activity</th>

<th width="150">IP Address</th>

<th width="190">Date & Time</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($logs) > 0)
{

    while($row = mysqli_fetch_assoc($logs))
    {

?>

<tr>

    <td>

        <?= $row['id']; ?>

    </td>

    <td>

        <strong>

            <?= htmlspecialchars($row['admin_name']); ?>

        </strong>

    </td>

    <td>

        <?php

        if($row['role']=="Super Admin")
        {
            echo "<span class='badge bg-danger'>Super Admin</span>";
        }
        else
        {
            echo "<span class='badge bg-primary'>Admin</span>";
        }

        ?>

    </td>

    <td>

        <?= htmlspecialchars($row['activity']); ?>

    </td>

    <td>

        <span class="badge bg-secondary">

            <?= htmlspecialchars($row['ip_address']); ?>

        </span>

    </td>

    <td>

        <?= date("d M Y h:i A", strtotime($row['created_at'])); ?>

    </td>

</tr>

<?php

    }

}
else
{

?>

<tr>

<td colspan="6" class="text-center p-5">

<i class="bi bi-inbox display-3 text-muted"></i>

<h4 class="mt-3">

No Activity Logs Found

</h4>

<p class="text-muted">

There are currently no activity records available.

</p>

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

<!-- =========================================
        FOOTER
========================================= -->

<footer class="mt-5">

<hr>

<div class="row">

<div class="col-md-6">

<strong>

APDCL Super Admin Portal

</strong>

<br>

<small class="text-muted">

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

Logged in as

<strong>

<?= htmlspecialchars($adminName); ?>

</strong>

<br>

<small>

<?= htmlspecialchars($role); ?>

</small>

</div>

</div>

<hr>

<div class="text-center">

© <?= date("Y"); ?>

APDCL Electricity Billing Management System

|

<span id="liveClock" class="fw-bold text-primary"></span>

</div>

</footer>

</div>

<!-- =========================================
        BOOTSTRAP JS
========================================= -->

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

    let date = now.toLocaleDateString('en-IN',options);

    let time = now.toLocaleTimeString('en-IN');

    document.getElementById("liveClock").innerHTML =
    date + " | " + time;

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
        TABLE ROW HOVER
=========================================*/

document.querySelectorAll("table tbody tr").forEach(function(row){

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