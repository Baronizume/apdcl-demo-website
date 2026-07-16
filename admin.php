<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

$result = mysqli_query($conn,"SELECT * FROM bills ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Bills</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="mb-4">Manage Bills</h2>

<a href="dashboard.php" class="btn btn-secondary mb-3">
← Back to Dashboard
</a>

<table class="table table-bordered table-striped shadow">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Consumer No</th>
<th>Month</th>
<th>Units</th>
<th>Energy Charge</th>
<th>Fixed Charge</th>
<th>Electricity Duty</th>
<th>Subsidy</th>
<th>Total Bill</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td><?php echo $row['month']; ?></td>

<td><?php echo $row['units']; ?></td>

<td>₹ <?php echo $row['energy_charge']; ?></td>

<td>₹ <?php echo $row['fixed_charge']; ?></td>

<td>₹ <?php echo $row['electricity_duty']; ?></td>

<td>₹ <?php echo $row['subsidy']; ?></td>

<td><strong>₹ <?php echo $row['total_bill']; ?></strong></td>

<td>

<?php
if($row['status']=="Paid"){
    echo "<span class='badge bg-success'>Paid</span>";
}else{
    echo "<span class='badge bg-danger'>Unpaid</span>";
}
?>

</td>

<td>

<a href="edit_bill.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete_bill.php?id=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to delete this bill?');">
Delete
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>