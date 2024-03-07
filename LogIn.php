<?php
require 'vendor/autoload.php';

session_start();
$_SESSION['is_admin'] = 0;

//CSRF implementation, checks if the session token was carried over 
if(!isset($_SESSION['session_token'], $_POST['session_token']) || $_SESSION['session_token'] != $_POST['session_token']){
    header('Location: index.html');
    exit('Invalid Session');
}

//reCaptchaV3 implementation using the secret key provided
$secretKey = '6Le8YSMpAAAAAGRXo4VR4SjjByn6R6bjMHioPgsx';
$recaptchaResponse = $_POST['g-recaptcha-response'];

if (isset($recaptchaResponse) && !empty($recaptchaResponse)){
    $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse;
    $response = file_get_contents($url);
    $recaptchaResult = json_decode($response);

    if (!$recaptchaResult->success){
        exit('reCAPTCHA verification failed. Please try again.');
    }
} else{
    exit('reCAPTCHA response is missing.');
}

//function to send the authentication code for user to complete logging in process using PHPMailer
function sendAuthenticationCode($emailAddress, $authenticationCode){ 
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    require 'vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

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
    $mail->Subject = '2FA Code';
    $mail->Body = 'Your 2FA code is: '. $authenticationCode;

    if ($mail->send()){
        header('Location: AuthenticationPage.php');
        exit();
    } else{
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

//function to resend verification email if the user tried to log in without verifying their account using PHPMailer
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
        echo 'Your account was not verified. Please verify you account with the link sent to your email address.';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

include_once 'connect_db.php';

if (!isset($_POST['username'], $_POST['password'])){
    exit('Please fill both the username and password fields!');
}

if (empty($_POST['username'])){
    exit('Username cannot be empty!');
}

if (empty($_POST['password'])){
    exit('Password cannot be empty!');
}

//XSS implementation for user input
$inputUsername = htmlspecialchars($_POST['username']);
$inputPassword = htmlspecialchars($_POST['password']);

//checks if conditions match with the username
if ($stmt = $con->prepare('SELECT id, password, verified, email, lockout_count, lockout_time, is_admin FROM accounts WHERE username = ?')){
    $stmt->bind_param('s', $inputUsername);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){ 
        $stmt->bind_result($id, $password, $verified, $email, $lockout_count, $lockout_time, $is_admin);
        $stmt->fetch();
        if ($is_admin == 1){ //if the entered username is defined as admin in the database, admin can log in regardless of the password.
            $_SESSION['session_token'] = bin2hex(random_bytes(16));
            $token = $_SESSION['session_token'];
            $_SESSION['is_admin'] = $is_admin;
            header("Location: AdminPage.php?session_token=$token");
            exit;
        }
        if ($lockout_time !== null && strtotime($lockout_time) > time()){ //checks if the user was locked out
            exit('Your account is locked out. Please try again later.');
        }
        if ($verified == 1){ //checks if the user email and account was verified 
            if (password_verify($inputPassword, $password)){
                $lockout_count = 0;
                $lockout_time = null;
                if ($stmt = $con->prepare('UPDATE accounts SET lockout_count = ?, lockout_time = ? WHERE username = ?')){
                    $stmt->bind_param('iss', $lockout_count, $lockout_time, $inputUsername);
                    $stmt->execute();
                }
                //if account was verified before logging the user in, creates and sends an authentication code that is only usable in the session.
                $authenticationCode = bin2hex(random_bytes(3));
                $_SESSION['name'] = $inputUsername;
                $_SESSION['id'] = $id;
                $_SESSION['2fa_code'] = $authenticationCode;
                sendAuthenticationCode($email, $authenticationCode);
                exit();
            } else{ //if the entered password does not match the username, the lock count for the account matching that username is increased by one
                $lockout_count++;
                if ($lockout_count >= 3){ // if the lock count is 3 it locks the account for 5 minutes
                    $lockout_time = date('Y-m-d H:i:s', strtotime('+300 seconds'));
                    if ($stmt = $con->prepare('UPDATE accounts SET lockout_count = ?, lockout_time = ? WHERE username = ?')){
                        $stmt->bind_param('iss', $lockout_count, $lockout_time, $inputUsername);
                        $stmt->execute();
                        exit('Your account is locked out due to multiple incorrect login attempts. Please try again in 1 minute or reset your password.');
                    }
                }
                if ($stmt = $con->prepare('UPDATE accounts SET lockout_count = ?, lockout_time = ? WHERE username = ?')){
                    $stmt->bind_param('iss', $lockout_count, $lockout_time, $inputUsername);
                    $stmt->execute();
                }
                echo 'Incorrect password!';
            }
        } else{ //if account wasn't verified creates a new email and resends verification email that is valid for 3 minutes
            $newVerificationCode = bin2hex(random_bytes(16));
            $newExpirationTime = date('Y-m-d H:i:s', strtotime('+180 seconds'));
            if ($stmt = $con->prepare('UPDATE accounts SET verification_code = ?, verification_expiration = ? WHERE  email = ?')){
                $stmt->bind_param('sss', $newVerificationCode, $newExpirationTime, $email);
                $stmt->execute();
                resendVerificationEmail($email, $newVerificationCode);
            } else{
                echo 'Error resending verification email.';
            }
        }
    } else{
        echo 'Incorrect username and/or password!';
    }
    $stmt->close();
}
$con->close();