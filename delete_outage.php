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

/*=========================================
    CHECK RECORD
=========================================*/

$check=mysqli_prepare($conn,"
SELECT id
FROM outages
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param(
    $check,
    "i",
    $id
);

mysqli_stmt_execute($check);

$result=mysqli_stmt_get_result($check);

if(mysqli_num_rows($result)==0)
{

    $_SESSION['error']="Outage record not found.";

    header("Location: outage_map.php");
    exit();

}

/*=========================================
    DELETE RECORD
=========================================*/

$delete=mysqli_prepare($conn,"
DELETE FROM outages
WHERE id=?
");

mysqli_stmt_bind_param(
    $delete,
    "i",
    $id
);

if(mysqli_stmt_execute($delete))
{

    $_SESSION['success']="Outage deleted successfully.";

}
else
{

    $_SESSION['error']="Unable to delete outage.";

}

/*=========================================
    REDIRECT
=========================================*/

header("Location: outage_map.php");
exit();

?>