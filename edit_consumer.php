<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* ------------------------------
   CHECK CONSUMER ID
--------------------------------*/

if (!isset($_GET['id'])) {
    header("Location: manage_consumer.php");
    exit();
}

$id = (int)$_GET['id'];

/* ------------------------------
   FETCH CONSUMER
--------------------------------*/

$result = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$id'
");

if(mysqli_num_rows($result)==0){
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($result);

$message = "";

/* ------------------------------
   UPDATE CONSUMER
--------------------------------*/

if(isset($_POST['update'])){

    $consumer_no = mysqli_real_escape_string($conn,$_POST['consumer_no']);
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $father_name = mysqli_real_escape_string($conn,$_POST['father_name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile = mysqli_real_escape_string($conn,$_POST['mobile']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
    $meter_no = mysqli_real_escape_string($conn,$_POST['meter_no']);
    $meter_type = mysqli_real_escape_string($conn,$_POST['meter_type']);
    $category = mysqli_real_escape_string($conn,$_POST['category']);
    $connection_type = mysqli_real_escape_string($conn,$_POST['connection_type']);
    $load_kw = mysqli_real_escape_string($conn,$_POST['load_kw']);
    $status = mysqli_real_escape_string($conn,$_POST['status']);
    $connection_date = mysqli_real_escape_string($conn,$_POST['connection_date']);

    $update = mysqli_query($conn,"
    UPDATE users SET

    consumer_no='$consumer_no',
    name='$name',
    father_name='$father_name',
    email='$email',
    mobile='$mobile',
    address='$address',
    meter_no='$meter_no',
    meter_type='$meter_type',
    category='$category',
    connection_type='$connection_type',
    load_kw='$load_kw',
    status='$status',
    connection_date='$connection_date'

    WHERE id='$id'
    ");

    if($update){

        $message = "
        <div class='alert alert-success'>
            Consumer Updated Successfully.
        </div>";

        $result = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE id='$id'
        ");

        $user = mysqli_fetch_assoc($result);

    }else{

        $message = "
        <div class='alert alert-danger'>
            Failed to Update Consumer.
        </div>";

    }

}
?>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Consumer Number
</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($user['consumer_no']); ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Meter Number
</label>

<input
type="text"
name="meter_no"
class="form-control"
value="<?= htmlspecialchars($user['meter_no']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Consumer Name
</label>

<input
type="text"
name="name"
class="form-control"
value="<?= htmlspecialchars($user['name']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Father / Guardian Name
</label>

<input
type="text"
name="father_name"
class="form-control"
value="<?= htmlspecialchars($user['father_name']); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Email Address
</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($user['email']); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Mobile Number
</label>

<input
type="text"
name="mobile"
class="form-control"
value="<?= htmlspecialchars($user['mobile']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Category
</label>

<select
name="category"
class="form-select"
required>

<option value="Domestic"
<?= ($user['category']=="Domestic") ? "selected" : ""; ?>>
Domestic
</option>

<option value="Commercial"
<?= ($user['category']=="Commercial") ? "selected" : ""; ?>>
Commercial
</option>

<option value="Industrial"
<?= ($user['category']=="Industrial") ? "selected" : ""; ?>>
Industrial
</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Meter Type
</label>

<select
name="meter_type"
class="form-select"
required>

<option value="Postpaid"
<?= ($user['meter_type']=="Postpaid") ? "selected" : ""; ?>>
Postpaid
</option>

<option value="Smart Prepaid"
<?= ($user['meter_type']=="Smart Prepaid") ? "selected" : ""; ?>>
Smart Prepaid
</option>

</select>

</div>

<div class="col-12 mb-3">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-control"
rows="3"
required><?= htmlspecialchars($user['address']); ?></textarea>

</div>

<div class="text-center mt-4">

<button
type="submit"
name="update"
class="btn btn-success btn-lg">

<i class="bi bi-check-circle"></i>

Update Consumer

</button>

<a
href="manage_consumer.php"
class="btn btn-secondary btn-lg">

<i class="bi bi-arrow-left-circle"></i>

Cancel

</a>

</div>

</form>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Consumer Number
</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($user['consumer_no']); ?>"
readonly>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Meter Number
</label>

<input
type="text"
name="meter_no"
class="form-control"
value="<?= htmlspecialchars($user['meter_no']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Consumer Name
</label>

<input
type="text"
name="name"
class="form-control"
value="<?= htmlspecialchars($user['name']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Email Address
</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($user['email']); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Mobile Number
</label>

<input
type="text"
name="mobile"
class="form-control"
value="<?= htmlspecialchars($user['mobile']); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Category
</label>

<select
name="category"
class="form-select"
required>

<option value="Domestic"
<?= ($user['category']=="Domestic") ? "selected" : ""; ?>>
Domestic
</option>

<option value="Commercial"
<?= ($user['category']=="Commercial") ? "selected" : ""; ?>>
Commercial
</option>

<option value="Industrial"
<?= ($user['category']=="Industrial") ? "selected" : ""; ?>>
Industrial
</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Meter Type
</label>

<select
name="meter_type"
class="form-select"
required>

<option value="Postpaid"
<?= ($user['meter_type']=="Postpaid") ? "selected" : ""; ?>>
Postpaid
</option>

<option value="Smart Prepaid"
<?= ($user['meter_type']=="Smart Prepaid") ? "selected" : ""; ?>>
Smart Prepaid
</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-control"
rows="3"
required><?= htmlspecialchars($user['address']); ?></textarea>

</div>

</div>

<div class="d-flex justify-content-center gap-3 mt-4">

<button
type="submit"
name="update"
class="btn btn-success btn-lg">

<i class="bi bi-check-circle-fill"></i>

Update Consumer

</button>

<a
href="manage_consumer.php"
class="btn btn-secondary btn-lg">

<i class="bi bi-arrow-left-circle"></i>

Back

</a>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>