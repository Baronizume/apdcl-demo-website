<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    LOAD CONSUMER DETAILS
=========================================*/

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

/*=========================================
    DEFAULT VALUES
=========================================*/

$category = "";
$priority = "Medium";
$subject = "";
$message = "";
$location = "";
$latitude = "";
$longitude = "";

$success = "";
$error = "";

/*=========================================
    GENERATE COMPLAINT ID
=========================================*/

$complaint_id = "CMP".date("YmdHis").rand(100,999);

/*=========================================
    SUBMIT COMPLAINT
=========================================*/

if(isset($_POST['submit'])){

    $category  = trim($_POST['category']);
    $priority  = trim($_POST['priority']);
    $subject   = trim($_POST['subject']);
    $message   = trim($_POST['message']);
    $location  = trim($_POST['location']);
    $latitude  = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    /*==============================
        PHOTO UPLOAD
    ==============================*/

    $photo = "";

    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){

        $uploadDir = "../uploads/complaints/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0777,true);
        }

        $extension = strtolower(pathinfo(
            $_FILES['photo']['name'],
            PATHINFO_EXTENSION
        ));

        $allowed = ["jpg","jpeg","png","webp"];

        if(in_array($extension,$allowed)){

            $photo = time()."_".basename($_FILES['photo']['name']);

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $uploadDir.$photo
            );

        }

    }

    /*==============================
        VALIDATION
    ==============================*/

    if(
        empty($category) ||
        empty($subject) ||
        empty($message)
    ){

        $error = "Please fill all required fields.";

    }else{

        /*==============================
            INSERT COMPLAINT
        ==============================*/

        $insert = mysqli_prepare($conn,"
        INSERT INTO complaint
        (
            complaint_id,
            consumer_no,
            name,
            mobile,
            category,
            subject,
            description,
            address,
            latitude,
            longitude,
            photo,
            priority,
            status
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,?,?,?
        )
        ");

        $status = "Pending";

        mysqli_stmt_bind_param(
            $insert,
            "sssssssssssss",
            $complaint_id,
            $consumer_no,
            $consumer['name'],
            $consumer['mobile'],
            $category,
            $subject,
            $message,
            $location,
            $latitude,
            $longitude,
            $photo,
            $priority,
            $status
        );

       if(mysqli_stmt_execute($insert)){

            $_SESSION['success']="Complaint submitted successfully.";

            header("Location: complaint.php");
            exit();

        }else{

            die(mysqli_error($conn));

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Add New Complaint | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;

}

body{

background:#eef4fb;

}

/*=========================================
    NAVBAR
=========================================*/

.navbar{

background:linear-gradient(90deg,#0d47a1,#1565c0);

box-shadow:0 5px 20px rgba(0,0,0,.15);

}

.navbar-brand{

font-size:24px;

font-weight:700;

color:#fff !important;

}

.navbar-brand i{

color:#ffd54f;

margin-right:8px;

}

/*=========================================
    PAGE HEADER
=========================================*/

.page-header{

background:linear-gradient(135deg,#1565c0,#0d47a1);

color:#fff;

padding:35px;

border-radius:18px;

margin-top:30px;

margin-bottom:25px;

box-shadow:0 8px 20px rgba(0,0,0,.15);

}

.page-header h2{

font-weight:700;

margin-bottom:10px;

}

.page-header p{

opacity:.9;

margin:0;

}

/*=========================================
    MAIN CARD
=========================================*/

.main-card{

border:none;

border-radius:18px;

overflow:hidden;

box-shadow:0 10px 25px rgba(0,0,0,.12);

margin-bottom:30px;

}

.card-header{

padding:18px 25px;

font-size:20px;

font-weight:600;

}

/*=========================================
    FORM
=========================================*/

.form-label{

font-weight:600;

}

.form-control,
.form-select{

height:50px;

border-radius:10px;

}

textarea.form-control{

height:auto;

resize:none;

}

.form-control:focus,
.form-select:focus{

border-color:#1565c0;

box-shadow:0 0 0 .2rem rgba(21,101,192,.15);

}

/*=========================================
    BUTTONS
=========================================*/

.btn{

border-radius:10px;

font-weight:600;

padding:10px 22px;

transition:.3s;

}

.btn:hover{

transform:translateY(-2px);

}

/*=========================================
    PROFILE CARD
=========================================*/

.profile-card{

background:#fff;

border-radius:15px;

padding:20px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

}

.profile-card h5{

font-weight:700;

margin-bottom:15px;

color:#1565c0;

}

.profile-card p{

margin-bottom:8px;

font-size:15px;

}

.profile-card i{

color:#1565c0;

width:22px;

}

/*=========================================
    IMAGE PREVIEW
=========================================*/

#preview{

display:none;

max-width:220px;

border-radius:12px;

border:2px solid #ddd;

padding:5px;

margin-top:10px;

}

/*=========================================
    RESPONSIVE
=========================================*/

@media(max-width:768px){

.page-header{

text-align:center;

padding:25px;

}

.profile-card{

margin-top:20px;

}

}

</style>

</head>
<body>

<!-- ==========================================
        NAVBAR
========================================== -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="dashboard.php">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Portal

</a>

<div class="ms-auto">

<a
href="complaint.php"
class="btn btn-light me-2">

<i class="bi bi-chat-left-text-fill"></i>

My Complaints

</a>

<a
href="dashboard.php"
class="btn btn-success me-2">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

<a
href="../logout.php"
class="btn btn-danger">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

</div>

</nav>

<div class="container mt-4">

<!-- Success -->

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

<!-- Error -->

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

<!-- ==========================================
        PAGE HEADER
========================================== -->

<div class="page-header">

<div class="row align-items-center">

<div class="col-lg-8">

<h2>

<i class="bi bi-pencil-square"></i>

Submit New Complaint

</h2>

<p>

Report electricity-related issues quickly. Your complaint will be forwarded to the concerned APDCL office.

</p>

</div>

<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

<span class="badge bg-warning text-dark fs-6">

Complaint ID

<br>

<strong>

<?= htmlspecialchars($complaint_id) ?>

</strong>

</span>

</div>

</div>

</div>

<div class="row">

<!-- ==========================================
        CONSUMER DETAILS
========================================== -->

<div class="col-lg-4">

<div class="profile-card">

<h5>

<i class="bi bi-person-circle"></i>

Consumer Details

</h5>

<p>

<i class="bi bi-person-fill"></i>

<strong>Name :</strong>

<?= htmlspecialchars($consumer['name']) ?>

</p>

<p>

<i class="bi bi-credit-card"></i>

<strong>Consumer No :</strong>

<?= htmlspecialchars($consumer['consumer_no']) ?>

</p>

<p>

<i class="bi bi-telephone-fill"></i>

<strong>Mobile :</strong>

<?= htmlspecialchars($consumer['mobile']) ?>

</p>

<p>

<i class="bi bi-envelope-fill"></i>

<strong>Email :</strong>

<?= htmlspecialchars($consumer['email']) ?>

</p>

<p>

<i class="bi bi-geo-alt-fill"></i>

<strong>Address :</strong>

<?= htmlspecialchars($consumer['address']) ?>

</p>

</div>

</div>

<!-- ==========================================
        COMPLAINT FORM
========================================== -->

<div class="col-lg-8">

<div class="card main-card">

<div class="card-header bg-primary text-white">

<i class="bi bi-file-earmark-plus-fill"></i>

Complaint Information

</div>

<div class="card-body">

<form
method="POST"
enctype="multipart/form-data">
<div class="row g-4">

    <!-- Category -->

    <div class="col-md-6">

        <label class="form-label">

            <i class="bi bi-grid-fill text-primary"></i>

            Complaint Category

        </label>

        <select
        name="category"
        class="form-select"
        required>

            <option value="">Select Category</option>

            <option value="Power Failure" <?= ($category=="Power Failure")?"selected":""; ?>>Power Failure</option>

            <option value="Low Voltage" <?= ($category=="Low Voltage")?"selected":""; ?>>Low Voltage</option>

            <option value="High Voltage" <?= ($category=="High Voltage")?"selected":""; ?>>High Voltage</option>

            <option value="Billing Issue" <?= ($category=="Billing Issue")?"selected":""; ?>>Billing Issue</option>

            <option value="Meter Problem" <?= ($category=="Meter Problem")?"selected":""; ?>>Meter Problem</option>

            <option value="Transformer Fault" <?= ($category=="Transformer Fault")?"selected":""; ?>>Transformer Fault</option>

            <option value="Line Broken" <?= ($category=="Line Broken")?"selected":""; ?>>Line Broken</option>

            <option value="Street Light" <?= ($category=="Street Light")?"selected":""; ?>>Street Light</option>

            <option value="Others" <?= ($category=="Others")?"selected":""; ?>>Others</option>

        </select>

    </div>


    <!-- Priority -->

    <div class="col-md-6">

        <label class="form-label">

            <i class="bi bi-exclamation-circle-fill text-danger"></i>

            Priority

        </label>

        <select
        name="priority"
        class="form-select"
        required>

            <option value="Low" <?= ($priority=="Low")?"selected":""; ?>>Low</option>

            <option value="Medium" <?= ($priority=="Medium")?"selected":""; ?>>Medium</option>

            <option value="High" <?= ($priority=="High")?"selected":""; ?>>High</option>

        </select>

    </div>


    <!-- Subject -->

    <div class="col-12">

        <label class="form-label">

            <i class="bi bi-chat-left-text-fill text-success"></i>

            Subject

        </label>

        <input
        type="text"
        name="subject"
        class="form-control"
        value="<?= htmlspecialchars($subject) ?>"
        placeholder="Enter complaint subject"
        required>

    </div>


    <!-- Message -->

    <div class="col-12">

        <label class="form-label">

            <i class="bi bi-pencil-square text-primary"></i>

            Complaint Details

        </label>

        <textarea
        name="message"
        class="form-control"
        rows="5"
        placeholder="Describe your complaint..."
        required><?= htmlspecialchars($message) ?></textarea>

    </div>


    <!-- Photo -->

    <div class="col-md-6">

        <label class="form-label">

            <i class="bi bi-camera-fill text-success"></i>

            Upload Photo (Optional)

        </label>

        <input
        type="file"
        name="photo"
        class="form-control"
        accept=".jpg,.jpeg,.png,.webp"
        onchange="previewImage(event)">

        <img
        id="preview"
        class="img-fluid mt-3 rounded shadow">

    </div>


    <!-- Location -->

    <div class="col-md-6">

        <label class="form-label">

            <i class="bi bi-geo-alt-fill text-danger"></i>

            Complaint Location

        </label>

        <input
        type="text"
        name="location"
        id="location"
        class="form-control"
        value="<?= htmlspecialchars($location) ?>"
        placeholder="Location">

        <input
        type="hidden"
        name="latitude"
        id="latitude"
        value="<?= htmlspecialchars($latitude) ?>">

        <input
        type="hidden"
        name="longitude"
        id="longitude"
        value="<?= htmlspecialchars($longitude) ?>">

        <button
        type="button"
        class="btn btn-outline-primary mt-3"
        onclick="getLocation()">

            <i class="bi bi-crosshair"></i>

            Detect My Location

        </button>

    </div>


    <!-- Buttons -->

    <div class="col-12">

        <hr>

        <div class="d-flex justify-content-end gap-2">

            <a
            href="complaint.php"
            class="btn btn-secondary">

                <i class="bi bi-arrow-left-circle-fill"></i>

                Back

            </a>

            <button
            type="reset"
            class="btn btn-warning">

                <i class="bi bi-arrow-clockwise"></i>

                Reset

            </button>

            <button
            type="submit"
            name="submit"
            class="btn btn-primary">

                <i class="bi bi-send-fill"></i>

                Submit Complaint

            </button>

        </div>

    </div>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    IMAGE PREVIEW
=========================================*/

function previewImage(event){

    const preview = document.getElementById("preview");

    preview.src = URL.createObjectURL(event.target.files[0]);

    preview.style.display = "block";

}

/*=========================================
    GET CURRENT LOCATION
=========================================*/

function getLocation(){

    if(navigator.geolocation){

        navigator.geolocation.getCurrentPosition(

            function(position){

                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;

                document.getElementById("latitude").value = latitude;
                document.getElementById("longitude").value = longitude;

                document.getElementById("location").value =
                latitude + ", " + longitude;

            },

            function(error){

                switch(error.code){

                    case error.PERMISSION_DENIED:
                        alert("Location permission denied.");
                    break;

                    case error.POSITION_UNAVAILABLE:
                        alert("Location unavailable.");
                    break;

                    case error.TIMEOUT:
                        alert("Location request timed out.");
                    break;

                    default:
                        alert("Unable to detect location.");
                    break;

                }

            }

        );

    }else{

        alert("Geolocation is not supported by this browser.");

    }

}

/*=========================================
    AUTO HIDE ALERTS
=========================================*/

setTimeout(function(){

    let alerts=document.querySelectorAll(".alert");

    alerts.forEach(function(alert){

        let bsAlert=new bootstrap.Alert(alert);

        bsAlert.close();

    });

},5000);

/*=========================================
    FORM VALIDATION
=========================================*/

document.querySelector("form").addEventListener("submit",function(e){

    let subject=document.querySelector("input[name='subject']").value.trim();

    let message=document.querySelector("textarea[name='message']").value.trim();

    if(subject.length<5){

        alert("Subject must contain at least 5 characters.");

        e.preventDefault();

        return;

    }

    if(message.length<10){

        alert("Complaint details must contain at least 10 characters.");

        e.preventDefault();

        return;

    }

});

/*=========================================
    DISABLE DOUBLE SUBMIT
=========================================*/

document.querySelector("form").addEventListener("submit",function(){

    let btn=document.querySelector("button[name='submit']");

    btn.disabled=true;

    btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Submitting...';

});

</script>

</body>

</html>