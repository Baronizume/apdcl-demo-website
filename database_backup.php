<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*====================================================
    PAGE TITLE
====================================================*/

$pageTitle = "Database Backup";

/*====================================================
    DELETE BACKUP
====================================================*/

if(isset($_GET['delete']))
{

    $file = basename($_GET['delete']);

    $path = "../backups/" . $file;

    if(file_exists($path))
    {
        unlink($path);

        echo "<script>

            alert('Backup deleted successfully.');

            window.location='database_backup.php';

        </script>";

        exit();
    }

}

/*====================================================
    BACKUP HISTORY
====================================================*/

$backupFolder = "../backups/";

$backupFiles = [];

if(is_dir($backupFolder))
{
    $backupFiles = glob($backupFolder . "*.sql");

    rsort($backupFiles);
}

/*====================================================
    DATABASE BACKUP
====================================================*/

$message = "";

if(isset($_POST['backup']))
{

    // Database Details
    $host     = "localhost";
    $username = "root";
    $password = "";
    $database = "apdcl_demo";   // <-- Change if needed

    // XAMPP mysqldump location
    $mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";

    // Backup Folder
    $backupFolder = "../backups/";

    // Create folder if not exists
    if(!is_dir($backupFolder))
    {
        mkdir($backupFolder,0777,true);
    }

    // Backup file name
    $backupFile = $backupFolder .
                  "apdcl_backup_" .
                  date("Y-m-d_H-i-s") .
                  ".sql";

    // Backup Command
    $command = "\"$mysqldump\" --user=$username --password=$password $database > \"$backupFile\"";

    exec($command,$output,$result);

    if($result==0)
    {
        $message = '
        <div class="alert alert-success">
            <strong>Success!</strong><br>
            Database backup created successfully.
        </div>';
    }
    else
    {
        $message = '
        <div class="alert alert-danger">
            <strong>Error!</strong><br>
            Database backup failed.
        </div>';
    }

}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title><?= $pageTitle; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

<i class="bi bi-database-fill"></i>

Database Backup

</h3>

</div>

<div class="card-body">

<?= $message; ?>

<p>

Create a complete backup of the APDCL database.

</p>

<form method="POST">

<button
type="submit"
name="backup"
class="btn btn-success btn-lg">

<i class="bi bi-download"></i>

Create Backup

</button>

<a
href="restore_backup.php"
class="btn btn-danger btn-lg">

<i class="bi bi-arrow-clockwise"></i>

Restore Database

</a>

<a href="dashboard.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</form>

</div>

</div>

</div>

<div class="card shadow mt-4">

    <div class="card-header bg-dark text-white">

        <h4>

            <i class="bi bi-clock-history"></i>

            Backup History

        </h4>

    </div>

    <div class="card-body">

        <?php if(count($backupFiles)>0){ ?>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-primary">

                    <tr>

                        <th>#</th>

                        <th>Backup File</th>

                        <th>Size</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $i=1;

                foreach($backupFiles as $file){

                ?>

                <tr>

                    <td><?= $i++; ?></td>

                    <td><?= basename($file); ?></td>

                    <td><?= round(filesize($file)/1024,2); ?> KB</td>

                    <td><?= date("d M Y h:i A",filemtime($file)); ?></td>

                    <td>

                        <a
                            href="../backups/<?= basename($file); ?>"
                            download
                            class="btn btn-success btn-sm">

                            <i class="bi bi-download"></i>

                            Download

                            </a>

                            <a
                            href="?delete=<?= urlencode(basename($file)); ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this backup permanently?');">

                            <i class="bi bi-trash"></i>

                            Delete

                         </a>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

        <?php } else { ?>

        <div class="alert alert-warning">

            No Backup Files Found.

        </div>

        <?php } ?>

    </div>

</div>

</body>

</html>