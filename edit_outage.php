<?php
session_start();
include("../db.php");

/*=========================================
    LOGIN CHECK
=========================================*/

if (!isset($_SESSION['logged_in'])) {

    header("Location: dashboard.php");
    exit();

}

/*=========================================
    VALIDATE ID
=========================================*/

if(!isset($_GET['id']))
{

    header("Location: outage_map.php");
    exit();

}

$id=(int)$_GET['id'];

$message="";

/*=========================================
    LOAD DROPDOWNS
=========================================*/

$zones=mysqli_query($conn,"
SELECT *
FROM zones
WHERE status='Active'
ORDER BY zone_name
");

$circles=mysqli_query($conn,"
SELECT *
FROM circles
WHERE status='Active'
ORDER BY circle_name
");

$subdivisions=mysqli_query($conn,"
SELECT *
FROM sub_divisions
WHERE status='Active'
ORDER BY sub_division_name
");

/*=========================================
    FETCH OUTAGE
=========================================*/

$stmt=mysqli_prepare($conn,"
SELECT *
FROM outages
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{

    header("Location: outage_map.php");
    exit();

}

$outage=mysqli_fetch_assoc($result);

/*=========================================
    FORM VALUES
=========================================*/

$zone=$outage['zone'];

$circle=$outage['circle'];

$sub_division=$outage['sub_division'];

$feeder_name=$outage['feeder_name'];

$transformer=$outage['transformer'];

$latitude=$outage['latitude'];

$longitude=$outage['longitude'];

$consumers_affected=$outage['consumers_affected'];

$outage_reason=$outage['outage_reason'];

$start_time=$outage['start_time'];

$estimated_restore=$outage['estimated_restore'];

$status=$outage['status'];

/*=========================================
    UPDATE
=========================================*/

if(isset($_POST['update']))
{

    $zone=trim($_POST['zone']);

    $circle=trim($_POST['circle']);

    $sub_division=trim($_POST['sub_division']);

    $feeder_name=trim($_POST['feeder_name']);

    $transformer=trim($_POST['transformer']);

    $latitude=trim($_POST['latitude']);

    $longitude=trim($_POST['longitude']);

    $consumers_affected=(int)$_POST['consumers_affected'];

    $outage_reason=trim($_POST['outage_reason']);

    $start_time=$_POST['start_time'];

    $estimated_restore=$_POST['estimated_restore'];

    $status=$_POST['status'];

    if(
        empty($zone) ||
        empty($circle) ||
        empty($sub_division) ||
        empty($feeder_name) ||
        empty($latitude) ||
        empty($longitude)
    )
    {

        $message='
        <div class="alert alert-danger">
            Please fill all required fields.
        </div>';

    }
    else
    {

        $update=mysqli_prepare($conn,"
        UPDATE outages
        SET

            zone=?,

            circle=?,

            sub_division=?,

            feeder_name=?,

            transformer=?,

            latitude=?,

            longitude=?,

            consumers_affected=?,

            outage_reason=?,

            start_time=?,

            estimated_restore=?,

            status=?

        WHERE id=?
        ");

        mysqli_stmt_bind_param(

            $update,

            "sssssddissssi",

            $zone,

            $circle,

            $sub_division,

            $feeder_name,

            $transformer,

            $latitude,

            $longitude,

            $consumers_affected,

            $outage_reason,

            $start_time,

            $estimated_restore,

            $status,

            $id

        );

        if(mysqli_stmt_execute($update))
        {

            $_SESSION['success']="Outage updated successfully.";

            header("Location: outage_map.php");

            exit();

        }
        else
        {

            $message='
            <div class="alert alert-danger">
                Unable to update outage.
            </div>';

        }

    }

}

if ($complaint['status'] != "Pending") {

    $_SESSION['error'] = "Only pending complaints can be edited.";

    header("Location: report_outage.php");

    exit();

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Outage</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
    font-family:'Segoe UI',sans-serif;
}

.card{
    border:none;
    border-radius:15px;
}

.card-header{
    border-radius:15px 15px 0 0 !important;
}

.form-label{
    font-weight:600;
}

.required{
    color:red;
}

</style>

</head>

<body>

<div class="container mt-4 mb-5">

<div class="row justify-content-center">

<div class="col-lg-10">

<div class="card shadow">

<div class="card-header bg-warning text-dark">

<div class="d-flex justify-content-between align-items-center">

<h3 class="mb-0">

<i class="fa-solid fa-pen-to-square"></i>

Edit Outage

</h3>

<a href="outage_map.php" class="btn btn-dark">

<i class="fa fa-arrow-left"></i>

Back

</a>

</div>

</div>

<div class="card-body">

<?= $message ?>

<form method="POST">

<div class="row">

<!-- Zone -->

<div class="col-md-6 mb-3">

<label class="form-label">

Zone <span class="required">*</span>

</label>

<select name="zone" class="form-select" required>

<option value="">Select Zone</option>

<?php
mysqli_data_seek($zones,0);

while($z=mysqli_fetch_assoc($zones)){
?>

<option
value="<?= $z['zone_name'] ?>"
<?= ($zone==$z['zone_name'])?'selected':''; ?>>

<?= $z['zone_name'] ?>

</option>

<?php } ?>

</select>

</div>

<!-- Circle -->

<div class="col-md-6 mb-3">

<label class="form-label">

Circle <span class="required">*</span>

</label>

<select name="circle" class="form-select" required>

<option value="">Select Circle</option>

<?php
mysqli_data_seek($circles,0);

while($c=mysqli_fetch_assoc($circles)){
?>

<option
value="<?= $c['circle_name'] ?>"
<?= ($circle==$c['circle_name'])?'selected':''; ?>>

<?= $c['circle_name'] ?>

</option>

<?php } ?>

</select>

</div>

<!-- Sub Division -->

<div class="col-md-6 mb-3">

<label class="form-label">

Sub-Division <span class="required">*</span>

</label>

<select name="sub_division" class="form-select" required>

<option value="">Select Sub-Division</option>

<?php
mysqli_data_seek($subdivisions,0);

while($s=mysqli_fetch_assoc($subdivisions)){
?>

<option
value="<?= $s['sub_division_name'] ?>"
<?= ($sub_division==$s['sub_division_name'])?'selected':''; ?>>

<?= $s['sub_division_name'] ?>

</option>

<?php } ?>

</select>

</div>

<!-- Feeder -->

<div class="col-md-6 mb-3">

<label class="form-label">

Feeder Name

</label>

<input
type="text"
name="feeder_name"
class="form-control"
value="<?= htmlspecialchars($feeder_name) ?>"
required>

</div>

<!-- Transformer -->

<div class="col-md-6 mb-3">

<label class="form-label">

Transformer

</label>

<input
type="text"
name="transformer"
class="form-control"
value="<?= htmlspecialchars($transformer) ?>">

</div>

<!-- Consumers -->

<div class="col-md-6 mb-3">

<label class="form-label">

Consumers Affected

</label>

<input
type="number"
name="consumers_affected"
class="form-control"
value="<?= $consumers_affected ?>">

</div>

<!-- Latitude -->

<div class="col-md-6 mb-3">

<label class="form-label">

Latitude

</label>

<input
type="text"
name="latitude"
class="form-control"
value="<?= $latitude ?>"
required>

</div>

<!-- Longitude -->

<div class="col-md-6 mb-3">

<label class="form-label">

Longitude

</label>

<input
type="text"
name="longitude"
class="form-control"
value="<?= $longitude ?>"
required>

</div>

<!-- Start Time -->

<div class="col-md-6 mb-3">

<label class="form-label">

Start Time

</label>

<input
type="datetime-local"
name="start_time"
class="form-control"
value="<?= !empty($start_time)?date('Y-m-d\TH:i',strtotime($start_time)):'' ?>">

</div>

<!-- Estimated Restore -->

<div class="col-md-6 mb-3">

<label class="form-label">

Estimated Restore

</label>

<input
type="datetime-local"
name="estimated_restore"
class="form-control"
value="<?= !empty($estimated_restore)?date('Y-m-d\TH:i',strtotime($estimated_restore)):'' ?>">

</div>

<!-- Status -->

<div class="col-md-6 mb-3">

<label class="form-label">

Status

</label>

<select name="status" class="form-select">

<option value="Active" <?=($status=="Active")?"selected":"";?>>

Active

</option>

<option value="Restored" <?=($status=="Restored")?"selected":"";?>>

Restored

</option>

</select>

</div>

<!-- Outage Reason -->

<div class="col-md-12 mb-3">

<label class="form-label">

Outage Reason

</label>

<textarea
name="outage_reason"
rows="4"
class="form-control"><?= htmlspecialchars($outage_reason) ?></textarea>

</div>

</div>

<hr>

<div class="d-flex justify-content-between">

<a
href="outage_map.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back

</a>

<div>

<button
type="reset"
class="btn btn-warning">

<i class="fa fa-rotate-left"></i>

Reset

</button>

<button
type="submit"
name="update"
class="btn btn-success">

<i class="fa fa-save"></i>

Update Outage

</button>

</div>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>