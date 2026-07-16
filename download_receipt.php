<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");
require("../fpdf/fpdf.php");

$consumer_no = $_SESSION['consumer'];

if(!isset($_GET['id'])){
    die("Invalid Receipt.");
}

$payment_id = intval($_GET['id']);

/*=====================================
    PAYMENT
=====================================*/

$paymentQuery = mysqli_query($conn,"
SELECT *
FROM payments
WHERE id='$payment_id'
AND consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($paymentQuery)==0){
    die("Receipt not found.");
}

$payment = mysqli_fetch_assoc($paymentQuery);

/*=====================================
    USER
=====================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/*=====================================
    BILL
=====================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='".$payment['bill_id']."'
LIMIT 1
");

$bill = mysqli_fetch_assoc($billQuery);

/*=====================================
    RECEIPT NUMBER
=====================================*/

$receiptNo = "APDCL-REC-".str_pad($payment['id'],6,"0",STR_PAD_LEFT);

/*=====================================
    PDF START
=====================================*/

$pdf = new FPDF();

$pdf->AddPage();

$pdf->SetTitle("APDCL Payment Receipt");

$pdf->SetAuthor("APDCL");

$pdf->SetFont("Arial","B",18);

/*=====================================
    APDCL HEADER
=====================================*/

$pdf->SetFillColor(13,71,161);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(190,15,"APDCL PAYMENT RECEIPT",0,1,'C',true);

$pdf->SetFont("Arial","",11);

$pdf->Cell(190,8,"Assam Power Distribution Company Limited",0,1,'C',true);

$pdf->Ln(8);

/*=====================================
    RESET TEXT COLOR
=====================================*/

$pdf->SetTextColor(0,0,0);

/*=====================================
    RECEIPT DETAILS
=====================================*/

$pdf->SetFont("Arial","B",11);

$pdf->Cell(95,8,"Receipt No :",1,0);

$pdf->SetFont("Arial","",11);

$pdf->Cell(95,8,$receiptNo,1,1);

$pdf->SetFont("Arial","B",11);

$pdf->Cell(95,8,"Payment Date :",1,0);

$pdf->SetFont("Arial","",11);

$pdf->Cell(
95,
8,
date("d-m-Y h:i A",strtotime($payment['payment_date'])),
1,
1
);

$pdf->Ln(5);

/*=====================================
    CONSUMER DETAILS
=====================================*/

$pdf->SetFillColor(230,240,255);

$pdf->SetFont("Arial","B",12);

$pdf->Cell(190,10,"Consumer Details",1,1,'L',true);

$pdf->SetFont("Arial","",11);

$pdf->Cell(55,8,"Consumer No",1,0);
$pdf->Cell(135,8,$user['consumer_no'],1,1);

$pdf->Cell(55,8,"Consumer Name",1,0);
$pdf->Cell(135,8,$user['name'],1,1);

$pdf->Cell(55,8,"Mobile",1,0);
$pdf->Cell(135,8,$user['mobile'],1,1);

$pdf->Cell(55,8,"Email",1,0);
$pdf->Cell(135,8,$user['email'],1,1);

$pdf->MultiCell(
190,
8,
"Address : ".$user['address'],
1
);

$pdf->Ln(5);

/*=====================================
    PAYMENT DETAILS
=====================================*/

$pdf->SetFont("Arial","B",12);

$pdf->Cell(190,10,"Payment Details",1,1,'L',true);

$pdf->SetFont("Arial","",11);

$pdf->Cell(55,8,"Transaction ID",1,0);
$pdf->Cell(135,8,$payment['transaction_id'],1,1);

$pdf->Cell(55,8,"Payment Method",1,0);
$pdf->Cell(135,8,$payment['payment_method'],1,1);

$pdf->Cell(55,8,"Status",1,0);
$pdf->Cell(135,8,"SUCCESS",1,1);

$pdf->Ln(5);

/*=====================================
    BILL DETAILS
=====================================*/

$pdf->SetFillColor(230,240,255);

$pdf->SetFont("Arial","B",12);

$pdf->Cell(190,10,"Bill Details",1,1,'L',true);

$pdf->SetFont("Arial","",11);

$pdf->Cell(55,8,"Bill Number",1,0);
$pdf->Cell(135,8,$bill['bill_no'],1,1);

$pdf->Cell(55,8,"Billing Month",1,0);
$pdf->Cell(135,8,$bill['month'],1,1);

$pdf->Cell(55,8,"Due Date",1,0);
$pdf->Cell(
135,
8,
date("d-m-Y",strtotime($bill['due_date'])),
1,
1
);

$pdf->Cell(55,8,"Bill Amount",1,0);
$pdf->Cell(
135,
8,
"Rs. ".number_format($bill['total_bill'],2),
1,
1
);

$pdf->Ln(5);

/*=====================================
    PAYMENT SUMMARY
=====================================*/

$pdf->SetFont("Arial","B",12);

$pdf->Cell(190,10,"Payment Summary",1,1,'L',true);

$pdf->SetFont("Arial","",11);

$pdf->Cell(95,10,"Total Bill Amount",1,0);

$pdf->Cell(
95,
10,
"Rs. ".number_format($bill['total_bill'],2),
1,
1,
'R'
);

$pdf->Cell(95,10,"Amount Paid",1,0);

$pdf->Cell(
95,
10,
"Rs. ".number_format($payment['amount'],2),
1,
1,
'R'
);

$pdf->Cell(95,10,"Balance",1,0);

$pdf->Cell(
95,
10,
"Rs. 0.00",
1,
1,
'R'
);

$pdf->SetFont("Arial","B",12);

$pdf->SetTextColor(0,128,0);

$pdf->Cell(95,10,"Payment Status",1,0);

$pdf->Cell(
95,
10,
"SUCCESS",
1,
1,
'C'
);

$pdf->SetTextColor(0,0,0);

$pdf->Ln(10);

/*=====================================
    THANK YOU
=====================================*/

$pdf->SetFont("Arial","B",14);

$pdf->Cell(
190,
10,
"Thank You For Your Payment!",
0,
1,
'C'
);

$pdf->SetFont("Arial","",11);

$pdf->MultiCell(
190,
8,
"This is a computer generated payment receipt issued by Assam Power Distribution Company Limited (APDCL). Please keep this receipt for your future reference."
);

$pdf->Ln(15);

/*=====================================
    SIGNATURE
=====================================*/

$pdf->Cell(90,8,"________________________",0,0,'C');

$pdf->Cell(100,8,"________________________",0,1,'C');

$pdf->Cell(90,8,"Consumer Signature",0,0,'C');

$pdf->Cell(100,8,"Authorized Officer",0,1,'C');

/*=====================================
    FOOTER
=====================================*/

$pdf->Ln(15);

$pdf->SetDrawColor(180,180,180);

$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

$pdf->Ln(5);

$pdf->SetFont("Arial","I",9);

$pdf->SetTextColor(100,100,100);

$pdf->Cell(
190,
6,
"Assam Power Distribution Company Limited (APDCL)",
0,
1,
'C'
);

$pdf->Cell(
190,
6,
"Consumer Self Service Portal",
0,
1,
'C'
);

$pdf->Cell(
190,
6,
"Generated On : ".date("d-m-Y h:i:s A"),
0,
1,
'C'
);

$pdf->Ln(3);

$pdf->MultiCell(
190,
5,
"This is a computer generated receipt. No signature is required.",
0,
'C'
);

/*=====================================
    DOWNLOAD PDF
=====================================*/

$fileName = "APDCL_Receipt_".$receiptNo.".pdf";

$pdf->Output("D",$fileName);

exit;