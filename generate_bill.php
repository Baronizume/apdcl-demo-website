<?php
session_start();
include("../db.php");

/*=========================================
    LOGIN CHECK
=========================================*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/*=========================================
    LOGGED IN ADMIN
    (uses a prepared statement instead of
    string-interpolated SQL)
=========================================*/
$admin_username = $_SESSION['admin'];

$adminStmt = mysqli_prepare($conn, "
    SELECT *
    FROM admin
    WHERE username = ?
    LIMIT 1
");
mysqli_stmt_bind_param($adminStmt, "s", $admin_username);
mysqli_stmt_execute($adminStmt);
$adminResult = mysqli_stmt_get_result($adminStmt);

if (mysqli_num_rows($adminResult) == 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$admin = mysqli_fetch_assoc($adminResult);
mysqli_stmt_close($adminStmt);

$admin_name   = $admin['name'];
$role         = $admin['role'];
$zone         = $admin['zone'];
$circle       = $admin['circle'];
$sub_division = $admin['sub_division'];

/*=========================================
    TARIFF / BILLING SETTINGS
    (kept together so rates can be changed
    in one place)
=========================================*/
$rate          = 7.74;
$fixed_charge  = 150.00;
$fixed_rate    = 70.00;
$fpppa_percent = 2.34;
$energy_rate   = $rate; // alias kept for clarity in the insert below

$message = "";

/*=========================================
    DEFAULT FORM / BILL VALUES
    Grouped into a single array so the
    "reset form" step later doesn't have to
    repeat this whole block.
=========================================*/
function default_bill_values($sub_division) {
    return [
        // consumer info
        'consumer_no'       => "",
        'consumer_name'     => "",
        'father_name'       => "",
        'mobile'            => "",
        'address'           => "",
        'meter_no'          => "",
        'category'          => "Domestic",

        // location
        'dtr_no'            => "",
        'pole_no'           => "",

        // billing period
        'month'             => date("Y-m"),
        'previous_reading'  => "",
        'current_reading'   => "",

        // computed
        'units'             => 0,
        'energy_charge'     => 0,
        'electricity_duty'  => 0,
        'subsidy'           => 0,
        'total_bill'        => 0,

        // demand / meter info
        'recorded_demand'   => 0,
        'maximum_demand'    => 0,
        'solar_adjusted'    => 0,
        'power_factor'      => 94,
        'division'          => $sub_division,
        'sanction_load'     => 0,
        'connected_load'    => 0,
        'contract_demand'   => 0,
        'supply_type'       => "LT",
        'meter_status'      => "Active",
        'billing_status'    => "Normal",
        'mf'                => 1,
        'current_demand'    => 0,

        // adjustments
        'outstanding_amount' => 0,
        'adjustment_amount'  => 0,
        'government_subsidy' => 0,
        'solar_rebate'        => 0,

        // tariff
        'supply_voltage'    => "LT",
        'tariff_category'   => "LT III Domestic B",
        'fpppa_charge'      => 0,
        'tariff_subsidy'    => 0,

        // arrears
        'area_principal'    => 0,
        'area_surcharge'    => 0,
        'current_surcharge' => 0,

        // payable
        'payable_before_due' => 0,
        'payable_after_due'  => 0,
        'amount_in_words'    => "",

        // meta
        'payment_mode'      => "Pending",
        'receipt_no'        => NULL,
        'users'             => 0,
    ];
}

extract(default_bill_values($sub_division));

/*=========================================
    AUTO BILL NUMBER / DATES
=========================================*/
$bill_no  = "APDCL" . date("YmdHis");
$bill_date = date("Y-m-d H:i:s");
$due_date  = date("Y-m-d", strtotime("+15 days"));

/*=========================================
    BILL PERIOD HELPERS
=========================================*/
function bill_period($month) {
    return [
        'from' => date("Y-m-01", strtotime($month)),
        'to'   => date("Y-m-t", strtotime($month)),
        'days' => (int) date("t", strtotime($month)),
    ];
}

$period            = bill_period($month);
$bill_period_from  = $period['from'];
$bill_period_to    = $period['to'];
$billing_days      = $period['days'];

/*=========================================
    EDIT MODE
    (uses a prepared statement; the old
    (int) cast was already safe, but this
    keeps the whole file consistent)
=========================================*/
if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $editStmt = mysqli_prepare($conn, "
        SELECT *
        FROM bills
        WHERE id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($editStmt, "i", $id);
    mysqli_stmt_execute($editStmt);
    $editResult = mysqli_stmt_get_result($editStmt);

    if (mysqli_num_rows($editResult) > 0) {

        $bill = mysqli_fetch_assoc($editResult);

        $consumer_no      = $bill['consumer_no'];
        $consumer_name    = $bill['consumer_name'];
        $father_name      = $bill['father_name'];
        $mobile           = $bill['mobile'];
        $address          = $bill['address'];
        $meter_no         = $bill['meter_no'];
        $category         = $bill['category'];

        $dtr_no           = $bill['dtr_no'];
        $pole_no          = $bill['pole_no'];

        $zone             = $bill['zone'];
        $circle           = $bill['circle'];
        $sub_division     = $bill['sub_division'];

        $month            = $bill['month'];

        $previous_reading = $bill['previous_reading'];
        $current_reading  = $bill['current_reading'];

        $units            = $bill['units'];

        $energy_charge    = $bill['energy_charge'];
        $electricity_duty = $bill['electricity_duty'];
        $subsidy          = $bill['subsidy'];
        $total_bill       = $bill['total_bill'];
    }

    mysqli_stmt_close($editStmt);
}

/*=========================================
    SAVE BILL
=========================================*/
if (isset($_POST['save'])) {

    /*==============================
        FORM VALUES
    ==============================*/
    $consumer_no   = trim($_POST['consumer_no']);
    $month         = trim($_POST['month']);
    $consumer_name = trim($_POST['consumer_name']);
    $father_name   = trim($_POST['father_name']);
    $mobile        = trim($_POST['mobile']);
    $address       = trim($_POST['address']);
    $meter_no      = trim($_POST['meter_no']);
    $category      = trim($_POST['category']);
    $dtr_no        = trim($_POST['dtr_no']);
    $pole_no       = trim($_POST['pole_no']);
    $zone          = trim($_POST['zone']);
    $circle        = trim($_POST['circle']);
    $sub_division  = trim($_POST['sub_division']);
    $division      = $sub_division;

    $previous_reading = (float) $_POST['previous_reading'];
    $current_reading  = (float) $_POST['current_reading'];

    /*==============================
        BILL PERIOD
    ==============================*/
    $period           = bill_period($month);
    $bill_period_from = $period['from'];
    $bill_period_to   = $period['to'];
    $billing_days     = $period['days'];

    /*==============================
        VALIDATION
    ==============================*/
    if (empty($consumer_no) || empty($consumer_name) || empty($month)) {

        $message = '
        <div class="alert alert-danger">
            Please fill all mandatory fields.
        </div>';

    } elseif ($current_reading < $previous_reading) {

        $message = '
        <div class="alert alert-danger">
            Current Reading cannot be smaller than Previous Reading.
        </div>';

    } else {

        /*==============================
            DUPLICATE BILL CHECK
            (now via a prepared statement)
        ==============================*/
        $checkStmt = mysqli_prepare($conn, "
            SELECT id
            FROM bills
            WHERE consumer_no = ?
            AND month = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($checkStmt, "ss", $consumer_no, $month);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {

            $message = '
            <div class="alert alert-danger">
                Bill already generated for this month.
            </div>';

        } else {

            /*==============================
                BILL CALCULATION
            ==============================*/
            $units = $current_reading - $previous_reading;

            if ($units < 0) {
                $units = 0;
            }

            $energy_charge    = $units * $energy_rate;
            $electricity_duty = $energy_charge * 0.05;
            $subsidy          = $energy_charge * 0.10;

            $total_bill = $energy_charge + $fixed_charge + $electricity_duty - $subsidy;

            $payable_before_due = $total_bill;
            $payable_after_due  = $total_bill;

            $amount_in_words = number_format($total_bill, 2) . " Rupees Only";

            $status = "Pending";

            /*==============================
                INSERT BILL
            ==============================*/
            $stmt = mysqli_prepare($conn, "
                INSERT INTO bills
                (
                    bill_no, consumer_no, consumer_name, father_name,
                    previous_reading, current_reading,
                    recorded_demand, maximum_demand, solar_adjusted, power_factor,
                    division, sanction_load, connected_load, contract_demand,
                    supply_type, meter_status, billing_status,
                    mf, current_demand,
                    outstanding_amount, adjustment_amount, government_subsidy, solar_rebate,
                    month, bill_period_from, bill_period_to, billing_days,
                    supply_voltage, tariff_category,
                    units, energy_charge, energy_rate,
                    fixed_charge, fixed_rate,
                    fpppa_charge, fpppa_percent,
                    electricity_duty, subsidy, tariff_subsidy,
                    area_principal, area_surcharge, current_surcharge,
                    payable_before_due, payable_after_due,
                    amount_in_words,
                    total_bill,
                    status, generated_by, bill_date, due_date,
                    payment_mode, receipt_no,
                    users,
                    mobile, address, meter_no, category,
                    dtr_no, pole_no,
                    zone, circle, sub_division
                )
                VALUES
                (
                    ?,?,?,?,
                    ?,?,
                    ?,?,?,?,
                    ?,?,?,?,
                    ?,?,?,
                    ?,?,
                    ?,?,?,?,
                    ?,?,?,?,
                    ?,?,
                    ?,?,?,
                    ?,?,
                    ?,?,
                    ?,?,?,
                    ?,?,?,
                    ?,?,
                    ?,
                    ?,
                    ?,?,?,?,
                    ?,?,
                    ?,
                    ?,?,?,?,
                    ?,?,
                    ?,?,?
                )
            ");

            $generated_by = $admin_name;

            mysqli_stmt_bind_param(
                $stmt,

                "ssss" .      // bill_no, consumer_no, consumer_name, father_name
                "dd" .        // previous_reading, current_reading
                "dddd" .      // recorded_demand, maximum_demand, solar_adjusted, power_factor
                "sddd" .      // division, sanction_load, connected_load, contract_demand
                "sss" .       // supply_type, meter_status, billing_status
                "dd" .        // mf, current_demand
                "dddd" .      // outstanding_amount, adjustment_amount, government_subsidy, solar_rebate
                "sssi" .      // month, bill_period_from, bill_period_to, billing_days
                "ss" .        // supply_voltage, tariff_category
                "ddd" .       // units, energy_charge, energy_rate
                "dd" .        // fixed_charge, fixed_rate
                "dd" .        // fpppa_charge, fpppa_percent
                "ddd" .       // electricity_duty, subsidy, tariff_subsidy
                "ddd" .       // area_principal, area_surcharge, current_surcharge
                "dd" .        // payable_before_due, payable_after_due
                "s" .         // amount_in_words
                "d" .         // total_bill
                "ssss" .      // status, generated_by, bill_date, due_date
                "ss" .        // payment_mode, receipt_no
                "i" .         // users
                "ssss" .      // mobile, address, meter_no, category
                "ss" .        // dtr_no, pole_no
                "sss",        // zone, circle, sub_division

                $bill_no, $consumer_no, $consumer_name, $father_name,
                $previous_reading, $current_reading,
                $recorded_demand, $maximum_demand, $solar_adjusted, $power_factor,
                $division, $sanction_load, $connected_load, $contract_demand,
                $supply_type, $meter_status, $billing_status,
                $mf, $current_demand,
                $outstanding_amount, $adjustment_amount, $government_subsidy, $solar_rebate,
                $month, $bill_period_from, $bill_period_to, $billing_days,
                $supply_voltage, $tariff_category,
                $units, $energy_charge, $energy_rate,
                $fixed_charge, $fixed_rate,
                $fpppa_charge, $fpppa_percent,
                $electricity_duty, $subsidy, $tariff_subsidy,
                $area_principal, $area_surcharge, $current_surcharge,
                $payable_before_due, $payable_after_due,
                $amount_in_words,
                $total_bill,
                $status, $generated_by, $bill_date, $due_date,
                $payment_mode, $receipt_no,
                $users,
                $mobile, $address, $meter_no, $category,
                $dtr_no, $pole_no,
                $zone, $circle, $sub_division
            );

            /*=================================
                EXECUTE INSERT
            =================================*/
            if (mysqli_stmt_execute($stmt)) {

                $message = '
                <div class="alert alert-success">
                    <strong>Success!</strong><br>
                    Electricity Bill Generated Successfully.
                </div>';

                /* Reset form back to defaults */
                extract(default_bill_values($sub_division));

            } else {

                $message = '
                <div class="alert alert-danger">
                    <strong>Database Error:</strong><br>'
                    . mysqli_error($conn) .
                '</div>';
            }

            mysqli_stmt_close($stmt);

        } // END duplicate bill check

        mysqli_stmt_close($checkStmt);

    } // END validation else

} // END SAVE BILL

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Generate Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

/*======================
NAVBAR
=======================*/

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    height:75px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
}

.logo{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#fff;
    padding:5px;
    margin-right:12px;
}

.brand-title{
    font-size:22px;
    font-weight:700;
    color:#fff;
    margin:0;
}

.brand-sub{
    font-size:12px;
    color:#dbeafe;
}

/*======================
SIDEBAR
=======================*/

.sidebar{
    position:fixed;
    top:75px;
    left:0;
    width:250px;
    height:100%;
    background:#083b8a;
    overflow:auto;
}

.sidebar a{
    display:flex;
    align-items:center;
    padding:15px 22px;
    color:#fff;
    text-decoration:none;
    transition:.3s;
    border-left:4px solid transparent;
}

.sidebar a:hover{
    background:#1565c0;
    border-left:4px solid gold;
    padding-left:28px;
}

.sidebar a.active{
    background:#1976d2;
    border-left:4px solid gold;
}

.sidebar i{
    font-size:20px;
    margin-right:12px;
}

/*======================
CONTENT
=======================*/

.content{
    margin-left:260px;
    margin-top:90px;
    padding:25px;
}

/*======================
CARDS
=======================*/

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 6px 15px rgba(0,0,0,.08);
    margin-bottom:25px;
}

.card-header{
    font-weight:600;
    font-size:18px;
}

.form-control,
.form-select{
    height:48px;
    border-radius:10px;
}

textarea.form-control{
    height:auto;
}

.btn{
    border-radius:10px;
    font-weight:600;
}

</style>

</head>

<body>

<!--=========================
NAVBAR
==========================-->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo">

<div>

<h4 class="brand-title">
APDCL Electricity Billing System
</h4>

<div class="brand-sub">
Assam Power Distribution Company Limited
</div>

</div>

</div>

<div class="text-white text-end">

<b><?= htmlspecialchars($admin_name) ?></b>

<br>

<small><?= htmlspecialchars($role) ?></small>

</div>

</div>

</nav>

<!--=========================
SIDEBAR
==========================-->

<div class="sidebar">

<a href="dashboard.php">
<i class="bi bi-speedometer2"></i>
Dashboard
</a>

<a href="manage_consumers.php">
<i class="bi bi-people-fill"></i>
Consumers
</a>

<a href="generate_bill.php" class="active">
<i class="bi bi-receipt-cutoff"></i>
Generate Bill
</a>

<a href="manage_bills.php">
<i class="bi bi-journal-text"></i>
Bills
</a>

<a href="complaints.php">
<i class="bi bi-chat-left-text-fill"></i>
Complaints
</a>

<a href="reports.php">
<i class="bi bi-bar-chart-fill"></i>
Reports
</a>

<a href="logout.php">
<i class="bi bi-box-arrow-right"></i>
Logout
</a>

</div>

<!--=========================
CONTENT
==========================-->

<div class="content">

<?= $message ?>

<form method="POST">

<div class="row">

<!-- ================= Consumer Details ================= -->

<div class="col-lg-6">

    <div class="card">

        <div class="card-header bg-primary text-white">

            <i class="bi bi-person-vcard-fill"></i>
            Consumer Details

        </div>

        <div class="card-body">

            <div class="mb-3">

                <label class="form-label">
                    Consumer Number
                </label>

                <input
                    type="text"
                    name="consumer_no"
                    class="form-control"
                    value="<?= htmlspecialchars($consumer_no) ?>"
                    placeholder="Enter Consumer Number"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Consumer Name
                </label>

                <input
                    type="text"
                    name="consumer_name"
                    class="form-control"
                    value="<?= htmlspecialchars($consumer_name) ?>"
                    placeholder="Enter Consumer Name"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Father / Husband Name
                </label>

                <input
                    type="text"
                    name="father_name"
                    class="form-control"
                    value="<?= htmlspecialchars($father_name) ?>"
                    placeholder="Enter Father / Husband Name">

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Mobile Number
                </label>

                <input
                    type="text"
                    name="mobile"
                    class="form-control"
                    maxlength="10"
                    value="<?= htmlspecialchars($mobile) ?>"
                    placeholder="Enter Mobile Number">

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Address
                </label>

                <textarea
                name="address"
                rows="3"
                class="form-control"
                placeholder="Enter Address"><?= htmlspecialchars($address) ?></textarea>

            </div>

            <div class="row">

                <div class="col-md-6">

                    <label class="form-label">
                        Meter Number
                    </label>

                    <input
                        type="text"
                        name="meter_no"
                        class="form-control"
                        value="<?= htmlspecialchars($meter_no) ?>"
                        placeholder="Enter Meter Number">

                </div>

                <div class="col-md-6">

                    <select
                        name="category"
                        class="form-select">

                        <option value="Domestic" <?=($category=="Domestic")?"selected":"";?>>Domestic</option>

                        <option value="Commercial" <?=($category=="Commercial")?"selected":"";?>>Commercial</option>

                        <option value="Industrial" <?=($category=="Industrial")?"selected":"";?>>Industrial</option>

                        <option value="Government" <?=($category=="Government")?"selected":"";?>>Government</option>

                    </select>

                </div>

            </div>

            <hr>

            <div class="row mt-3">

                <div class="col-md-6">

                    <label class="form-label">
                        DTR Number
                    </label>

                    <input
                        type="text"
                        name="dtr_no"
                        class="form-control"
                        value="<?= htmlspecialchars($dtr_no) ?>"
                        placeholder="Enter DTR Number">

                </div>

                <div class="col-md-6">

                    <label class="form-label">
                        Pole Number
                    </label>

                  <input
                    type="text"
                    name="pole_no"
                    class="form-control"
                    value="<?= htmlspecialchars($pole_no) ?>"
                    placeholder="Enter Pole Number">

                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Zone
                    </label>

                 <input
                    type="text"
                    name="zone"
                    class="form-control"
                    value="<?= htmlspecialchars($zone) ?>"
                    placeholder="Enter Zone">
                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Circle
                    </label>

                    <input
                        type="text"
                        name="circle"
                        class="form-control"
                        value="<?= htmlspecialchars($circle) ?>"
                        placeholder="Enter Circle">

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Sub Division
                    </label>

                    <input
                        type="text"
                        name="sub_division"
                        class="form-control"
                        value="<?= htmlspecialchars($sub_division) ?>"
                        placeholder="Enter Sub Division">

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= Bill Details ================= -->

<div class="col-lg-6">

    <div class="card">

        <div class="card-header bg-success text-white">

            <i class="bi bi-lightning-charge-fill"></i>

            Bill Details

        </div>

        <div class="card-body">

            <div class="mb-3">

                <label class="form-label">

                    Bill Number

                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= $bill_no ?>"
                    readonly>

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Billing Month

                </label>

                <input
                    type="month"
                    name="month"
                    class="form-control"
                    value="<?= $month ?>"
                    required>

            </div>

            <div class="row">

                <div class="col-md-6">

                    <label class="form-label">

                        Previous Reading

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="previous_reading"
                        id="previous_reading"
                        class="form-control"
                        value="<?= $previous_reading ?>"
                        required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">

                        Current Reading

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="current_reading"
                        id="current_reading"
                        class="form-control"
                        value="<?= $current_reading ?>"
                        required>

                </div>

            </div>

            <div class="mt-3">

                <label class="form-label">

                    Units Consumed

                </label>

                <input
                    type="text"
                    name="units"
                    id="units"
                    class="form-control bg-light fw-bold"
                    value="<?= $units ?>"
                    readonly>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-6">

                    <label class="form-label">

                        Energy Rate

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="₹ <?= number_format($energy_rate,2) ?>/Unit"
                        readonly>

                </div>

                <div class="col-md-6">

                    <label class="form-label">

                        Fixed Charge

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="₹ <?= number_format($fixed_charge,2) ?>"
                        readonly>

                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-6">

                    <label class="form-label">

                        Due Date

                    </label>

                    <input
                        type="date"
                        class="form-control"
                        value="<?= $due_date ?>"
                        readonly>

                </div>

                <div class="col-md-6">

                    <label class="form-label">

                        Bill Date

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= date('d-m-Y', strtotime($bill_date)) ?>"
                        readonly>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

<!-- ================= Bill Calculation ================= -->

<div class="card">

    <div class="card-header bg-warning">

        <i class="bi bi-calculator-fill"></i>

        Bill Calculation

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">

                <label class="form-label">
                    Energy Charge
                </label>

                <input
                    type="text"
                    id="energy_charge"
                    class="form-control"
                    value="<?= number_format($energy_charge,2) ?>"
                    readonly>

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    Electricity Duty (5%)
                </label>

                <input
                    type="text"
                    id="electricity_duty"
                    class="form-control"
                    value="<?= number_format($electricity_duty,2) ?>"
                    readonly>

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    Subsidy (10%)
                </label>

                <input
                    type="text"
                    id="subsidy"
                    class="form-control"
                    value="<?= number_format($subsidy,2) ?>"
                    readonly>

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    Total Bill
                </label>

                <input
                    type="text"
                    id="total_bill"
                    class="form-control fw-bold text-danger"
                    value="<?= number_format($total_bill,2) ?>"
                    readonly>

            </div>

        </div>

    </div>

</div>

<div class="text-center mt-4 mb-5">

<?php if(isset($_GET['edit'])){ ?>

    <button
        type="submit"
        name="update"
        class="btn btn-warning btn-lg px-5">

        <i class="bi bi-pencil-square"></i>

        Update Bill

    </button>

<?php } else { ?>

    <button
        type="submit"
        name="save"
        class="btn btn-success btn-lg px-5">

        <i class="bi bi-lightning-charge-fill"></i>

        Generate Bill

    </button>

<?php } ?>

<a
href="manage_bills.php"
class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-journal-text"></i>

Manage Bills

</a>

<a
href="dashboard.php"
class="btn btn-primary btn-lg ms-2">

<i class="bi bi-house-door-fill"></i>

Dashboard

</a>

</div>


</form>

<footer class="mt-5 p-4 bg-white rounded shadow-sm">

<div class="row">

<div class="col-md-6">

<strong>

APDCL Electricity Billing Management System

</strong>

<br>

<small>

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

<strong>

Logged in as

</strong>

<?= htmlspecialchars($admin_name); ?>

<br>

<small>

<?= htmlspecialchars($role); ?>

</small>

</div>

</div>

<hr>

<div class="d-flex justify-content-between">

<span>

© <?= date("Y"); ?>

APDCL. All Rights Reserved.

</span>

<span id="clock" class="fw-bold text-primary"></span>

</div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

/*=========================================
    BILL CALCULATION
=========================================*/

function calculateBill(){

    let previous = parseFloat(document.getElementById("previous_reading").value) || 0;
    let current  = parseFloat(document.getElementById("current_reading").value) || 0;

    let units = current - previous;

    if(units < 0){
        units = 0;
    }

    document.getElementById("units").value = units.toFixed(2);

    let rate  = <?= $energy_rate ?>;
    let fixed = <?= $fixed_charge ?>;

    let energy  = units * rate;
    let duty    = energy * 0.05;
    let subsidy = energy * 0.10;
    let total   = energy + fixed + duty - subsidy;

    document.getElementById("energy_charge").value = energy.toFixed(2);
    document.getElementById("electricity_duty").value = duty.toFixed(2);
    document.getElementById("subsidy").value = subsidy.toFixed(2);
    document.getElementById("total_bill").value = total.toFixed(2);
}

/*=========================================
    AUTO FETCH CONSUMER
=========================================*/

const consumerField = document.getElementById("consumer_no");

if (consumerField) {

    consumerField.addEventListener("change", loadConsumer);
    consumerField.addEventListener("blur", loadConsumer);

}

function loadConsumer(){

    let consumer = consumerField.value.trim();

    if(consumer === "") return;

    fetch("fetch_consumer.php?consumer_no=" + encodeURIComponent(consumer))
    .then(response => response.json())
    .then(data => {

        if(data.error){
            alert(data.error);
            return;
        }

        document.getElementById("consumer_name").value = data.consumer_name || "";
        document.getElementById("father_name").value = data.father_name || "";
        document.getElementById("mobile").value = data.mobile || "";
        document.getElementById("address").value = data.address || "";
        document.getElementById("meter_no").value = data.meter_no || "";
        document.getElementById("category").value = data.category || "";
        document.getElementById("dtr_no").value = data.dtr_no || "";
        document.getElementById("pole_no").value = data.pole_no || "";
        document.getElementById("zone").value = data.zone || "";
        document.getElementById("circle").value = data.circle || "";
        document.getElementById("sub_division").value = data.sub_division || "";
        document.getElementById("previous_reading").value = data.previous_reading || 0;

        calculateBill();

    })
    .catch(error => {
        console.error("Fetch Error:", error);
    });

}

/*=========================================
    EVENTS
=========================================*/

document.getElementById("previous_reading").addEventListener("keyup", calculateBill);
document.getElementById("previous_reading").addEventListener("change", calculateBill);

document.getElementById("current_reading").addEventListener("keyup", calculateBill);
document.getElementById("current_reading").addEventListener("change", calculateBill);

/*=========================================
    LIVE CLOCK
=========================================*/

function updateClock(){

    let now = new Date();

    let date = now.toLocaleDateString("en-IN",{
        weekday:"short",
        day:"2-digit",
        month:"short",
        year:"numeric"
    });

    let time = now.toLocaleTimeString("en-IN");

    document.getElementById("clock").innerHTML = date + " | " + time;

}

updateClock();
setInterval(updateClock,1000);

/*=========================================
    PAGE LOAD
=========================================*/

window.onload = function(){

    calculateBill();

};

</script>

</body>

</html>