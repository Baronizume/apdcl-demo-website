<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$pageTitle = "Restore Database";

$message = "";

/*=========================================
    RESTORE DATABASE
=========================================*/

if(isset($_POST['restore']))
{

    $backupFile = basename($_POST['backup_file']);

    $filePath = "../backups/" . $backupFile;

    if(file_exists($filePath))
    {

        $host     = "localhost";
        $username = "root";
        $password = "";
        $database = "apdcl_demo";

        // Change this if XAMPP is not in C:
        $mysql = "C:\\xampp\\mysql\\bin\\mysql.exe";

        $command = "\"$mysql\" --user=$username --password=$password $database < \"$filePath\"";

        exec($command,$output,$result);

        if($result==0)
        {

            $message = '
            <div class="alert alert-success">

                <strong>Success!</strong><br>

                Database restored successfully.

            </div>';

        }
        else
        {

            $message = '
            <div class="alert alert-danger">

                Database restore failed.

            </div>';

        }

    }

}

/*=========================================
    BACKUP FILES
=========================================*/

$backupFolder = "../backups/";

$backupFiles = [];

if(is_dir($backupFolder))
{

    $backupFiles = glob($backupFolder . "*.sql");

    rsort($backupFiles);

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title><?= $pageTitle ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-danger text-white">

<h3>

<i class="bi bi-arrow-clockwise"></i>

Restore Database

</h3>

</div>

<div class="card-body">

<?= $message ?>

<p>

Select a backup file to restore the database.

</p>

<form method="POST">

<div class="mb-3">

<label class="form-label">

Backup File

</label>

<select
name="backup_file"
class="form-select"
required>

<option value="">

-- Select Backup --

</option>

<?php

foreach($backupFiles as $file)
{

?>

<option value="<?= basename($file) ?>">

<?= basename($file) ?>

</option>

<?php

}

?>

</select>

</div>

<button
type="submit"
name="restore"
class="btn btn-danger"
onclick="return confirm('This will overwrite the current database. Continue?');">

<i class="bi bi-arrow-repeat"></i>

Restore Database

</button>

<a
href="database_backup.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>