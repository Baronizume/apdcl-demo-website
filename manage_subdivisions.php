<?php
session_start();

include("../db.php");

/*====================================================
    SUPER ADMIN LOGIN CHECK
====================================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['admin'];

/*====================================================
    FETCH LOGGED IN ADMIN
====================================================*/

$stmt = mysqli_prepare($conn,"
    SELECT *
    FROM admin
    WHERE username=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    session_destroy();

    header("Location: login.php");

    exit();

}

$admin=mysqli_fetch_assoc($result);

/*====================================================
    ALLOW ONLY SUPER ADMIN
====================================================*/

if($admin['role']!="Super Admin"){

    die("
    <h2 style='color:red;text-align:center;margin-top:100px;'>
        Access Denied
    </h2>
    ");

}

$message="";

/*====================================================
    SEARCH
====================================================*/

$search="";

if(isset($_GET['search'])){

    $search=trim($_GET['search']);

}

/*====================================================
    PAGINATION
====================================================*/

$limit=10;

$page=1;

if(isset($_GET['page'])){

    $page=(int)$_GET['page'];

    if($page<1){

        $page=1;

    }

}

$start=($page-1)*$limit;
/*====================================================
    ADD SUB-DIVISION
====================================================*/

if(isset($_POST['save'])){

    $circle_id = (int)$_POST['circle_id'];

    $sub_division_name = trim($_POST['sub_division_name']);

    $office_code = trim($_POST['office_code']);

    $status = trim($_POST['status']);

    if(
        empty($circle_id) ||
        empty($sub_division_name)
    ){

        $message='
        <div class="alert alert-danger">
            Please fill all required fields.
        </div>';

    }else{

        /* Duplicate Check */

        $check=mysqli_prepare($conn,"
            SELECT id
            FROM sub_divisions
            WHERE circle_id=?
            AND sub_division_name=?
            LIMIT 1
        ");

        mysqli_stmt_bind_param(
            $check,
            "is",
            $circle_id,
            $sub_division_name
        );

        mysqli_stmt_execute($check);

        $result=mysqli_stmt_get_result($check);

        if(mysqli_num_rows($result)>0){

            $message='
            <div class="alert alert-danger">
                Sub-Division already exists.
            </div>';

        }else{

            $insert=mysqli_prepare($conn,"
                INSERT INTO sub_divisions
                (
                    circle_id,
                    sub_division_name,
                    office_code,
                    status
                )
                VALUES
                (
                    ?,?,?,?
                )
            ");

            mysqli_stmt_bind_param(
                $insert,
                "isss",
                $circle_id,
                $sub_division_name,
                $office_code,
                $status
            );

            if(mysqli_stmt_execute($insert)){

                $message='
                <div class="alert alert-success">
                    Sub-Division Added Successfully.
                </div>';

            }else{

                $message='
                <div class="alert alert-danger">
                    Unable to save record.
                </div>';

            }

        }

    }

}

/*====================================================
    DELETE SUB-DIVISION
====================================================*/

if(isset($_GET['delete'])){

    $id=(int)$_GET['delete'];

    $delete=mysqli_prepare($conn,"
        DELETE
        FROM sub_divisions
        WHERE id=?
    ");

    mysqli_stmt_bind_param(
        $delete,
        "i",
        $id
    );

    if(mysqli_stmt_execute($delete)){

        header("Location: manage_subdivisions.php");

        exit();

    }

}

/*====================================================
    FETCH RECORD FOR EDIT
====================================================*/

$edit_mode=false;

$edit_id=0;

$circle_id="";

$sub_division_name="";

$office_code="";

$status="Active";

if(isset($_GET['edit'])){

    $edit_mode=true;

    $edit_id=(int)$_GET['edit'];

    $stmt=mysqli_prepare($conn,"
        SELECT *
        FROM sub_divisions
        WHERE id=?
        LIMIT 1
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $edit_id
    );

    mysqli_stmt_execute($stmt);

    $result=mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)>0){

        $row=mysqli_fetch_assoc($result);

        $circle_id=$row['circle_id'];

        $sub_division_name=$row['sub_division_name'];

        $office_code=$row['office_code'];

        $status=$row['status'];

    }

}

/*====================================================
    UPDATE SUB-DIVISION
====================================================*/

if(isset($_POST['update'])){

    $id=(int)$_POST['id'];

    $circle_id=(int)$_POST['circle_id'];

    $sub_division_name=trim($_POST['sub_division_name']);

    $office_code=trim($_POST['office_code']);

    $status=trim($_POST['status']);

    $update=mysqli_prepare($conn,"
        UPDATE sub_divisions
        SET
            circle_id=?,
            sub_division_name=?,
            office_code=?,
            status=?
        WHERE id=?
    ");

    mysqli_stmt_bind_param(

        $update,

        "isssi",

        $circle_id,

        $sub_division_name,

        $office_code,

        $status,

        $id

    );

    if(mysqli_stmt_execute($update)){

        header("Location: manage_subdivisions.php");

        exit();

    }

}

/*====================================================
    LOAD CIRCLES FOR DROPDOWN
====================================================*/

$circle_list = mysqli_query($conn,"
    SELECT
        circles.id,
        circles.circle_name,
        zones.zone_name
    FROM circles
    INNER JOIN zones
        ON circles.zone_id = zones.id
    ORDER BY
        zones.zone_name ASC,
        circles.circle_name ASC
");

/*====================================================
    TOTAL RECORDS
====================================================*/

if($search==""){

    $countQuery=mysqli_query($conn,"
        SELECT COUNT(*) AS total
        FROM sub_divisions
    ");

}else{

    $searchText=mysqli_real_escape_string($conn,$search);

    $countQuery=mysqli_query($conn,"
        SELECT COUNT(*) AS total
        FROM sub_divisions sd
        INNER JOIN circles c
            ON sd.circle_id=c.id
        INNER JOIN zones z
            ON c.zone_id=z.id
        WHERE
            sd.sub_division_name LIKE '%$searchText%'
            OR
            c.circle_name LIKE '%$searchText%'
            OR
            z.zone_name LIKE '%$searchText%'
            OR
            sd.office_code LIKE '%$searchText%'
    ");

}

$totalRow=mysqli_fetch_assoc($countQuery);

$totalRecords=$totalRow['total'];

$totalPages=ceil($totalRecords/$limit);

/*====================================================
    FETCH SUBDIVISIONS
====================================================*/

if($search==""){

    $records=mysqli_query($conn,"
        SELECT

            sd.*,

            c.circle_name,

            z.zone_name

        FROM sub_divisions sd

        INNER JOIN circles c
            ON sd.circle_id=c.id

        INNER JOIN zones z
            ON c.zone_id=z.id

        ORDER BY
            z.zone_name,
            c.circle_name,
            sd.sub_division_name

        LIMIT $start,$limit
    ");

}else{

    $searchText=mysqli_real_escape_string($conn,$search);

    $records=mysqli_query($conn,"
        SELECT

            sd.*,

            c.circle_name,

            z.zone_name

        FROM sub_divisions sd

        INNER JOIN circles c
            ON sd.circle_id=c.id

        INNER JOIN zones z
            ON c.zone_id=z.id

        WHERE

            sd.sub_division_name LIKE '%$searchText%'

            OR

            c.circle_name LIKE '%$searchText%'

            OR

            z.zone_name LIKE '%$searchText%'

            OR

            sd.office_code LIKE '%$searchText%'

        ORDER BY

            z.zone_name,

            c.circle_name,

            sd.sub_division_name

        LIMIT $start,$limit
    ");

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Manage Sub-Divisions</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{

    background:#f4f6f9;

}

.card{

    border:none;

    border-radius:12px;

}

.table{

    vertical-align:middle;

}

.page-title{

    font-size:28px;

    font-weight:700;

}

.stat-card{

    color:#fff;

}

.bg1{

    background:#0d6efd;

}

.bg2{

    background:#198754;

}

.bg3{

    background:#fd7e14;

}

.bg4{

    background:#dc3545;

}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="row mb-3">

    <div class="col-md-6">

        <h2 class="page-title">

            <i class="fa-solid fa-building"></i>

            Manage Sub-Divisions

        </h2>

    </div>

    <div class="col-md-6 text-end">

        <a
        href="dashboard.php"
        class="btn btn-secondary">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>

</div>

<hr>

<?php echo $message; ?>

<div class="row mb-4">

<div class="col-md-3">

<div class="card stat-card bg1">

<div class="card-body">

<h5>Total Sub-Divisions</h5>

<h2>

<?php echo $totalRecords; ?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card bg2">

<div class="card-body">

<h5>Total Pages</h5>

<h2>

<?php echo $totalPages; ?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card bg3">

<div class="card-body">

<h5>Showing</h5>

<h2>

<?php echo mysqli_num_rows($records); ?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card bg4">

<div class="card-body">

<h5>Search</h5>

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search..."
value="<?php echo htmlspecialchars($search); ?>">

<button
class="btn btn-light">

<i class="fa fa-search"></i>

</button>

</div>

</form>

</div>

</div>

</div>

</div>

<div class="row">

<div class="col-lg-4">

<div class="card shadow-sm">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<?php echo ($edit_mode) ? "Edit Sub-Division" : "Add New Sub-Division"; ?>

</h5>

</div>

<div class="card-body">

<form method="POST">

<?php if($edit_mode){ ?>

<input
type="hidden"
name="id"
value="<?php echo $edit_id; ?>">

<?php } ?>

<!-- Circle -->

<div class="mb-3">

<label class="form-label">

Circle

</label>

<select
name="circle_id"
class="form-select"
required>

<option value="">

Select Circle

</option>

<?php

mysqli_data_seek($circle_list,0);

while($circle=mysqli_fetch_assoc($circle_list)){

?>

<option
value="<?php echo $circle['id']; ?>"

<?php

if($circle_id==$circle['id']){

echo "selected";

}

?>

>

<?php

echo $circle['zone_name'];

echo " → ";

echo $circle['circle_name'];

?>

</option>

<?php } ?>

</select>

</div>

<!-- Sub Division -->

<div class="mb-3">

<label class="form-label">

Sub-Division Name

</label>

<input

type="text"

name="sub_division_name"

class="form-control"

required

value="<?php echo htmlspecialchars($sub_division_name); ?>">

</div>

<!-- Office Code -->

<div class="mb-3">

<label class="form-label">

Office Code

</label>

<input

type="text"

name="office_code"

class="form-control"

value="<?php echo htmlspecialchars($office_code); ?>">

</div>

<!-- Status -->

<div class="mb-3">

<label class="form-label">

Status

</label>

<select

name="status"

class="form-select">

<option

value="Active"

<?php

if($status=="Active") echo "selected";

?>

>

Active

</option>

<option

value="Inactive"

<?php

if($status=="Inactive") echo "selected";

?>

>

Inactive

</option>

</select>

</div>

<!-- Buttons -->

<div class="d-grid gap-2">

<?php if($edit_mode){ ?>

<button

type="submit"

name="update"

class="btn btn-success">

<i class="fa fa-save"></i>

Update Sub-Division

</button>

<a

href="manage_subdivisions.php"

class="btn btn-secondary">

Cancel

</a>

<?php }else{ ?>

<button

type="submit"

name="save"

class="btn btn-primary">

<i class="fa fa-plus-circle"></i>

Add Sub-Division

</button>

<?php } ?>

</div>

</form>

</div>

</div>

</div>

<div class="col-lg-8">

<div class="card shadow-sm">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

<i class="fa-solid fa-list"></i>

Sub-Division List

</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-primary">

<tr>

<th width="60">ID</th>

<th>Zone</th>

<th>Circle</th>

<th>Sub-Division</th>

<th>Office Code</th>

<th width="100">Status</th>

<th width="150" class="text-center">

Action

</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($records)>0){

while($row=mysqli_fetch_assoc($records)){

?>

<tr>

<td>

<?php echo $row['id']; ?>

</td>

<td>

<?php echo htmlspecialchars($row['zone_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['circle_name']); ?>

</td>

<td>

<strong>

<?php echo htmlspecialchars($row['sub_division_name']); ?>

</strong>

</td>

<td>

<?php echo htmlspecialchars($row['office_code']); ?>

</td>

<td>

<?php

if($row['status']=="Active"){

?>

<span class="badge bg-success">

Active

</span>

<?php

}else{

?>

<span class="badge bg-danger">

Inactive

</span>

<?php

}

?>

</td>

<td class="text-center">

<a

href="manage_subdivisions.php?edit=<?php echo $row['id']; ?>"

class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>

</a>

<a

href="manage_subdivisions.php?delete=<?php echo $row['id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this Sub-Division?');">

<i class="fa fa-trash"></i>

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center text-danger">

No Sub-Divisions Found

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

</div>

</div>

<!-- ===========================
        PAGINATION
=========================== -->

<?php if($totalPages > 1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php

if($page > 1){

?>

<li class="page-item">

<a
class="page-link"

href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">

Previous

</a>

</li>

<?php

}

?>

<?php

for($i=1;$i<=$totalPages;$i++){

?>

<li class="page-item <?php if($page==$i) echo 'active'; ?>">

<a
class="page-link"

href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">

<?php echo $i; ?>

</a>

</li>

<?php

}

?>

<?php

if($page < $totalPages){

?>

<li class="page-item">

<a
class="page-link"

href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">

Next

</a>

</li>

<?php

}

?>

</ul>

</nav>

<?php } ?>

</div>

</div>

</div>

<!-- ===========================
        FOOTER
=========================== -->

<footer class="mt-5">

<hr>

<div class="text-center text-muted mb-3">

© <?php echo date("Y"); ?>

APDCL Electricity Billing Management System

</div>

</footer>

</div>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){

    let input=document.querySelector("input[name='sub_division_name']");

    if(input){

        input.focus();

    }

});

</script>

</body>

</html>