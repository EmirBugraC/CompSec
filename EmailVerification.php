<?php
require 'vendor/autoload.php';

session_start();

//function to resend the verification email after link expiration using PHPMailer
function resendVerificationEmail($emailAddress, $verificationCode){ 
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    require 'vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $activation_link = "http://localhost/ComputerSecurityEmir/EmailVerification.php?email=$emailAddress&verification_code=$verificationCode";
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'compsectest1@gmail.com';
    $mail->Password = 'imzc sxpi vxrt kukl';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('compsectest1@gmail.com', 'CompSec');
    $mail->addAddress($emailAddress);

    $mail->isHTML(true);
    $mail->Subject = 'Account Verification';
    $mail->Body = 'Your verification link is: '. $activation_link;

    if ($mail->send()){
        echo 'Your verification email was expired. Check your email for the new verification link.';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

include_once 'connect_db.php';

//XSS implementation
$email = htmlspecialchars($_GET['email']);
$verificationCode = htmlspecialchars($_GET['verification_code']);

//gets the user id and verification email's expiration from email gotten from $_GET
if ($stmt = $con->prepare('SELECT id, verification_expiration FROM accounts WHERE verification_code = ? AND email = ?')){
    $stmt->bind_param('ss', $verificationCode, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){
        $stmt->bind_result($id, $expirationTime);
        $stmt->fetch();
        $stmt->close();
        $currentTime = date('Y-m-d H:i:s');
        if ($expirationTime >= $currentTime){ //checks if the email was expired and verifies the account if it is not 
            if ($stmt = $con->prepare('UPDATE accounts SET verified = 1, verified_at = NOW(), verification_code = NULL AND email = ?')){
                $stmt->bind_param('s', $email);
                $stmt->execute();
                echo 'Account verified successfully! You can now <a href="LogInPage.php">log in</a>.';
            } else{
                echo 'Error updating account verification status.';
            }
        } else{//if email was expired resetts both the code and its expiration and resends the email using function resendVerificationEmail()
            $newVerificationCode = bin2hex(random_bytes(16));
            $newExpirationTime = date('Y-m-d H:i:s', strtotime('+180 seconds'));
            if ($stmt = $con->prepare('UPDATE accounts SET verification_code = ?, verification_expiration = ? WHERE verification_code = ? AND email = ?')){
                $stmt->bind_param('ssss', $newVerificationCode, $newExpirationTime, $verificationCode, $email);
                $stmt->execute();
                resendVerificationEmail($email, $newVerificationCode);
            } else{
                echo 'Error resending verification email.';
            }
        }
    } else{
        echo 'Incorrect or Invalid verification code.';
    }
} else{
    echo 'Error checking verification code.';
}

$con->close();

