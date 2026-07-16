<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

$result = mysqli_query($conn,"SELECT * FROM notices ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Notices</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="mb-4">Manage Notices</h2>

<a href="dashboard.php" class="btn btn-secondary mb-3">
← Dashboard
</a>

<a href="add_notice.php" class="btn btn-success mb-3">
+ Add Notice
</a>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Title</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['notice_date']; ?></td>

<td>

<a href="edit_notice.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete_notice.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Delete this notice?')">
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