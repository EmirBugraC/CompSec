<?php

require 'vendor/autoload.php';

session_start();
//CSRF implementation, checks if the session token was carried over 
if(!isset($_SESSION['session_token'], $_POST['session_token']) || $_SESSION['session_token'] != $_POST['session_token']){
    header('Location: index.html');
    exit('Invalid Session');
}
//reCaptchaV3 implementation
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
} else {
    exit('reCAPTCHA response is missing.');
}

//checks if the password meets the password entropy conditions
function checkNewPassword($password){
    if (strlen($password) <= 6){
        exit('Password must be longer than 6 characters.');
    }

    if (!preg_match('/[A-Z]/', $password)){
        exit('Password must have at least one uppercase letter.');
    }

    if (!preg_match('/[a-z]/', $password)){
        exit('Password must have at least one lowercase letter.');
    }

    if (!preg_match('/\d/', $password)){
        exit('Password must have at least one digit.');
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)){
        exit('Password must have at least one special character.');
    }

    return true;
}

//function to send password reset link to the email input by the user using PHPMailer
function sendVerificationEmail($emailAddress, $verificationCode){
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
        echo 'You have successfully registered! Check your email for verification';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

include_once 'connect_db.php';

if (!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['phone_number'], $_POST['security_question'], $_POST['security_answer'])){
    exit('Please fill all the fields!');
}

if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['phone_number']) || empty($_POST['security_question']) || empty($_POST['security_answer'])){
    exit('Please fill all the fields!');
}

if (preg_match('/^[a-zA-Z0-9]{5,}$/', $_POST['username']) == 0){
    exit('Username is not valid!');
}

//checks if the given passwords match the given conditions
$passwordValidationResult = checkNewPassword($_POST['password']);
if ($passwordValidationResult !== true) {
    exit($passwordValidationResult);
}

if ($_POST['password'] !== $_POST['confirm_password']){
    exit('Passwords do not match.');
}

//XSS prevention implementation
$inputUsername = htmlspecialchars($_POST['username']);
$inputPassword = htmlspecialchars($_POST['password']);
$inputPhone = htmlspecialchars($_POST['phone_number']);
$inputEmail = htmlspecialchars($_POST['email']);
$securityQuestion = htmlspecialchars($_POST['security_question']);
$securityAnswer = htmlspecialchars($_POST['security_answer']);

//checks if there is another user with the same email in the database
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE email = ?')){
    $stmt->bind_param('s', $inputEmail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){
        exit('Email is already registered, please use another!');
    }
    $stmt->close();
} else {
    exit('Could not prepare statement for email check!');
}

//checks if there is another user with the same phone number in the database
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE phone_number = ?')){
    $stmt->bind_param('s', $inputPhone);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){
        exit('Phone number is already registered, please use another!');
    }
    $stmt->close();
} else {
    exit('Could not prepare statement for email check!');
}

//first checks if there is another user with the same username in the database
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')){
    $stmt->bind_param('s', $inputUsername);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){
        echo 'Username exists, please choose another!';
    } else{// if every condition up until now was met, creats the account, sets an expiration time for the verification email and sends it
        $linkExpiration = date('Y-m-d H:i:s', strtotime('+180 seconds'));
        if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, phone_number, verification_code, verification_expiration, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')){
            $verificationCode = bin2hex(random_bytes(16));
            $password = password_hash($inputPassword, PASSWORD_DEFAULT); //encryption for the password before storing it in the database
            $encryptedSecurityAnswer = password_hash($securityAnswer, PASSWORD_DEFAULT); //encryption for the security answer before storing it in the database
            $stmt->bind_param('ssssssss', $inputUsername, $password, $inputEmail, $inputPhone, $verificationCode, $linkExpiration, $securityQuestion, $encryptedSecurityAnswer);
            $stmt->execute();
            sendVerificationEmail($inputEmail, $verificationCode);
        } else{
            echo 'Could not prepare statement!';
        }
    }
    $stmt->close();
} else{
    echo 'Could not prepare statement!';
}
$con->close();

