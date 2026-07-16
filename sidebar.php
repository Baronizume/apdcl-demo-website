<?php

$admin_name = $_SESSION['name'] ?? "Administrator";

$zone = $_SESSION['zone'] ?? "Not Assigned";

$circle = $_SESSION['circle'] ?? "Not Assigned";

$sub_division = $_SESSION['sub_division'] ?? "Not Assigned";

?>


<style>

.sidebar{

position:fixed;

top:0;

left:0;

width:260px;

height:100vh;

background:#0d47a1;

color:white;

overflow-y:auto;

z-index:1000;

}


.sidebar-header{

padding:20px;

text-align:center;

background:#08306b;

}


.sidebar-logo{

width:70px;

height:70px;

border-radius:50%;

background:white;

padding:5px;

}



.sidebar a{

display:flex;

align-items:center;

gap:12px;

padding:14px 20px;

color:white;

text-decoration:none;

font-size:15px;

transition:.3s;

}



.sidebar a:hover{

background:#1565c0;

padding-left:30px;

}



.sidebar i{

font-size:20px;

}



.location-box{

background:rgba(255,255,255,.15);

margin:15px;

padding:15px;

border-radius:12px;

}



.location-box h6{

font-size:12px;

opacity:.8;

margin-bottom:3px;

}



.location-box p{

margin-bottom:12px;

font-weight:600;

}



.logout{

background:#dc3545;

margin:15px;

border-radius:10px;

}


.logout:hover{

background:#bb2d3b!important;

}



</style>



<div class="sidebar">


<div class="sidebar-header">


<img src="../assets/images/logo-circle.png"

class="sidebar-logo">


<h5 class="mt-2">

⚡ APDCL

</h5>


<small>

Admin Panel

</small>


</div>




<div class="location-box">


<h6>

<i class="bi bi-building"></i>

ZONE

</h6>

<p>

<?=htmlspecialchars($zone)?>

</p>



<h6>

<i class="bi bi-diagram-3"></i>

CIRCLE

</h6>

<p>

<?=htmlspecialchars($circle)?>

</p>



<h6>

<i class="bi bi-geo-alt"></i>

SUB DIVISION

</h6>

<p>

<?=htmlspecialchars($sub_division)?>

</p>


</div>





<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>




<a href="manage_consumers.php">

<i class="bi bi-people-fill"></i>

Consumers

</a>




<a href="add_consumer.php">

<i class="bi bi-person-plus-fill"></i>

Add Consumer

</a>




<a href="generate_bill.php">

<i class="bi bi-receipt"></i>

Generate Bill

</a>




<a href="manage_bills.php">

<i class="bi bi-file-text"></i>

Bills

</a>




<a href="meter_readings.php">

<i class="bi bi-speedometer"></i>

Meter Reading

</a>




<a href="manage_payments.php">

<i class="bi bi-credit-card"></i>

Payments

</a>




<a href="manage_complaint.php">

<i class="bi bi-exclamation-triangle-fill"></i>

Complaints

</a>




<a href="manage_outages.php">

<i class="bi bi-lightning-fill"></i>

Outages

</a>




<a href="manage_notices.php">

<i class="bi bi-megaphone-fill"></i>

Notices

</a>




<a href="reports.php">

<i class="bi bi-bar-chart-fill"></i>

Reports

</a>




<a href="settings.php">

<i class="bi bi-gear-fill"></i>

Settings

</a>




<a href="logout.php"

class="logout">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>



</div>