<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL Electricity Billing System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0d6efd;
}

.hero{
    padding:70px 0;
}

.hero img{
    width:140px;
}

.hero h1{
    font-size:45px;
    font-weight:bold;
    color:#0d6efd;
}

.hero p{
    font-size:18px;
    color:#555;
}

.section-title{
    color:#0d6efd;
    font-weight:bold;
    margin-bottom:20px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
}

footer{
    background:#0d6efd;
    color:#fff;
    padding:20px;
    margin-top:40px;
}

</style>

</head>

<body>

<!-- Navbar -->

<nav class="navbar navbar-expand-lg navbar-dark">

<div class="container">

<a class="navbar-brand fw-bold" href="#">

⚡ APDCL

</a>

</div>

</nav>

<!-- Hero Section -->

<div class="container hero">

<div class="row align-items-center">

<div class="col-md-6 text-center">

<img src="assets/images/apdcl_logo.png" alt="APDCL Logo">

</div>

<div class="col-md-6">

<h1>APDCL Electricity Billing System</h1>

<p>

Manage electricity billing, payments, complaints and notices through one secure online platform.

</p>

<div class="mt-4">

<a href="admin/login.php" class="btn btn-primary btn-lg me-3">

<i class="bi bi-person-lock"></i>

Admin Login

</a>

<a href="consumer/login.php" class="btn btn-success btn-lg">

<i class="bi bi-person-circle"></i>

Consumer Login

</a>

</div>

</div>

</div>

</div>

<!-- About -->

<div class="container mt-5">

<h2 class="section-title">

About APDCL

</h2>

<div class="card">

<div class="card-body">

<p>

Assam Power Distribution Company Limited (APDCL) is responsible for distributing electricity across Assam. This Electricity Billing System enables administrators to manage consumers, generate bills, receive payments, publish notices, and resolve complaints efficiently.

</p>

</div>

</div>

</div>

<!-- Features -->

<div class="container mt-5">

<h2 class="section-title">

System Features

</h2>

<div class="row">

<div class="col-md-4 mb-4">

<div class="card text-center">

<div class="card-body">

<i class="bi bi-lightning-charge-fill text-warning" style="font-size:50px;"></i>

<h4 class="mt-3">

Generate Bills

</h4>

<p>

Generate monthly electricity bills automatically.

</p>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card text-center">

<div class="card-body">

<i class="bi bi-credit-card-fill text-success" style="font-size:50px;"></i>

<h4 class="mt-3">

Online Payment

</h4>

<p>

Consumers can pay electricity bills securely.

</p>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card text-center">

<div class="card-body">

<i class="bi bi-chat-left-text-fill text-primary" style="font-size:50px;"></i>

<h4 class="mt-3">

Complaint System

</h4>

<p>

Register and track electricity-related complaints.

</p>

</div>

</div>

</div>

</div>

</div>

<!-- Contact -->

<div class="container mt-5">

<h2 class="section-title">

Contact Information

</h2>

<div class="card">

<div class="card-body">

<p>

<strong>Office:</strong>

Assam Power Distribution Company Limited

</p>

<p>

<strong>Email:</strong>

support@apdcl.org

</p>

<p>

<strong>Phone:</strong>

1912 (Toll Free)

</p>

<p>

<strong>Website:</strong>

www.apdcl.org

</p>

</div>

</div>

</div>

<!-- Footer -->

<footer>

<div class="container text-center">

<h5>

⚡ APDCL Electricity Billing System

</h5>

<p>

© <?php echo date("Y"); ?> APDCL. All Rights Reserved.

</p>

<p>

Developed for Major Project

</p>

</div>

</footer>

</body>

</html>