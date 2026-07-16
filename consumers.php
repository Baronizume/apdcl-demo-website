<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$search = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $result = mysqli_query($conn,
        "SELECT * FROM users
         WHERE consumer_no LIKE '%$search%'
         OR name LIKE '%$search%'");
} else {
    $result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Consumers</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2>Consumer Management</h2>

<form method="GET" class="row mt-4 mb-4">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search by Consumer Number or Name"
value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">
Search
</button>

</div>

</form>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Consumer No</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Action</th>

</tr>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>
<td><?php echo $row['consumer_no']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td>
<?php
echo isset($row['phone']) ? htmlspecialchars($row['phone']) : "N/A";
?>
</td>

<td>

<a href="add_consumer.php" class="btn btn-success mb-3">
    + Add Consumer
</a>

<a href="view_consumer.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View</a>

<a href="edit_consumer.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

<a href="delete_consumer.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Delete this consumer?')">
Delete
</a>

<a href="generate_bill.php?consumer_no=<?php echo $row['consumer_no']; ?>" class="btn btn-success btn-sm">
Bill
</a>

</td>

</tr>

<?php } ?>

</tbody>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['phone']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="dashboard.php" class="btn btn-secondary">
Back
</a>

</div>

</body>

</html>