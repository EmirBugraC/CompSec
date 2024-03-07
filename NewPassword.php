<?php
session_start();

//CSRF implementation, checks if the session token was carried over 
if(!isset($_SESSION['session_token'], $_POST['session_token']) || $_SESSION['session_token'] != $_POST['session_token']){
    header('Location: index.html');
    exit('Invalid Session');
}

//checks if the new password meets the password entropy conditions
function checkNewPassword($password){
    if (strlen($password) <= 6) {
        exit('Password must be longer than 6 characters.');
    }

    if (!preg_match('/[A-Z]/', $password)) {
        exit('Password must have at least one uppercase letter.');
    }

    if (!preg_match('/[a-z]/', $password)) {
        exit('Password must have at least one lowercase letter.');
    }

    if (!preg_match('/\d/', $password)) {
        exit('Password must have at least one digit.');
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        exit('Password must have at least one special character.');
    }

    return true;
}

include_once 'connect_db.php';

if (!isset($_POST['newPassword'], $_POST['confirm_newPassword'])){
    echo 'Please fill both the new password and confirm password fields!';
}

if (!isset($_POST['security_answer'])){
    echo 'Please fill the security answer.';
}

if ($_POST['newPassword'] !== $_POST['confirm_newPassword']){
    exit('Passwords do not match.');
}

//checks if the new password meets the password entropy conditions
$passwordValidationResult = checkNewPassword($_POST['newPassword']);
if ($passwordValidationResult !== true) {
    exit($passwordValidationResult);
}

//XSS implementation for user input and session data
$newPassword = htmlspecialchars($_POST['newPassword']);
$confirmNewPassword = htmlspecialchars($_POST['confirm_newPassword']);
$userSecurityAnswer = htmlspecialchars($_POST['security_answer']);
$userEmail = htmlspecialchars($_SESSION['email']);

//gets the user details from the given email during email confirmation
if ($stmt = $con->prepare('SELECT id, password_reset_expiration, security_question, security_answer FROM accounts WHERE email = ?')){
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $resetExpiration, $securityQuestion, $securityAnswer);
        $stmt->fetch();
        $stmt->close();
        $currentTime = date('Y-m-d H:i:s');
        if ($resetExpiration >= $currentTime){ //checks if the reset link was expired during reset process
            if (password_verify($userSecurityAnswer, $securityAnswer)){ //checks if the security answer given by the user matches the answer given during sign up
                if ($stmt = $con->prepare('UPDATE accounts SET password = ?, password_reset_expiration = NULL, reset_token = NULL WHERE email = ?')){
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);//encrypts and stores the new password
                    $stmt->bind_param('ss', $hashedNewPassword, $userEmail);
                    $stmt->execute();
                    echo 'Password reset successfully! You can now <a href="LogInPage.php">Log in</a> with your new password.';
                } else{
                    echo 'Error updating password.';
                }
            } else{
                echo 'Wrong security answer. Please provide the correct answer.';
            }
        } else{
            echo 'The password reset link has expired. Please request a new password.<a href="ForgotPasswordEmailPage.php">Request</a>';
        }
    } else{
        echo 'Email not found. Please enter a valid email address.';
    }
} else{
    echo 'Error checking confirmation email.';
}
$con->close();
