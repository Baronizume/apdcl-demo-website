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

/*=========================================
    MESSAGE
=========================================*/

$message = "";

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

$page = 1;

if(isset($_GET['page']))
{
    $page = (int)$_GET['page'];

    if($page < 1)
    {
        $page = 1;
    }
}

$start = ($page - 1) * $limit;

/*=========================================
    TOTAL RECORDS
=========================================*/

$where = "";

if($search != "")
{
    $search = mysqli_real_escape_string($conn,$search);

    $where = "
    WHERE
        name LIKE '%$search%'
        OR username LIKE '%$search%'
        OR zone LIKE '%$search%'
        OR circle LIKE '%$search%'
        OR sub_division LIKE '%$search%'
    ";
}

$countQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM admin
    $where
    "
);

$countRow = mysqli_fetch_assoc($countQuery);

$totalRecords = $countRow['total'];

$totalPages = ceil($totalRecords / $limit);

/*=========================================
    FETCH ADMINS
=========================================*/

$admins = mysqli_query(
    $conn,
    "
    SELECT *
    FROM admin
    $where
    ORDER BY id DESC
    LIMIT $start,$limit
    "
);

/*=========================================
    DEFAULT VALUES
=========================================*/

$id = 0;

$name = "";

$username = "";

$password = "";

$role = "Admin";

$zone = "";

$circle = "";

$sub_division = "";

$status = "Active";

$edit = false;

/*=========================================
    ADD ADMIN
=========================================*/

if(isset($_POST['save']))
{
    $name          = trim($_POST['name']);
    $username      = trim($_POST['username']);
    $password      = trim($_POST['password']);
    $role          = trim($_POST['role']);
    $zone          = trim($_POST['zone']);
    $circle        = trim($_POST['circle']);
    $sub_division  = trim($_POST['sub_division']);
    $status        = trim($_POST['status']);

    if(
        empty($name) ||
        empty($username) ||
        empty($password)
    )
    {
        $message = '
        <div class="alert alert-danger">
            Please fill all required fields.
        </div>';
    }
    else
    {
        $username = mysqli_real_escape_string($conn,$username);

        $check = mysqli_query(
            $conn,
            "SELECT id FROM admin
             WHERE username='$username'
             LIMIT 1"
        );

        if(mysqli_num_rows($check)>0)
        {
            $message = '
            <div class="alert alert-danger">
                Username already exists.
            </div>';
        }
        else
        {
            $hashPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            mysqli_query(
                $conn,
                "INSERT INTO admin
                (
                    name,
                    username,
                    password,
                    role,
                    zone,
                    circle,
                    sub_division,
                    status
                )
                VALUES
                (
                    '$name',
                    '$username',
                    '$hashPassword',
                    '$role',
                    '$zone',
                    '$circle',
                    '$sub_division',
                    '$status'
                )"
            );

            $message = '
            <div class="alert alert-success">
                Admin added successfully.
            </div>';
        }
    }
}

/*=========================================
    DELETE ADMIN
=========================================*/

if(isset($_GET['delete']))
{
    $deleteId = (int)$_GET['delete'];

    mysqli_query(
        $conn,
        "DELETE FROM admin
         WHERE id='$deleteId'"
    );

    header("Location: manage_admins.php");

    exit();
}

/*=========================================
    EDIT ADMIN
=========================================*/

if(isset($_GET['edit']))
{
    $editId = (int)$_GET['edit'];

    $result = mysqli_query(
        $conn,
        "SELECT *
         FROM admin
         WHERE id='$editId'
         LIMIT 1"
    );

    if(mysqli_num_rows($result)>0)
    {
        $edit = true;

        $row = mysqli_fetch_assoc($result);

        $id             = $row['id'];
        $name           = $row['name'];
        $username       = $row['username'];

        $password       = "";

        $role           = $row['role'];

        $zone           = $row['zone'];

        $circle         = $row['circle'];

        $sub_division   = $row['sub_division'];

        $status         = $row['status'];
    }
}

/*=========================================
    UPDATE ADMIN
=========================================*/

if(isset($_POST['update']))
{
    $id             = (int)$_POST['id'];

    $name           = trim($_POST['name']);

    $username       = trim($_POST['username']);

    $password       = trim($_POST['password']);

    $role           = trim($_POST['role']);

    $zone           = trim($_POST['zone']);

    $circle         = trim($_POST['circle']);

    $sub_division   = trim($_POST['sub_division']);

    $status         = trim($_POST['status']);

    if(empty($password))
    {
        mysqli_query(
            $conn,
            "UPDATE admin SET

            name='$name',

            username='$username',

            role='$role',

            zone='$zone',

            circle='$circle',

            sub_division='$sub_division',

            status='$status'

            WHERE id='$id'"
        );
    }
    else
    {
        $hashPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        mysqli_query(
            $conn,
            "UPDATE admin SET

            name='$name',

            username='$username',

            password='$hashPassword',

            role='$role',

            zone='$zone',

            circle='$circle',

            sub_division='$sub_division',

            status='$status'

            WHERE id='$id'"
        );
    }

    header("Location: manage_admins.php");

    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Manage Admins |
APDCL Super Admin Portal

</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

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

/*==========================
NAVBAR
==========================*/

.navbar{

height:75px;

background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

box-shadow:0 10px 25px rgba(0,0,0,.18);

position:fixed;

top:0;

left:0;

right:0;

z-index:999;

}

.logo{

width:58px;

height:58px;

background:#fff;

border-radius:50%;

padding:4px;

margin-right:15px;

}

.brand-title{

color:#fff;

font-size:24px;

font-weight:700;

margin:0;

}

.brand-sub{

color:#dbeafe;

font-size:13px;

}

/*==========================
SIDEBAR
==========================*/

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

text-decoration:none;

color:#fff;

font-size:15px;

transition:.3s;

border-left:4px solid transparent;

}

.sidebar a i{

font-size:20px;

margin-right:12px;

}

.sidebar a:hover{

background:#1976d2;

padding-left:30px;

border-left:4px solid #ffc107;

}

.sidebar a.active{

background:#1565c0;

border-left:4px solid #ffc107;

}

/*==========================
CONTENT
==========================*/

.main-content{

margin-left:260px;

margin-top:95px;

padding:30px;

}

/*==========================
CARD
==========================*/

.card{

border:none;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.card-header{

border-radius:18px 18px 0 0!important;

}

</style>

</head>

<body>

<!--==========================
NAVBAR
==========================-->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
class="logo">

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

<!--==========================
SIDEBAR
==========================-->

<div class="sidebar">

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

<a href="manage_subdivisions.php">

<i class="bi bi-building"></i>

Manage Sub-Divisions

</a>

<a href="manage_admins.php"
class="active">

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

<!--==========================
CONTENT
==========================-->

<div class="main-content">

<div class="card mb-4">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

<i class="bi bi-person-badge-fill"></i>

Manage Admins

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

Full Name

</label>

<input
type="text"
name="name"
class="form-control"
required
value="<?= htmlspecialchars($name) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Username

</label>

<input
type="text"
name="username"
class="form-control"
required
value="<?= htmlspecialchars($username) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Password

</label>

<input
type="password"
name="password"
class="form-control"
placeholder="<?= $edit ? 'Leave blank to keep current password' : 'Enter password' ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Role

</label>

<select
name="role"
class="form-select">

<option value="Admin"
<?= ($role=="Admin")?"selected":""; ?>>

Admin

</option>

<option value="Super Admin"
<?= ($role=="Super Admin")?"selected":""; ?>>

Super Admin

</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Zone

</label>

<input
type="text"
name="zone"
class="form-control"
value="<?= htmlspecialchars($zone) ?>">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Circle

</label>

<input
type="text"
name="circle"
class="form-control"
value="<?= htmlspecialchars($circle) ?>">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Sub-Division

</label>

<input
type="text"
name="sub_division"
class="form-control"
value="<?= htmlspecialchars($sub_division) ?>">

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

<div class="mt-3">

<?php if($edit){ ?>

<button
type="submit"
name="update"
class="btn btn-success">

<i class="bi bi-pencil-square"></i>

Update Admin

</button>

<a
href="manage_admins.php"
class="btn btn-secondary">

Cancel

</a>

<?php } else { ?>

<button
type="submit"
name="save"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Add Admin

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
    ADMIN LIST
==========================================-->

<div class="card">

    <div class="card-header bg-dark text-white">

        <div class="row align-items-center">

            <div class="col-md-6">

                <h4 class="mb-0">

                    <i class="bi bi-list-ul"></i>

                    Admin List

                </h4>

            </div>

            <div class="col-md-6">

                <form method="GET">

                    <div class="input-group">

                        <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search Admin..."

                        value="<?= htmlspecialchars($search) ?>">

                        <button
                        class="btn btn-primary">

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

                        <th>ID</th>

                        <th>Name</th>

                        <th>Username</th>

                        <th>Role</th>

                        <th>Zone</th>

                        <th>Circle</th>

                        <th>Sub-Division</th>

                        <th>Status</th>

                        <th width="150">

                            Action

                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($admins)>0)
                {

                    while($row=mysqli_fetch_assoc($admins))
                    {

                ?>

                    <tr>

                        <td>

                            <?= $row['id'] ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['name']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['username']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['role']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['zone']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['circle']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['sub_division']) ?>

                        </td>

                        <td>

                            <?php

                            $status = $row['status'] ?? 'Inactive';

                            if ($status == "Active") {

                                echo '<span class="badge bg-success">Active</span>';

                            } else {

                                echo '<span class="badge bg-danger">Inactive</span>';

                            }

                            ?>

                        </td>
                        <td>

                            <a

                            href="?edit=<?= $row['id'] ?>"

                            class="btn btn-sm btn-warning">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                            <a

                            href="?delete=<?= $row['id'] ?>"

                            class="btn btn-sm btn-danger"

                            onclick="return confirm('Delete this admin?')">

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

                        <td colspan="9" class="text-center">

                            No Admin Found

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

let time = now.toLocaleTimeString();

document.getElementById("liveClock").innerHTML =

date + " | " + time;

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
    CARD ANIMATION
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
    AUTO REFRESH (Optional)
=========================================*/

/*
setTimeout(function(){

location.reload();

},300000);

*/

</script>

</body>

</html>s