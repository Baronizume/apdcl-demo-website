<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/* Logged in Admin */
$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($adminQuery);

$search="";

if(isset($_GET['search']) && $_GET['search']!="")
{
    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $query="
    SELECT
        MAX(id) AS id,
        consumer_no,
        consumer_name,
        father_name,
        mobile,
        address,
        meter_no,
        category,
        status
    FROM bills

    WHERE consumer_no LIKE '%$search%'
       OR consumer_name LIKE '%$search%'
       OR mobile LIKE '%$search%'
       OR meter_no LIKE '%$search%'

    GROUP BY consumer_no

    ORDER BY id DESC
    ";
}
else
{
    $query="
    SELECT
        MAX(id) AS id,
        consumer_no,
        consumer_name,
        father_name,
        mobile,
        address,
        meter_no,
        category,
        status
    FROM bills

    GROUP BY consumer_no

    ORDER BY id DESC
    ";
}

$result=mysqli_query($conn,$query);

/* Statistics */

$totalConsumers=mysqli_num_rows(mysqli_query($conn,"
SELECT DISTINCT consumer_no
FROM bills
"));

$totalSearch=mysqli_num_rows($result);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Manage Consumers | APDCL</title>

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

box-shadow:0 4px 15px rgba(0,0,0,.2);

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

line-height:1.2;

}

.nav-title small{

display:block;

font-size:14px;

font-weight:400;

}

.profile-btn{

color:white;

text-decoration:none;

font-weight:600;

}

.profile-avatar{

width:45px;

height:45px;

background:#fff;

color:#0d47a1;

border-radius:50%;

display:flex;

align-items:center;

justify-content:center;

font-size:22px;

margin-right:10px;

}

.main-content{

padding:30px;

}

/* Cards */

.card{

border:none;

border-radius:18px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.stats-card{

color:white;

padding:25px;

border-radius:18px;

}

.stats-card i{

font-size:42px;

opacity:.9;

}

.stats-card h2{

font-weight:bold;

margin-top:15px;

}

.bg-blue{

background:linear-gradient(135deg,#1565c0,#42a5f5);

}

.bg-green{

background:linear-gradient(135deg,#2e7d32,#66bb6a);

}

.bg-orange{

background:linear-gradient(135deg,#ef6c00,#ffa726);

}

.table thead{

background:#0d47a1;

color:white;

}

.btn{

border-radius:10px;

}

.page-title{

font-size:30px;

font-weight:bold;

color:#0d47a1;

}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="../assets/images/logo-circle.png">

<div class="nav-title">

APDCL

<small>Manage Consumers</small>

</div>

</a>

<div class="dropdown">

<a
class="d-flex align-items-center profile-btn dropdown-toggle"
href="#"
data-bs-toggle="dropdown">

<div class="profile-avatar">

<i class="bi bi-person-fill"></i>

</div>

<div>

<b><?= htmlspecialchars($admin['name']); ?></b>

<br>

<small><?= htmlspecialchars($admin['username']); ?></small>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end shadow">

<li>

<a class="dropdown-item"
href="profile.php">

<i class="bi bi-person-circle"></i>

My Profile

</a>

</li>

<li><hr class="dropdown-divider"></li>

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

</nav>

<div class="container-fluid main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="page-title">

<i class="bi bi-people-fill"></i>

Manage Consumers

</h2>

<p class="text-muted">

Manage all registered electricity consumers

</p>

</div>

<div>

<a href="dashboard.php"
class="btn btn-outline-primary btn-lg me-2">

<i class="bi bi-arrow-left-circle-fill"></i>

Back to Dashboard

</a>

<a href="add_consumer.php"
class="btn btn-success btn-lg">

<i class="bi bi-person-plus-fill"></i>

Add Consumer

</a>

</div>

</div>

<!-- Statistics -->

<div class="row mb-4">

<div class="col-md-4">

<div class="stats-card bg-blue">

<i class="bi bi-people-fill"></i>

<h2><?= $totalConsumers; ?></h2>

<p class="mb-0">

Total Consumers

</p>

</div>

</div>

<div class="col-md-4">

<div class="stats-card bg-green">

<i class="bi bi-person-check-fill"></i>

<h2><?= $totalConsumers; ?></h2>

<p class="mb-0">

Active Consumers

</p>

</div>

</div>

<div class="col-md-4">

<div class="stats-card bg-orange">

<i class="bi bi-search"></i>

<h2><?= $totalSearch; ?></h2>

<p class="mb-0">

Search Results

</p>

</div>

</div>

</div>

<!-- ================= SEARCH CARD ================= -->

<div class="card mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <h5 class="mb-1">
                    <i class="bi bi-search text-primary"></i>
                    Search Consumers
                </h5>

                <small class="text-muted">
                    Search using Consumer Number, Name, Email or Mobile Number
                </small>

            </div>

        </div>

        <hr>

        <form method="GET">

            <div class="row g-3">

                <div class="col-md-10">

                    <div class="input-group">

                        <span class="input-group-text bg-primary text-white">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                        type="text"
                        name="search"
                        class="form-control form-control-lg"
                        placeholder="Search Consumer Number, Name, Email or Mobile..."
                        value="<?= htmlspecialchars($search); ?>">

                    </div>

                </div>

                <div class="col-md-2 d-grid">

                    <button class="btn btn-primary btn-lg">

                        <i class="bi bi-search"></i>

                        Search

                    </button>

                </div>

            </div>

            <?php if($search!=""){ ?>

            <div class="mt-3">

                <a href="manage_consumer.php"
                   class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-clockwise"></i>

                    Reset Search

                </a>

            </div>

            <?php } ?>

        </form>

    </div>

</div>

<!-- ================= CONSUMER TABLE ================= -->

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="bi bi-people-fill text-primary"></i>

                Consumer List

            </h4>

            <span class="badge bg-primary fs-6">

                <?= mysqli_num_rows($result); ?> Consumers

            </span>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                <tr>

                    <th width="6%">ID</th>

                    <th width="8%">Profile</th>

                    <th>Consumer Number</th>

                    <th>Name</th>
                    <th>Address</th>
                    <th>Mobile</th>

                    <th>Status</th>

                    <th width="220">Actions</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($result)>0){

                    while($row=mysqli_fetch_assoc($result)){

                ?>

                <tr>

                    <td>

                        <strong>#<?= $row['id']; ?></strong>

                    </td>

                    <td>

                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"

                             style="width:45px;height:45px;font-size:20px;">

                            <i class="bi bi-person-fill"></i>

                        </div>

                    </td>

                    <td>

                        <span class="fw-bold text-primary">

                            <?= htmlspecialchars($row['consumer_no']); ?>

                        </span>

                    </td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($row['consumer_name']); ?>

                        </strong>

                    </td>

                    <td>

                       <?= htmlspecialchars($row['address']); ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['mobile']); ?>

                    </td>

                    <td>

                        <span class="badge bg-success">

                            Active

                        </span>

                    </td>

                    <td>

                        <a href="view_bill.php?id=<?= $row['id']; ?>"

                           class="btn btn-info btn-sm">

                            <i class="bi bi-eye-fill"></i>

                            View

                        </a>

                        <a href="generate_bill.php?edit=<?= $row['id']; ?>"

                           class="btn btn-warning btn-sm">

                            <i class="bi bi-pencil-fill"></i>

                            Edit

                        </a>

                        <a href="delete_bill.php?id=<?= $row['id']; ?>"

                           class="btn btn-danger btn-sm"

                           onclick="return confirm('Are you sure you want to delete this consumer?');">

                            <i class="bi bi-trash-fill"></i>

                            Delete

                        </a>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="8" class="text-center py-5">

                        <i class="bi bi-people fs-1 text-secondary"></i>

                        <h4 class="mt-3">

                            No Consumers Found

                        </h4>

                        <p class="text-muted">

                            No consumer records match your search.

                        </p>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>