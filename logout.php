<?php
session_start();

/* Destroy all session data */

$_SESSION = [];

session_unset();

session_destroy();

/* Prevent browser cache */

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* Redirect to Consumer Login */

header("Location: login.php");
exit();
?>