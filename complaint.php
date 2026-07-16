<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$success = "";
$error = "";

/*=========================================================
    LOAD CONSUMER DETAILS
=========================================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$consumer = mysqli_fetch_assoc($result);

if(!$consumer){
    die("Consumer not found.");
}

/*=========================================================
    SUCCESS MESSAGE
=========================================================*/

if(isset($_SESSION['success'])){
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

/*=========================================================
    TOTAL COMPLAINTS
=========================================================*/

function getCount($conn,$consumer_no,$status=null){

    if($status==""){

        $stmt=mysqli_prepare($conn,"
        SELECT COUNT(*) total
        FROM complaint
        WHERE consumer_no=?
        ");

        mysqli_stmt_bind_param($stmt,"s",$consumer_no);

    }else{

        $stmt=mysqli_prepare($conn,"
        SELECT COUNT(*) total
        FROM complaint
        WHERE consumer_no=?
        AND status=?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $consumer_no,
            $status
        );

    }

    mysqli_stmt_execute($stmt);

    $result=mysqli_stmt_get_result($stmt);

    $row=mysqli_fetch_assoc($result);

    return $row['total'];

}

$totalComplaints    = getCount($conn,$consumer_no);
$pendingComplaints  = getCount($conn,$consumer_no,"Pending");
$assignedComplaints = getCount($conn,$consumer_no,"Assigned");
$progressComplaints = getCount($conn,$consumer_no,"In Progress");
$resolvedComplaints = getCount($conn,$consumer_no,"Resolved");

/*=========================================================
    TODAY'S COMPLAINTS
=========================================================*/

$stmt=mysqli_prepare($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE consumer_no=?
AND DATE(created_at)=CURDATE()
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$row=mysqli_fetch_assoc($result);

$todayComplaints=$row['total'];

/*=========================================================
    SEARCH
=========================================================*/

$search = trim($_GET['search'] ?? "");

if($search==""){

    $stmt=mysqli_prepare($conn,"
    SELECT *
    FROM complaint
    WHERE consumer_no=?
    ORDER BY id DESC
    ");

    mysqli_stmt_bind_param($stmt,"s",$consumer_no);

}
else{

    $like="%".$search."%";

    $stmt=mysqli_prepare($conn,"
    SELECT *
    FROM complaint
    WHERE consumer_no=?
    AND
    (
        complaint_id LIKE ?
        OR category LIKE ?
        OR subject LIKE ?
        OR status LIKE ?
    )
    ORDER BY id DESC
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $consumer_no,
        $like,
        $like,
        $like,
        $like
    );

}

mysqli_stmt_execute($stmt);

$complaints=mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Complaint Management | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef3fb;
    font-family:'Segoe UI',sans-serif;
}

/*=============================
NAVBAR
==============================*/

.navbar{

    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

    box-shadow:0 8px 20px rgba(0,0,0,.15);

}

.navbar-brand{

    color:#fff!important;

    font-weight:700;

    font-size:24px;

}

.navbar .btn{

    border-radius:10px;

    font-weight:600;

}

/*=============================
HEADER CARD
==============================*/

.header-card{

    background:linear-gradient(135deg,#1565c0,#1976d2,#1e88e5);

    color:#fff;

    border-radius:20px;

    padding:35px;

    box-shadow:0 12px 30px rgba(0,0,0,.15);

}

.header-card h2{

    font-weight:700;

}

.header-card p{

    opacity:.95;

    margin-bottom:0;

}

/*=============================
STATISTICS
==============================*/

.stats-card{

    border:none;

    border-radius:18px;

    color:#fff;

    transition:.3s;

    overflow:hidden;

    box-shadow:0 8px 20px rgba(0,0,0,.12);

}

.stats-card:hover{

    transform:translateY(-8px);

}

.stats-card .card-body{

    padding:25px;

}

.stats-card i{

    font-size:40px;

    opacity:.85;

}

.card-total{

    background:linear-gradient(135deg,#1565c0,#1e88e5);

}

.card-pending{

    background:linear-gradient(135deg,#ff9800,#ffc107);

    color:#212529;

}

.card-assigned{

    background:linear-gradient(135deg,#5e35b1,#7e57c2);

}

.card-progress{

    background:linear-gradient(135deg,#00838f,#26c6da);

}

.card-resolved{

    background:linear-gradient(135deg,#2e7d32,#4caf50);

}

.card-today{

    background:linear-gradient(135deg,#ef6c00,#fb8c00);

}

/*=============================
TABLE
==============================*/

.table-card{

    border:none;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.table th{

    white-space:nowrap;

}

.table td{

    vertical-align:middle;

}

/*=============================
BADGES
==============================*/

.badge{

    font-size:13px;

    padding:8px 12px;

    border-radius:25px;

}

/*=============================
BUTTONS
==============================*/

.btn{

    border-radius:10px;

}

/*=============================
RESPONSIVE
==============================*/

@media(max-width:768px){

.header-card{

text-align:center;

}

.header-card .btn{

margin-top:10px;

}

}

</style>

</head>

<body>

<!-- ===========================
NAVBAR
=========================== -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="dashboard.php">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Portal

</a>

<div>

<a href="dashboard.php" class="btn btn-light me-2">

<i class="bi bi-house-door-fill"></i>

Dashboard

</a>

<a href="new_complaint.php" class="btn btn-warning">

<i class="bi bi-plus-circle-fill"></i>

New Complaint

</a>

</div>

</div>

</nav>

<div class="container py-4">

<!-- ===========================
HEADER
=========================== -->

<div class="header-card mb-4">

<div class="row align-items-center">

<div class="col-lg-8">

<h2>

<i class="bi bi-chat-left-text-fill"></i>

Complaint Management

</h2>

<p class="mt-2">

Welcome,

<strong><?= htmlspecialchars($consumer['name']) ?></strong>

Manage and track your electricity complaints online.

</p>

</div>

<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

<h5 class="mb-1">

Consumer No.

</h5>

<h3>

<?= htmlspecialchars($consumer_no) ?>

</h3>

</div>

</div>

</div>

<!-- =========================================
     DASHBOARD STATISTICS
========================================= -->

<div class="row g-4 mb-4">

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-total">

            <div class="card-body text-center">

                <i class="bi bi-chat-square-text-fill"></i>

                <h2 class="mt-3 mb-1">
                    <?= $totalComplaints ?>
                </h2>

                <small>Total Complaints</small>

            </div>

        </div>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-pending">

            <div class="card-body text-center">

                <i class="bi bi-hourglass-split"></i>

                <h2 class="mt-3 mb-1">
                    <?= $pendingComplaints ?>
                </h2>

                <small>Pending</small>

            </div>

        </div>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-assigned">

            <div class="card-body text-center">

                <i class="bi bi-person-check-fill"></i>

                <h2 class="mt-3 mb-1">
                    <?= $assignedComplaints ?>
                </h2>

                <small>Assigned</small>

            </div>

        </div>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-progress">

            <div class="card-body text-center">

                <i class="bi bi-tools"></i>

                <h2 class="mt-3 mb-1">
                    <?= $progressComplaints ?>
                </h2>

                <small>In Progress</small>

            </div>

        </div>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-resolved">

            <div class="card-body text-center">

                <i class="bi bi-patch-check-fill"></i>

                <h2 class="mt-3 mb-1">
                    <?= $resolvedComplaints ?>
                </h2>

                <small>Resolved</small>

            </div>

        </div>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <div class="card stats-card card-today">

            <div class="card-body text-center">

                <i class="bi bi-calendar-check-fill"></i>

                <h2 class="mt-3 mb-1">
                    <?= $todayComplaints ?>
                </h2>

                <small>Today</small>

            </div>

        </div>

    </div>

</div>

<!-- =========================================
     SUCCESS / ERROR MESSAGE
========================================= -->

<?php if(!empty($success)){ ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill"></i>

    <?= htmlspecialchars($success) ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

<?php } ?>

<?php if(!empty($error)){ ?>

<div class="alert alert-danger alert-dismissible fade show">

    <i class="bi bi-exclamation-triangle-fill"></i>

    <?= htmlspecialchars($error) ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

<?php } ?>

<!-- =========================================
     SEARCH PANEL
========================================= -->

<div class="card table-card mb-4">

    <div class="card-header bg-primary text-white">

        <div class="d-flex justify-content-between align-items-center flex-wrap">

            <h5 class="mb-0">

                <i class="bi bi-search"></i>

                Search Complaints

            </h5>

            <form method="GET" class="d-flex mt-2 mt-lg-0">

                <input
                    type="text"
                    name="search"
                    class="form-control me-2"
                    placeholder="Search Complaint ID, Subject, Category..."
                    value="<?= htmlspecialchars($search) ?>">

                <button class="btn btn-light">

                    <i class="bi bi-search"></i>

                </button>

            </form>

        </div>

    </div>

</div>

<!-- =========================================
     MY COMPLAINTS TABLE
========================================= -->

<div class="card table-card">

    <div class="card-header bg-dark text-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="bi bi-list-ul"></i>

                My Complaints

            </h5>

            <span class="badge bg-light text-dark">

                Total :
                <?= mysqli_num_rows($complaints) ?>

            </span>

        </div>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead class="table-primary">

                <tr>

                    <th>#</th>

                    <th>Complaint ID</th>

                    <th>Category</th>

                    <th>Subject</th>

                    <th>Priority</th>

                    <th>Status</th>

                    <th>Date</th>

                    <th width="220">Action</th>

                </tr>

                </thead>

                <tbody>

                <?php

                $sl=1;

                while($row=mysqli_fetch_assoc($complaints)){

                ?>

                <tr>

                    <td><?= $sl++ ?></td>

                    <td>

                        <strong class="text-primary">

                            <?= htmlspecialchars($row['complaint_id']) ?>

                        </strong>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['category']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['subject']) ?>

                    </td>

                    <td>

                        <?php

                        switch($row['priority']){

                            case "Low":
                                $priority="success";
                                break;

                            case "Medium":
                                $priority="warning";
                                break;

                            case "High":
                                $priority="danger";
                                break;

                            default:
                                $priority="secondary";

                        }

                        ?>

                        <span class="badge bg-<?= $priority ?>">

                            <?= htmlspecialchars($row['priority']) ?>

                        </span>

                    </td>

                    <td>

                        <?php

                        switch($row['status']){

                            case "Pending":
                                $status="warning";
                                break;

                            case "Assigned":
                                $status="info";
                                break;

                            case "In Progress":
                                $status="primary";
                                break;

                            case "Resolved":
                                $status="success";
                                break;

                            case "Rejected":
                                $status="danger";
                                break;

                            default:
                                $status="secondary";

                        }

                        ?>

                        <span class="badge bg-<?= $status ?>">

                            <?= htmlspecialchars($row['status']) ?>

                        </span>

                    </td>

                    <td>

                        <?= date("d M Y",strtotime($row['created_at'])) ?>

                    </td>

                    <td>

                        <a
                        href="track_complaint.php?id=<?= $row['id'] ?>"
                        class="btn btn-primary btn-sm">

                            <i class="bi bi-geo-alt-fill"></i>

                            Track

                        </a>

                        <a
                        href="view_complaint.php?id=<?= $row['id'] ?>"
                        class="btn btn-success btn-sm">

                            <i class="bi bi-eye-fill"></i>

                            View

                        </a>

                        <?php if($row['status']=="Pending"){ ?>

                        <a
                        href="edit_complaint.php?id=<?= $row['id'] ?>"
                        class="btn btn-warning btn-sm">

                            <i class="bi bi-pencil-fill"></i>

                            Edit

                        </a>

                        <?php } ?>

                    </td>

                </tr>

                <?php } ?>

                <?php if(mysqli_num_rows($complaints)==0){ ?>

                <tr>

                    <td colspan="8" class="text-center py-5">

                        <i class="bi bi-inbox display-3 text-secondary"></i>

                        <h4 class="mt-3">

                            No Complaints Found

                        </h4>

                        <p class="text-muted">

                            Click the <strong>New Complaint</strong> button to register your first complaint.

                        </p>

                        <a
                        href="new_complaint.php"
                        class="btn btn-primary">

                            <i class="bi bi-plus-circle-fill"></i>

                            Register Complaint

                        </a>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ==========================================
     FOOTER
========================================== -->

<footer class="mt-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center">

            <img
            src="../assets/images/logo-circle.png"
            width="55"
            class="mb-3">

            <h5 class="text-primary fw-bold">

                Assam Power Distribution Company Limited

            </h5>

            <p class="text-muted mb-2">

                APDCL Consumer Complaint Management Portal

            </p>

            <small class="text-secondary">

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>
<!-- End Container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    CARD ANIMATION
=========================================*/

window.addEventListener("load",function(){

    let cards=document.querySelectorAll(".stats-card,.table-card");

    cards.forEach(function(card,index){

        card.style.opacity="0";
        card.style.transform="translateY(25px)";

        setTimeout(function(){

            card.style.transition=".5s";

            card.style.opacity="1";

            card.style.transform="translateY(0)";

        },index*120);

    });

});

/*=========================================
    AUTO HIDE ALERT
=========================================*/

setTimeout(function(){

    let alerts=document.querySelectorAll(".alert");

    alerts.forEach(function(alert){

        let bsAlert=new bootstrap.Alert(alert);

        bsAlert.close();

    });

},5000);

/*=========================================
    DELETE CONFIRMATION
=========================================*/

document.querySelectorAll(".deleteComplaint").forEach(function(btn){

    btn.addEventListener("click",function(e){

        if(!confirm("Are you sure you want to delete this complaint?")){

            e.preventDefault();

        }

    });

});

</script>

</body>

</html>