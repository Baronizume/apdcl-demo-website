<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL Admin Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

.topbar{
    height:70px;
    background:#0d6efd;
    color:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 30px;
    box-shadow:0 2px 10px rgba(0,0,0,.15);
}

.logo{
    display:flex;
    align-items:center;
}

.logo img{
    width:45px;
    height:45px;
    border-radius:50%;
    margin-right:15px;
}

.logo h4{
    margin:0;
    font-weight:700;
}

.right-section{
    display:flex;
    align-items:center;
    gap:20px;
}

.profile{
    font-weight:600;
}

.logout-btn{
    background:#dc3545;
    color:white;
    padding:8px 18px;
    border-radius:8px;
    text-decoration:none;
}

.logout-btn:hover{
    background:#bb2d3b;
    color:white;
}

.wrapper{
    display:flex;
}

.content{
    flex:1;
    padding:25px;
}

</style>

</head>

<body>

<div class="topbar">

    <div class="logo">

        <img src="../assets/images/apdcl-logo.png" alt="Logo">

        <div>
            <h4>APDCL Admin Portal</h4>
            <small>Assam Power Distribution Company Ltd.</small>
        </div>

    </div>

    <div class="right-section">

        <span class="profile">
            <i class="fas fa-user-circle"></i>
            Welcome,
            <?php echo htmlspecialchars($admin_name); ?>
        </span>

        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

    </div>

</div>

<div class="wrapper">

<tbody>

<?php

if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td><?= $row['id']; ?></td>

<td>
    <strong><?= htmlspecialchars($row['consumer_no']); ?></strong>
</td>

<td><?= date("F Y", strtotime($row['month']."-01")); ?></td>

<td><?= number_format($row['units']); ?> Units</td>

<td class="fw-bold text-success">
    ₹ <?= number_format($row['total_bill'],2); ?>
</td>

<td>

<?php

if($row['status']=="Paid"){

?>

<span class="badge bg-success px-3 py-2">
    <i class="fas fa-check-circle"></i>
    Paid
</span>

<?php

}else{

?>

<span class="badge bg-warning text-dark px-3 py-2">
    <i class="fas fa-clock"></i>
    Pending
</span>

<?php

}

?>

</td>

<td>

<div class="btn-group" role="group">

<a href="view_bill.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm">

<i class="fas fa-eye"></i>

</a>

<a href="generate_bill.php?edit=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="fas fa-edit"></i>

</a>

<a href="download_bill.php?id=<?= $row['id']; ?>"
class="btn btn-success btn-sm">

<i class="fas fa-download"></i>

</a>

<a href="?delete=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to delete this bill?')">

<i class="fas fa-trash"></i>

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center py-5">

<i class="fas fa-folder-open fa-3x text-secondary mb-3"></i>

<h5>No Bills Found</h5>

<p class="text-muted">

Try changing your search filters.

</p>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

<div class="d-flex justify-content-between mt-4">

<a href="dashboard.php" class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Dashboard

</a>

<a href="generate_bill.php" class="btn btn-primary">

<i class="fas fa-plus-circle"></i>

Generate New Bill

</a>

</div>

</div>

</div>

</div>
