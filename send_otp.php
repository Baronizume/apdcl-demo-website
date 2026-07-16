<?php
session_start();
include("../db.php");

$message = "";

if(isset($_POST['send'])){

    $username = trim($_POST['username']);
    $mobile   = trim($_POST['mobile']);

    if(empty($username) || empty($mobile)){

        $message="<div class='alert alert-danger'>
        Enter Username and Mobile Number.
        </div>";

    }else{

        $stmt=mysqli_prepare($conn,"
            SELECT *
            FROM admin
            WHERE username=? AND mobile=?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt,"ss",$username,$mobile);

        mysqli_stmt_execute($stmt);

        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)==1){

            $admin=mysqli_fetch_assoc($result);

            $otp = rand(100000,999999);

            $expires=date(
                "Y-m-d H:i:s",
                strtotime("+5 minutes")
            );

            mysqli_query($conn,"
                INSERT INTO otp_verification
                (
                    admin_id,
                    mobile,
                    otp,
                    expires_at
                )
                VALUES
                (
                    '{$admin['id']}',
                    '{$mobile}',
                    '{$otp}',
                    '{$expires}'
                )
            ");

            $_SESSION['reset_admin_id']=$admin['id'];
            $_SESSION['reset_username']=$admin['username'];
            $_SESSION['reset_mobile']=$mobile;

            /*
            DEMO MODE
            */

            $_SESSION['demo_otp']=$otp;

            header("Location: verify_otp.php");
            exit();

        }else{

            $message="<div class='alert alert-danger'>
            Username or Mobile Number is incorrect.
            </div>";

        }

    }

}
?>