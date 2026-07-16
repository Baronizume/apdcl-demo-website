<?php

session_start();

include("../db.php");


/*=========================================
 ADMIN LOGIN CHECK
=========================================*/

if(!isset($_SESSION['admin'])){

    header("Location: login.php");
    exit();

}


/*=========================================
 CHECK ID
=========================================*/

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){

    die("Invalid Complaint ID");

}


$id = (int)$_GET['id'];



/*=========================================
 UPDATE COMPLAINT
=========================================*/


if(isset($_POST['update'])){


    $category = mysqli_real_escape_string(
        $conn,
        $_POST['category']
    );


    $description = mysqli_real_escape_string(
        $conn,
        $_POST['description']
    );


    $priority = mysqli_real_escape_string(
        $conn,
        $_POST['priority']
    );


    $status = mysqli_real_escape_string(
        $conn,
        $_POST['status']
    );


    $assigned = !empty($_POST['assigned_admin_id']) 
    ? $_POST['assigned_admin_id'] 
    : NULL;


    $remark = mysqli_real_escape_string(
        $conn,
        $_POST['remark']
    );



    $sql="

    UPDATE complaint SET

    category='$category',

    description='$description',

    priority='$priority',

    status='$status',

    assigned_admin_id=".($assigned ? "'$assigned'" : "NULL").",

    remark='$remark'

    WHERE id='$id'

    ";



    if(mysqli_query($conn,$sql)){


        $_SESSION['success']="Complaint Updated Successfully";


        header("Location:view_complaint.php?id=".$id);

        exit();


    }else{


        $error=mysqli_error($conn);

    }


}



/*=========================================
 FETCH COMPLAINT
=========================================*/


$result=mysqli_query($conn,

"

SELECT *

FROM complaint

WHERE id='$id'

LIMIT 1

"

);



if(mysqli_num_rows($result)==0){

    die("Complaint Not Found");

}



$complaint=mysqli_fetch_assoc($result);




/*=========================================
 ADMIN LIST
=========================================*/


$admins=mysqli_query($conn,

"

SELECT id,name

FROM admin

WHERE status='Active'

ORDER BY name ASC

"

);


?>


<!DOCTYPE html>

<html>

<head>


<title>Edit Complaint | APDCL Admin</title>


<meta name="viewport" content="width=device-width, initial-scale=1">


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<style>


body{

background:#f4f7fb;

font-family:'Segoe UI';

}


.container{

margin-top:40px;

}



.card{

border:none;

border-radius:20px;

box-shadow:0 10px 30px rgba(0,0,0,.08);

}



.card-header{

background:#0d47a1;

color:white;

border-radius:20px 20px 0 0!important;

padding:20px;

}



.form-label{

font-weight:600;

}



.form-control,
.form-select{

border-radius:12px;

padding:12px;

}



.btn{

border-radius:12px;

padding:10px 25px;

}



.info-box{

background:#eef5ff;

border-radius:15px;

padding:15px;

}



</style>


</head>


<body>



<div class="container">



<div class="card">



<div class="card-header">


<h3 class="mb-0">

<i class="bi bi-pencil-square"></i>

Edit Complaint

</h3>


</div>



<div class="card-body">



<?php if(isset($error)){ ?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php } ?>




<!-- Complaint Info -->


<div class="info-box mb-4">


<div class="row">


<div class="col-md-4">

<b>Complaint ID</b><br>

<?= htmlspecialchars($complaint['complaint_id']) ?>

</div>



<div class="col-md-4">

<b>Consumer No</b><br>

<?= htmlspecialchars($complaint['consumer_no']) ?>

</div>



<div class="col-md-4">

<b>Date</b><br>

<?= date("d M Y",strtotime($complaint['created_at'])) ?>

</div>


</div>


</div>





<form method="POST">



<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">

Category

</label>


<select name="category"

class="form-select">


<option <?= $complaint['category']=="Low Voltage"?"selected":"" ?>>

Low Voltage

</option>


<option <?= $complaint['category']=="Transformer Fault"?"selected":"" ?>>

Transformer Fault

</option>


<option>

Meter Problem

</option>


<option>

Billing Issue

</option>


<option>

Power Failure

</option>


</select>


</div>




<div class="col-md-6 mb-3">


<label class="form-label">

Priority

</label>


<select name="priority"

class="form-select">


<option <?= $complaint['priority']=="High"?"selected":"" ?>>

High

</option>


<option <?= $complaint['priority']=="Medium"?"selected":"" ?>>

Medium

</option>


<option <?= $complaint['priority']=="Low"?"selected":"" ?>>

Low

</option>


</select>


</div>


</div>





<div class="mb-3">


<label class="form-label">

Complaint Description

</label>


<textarea

name="description"

class="form-control"

rows="5">


<?= htmlspecialchars($complaint['description']) ?>


</textarea>


</div>






<div class="row">



<div class="col-md-4 mb-3">


<label class="form-label">

Assign Admin

</label>


<select name="assigned_admin_id"

class="form-select">


<option value="">

Not Assigned

</option>



<?php while($a=mysqli_fetch_assoc($admins)){ ?>


<option value="<?= $a['id']; ?>"

<?= $complaint['assigned_admin_id']==$a['id']?'selected':'' ?>>


<?= htmlspecialchars($a['name']); ?>


</option>


<?php } ?>


</select>


</div>





<div class="col-md-4 mb-3">


<label class="form-label">

Status

</label>


<select name="status"

class="form-select">


<option>Pending</option>

<option>Assigned</option>

<option>In Progress</option>

<option>Resolved</option>


</select>


</div>





<div class="col-md-4 mb-3">


<label class="form-label">

Remark

</label>


<input

type="text"

name="remark"

class="form-control"

value="<?= htmlspecialchars($complaint['remark'] ?? '') ?>">


</div>



</div>





<div class="text-end">


<a href="manage_complaint.php"

class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Cancel

</a>



<button

name="update"

class="btn btn-primary">


<i class="bi bi-save"></i>

Update Complaint


</button>


</div>



</form>


</div>


</div>


</div>


</body>

</html>