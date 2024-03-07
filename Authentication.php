<?php
session_start();

//CSRF implementation, checks if the session token was carried over 
if(!isset($_SESSION['session_token'], $_POST['session_token']) || $_SESSION['session_token'] != $_POST['session_token']){
    header('Location: index.html');
    exit('Invalid Session');
}

if (!isset($_POST['authenticationCode'])){
    exit('Authentication code is missing!');
}

$authenticationCode = $_SESSION['2fa_code'];
$inputAuthenticationCode = $_POST['authenticationCode'];

if ($authenticationCode !== $inputAuthenticationCode){
    exit('Incorrect authentication code.');
}else{ //if the authentication code is correct loggs the user and sets the session as logged in
    session_regenerate_id();
    $_SESSION['loggedin'] = true;
    header('Location: HomePage.php');
    exit();
}


