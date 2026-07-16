<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

require_once("../tcpdf/tcpdf.php");
include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    CHECK BILL ID
=========================================*/

if(!isset($_GET['id']))
{
    die("Invalid Bill.");
}

$billId = (int)$_GET['id'];

/*=========================================
    FETCH BILL
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='$billId'
AND consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($billQuery)==0)
{
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($billQuery);

/*=========================================
    FETCH CONSUMER
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    CALCULATIONS
=========================================*/

$units = $bill['current_reading'] - $bill['previous_reading'];

$totalBill = $bill['total_bill'];

function numberToWords($number)
{
    $fmt = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    return ucwords($fmt->format(round($number)));
}

$amountWords = numberToWords($totalBill);

/*=========================================
    CREATE PDF
=========================================*/

$pdf = new TCPDF(
    PDF_PAGE_ORIENTATION,
    PDF_UNIT,
    PDF_PAGE_FORMAT,
    true,
    'UTF-8',
    false
);

$pdf->SetCreator('APDCL');
$pdf->SetAuthor('APDCL');
$pdf->SetTitle('Electricity Bill');
$pdf->SetSubject('Consumer Bill');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10,10,10);

$pdf->AddPage();

/*=========================================
    PDF CONTENT
=========================================*/

$html = '

<style>

body{
    font-family:helvetica;
    font-size:10pt;
}

table{
    border-collapse:collapse;
}

th{
    background-color:#0d6efd;
    color:white;
    font-weight:bold;
}

td{
    padding:5px;
}

.title{
    font-size:22pt;
    font-weight:bold;
    color:#0056b3;
}

.subtitle{
    font-size:11pt;
    color:#444;
}

.section{
    background:#eeeeee;
    font-weight:bold;
    padding:6px;
}

</style>

<table width="100%" border="0">

<tr>

<td width="15%" align="center">

<img src="../assets/images/logo-circle.png" width="70">

</td>

<td width="65%" align="center">

<span class="title">

ASSAM POWER DISTRIBUTION COMPANY LIMITED

</span>

<br>

<span class="subtitle">

Official Electricity Bill

</span>

<br>

GSTIN : 18AAFCA4973J1ZX

</td>

<td width="20%" align="right">

<b>Bill No</b><br>

'.$bill['bill_no'].'

<br><br>

<b>Bill Date</b><br>

'.date("d-m-Y",strtotime($bill['bill_date'])).'

</td>

</tr>

</table>

<br>

<table border="1" cellpadding="6" width="100%">

<tr>

<td colspan="4" class="section">

CONSUMER DETAILS

</td>

</tr>

<tr>

<td width="20%"><b>Consumer No</b></td>

<td width="30%">'.$user['consumer_no'].'</td>

<td width="20%"><b>Meter No</b></td>

<td width="30%">'.$user['meter_no'].'</td>

</tr>

<tr>

<td><b>Name</b></td>

<td>'.$user['name'].'</td>

<td><b>Category</b></td>

<td>'.$bill['category'].'</td>

</tr>

<tr>

<td><b>Mobile</b></td>

<td>'.$user['mobile'].'</td>

<td><b>Supply Type</b></td>

<td>'.$bill['supply_type'].'</td>

</tr>

<tr>

<td><b>Email</b></td>

<td>'.$user['email'].'</td>

<td><b>Status</b></td>

<td>'.$bill['status'].'</td>

</tr>

<tr>

<td><b>Address</b></td>

<td colspan="3">

'.$user['address'].'

</td>

</tr>

<tr>

<td><b>Zone</b></td>

<td>'.$user['zone'].'</td>

<td><b>Circle</b></td>

<td>'.$user['circle'].'</td>

</tr>

<tr>

<td><b>Sub Division</b></td>

<td>'.$user['sub_division'].'</td>

<td><b>DTR No</b></td>

<td>'.$bill['dtr_no'].'</td>

</tr>

</table>

<br>

<table border="1" cellpadding="6" width="100%">

<tr>

<td colspan="4" class="section">

BILL INFORMATION

</td>

</tr>

<tr>

<td width="20%"><b>Billing Month</b></td>

<td width="30%">'.$bill['month'].'</td>

<td width="20%"><b>Due Date</b></td>

<td width="30%">

'.date("d-m-Y",strtotime($bill['due_date'])).'

</td>

</tr>

<tr>

<td><b>Bill Period</b></td>

<td>

'.date("d-m-Y",strtotime($bill['bill_period_from'])).'

 -

'.date("d-m-Y",strtotime($bill['bill_period_to'])).'

</td>

<td><b>Payment Mode</b></td>

<td>'.$bill['payment_mode'].'</td>

</tr>

</table>

<br>

<table border="1" cellpadding="6" width="100%">

<tr>

<th width="25%">

Previous Reading

</th>

<th width="25%">

Current Reading

</th>

<th width="25%">

Units

</th>

<th width="25%">

MF

</th>

</tr>

<tr>

<td align="center">

'.$bill['previous_reading'].'

</td>

<td align="center">

'.$bill['current_reading'].'

</td>

<td align="center">

'.$units.'

</td>

<td align="center">

'.$bill['mf'].'

</td>

</tr>

</table>

<br>

';
$html .= '

<table border="1" cellpadding="6" width="100%">

<tr>

<td colspan="2" class="section">

BILL CHARGES

</td>

</tr>

<tr>

<th width="70%">

Description

</th>

<th width="30%" align="right">

Amount (₹)

</th>

</tr>

<tr>

<td>

Energy Charge

</td>

<td align="right">

'.number_format($bill['energy_charge'],2).'

</td>

</tr>

<tr>

<td>

Fixed Charge

</td>

<td align="right">

'.number_format($bill['fixed_charge'],2).'

</td>

</tr>

<tr>

<td>

Electricity Duty

</td>

<td align="right">

'.number_format($bill['electricity_duty'],2).'

</td>

</tr>

<tr>

<td>

FPPPA Charge

</td>

<td align="right">

'.number_format($bill['fpppa_charge'],2).'

</td>

</tr>

<tr>

<td>

Outstanding Amount

</td>

<td align="right">

'.number_format($bill['outstanding_amount'],2).'

</td>

</tr>

<tr>

<td>

Adjustment Amount

</td>

<td align="right">

'.number_format($bill['adjustment_amount'],2).'

</td>

</tr>

<tr>

<td>

Government Subsidy

</td>

<td align="right">

- '.number_format($bill['government_subsidy'],2).'

</td>

</tr>

<tr>

<td>

Tariff Subsidy

</td>

<td align="right">

- '.number_format($bill['tariff_subsidy'],2).'

</td>

</tr>

<tr>

<td>

Solar Rebate

</td>

<td align="right">

- '.number_format($bill['solar_rebate'],2).'

</td>

</tr>

<tr>

<td>

Other Subsidy

</td>

<td align="right">

- '.number_format($bill['subsidy'],2).'

</td>

</tr>

<tr style="font-weight:bold;background-color:#eeeeee;">

<td>

TOTAL BILL AMOUNT

</td>

<td align="right">

₹ '.number_format($bill['total_bill'],2).'

</td>

</tr>

</table>

<br><br>

<table border="1" cellpadding="6" width="100%">

<tr>

<td colspan="2" class="section">

PAYMENT SUMMARY

</td>

</tr>

<tr>

<td width="60%">

Amount Payable Before Due Date

</td>

<td width="40%" align="right">

₹ '.number_format($bill['payable_before_due'],2).'

</td>

</tr>

<tr>

<td>

Amount Payable After Due Date

</td>

<td align="right">

₹ '.number_format($bill['payable_after_due'],2).'

</td>

</tr>

<tr>

<td>

Payment Status

</td>

<td align="right">

'.$bill['status'].'

</td>

</tr>

<tr>

<td>

Payment Mode

</td>

<td align="right">

'.$bill['payment_mode'].'

</td>

</tr>

<tr>

<td>

Due Date

</td>

<td align="right">

'.date("d-m-Y",strtotime($bill['due_date'])).'

</td>

</tr>

</table>

<br><br>

<table border="0" width="100%">

<tr>

<td width="70%">

<b>

Amount in Words

</b>

<br><br>

'.$amountWords.' Rupees Only

</td>

<td width="30%" align="right">

<h2>

₹ '.number_format($bill['total_bill'],2).'

</h2>

</td>

</tr>

</table>

<br><br>

<hr>

<h3>

DIGITAL PAYMENT

</h3>

<table border="1" cellpadding="8" width="100%">

<tr>

<td width="30%" align="center">

QR CODE

<br><br>

(Scan using any UPI App)

</td>

<td width="70%">

<b>Consumer Number :</b>

'.$user['consumer_no'].'

<br><br>

<b>Bill Number :</b>

'.$bill['bill_no'].'

<br><br>

<b>Total Amount :</b>

₹ '.number_format($bill['total_bill'],2).'

<br><br>

Accepted Payment Methods

<br>

• UPI

<br>

• PhonePe

<br>

• Google Pay

<br>

• Paytm

<br>

• Net Banking

<br>

• Debit / Credit Card

</td>

</tr>

</table>

<br><br>

';
$html .= '

<hr>

<h3>IMPORTANT INFORMATION</h3>

<ul>

<li>Pay your electricity bill before the due date to avoid late payment surcharge.</li>

<li>Please mention your Consumer Number while making payment.</li>

<li>This bill is computer generated and does not require a physical signature.</li>

<li>For any billing dispute, contact your nearest APDCL office.</li>

<li>Do not touch damaged electric wires. Report immediately through the Consumer Portal.</li>

</ul>

<br>

<table border="0" width="100%">

<tr>

<td width="60%">

<b>Generated On :</b>

'.date("d-m-Y h:i:s A").'

<br><br>

<b>Portal :</b>

APDCL Consumer Portal

<br>

www.apdcl.org

</td>

<td width="40%" align="center">

<br><br><br>

_________________________

<br>

<b>Authorized Officer</b>

<br>

Assam Power Distribution Company Limited

</td>

</tr>

</table>

<br><br>

<hr>

<div style="text-align:center;font-size:9pt;color:#666;">

This is a system generated electricity bill.

<br>

Powered by APDCL Consumer Portal

</div>

';

/*=========================================
    WRITE HTML TO PDF
=========================================*/

$pdf->writeHTML(
    $html,
    true,
    false,
    true,
    false,
    ''
);

/*=========================================
    DOWNLOAD PDF
=========================================*/

$fileName = "APDCL_Bill_".$bill['bill_no'].".pdf";

$pdf->Output($fileName,'D');

exit;