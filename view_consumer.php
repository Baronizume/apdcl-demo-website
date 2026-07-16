Not Found
<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (!isset($_GET['id'])) {
    die("Consumer ID not found.");
}

$id = intval($_GET['id']);

// Fetch consumer details
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

if (mysqli_num_rows($userQuery) == 0) {
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userQuery);

// Fetch latest bill
$consumer_no = $user['consumer_no'];

$billQuery = mysqli_query($conn,
"SELECT * FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1");

$bill = mysqli_fetch_assoc($billQuery);
?>

<!DOCTYPE html>
<html>

<head>

<title>View Consumer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Consumer Details</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Consumer Number</th>
<td><?php echo $user['consumer_no']; ?></td>
</tr>

<tr>
<th>Name</th>
<td><?php echo $user['name']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $user['email']; ?></td>
</tr>

<?php if(isset($user['phone'])){ ?>

<tr>
<th>Phone</th>
<td><?php echo $user['phone']; ?></td>
</tr>

<?php } ?>

<?php if(isset($user['address'])){ ?>

<tr>
<th>Address</th>
<td><?php echo $user['address']; ?></td>
</tr>

<?php } ?>

</table>

<hr>

<h4>Latest Bill</h4>

<?php if($bill){ ?>

<table class="table table-bordered">

<tr>
<th>Month</th>
<td><?php echo $bill['month']; ?></td>
</tr>

<tr>
<th>Units</th>
<td><?php echo $bill['units']; ?></td>
</tr>

<tr>
<th>Total Bill</th>
<td>₹ <?php echo $bill['total_bill']; ?></td>
</tr>

<tr>
<th>Status</th>
<td><?php echo $bill['status']; ?></td>
</tr>

</table>

<?php } else { ?>

<div class="alert alert-warning">

No bills found for this consumer.

</div>

<?php } ?>

<a href="consumers.php" class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</body>

</html>