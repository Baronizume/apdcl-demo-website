<?php
session_start();

if (!isset($_SESSION['admin'])) {
    exit;
}

include("../db.php");

if (!isset($_GET['consumer_no'])) {
    exit;
}

$consumer_no = trim($_GET['consumer_no']);

$stmt = mysqli_prepare($conn, "
SELECT *
FROM consumers
WHERE consumer_no = ?
LIMIT 1
");

mysqli_stmt_bind_param($stmt, "s", $consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($consumer = mysqli_fetch_assoc($result)) {

    // Last bill reading
    $previous = 0;

    $billStmt = mysqli_prepare($conn,"
    SELECT current_reading
    FROM bills
    WHERE consumer_no=?
    ORDER BY bill_date DESC
    LIMIT 1
    ");

    mysqli_stmt_bind_param($billStmt,"s",$consumer_no);
    mysqli_stmt_execute($billStmt);

    $billResult = mysqli_stmt_get_result($billStmt);

    if($bill=mysqli_fetch_assoc($billResult)){
        $previous = $bill['current_reading'];
    }

    $consumer['previous_reading']=$previous;

    echo json_encode($consumer);

}else{

    echo json_encode([
        "error"=>"Consumer not found"
    ]);

}