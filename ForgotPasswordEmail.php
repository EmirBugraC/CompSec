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

//function to send password reset link to the email input by the user using PHPMailer
function sendResetLink($emailAddress,$token){
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    require 'vendor/phpmailer/phpmailer/src/Exception.php';

    $resetLink = "http://localhost/ComputerSecurityEmir/NewPasswordLink.php?email=$emailAddress&reset_token=$token";

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
    $mail->Subject = 'Password Reset';
    $mail->Body = "Click the following link to reset your password: $resetLink";

    if ($mail->send()){
        echo 'Reset link sent successfully! Check your email to reset your password.';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

include_once 'connect_db.php';

if (!isset($_POST['confirmationEmail'])){
    echo 'Confirmation email is missing!';
}

//XSS implementation
$confirmationEmail = htmlspecialchars($_POST['confirmationEmail']);

//finds the user from the email provided by the user
if ($stmt = $con->prepare('SELECT id, username FROM accounts WHERE email = ?')){
    $stmt->bind_param('s', $confirmationEmail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0){
        $stmt->bind_result($id, $username);
        $stmt->fetch();
        //sets a reset token and expiration for the reset link and sends it, sets the session according to user's details
        if ($stmt = $con->prepare('UPDATE accounts SET reset_token = ?, password_reset_expiration = ? WHERE email = ?')){
            $token = bin2hex(random_bytes(16));
            $_SESSION['reset_user_id'] = $id; 
            $_SESSION['reset_username'] = $username;
            $_SESSION['reset_email'] = $confirmationEmail;
            $linkExpiration = date('Y-m-d H:i:s', strtotime('+180 seconds'));
            $stmt->bind_param('sss', $token, $linkExpiration, $confirmationEmail);
            $stmt->execute();
            sendResetLink($confirmationEmail, $token);
        } else{
            echo 'Could not prepare statement!';
        }
    } else{
        echo 'Email not found. Please enter a valid email address.';
    }
} else{
    echo 'Error checking confirmation email.';
}
$con->close();