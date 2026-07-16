<?php
session_start();

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['role'] != "Super Admin"
){
    header("Location: login.php");
    exit();
}

include("../db.php");

/*====================================================
    PAGE TITLE
====================================================*/

$pageTitle = "System Settings";

/*====================================================
    LOAD SETTINGS
====================================================*/

$settings = [];

$query = mysqli_query($conn,"
SELECT *
FROM settings
LIMIT 1
");

if($query && mysqli_num_rows($query)>0){

    $settings = mysqli_fetch_assoc($query);

}

/*====================================================
    DEFAULT VALUES
====================================================*/

$company_name      = $settings['company_name']      ?? '';
$company_address   = $settings['company_address']   ?? '';
$company_phone     = $settings['company_phone']     ?? '';
$company_email     = $settings['company_email']     ?? '';
$company_website   = $settings['company_website']   ?? '';

$energy_rate       = $settings['energy_rate']       ?? 7.74;
$fixed_charge      = $settings['fixed_charge']      ?? 150;
$electricity_duty  = $settings['electricity_duty']  ?? 5;
$subsidy           = $settings['subsidy']           ?? 10;
$fpppa             = $settings['fpppa']             ?? 0;
$due_days          = $settings['due_days']          ?? 15;

$currency          = $settings['currency']          ?? '₹';
$timezone          = $settings['timezone']          ?? 'Asia/Kolkata';
$date_format       = $settings['date_format']       ?? 'd-m-Y';

$portal_title      = $settings['portal_title']      ?? '';
$footer_text       = $settings['footer_text']       ?? '';
$version           = $settings['version']           ?? '1.0';


/*====================================================
    SAVE SETTINGS
====================================================*/

if(isset($_POST['save']))
{

/*====================================================
    EMAIL SETTINGS
====================================================*/

$smtp_host      = mysqli_real_escape_string($conn,$_POST['smtp_host']);
$smtp_port      = mysqli_real_escape_string($conn,$_POST['smtp_port']);
$smtp_username  = mysqli_real_escape_string($conn,$_POST['smtp_username']);
$smtp_password  = mysqli_real_escape_string($conn,$_POST['smtp_password']);

/*====================================================
    SMS SETTINGS
====================================================*/

$sms_api_key    = mysqli_real_escape_string($conn,$_POST['sms_api_key']);
$sms_sender_id  = mysqli_real_escape_string($conn,$_POST['sms_sender_id']);
$enable_sms     = (int)$_POST['enable_sms'];

/*====================================================
    SECURITY
====================================================*/

$session_timeout   = (int)$_POST['session_timeout'];
$max_login_attempts= (int)$_POST['max_login_attempts'];

/*====================================================
    APPEARANCE
====================================================*/

$portal_theme = mysqli_real_escape_string($conn,$_POST['portal_theme']);
$version      = mysqli_real_escape_string($conn,$_POST['version']);
    $sql = "
    UPDATE settings SET

        smtp_host='$smtp_host',
        smtp_port='$smtp_port',
        smtp_username='$smtp_username',
        smtp_password='$smtp_password',

        sms_api_key='$sms_api_key',
        sms_sender_id='$sms_sender_id',
        enable_sms='$enable_sms',

        session_timeout='$session_timeout',
        max_login_attempts='$max_login_attempts',

        portal_theme='$portal_theme',
        version='$version'

    WHERE id = 1
    ";

    if(mysqli_query($conn,$sql))
    {

        echo "<script>
                alert('System Settings Updated Successfully.');
                window.location='settings.php';
              </script>";

        exit();

    }
    else
    {

        echo "<script>
                alert('Error : ".mysqli_error($conn)."');
              </script>";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>System Settings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:#f4f7fb;

font-family:'Segoe UI',sans-serif;

}

.settings-card{

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.12);

border:none;

margin-bottom:30px;

}

.card-header{

font-weight:600;

font-size:18px;

}

.form-control{

border-radius:10px;

}

.form-select{

border-radius:10px;

}

.btn-save{

padding:12px 40px;

font-size:17px;

border-radius:30px;

}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<h2 class="mb-4">

<i class="bi bi-gear-fill text-primary"></i>

System Settings

</h2>

<form method="POST">

<!-- ====================================== -->
<!-- COMPANY INFORMATION -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-primary text-white">

<i class="bi bi-building"></i>

Company Information

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>Company Name</label>

<input type="text"
class="form-control"
name="company_name"
value="<?= $company_name ?>">

</div>

<div class="col-md-6 mb-3">

<label>Phone</label>

<input type="text"
class="form-control"
name="company_phone"
value="<?= $company_phone ?>">

</div>

<div class="col-md-6 mb-3">

<label>Email</label>

<input type="email"
class="form-control"
name="company_email"
value="<?= $company_email ?>">

</div>

<div class="col-md-6 mb-3">

<label>Website</label>

<input type="text"
class="form-control"
name="company_website"
value="<?= $company_website ?>">

</div>

<div class="col-md-12">

<label>Company Address</label>

<textarea
class="form-control"
rows="3"
name="company_address"><?= $company_address ?></textarea>

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- BILLING SETTINGS -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-success text-white">

<i class="bi bi-lightning-charge-fill"></i>

Billing Settings

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label>Energy Rate</label>

<input type="number"
step="0.01"
class="form-control"
name="energy_rate"
value="<?= $energy_rate ?>">

</div>

<div class="col-md-4 mb-3">

<label>Fixed Charge</label>

<input type="number"
step="0.01"
class="form-control"
name="fixed_charge"
value="<?= $fixed_charge ?>">

</div>

<div class="col-md-4 mb-3">

<label>Electricity Duty (%)</label>

<input type="number"
step="0.01"
class="form-control"
name="electricity_duty"
value="<?= $electricity_duty ?>">

</div>

<div class="col-md-4 mb-3">

<label>Subsidy (%)</label>

<input type="number"
step="0.01"
class="form-control"
name="subsidy"
value="<?= $subsidy ?>">

</div>

<div class="col-md-4 mb-3">

<label>FPPPA (%)</label>

<input type="number"
step="0.01"
class="form-control"
name="fpppa"
value="<?= $fpppa ?>">

</div>

<div class="col-md-4 mb-3">

<label>Due Days</label>

<input type="number"
class="form-control"
name="due_days"
value="<?= $due_days ?>">

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- SYSTEM SETTINGS -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-info text-white">

<i class="bi bi-globe2"></i>

System Settings

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label>Currency</label>

<input type="text"
class="form-control"
name="currency"
value="<?= $currency ?>">

</div>

<div class="col-md-4 mb-3">

<label>Timezone</label>

<input type="text"
class="form-control"
name="timezone"
value="<?= $timezone ?>">

</div>

<div class="col-md-4 mb-3">

<label>Date Format</label>

<input type="text"
class="form-control"
name="date_format"
value="<?= $date_format ?>">

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- EMAIL SETTINGS -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-secondary text-white">

<i class="bi bi-envelope-fill"></i>

SMTP Email Settings

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3 mb-3">

<label>SMTP Host</label>

<input type="text"
class="form-control"
name="smtp_host"
value="<?= $settings['smtp_host'] ?? '' ?>">

</div>

<div class="col-md-3 mb-3">

<label>SMTP Port</label>

<input type="text"
class="form-control"
name="smtp_port"
value="<?= $settings['smtp_port'] ?? '' ?>">

</div>

<div class="col-md-3 mb-3">

<label>SMTP Username</label>

<input type="text"
class="form-control"
name="smtp_username"
value="<?= $settings['smtp_username'] ?? '' ?>">

</div>

<div class="col-md-3 mb-3">

<label>SMTP Password</label>

<input type="password"
class="form-control"
name="smtp_password"
value="<?= $settings['smtp_password'] ?? '' ?>">

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- SMS SETTINGS -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-success text-white">

<i class="bi bi-phone-fill"></i>

SMS Gateway

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>SMS API Key</label>

<input type="text"
class="form-control"
name="sms_api_key"
value="<?= $settings['sms_api_key'] ?? '' ?>">

</div>

<div class="col-md-3 mb-3">

<label>Sender ID</label>

<input type="text"
class="form-control"
name="sms_sender_id"
value="<?= $settings['sms_sender_id'] ?? '' ?>">

</div>

<div class="col-md-3 mb-3">

<label>Enable SMS</label>

<select
class="form-select"
name="enable_sms">

<option value="1"
<?= (($settings['enable_sms'] ?? 0)==1)?'selected':''; ?>>

Enabled

</option>

<option value="0"
<?= (($settings['enable_sms'] ?? 0)==0)?'selected':''; ?>>

Disabled

</option>

</select>

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- SECURITY -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-danger text-white">

<i class="bi bi-shield-lock-fill"></i>

Security

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>Session Timeout (Minutes)</label>

<input type="number"
class="form-control"
name="session_timeout"
value="<?= $settings['session_timeout'] ?? 30 ?>">

</div>

<div class="col-md-6 mb-3">

<label>Maximum Login Attempts</label>

<input type="number"
class="form-control"
name="max_login_attempts"
value="<?= $settings['max_login_attempts'] ?? 5 ?>">

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- APPEARANCE -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-primary text-white">

<i class="bi bi-palette-fill"></i>

Appearance

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>Portal Theme</label>

<select
class="form-select"
name="portal_theme">

<option>Blue</option>

<option>Green</option>

<option>Dark</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Portal Version</label>

<input type="text"
class="form-control"
name="version"
value="<?= $version ?>">

</div>

</div>

</div>

</div>

<!-- ====================================== -->
<!-- DATABASE BACKUP -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-dark text-white">

<i class="bi bi-database-fill"></i>

Database Backup

</div>

<div class="card-body text-center">

<a href="backup_database.php"
class="btn btn-success">

<i class="bi bi-download"></i>

Download Backup

</a>

<a href="restore_database.php"
class="btn btn-warning">

<i class="bi bi-upload"></i>

Restore Backup

</a>

</div>

</div>

<!-- ====================================== -->
<!-- PORTAL SETTINGS -->
<!-- ====================================== -->

<div class="card settings-card">

<div class="card-header bg-warning">

<i class="bi bi-display"></i>

Portal Settings

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label>Portal Title</label>

<input type="text"
class="form-control"
name="portal_title"
value="<?= $portal_title ?>">

</div>

<div class="col-md-6 mb-3">

<label>Footer Text</label>

<input type="text"
class="form-control"
name="footer_text"
value="<?= $footer_text ?>">

</div>

</div>

</div>

</div>

<div class="text-center mb-4">

    <button
        type="submit"
        name="save"
        class="btn btn-primary btn-lg px-5">

        <i class="bi bi-check-circle-fill"></i>

        Save Settings

    </button>

</div>

<div class="mb-3">

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Back to Dashboard

    </a>

</div>

</div>

</form>

</div>

</body>

</html>