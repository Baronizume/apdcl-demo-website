<?php
session_start();
include("../db.php");

/*=========================================
LOGIN CHECK
=========================================*/
if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

$consumer_no = $_SESSION['consumer'];

/*=========================================
GET CONSUMER DETAILS
=========================================*/
$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($userQuery)==0){
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
PROFILE PHOTO
=========================================*/
$profilePhoto = "default-user.png";

if (
    !empty($user['photo']) &&
    file_exists("../assets/profile/" . $user['photo'])
) {
    $profilePhoto = $user['photo'];
}

date_default_timezone_set("Asia/Kolkata");
/*=========================================
CURRENT BILL
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1
");

$currentBill = mysqli_fetch_assoc($billQuery);

$currentDue = $currentBill['total_bill'] ?? 0;
$dueDate    = $currentBill['due_date'] ?? "N/A";

$today = date("l, d F Y");
/*=========================================
DASHBOARD STATISTICS
=========================================*/

// Total Bills
$totalBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
"));

// Paid Bills
$paidBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
AND payment_mode<>'Pending'
"));

// Pending Bills
$pendingBills = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM bills
WHERE consumer_no='$consumer_no'
AND payment_mode='Pending'
"));

// Total Payments
$totalPayments = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM payments
WHERE consumer_no='$consumer_no'
"));

// Complaints
$totalComplaints = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM complaint
WHERE consumer_no='$consumer_no'
"));

// Notices
$totalNotices = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM notices
"));

/*=========================================
CHART DATA
=========================================*/

// Monthly Bills
$monthlyBills = [];

$billChart = mysqli_query($conn,"
SELECT
DATE_FORMAT(bill_date,'%b') AS month,
SUM(total_bill) AS amount
FROM bills
WHERE consumer_no='$consumer_no'
GROUP BY MONTH(bill_date)
ORDER BY MONTH(bill_date)
");

while($row = mysqli_fetch_assoc($billChart)){
    $monthlyBills[] = $row;
}

// Complaint Status

$pendingComplaint = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM complaint
WHERE consumer_no='$consumer_no'
AND status='Pending'
"));

$resolvedComplaint = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM complaint
WHERE consumer_no='$consumer_no'
AND status='Resolved'
"));

$progressComplaint = mysqli_num_rows(mysqli_query($conn,"
SELECT id
FROM complaint
WHERE consumer_no='$consumer_no'
AND status='In Progress'
"));

/*=========================================
PENDING BILLS
=========================================*/

$pendingBillsList = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
AND payment_mode='Pending'
ORDER BY due_date ASC
LIMIT 5
");

/*=========================================
RECENT PAYMENTS
=========================================*/

$recentPayments = mysqli_query($conn,"
SELECT *
FROM payments
WHERE consumer_no='$consumer_no'
ORDER BY payment_date DESC
LIMIT 5
");

/*=========================================
RECENT COMPLAINTS
=========================================*/

$recentComplaints = mysqli_query($conn,"
SELECT *
FROM complaint
WHERE consumer_no='$consumer_no'
ORDER BY created_at DESC
LIMIT 5
");

/*=========================================
LATEST NOTICES
=========================================*/

$latestNotices = mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
LIMIT 5
");

$profilePhoto = "default-user.png";

if (
    !empty($user['photo']) &&
    file_exists("../assets/profile/" . $user['photo'])
) {
    $profilePhoto = $user['photo'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Consumer Dashboard | APDCL</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>

/*==================================================
GOOGLE FONT
==================================================*/
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

/*==================================================
BODY
==================================================*/
body{
    background:#f4f7fc;
    color:#333;
    overflow-x:hidden;
}

/*==================================================
LINKS
==================================================*/
a{
    text-decoration:none;
    transition:.3s;
}

/*==================================================
LIST
==================================================*/
ul{
    list-style:none;
    margin:0;
    padding:0;
}

/*==================================================
WRAPPER
==================================================*/
.wrapper{
    display:flex;
    width:100%;
    min-height:100vh;
}

/*==================================================
SIDEBAR
==================================================*/
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:280px;
    height:100vh;
    background:linear-gradient(180deg,#0d47a1,#1565c0);
    color:#fff;
    overflow-y:auto;
    overflow-x:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
    z-index:1000;
}

/*==================================================
SCROLLBAR
==================================================*/
.sidebar::-webkit-scrollbar{
    width:6px;
}

.sidebar::-webkit-scrollbar-thumb{
    background:rgba(255,255,255,.30);
    border-radius:30px;
}

.sidebar::-webkit-scrollbar-track{
    background:transparent;
}

/*==================================================
SIDEBAR LOGO
==================================================*/
.sidebar-logo{
    padding:30px 20px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.sidebar-logo .logo{
    width:80px;
    height:80px;
    object-fit:contain;
    background:#fff;
    padding:8px;
    border-radius:50%;
    box-shadow:0 5px 15px rgba(0,0,0,.20);
}

.sidebar-logo h3{
    margin-top:15px;
    margin-bottom:5px;
    font-size:24px;
    font-weight:700;
    color:#fff;
}

.sidebar-logo small{
    color:#d6e4ff;
    font-size:13px;
}

/*==================================================
PROFILE
==================================================*/
.sidebar-profile{
    padding:25px 20px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.sidebar-profile .profile-photo{
    width:95px;
    height:95px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #fff;
    box-shadow:0 8px 20px rgba(0,0,0,.20);
}

.sidebar-profile h5{
    margin-top:15px;
    margin-bottom:8px;
    font-size:18px;
    font-weight:600;
    color:#fff;
}

.sidebar-profile p{
    margin-bottom:10px;
    font-size:14px;
    color:#d6e4ff;
    line-height:1.6;
}

.sidebar-profile strong{
    color:#fff;
}

.sidebar-profile .badge{
    padding:8px 18px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

/*==================================================
MAIN CONTENT
==================================================*/
.main-content{
    margin-left:280px;
    width:calc(100% - 280px);
    min-height:100vh;
    background:#f4f7fc;
}

/*==================================================
SIDEBAR MENU
==================================================*/

.sidebar-menu{
    padding:20px 0;
}

.sidebar-menu li{
    margin:4px 0;
}

.sidebar-menu li a{

    display:flex;
    align-items:center;
    gap:15px;

    padding:14px 25px;

    color:#ffffff;

    font-size:15px;
    font-weight:500;

    border-left:4px solid transparent;

    transition:.3s ease;

}

.sidebar-menu li a i{

    width:25px;

    font-size:20px;

    text-align:center;

}

/*==================================================
HOVER
==================================================*/

.sidebar-menu li a:hover{

    background:rgba(255,255,255,.12);

    border-left:4px solid #ffc107;

    padding-left:30px;

    color:#fff;

}

/*==================================================
ACTIVE MENU
==================================================*/

.sidebar-menu li a.active{

    background:rgba(255,255,255,.18);

    border-left:4px solid #ffc107;

    color:#fff;

    font-weight:600;

}

/*==================================================
ACTIVE ICON
==================================================*/

.sidebar-menu li a.active i{

    color:#ffc107;

}

/*==================================================
MENU ICON HOVER
==================================================*/

.sidebar-menu li a:hover i{

    color:#ffc107;

    transform:scale(1.15);

    transition:.3s;

}

/*==================================================
DIVIDER
==================================================*/

.sidebar-menu hr{

    border-color:rgba(255,255,255,.20);

    margin:18px 20px;

}

/*==================================================
LOGOUT BUTTON
==================================================*/

.sidebar-menu .logout{

    background:#d32f2f;

    margin:15px 15px 0;

    border-radius:10px;

    border-left:none !important;

}

.sidebar-menu .logout:hover{

    background:#b71c1c;

    padding-left:25px;

}

.sidebar-menu .logout i{

    color:#fff !important;

}

/*==================================================
SMOOTH SCROLL
==================================================*/

html{

    scroll-behavior:smooth;

}

/*==================================================
MENU ANIMATION
==================================================*/

.sidebar-menu li{

    animation:fadeMenu .5s ease;

}

@keyframes fadeMenu{

    from{

        opacity:0;

        transform:translateX(-20px);

    }

    to{

        opacity:1;

        transform:translateX(0);

    }

}

/*==================================================
TOP NAVBAR
==================================================*/
.top-navbar{
    height:75px;
    background:#ffffff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 30px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    position:sticky;
    top:0;
    z-index:999;
}

/*==================================================
LEFT SIDE
==================================================*/
.nav-left{
    display:flex;
    align-items:center;
    gap:20px;
}

.nav-left h4{
    margin:0;
    font-size:24px;
    font-weight:700;
    color:#0d47a1;
}

.menu-btn{
    width:45px;
    height:45px;
    border:none;
    border-radius:10px;
    background:#0d47a1;
    color:#fff;
    font-size:22px;
    display:flex;
    justify-content:center;
    align-items:center;
    cursor:pointer;
    transition:.3s;
}

.menu-btn:hover{
    background:#1565c0;
    transform:rotate(90deg);
}

/*==================================================
SEARCH BOX
==================================================*/
.nav-search{
    flex:1;
    max-width:450px;
    margin:0 40px;
}

.nav-search .input-group{
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    border-radius:50px;
    overflow:hidden;
}

.nav-search .input-group-text{
    border:none;
}

.nav-search .form-control{
    border:none;
    box-shadow:none;
    height:48px;
    font-size:15px;
}

.nav-search .form-control:focus{
    box-shadow:none;
}

/*==================================================
RIGHT SIDE
==================================================*/
.nav-right{
    display:flex;
    align-items:center;
    gap:20px;
}

/*==================================================
CLOCK
==================================================*/
.clock-box{
    background:#f4f7fc;
    padding:10px 18px;
    border-radius:30px;
    font-size:14px;
    font-weight:600;
    color:#0d47a1;
    display:flex;
    align-items:center;
    gap:8px;
}

/*==================================================
NOTIFICATION ICON
==================================================*/
.nav-icon{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#f4f7fc;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#0d47a1;
    font-size:20px;
    transition:.3s;
}

.nav-icon:hover{
    background:#0d47a1;
    color:#fff;
    transform:translateY(-3px);
}

/*==================================================
PROFILE
==================================================*/
.profile-dropdown{
    display:flex;
    align-items:center;
    gap:10px;
    color:#333;
    font-weight:600;
}

.profile-dropdown:hover{
    color:#0d47a1;
}

.nav-profile-photo{
    .nav-profile-photo{
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #0d47a1;
}
}

/*==================================================
DROPDOWN
==================================================*/
.dropdown-menu{
    border:none;
    border-radius:15px;
    padding:10px 0;
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.dropdown-item{
    padding:10px 20px;
    transition:.3s;
}

.dropdown-item:hover{
    background:#0d47a1;
    color:#fff;
}

.dropdown-item i{
    margin-right:10px;
}

/*==================================================
PAGE CONTENT
==================================================*/
.page-content{
    padding:30px;
}

/*==================================================
FOOTER
==================================================*/
.footer{
    margin-top:40px;
    padding:20px;
    background:#0d47a1;
    color:#fff;
    text-align:center;
    font-size:14px;
}

/*==================================================
BOOTSTRAP CARDS
==================================================*/
.card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
}

/*==================================================
BUTTONS
==================================================*/
.btn{
    border-radius:10px;
    transition:.3s;
}

.btn:hover{
    transform:translateY(-2px);
}

/*==================================================
TABLE
==================================================*/
.table{
    margin-bottom:0;
}

.table thead{
    background:#0d47a1;
    color:#fff;
}

/*==================================================
RESPONSIVE
==================================================*/

@media(max-width:992px){

    .sidebar{
        left:-280px;
        transition:.3s;
    }

    .sidebar.active{
        left:0;
    }

    .main-content{
        margin-left:0;
        width:100%;
    }

    .nav-search{
        display:none;
    }

    .top-navbar{
        padding:15px;
    }

    .nav-left h4{
        font-size:18px;
    }

}

@media(max-width:768px){

    .clock-box{
        display:none;
    }

    .profile-dropdown span{
        display:none;
    }

    .top-navbar{
        height:auto;
        padding:12px;
    }

    .page-content{
        padding:15px;
    }

}

@media(max-width:576px){

    .sidebar{
        width:260px;
    }

    .nav-right{
        gap:10px;
    }

    .menu-btn{
        width:40px;
        height:40px;
    }

    .nav-profile-photo{
        width:40px;
        height:40px;
    }

}

/*=========================================
WELCOME CARD
=========================================*/

.welcome-card{

    border:none;

    border-radius:25px;

    background:linear-gradient(135deg,#0d47a1,#1976d2);

    color:#fff;

    overflow:hidden;

    box-shadow:0 15px 35px rgba(0,0,0,.15);

}

.welcome-card .card-body{

    padding:40px;

}

.welcome-card h2{

    font-size:34px;

    font-weight:700;

}

.welcome-card h3{

    font-weight:700;

}

.welcome-card h4{

    font-weight:600;

}

.welcome-card h6{

    opacity:.9;

    margin-bottom:8px;

}

.welcome-card img{

    filter:drop-shadow(0 10px 25px rgba(0,0,0,.25));

}

.welcome-card .btn{

    border-radius:12px;

    font-weight:600;

}

/*=========================================
STATISTICS CARDS
=========================================*/

.stat-card{

    color:#fff;

    border-radius:20px;

    padding:25px;

    text-align:center;

    transition:.35s;

    box-shadow:0 10px 25px rgba(0,0,0,.12);

    cursor:pointer;

    height:100%;

}

.stat-card:hover{

    transform:translateY(-8px);

}

.stat-card i{

    font-size:40px;

    margin-bottom:15px;

}

.stat-card h2{

    font-size:32px;

    font-weight:700;

    margin-bottom:5px;

}

.stat-card p{

    margin:0;

    font-weight:500;

}

.stat-card.bg-primary{

    background:#1565c0;

}

.stat-card.bg-success{

    background:#198754;

}

.stat-card.bg-warning{

    background:#ff9800;

}

.stat-card.bg-info{

    background:#00acc1;

}

.stat-card.bg-danger{

    background:#dc3545;

}

.stat-card.bg-dark{

    background:#37474f;

}

.stat-card a{

    color:#fff;

}

/*=========================================
LIVE CLOCK
=========================================*/

.clock-box{

    display:flex;

    align-items:center;

    gap:10px;

    padding:10px 18px;

    background:#f5f8fc;

    border-radius:50px;

    font-weight:600;

    color:#0d47a1;

    box-shadow:0 4px 12px rgba(0,0,0,.08);

}

.clock-box i{

    font-size:18px;

}

</style>

</head>

<body>
<!-- =========================================
WRAPPER START
========================================= -->

<div class="wrapper">

<!-- =========================================
SIDEBAR
========================================= -->

    <aside class="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo text-center">

            <img src="../assets/images/logo-circle.png"
                 class="logo"
                 alt="APDCL Logo">

            <h3>APDCL</h3>

            <small>Consumer Portal</small>

        </div>

        <!-- Consumer Profile -->

        <div class="sidebar-profile">

            <i class="bi bi-person-circle profile-icon"></i>

            <h5><?= htmlspecialchars($user['name']); ?></h5>

            <small>Consumer No.</small>

            <strong><?= htmlspecialchars($user['consumer_no']); ?></strong>

            <span class="badge bg-warning text-dark mt-2">
                <?= htmlspecialchars($user['category']); ?>
            </span>

        </div>

        <!-- Navigation -->

        <ul class="sidebar-menu">

            <li>
                <a href="dashboard.php" class="active">
                    <i class="bi bi-house-door-fill"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="profile.php">
                    <i class="bi bi-person-circle"></i>
                    My Profile
                </a>
            </li>

            <li>
                <a href="current_bill.php">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Current Bill
                </a>
            </li>

            <li>
                <a href="bill_history.php">
                    <i class="bi bi-receipt"></i>
                    Bill History
                </a>
            </li>

            <li>
                <a href="pay_bill.php">
                    <i class="bi bi-credit-card-fill"></i>
                    Pay Bill
                </a>
            </li>

            <li>
                <a href="payment_history.php">
                    <i class="bi bi-wallet2"></i>
                    Payment History
                </a>
            </li>

            <li>
                <a href="download_receipt.php">
                    <i class="bi bi-download"></i>
                    Download Receipt
                </a>
            </li>

            <li>
                <a href="report_outage.php">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Report Outage
                </a>
            </li>

            <li>
                <a href="complaint_history.php">
                    <i class="bi bi-clipboard-check-fill"></i>
                    Complaint History
                </a>
            </li>

            <li>
                <a href="track_complaint.php">
                    <i class="bi bi-geo-alt-fill"></i>
                    Track Complaint
                </a>
            </li>

            <li>
                <a href="outage_map.php">
                    <i class="bi bi-map-fill"></i>
                    Live Outage Map
                </a>
            </li>

            <li>
                <a href="notices.php">
                    <i class="bi bi-megaphone-fill"></i>
                    Notices
                </a>
            </li>

            <li>
                <a href="settings.php">
                    <i class="bi bi-gear-fill"></i>
                    Settings
                </a>
            </li>

            <li>
                <a href="logout.php"
                   onclick="return confirm('Logout from APDCL Portal?');">

                    <i class="bi bi-box-arrow-right"></i>

                    Logout

                </a>
            </li>

        </ul>

    </aside>

<!-- =========================================
MAIN CONTENT START
========================================= -->

<div class="main-content">

<!-- =========================================
TOP NAVBAR
========================================= -->

<div class="clock-box">
    <i class="bi bi-calendar3"></i>
    <span id="liveDateTime"></span>
</div>

<nav class="top-navbar">

    <!-- Left Side -->

    <div class="nav-left">

        <button class="menu-btn">

            <i class="bi bi-list"></i>

        </button>

        <h4 class="mb-0">

            <i class="bi bi-speedometer2 text-primary"></i>

            Consumer Dashboard

        </h4>

    </div>

    <!-- Center Search -->

    <div class="nav-search">

        <div class="input-group">

            <span class="input-group-text bg-white">

                <i class="bi bi-search"></i>

            </span>

            <input
                type="text"
                class="form-control"
                placeholder="Search here...">

        </div>

    </div>

    <!-- Right Side -->

    <div class="nav-right">

        <!-- Live Clock -->

        <div class="clock-box">

            <i class="bi bi-clock-fill"></i>

            <span id="liveClock"></span>

        </div>

        <!-- Notification -->

        <a href="notices.php" class="nav-icon">

            <i class="bi bi-bell-fill"></i>

        </a>

        <!-- Profile -->

        <div class="dropdown">

            <a href="#"
               class="profile-dropdown"
               data-bs-toggle="dropdown">

                <span>

                    <?= htmlspecialchars($user['name']); ?>

                </span>

                <i class="bi bi-chevron-down"></i>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow">

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

                        <i class="bi bi-gear-fill"></i>

                        Settings

                    </a>

                </li>

                <li><hr class="dropdown-divider"></li>

                <li>

                    <a class="dropdown-item text-danger"
                       href="logout.php">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>

<!-- =========================================
PAGE CONTENT
========================================= -->

<div class="page-content">

<!-- =========================================
WELCOME CARD
========================================= -->

<div class="card welcome-card mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <!-- LEFT -->

            <div class="col-lg-8">

                <h2 class="fw-bold mb-3">

                    Welcome,

                    <?= htmlspecialchars($user['name']); ?>

                    👋

                </h2>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <h6 class="text-light">

                            Consumer Number

                        </h6>

                        <h4>

                            <?= htmlspecialchars($user['consumer_no']); ?>

                        </h4>

                    </div>

                    <div class="col-md-6 mb-3">

                        <h6 class="text-light">

                            Category

                        </h6>

                        <h4>

                            <?= htmlspecialchars($user['category']); ?>

                        </h4>

                    </div>

                    <div class="col-md-6">

                        <h6 class="text-light">

                            Current Due

                        </h6>

                        <h3>

                            ₹<?= number_format($currentDue,2); ?>

                        </h3>

                    </div>

                    <div class="col-md-6">

                        <h6 class="text-light">

                            Due Date

                        </h6>

                        <h3>

                            <?= $dueDate=="N/A" ? "N/A" : date("d M Y",strtotime($dueDate)); ?>

                        </h3>

                    </div>

                </div>

            </div>

            <!-- RIGHT -->

            <div class="col-lg-4 text-center">

                <img src="../assets/images/logo-circle.png"
                     width="130"
                     class="img-fluid mb-4">

                <div class="d-grid gap-3">

                    <a href="pay_bill.php"
                       class="btn btn-warning btn-lg">

                        <i class="bi bi-credit-card-fill"></i>

                        Pay Now

                    </a>

                    <a href="current_bill.php"
                       class="btn btn-light btn-lg">

                        <i class="bi bi-receipt"></i>

                        View Current Bill

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- =========================================
STATISTICS
========================================= -->

<div class="row g-4 mb-4">

    <div class="col-lg-2 col-md-4 col-6">

        <a href="bill_history.php">

            <div class="stat-card bg-primary">

                <i class="bi bi-receipt-cutoff"></i>

                <h2><?= $totalBills ?></h2>

                <p>Total Bills</p>

            </div>

        </a>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <a href="payment_history.php">

            <div class="stat-card bg-success">

                <i class="bi bi-check-circle-fill"></i>

                <h2><?= $paidBills ?></h2>

                <p>Paid Bills</p>

            </div>

        </a>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <a href="pay_bill.php">

            <div class="stat-card bg-warning">

                <i class="bi bi-clock-fill"></i>

                <h2><?= $pendingBills ?></h2>

                <p>Pending Bills</p>

            </div>

        </a>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <a href="payment_history.php">

            <div class="stat-card bg-info">

                <i class="bi bi-credit-card-fill"></i>

                <h2><?= $totalPayments ?></h2>

                <p>Payments</p>

            </div>

        </a>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <a href="complaint_history.php">

            <div class="stat-card bg-danger">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <h2><?= $totalComplaints ?></h2>

                <p>Complaints</p>

            </div>

        </a>

    </div>

    <div class="col-lg-2 col-md-4 col-6">

        <a href="notices.php">

            <div class="stat-card bg-dark">

                <i class="bi bi-megaphone-fill"></i>

                <h2><?= $totalNotices ?></h2>

                <p>Notices</p>

            </div>

        </a>

    </div>

</div>

<!-- =========================================
CHARTS
========================================= -->

<div class="row mb-4">

    <div class="col-lg-8">

        <div class="card">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-bar-chart-fill"></i>

                    Monthly Electricity Bills

                </h5>

            </div>

            <div class="card-body">

                <canvas id="billChart" height="120"></canvas>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    <i class="bi bi-pie-chart-fill"></i>

                    Complaint Status

                </h5>

            </div>

            <div class="card-body">

                <canvas id="complaintChart"></canvas>

            </div>

        </div>

    </div>

</div>

<div class="row">

    <!-- Pending Bills -->

    <div class="col-lg-6 mb-4">

        <div class="card">

            <div class="card-header bg-danger text-white">

                <h5 class="mb-0">

                    <i class="bi bi-exclamation-circle-fill"></i>

                    Pending Bills

                </h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Bill No</th>

                                <th>Month</th>

                                <th>Due Date</th>

                                <th>Amount</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

<?php while($bill=mysqli_fetch_assoc($pendingBillsList)){ ?>

<tr>

<td><?= htmlspecialchars($bill['bill_no']) ?></td>

<td><?= htmlspecialchars($bill['billing_month']) ?></td>

<td><?= date("d M Y",strtotime($bill['due_date'])) ?></td>

<td>₹<?= number_format($bill['total_bill'],2) ?></td>

<td>

<a href="pay_bill.php?id=<?= $bill['id'] ?>"
   class="btn btn-sm btn-success">

Pay Now

</a>

</td>

</tr>

<?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- Recent Payments -->

    <div class="col-lg-6 mb-4">

        <div class="card">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    <i class="bi bi-credit-card-fill"></i>

                    Recent Payments

                </h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Receipt</th>

                                <th>Date</th>

                                <th>Amount</th>

                                <th>Download</th>

                            </tr>

                        </thead>

                        <tbody>

<?php while($pay=mysqli_fetch_assoc($recentPayments)){ ?>

<tr>

<td><?= htmlspecialchars($pay['receipt_no']) ?></td>

<td><?= date("d M Y",strtotime($pay['payment_date'])) ?></td>

<td>₹<?= number_format($pay['amount'],2) ?></td>

<td>

<a href="download_receipt.php?id=<?= $pay['id'] ?>"
   class="btn btn-primary btn-sm">

<i class="bi bi-download"></i>

</a>

</td>

</tr>

<?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="row">

    <!-- Complaint Activity -->

    <div class="col-lg-6 mb-4">

        <div class="card">

            <div class="card-header bg-danger text-white">

                <h5 class="mb-0">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    Complaint Activity

                </h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                        <tr>

                            <th>Complaint ID</th>

                            <th>Status</th>

                            <th>Track</th>

                        </tr>

                        </thead>

                        <tbody>

<?php while($c=mysqli_fetch_assoc($recentComplaints)){ ?>

<tr>

<td><?= htmlspecialchars($c['complaint_id']) ?></td>

<td>

<?php

$statusColor="secondary";

if($c['status']=="Pending") $statusColor="warning";
if($c['status']=="Assigned") $statusColor="info";
if($c['status']=="In Progress") $statusColor="primary";
if($c['status']=="Resolved") $statusColor="success";
if($c['status']=="Rejected") $statusColor="danger";

?>

<span class="badge bg-<?= $statusColor ?>">

<?= htmlspecialchars($c['status']) ?>

</span>

</td>

<td>

<a href="track_complaint.php?id=<?= $c['id'] ?>"
class="btn btn-primary btn-sm">

Track

</a>

</td>

</tr>

<?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

 <!-- Latest Notices -->

    <div class="col-lg-6 mb-4">

        <div class="card">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-megaphone-fill"></i>

                    Latest Notices

                </h5>

            </div>

            <div class="card-body">

<?php while($notice=mysqli_fetch_assoc($latestNotices)){ ?>

<div class="border-bottom pb-3 mb-3">

<h6 class="fw-bold">

<?= htmlspecialchars($notice['title']) ?>

</h6>

<p class="text-muted mb-2">

<?= nl2br(htmlspecialchars(substr($notice['message'],0,120))) ?>...

</p>

<a href="notices.php"

class="btn btn-outline-primary btn-sm">

Read More

</a>

</div>

<?php } ?>

            </div>

        </div>

    </div>

</div>

<!-- =========================================
LIVE OUTAGE MAP
========================================= -->

<div class="card mb-4">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="bi bi-map-fill"></i>

            Live Outage Map

        </h5>

    </div>

    <div class="card-body p-0">

        <div id="outageMap"
             style="height:400px;width:100%;"></div>

    </div>

</div>

<!-- =========================================
QUICK SERVICES
========================================= -->

<div class="card mb-5">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="bi bi-grid-fill"></i>

            Quick Services

        </h5>

    </div>

    <div class="card-body">

        <div class="row g-4">

            <div class="col-lg-3 col-md-4 col-6">
                <a href="profile.php" class="btn btn-outline-primary w-100 p-4">
                    <i class="bi bi-person-circle display-5"></i><br><br>
                    My Profile
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="current_bill.php" class="btn btn-outline-success w-100 p-4">
                    <i class="bi bi-lightning-charge-fill display-5"></i><br><br>
                    Current Bill
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="pay_bill.php" class="btn btn-outline-warning w-100 p-4">
                    <i class="bi bi-credit-card-fill display-5"></i><br><br>
                    Pay Bill
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="complaint_history.php" class="btn btn-outline-danger w-100 p-4">
                    <i class="bi bi-exclamation-triangle-fill display-5"></i><br><br>
                    Complaints
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="download_receipt.php" class="btn btn-outline-info w-100 p-4">
                    <i class="bi bi-download display-5"></i><br><br>
                    Receipt
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="notices.php" class="btn btn-outline-secondary w-100 p-4">
                    <i class="bi bi-megaphone-fill display-5"></i><br><br>
                    Notices
                </a>
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <a href="settings.php" class="btn btn-outline-dark w-100 p-4">
                    <i class="bi bi-gear-fill display-5"></i><br><br>
                    Settings
                </a>
            </div>

            
            <div class="col-lg-3 col-md-4 col-6">
                <a href="logout.php"
                   onclick="return confirm('Logout?');"
                   class="btn btn-outline-danger w-100 p-4">
                    <i class="bi bi-box-arrow-right display-5"></i><br><br>
                    Logout
                </a>
            </div>

        </div>

    </div>

</div>

<script>

const billLabels = [
<?php
foreach($monthlyBills as $m){
    echo "'".$m['month']."',";
}
?>
];

const billData = [
<?php
foreach($monthlyBills as $m){
    echo $m['amount'].",";
}
?>
];

// Bill Chart

new Chart(document.getElementById("billChart"),{

    type:'bar',

    data:{

        labels:billLabels,

        datasets:[{

            label:'Bill Amount',

            data:billData,

            borderRadius:8

        }]

    },

    options:{

        responsive:true,

        plugins:{
            legend:{
                display:false
            }
        }

    }

});

// Complaint Pie

new Chart(document.getElementById("complaintChart"),{

    type:'pie',

    data:{

        labels:[
            'Pending',
            'Resolved',
            'In Progress'
        ],

        datasets:[{

            data:[
                <?= $pendingComplaint ?>,
                <?= $resolvedComplaint ?>,
                <?= $progressComplaint ?>
            ]

        }]

    }

});

</script>

<script>

var map = L.map('outageMap').setView([26.1445,91.7362],7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
}).addTo(map);

// Random APDCL Demo Locations

const locations = [

    {
        lat:26.1445,
        lng:91.7362,
        name:"Guwahati Outage"
    },

    {
        lat:26.7271,
        lng:94.2160,
        name:"Jorhat Outage"
    },

    {
        lat:24.8333,
        lng:92.7789,
        name:"Silchar Outage"
    },

    {
        lat:26.6528,
        lng:92.7926,
        name:"Tezpur Outage"
    },

    {
        lat:26.7465,
        lng:94.2026,
        name:"Golaghat Outage"
    },

    {
        lat:27.4922,
        lng:95.3554,
        name:"Dibrugarh Outage"
    }

];

const random = locations[Math.floor(Math.random() * locations.length)];

map.setView([random.lat, random.lng], 12);

L.marker([random.lat, random.lng])
.addTo(map)
.bindPopup("<b>"+random.name+"</b><br>Power Outage Reported.")
.openPopup();

</script>

<script>

function updateClock(){

    const now = new Date();

    const options = {
        weekday:'long',
        year:'numeric',
        month:'long',
        day:'numeric',
        hour:'2-digit',
        minute:'2-digit',
        second:'2-digit'
    };

    document.getElementById("liveDateTime").innerHTML =
        now.toLocaleString('en-IN', options);

}

updateClock();

setInterval(updateClock,1000);

</script>

</html>

</body>