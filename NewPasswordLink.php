<?php

session_start();

//CSRF session token generation
$_SESSION['session_token'] = bin2hex(random_bytes(16));

include_once 'connect_db.php';

//XSS implementation
$_SESSION['email'] = htmlspecialchars($_GET['email']);

//checks if the reset link was expired before opening it
if ($stmt = $con->prepare('SELECT password_reset_expiration, security_question, reset_token FROM accounts WHERE email = ?')){
    $stmt->bind_param('s', $_SESSION['email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($resetExpiration, $securityQuestion, $resetToken);
        $stmt->fetch();
        $stmt->close();
        if($_GET['reset_token'] != $resetToken){
            exit('Invalid Link!');
        }
        $_SESSION['security_question'] = htmlspecialchars($securityQuestion);
        $currentTime = date('Y-m-d H:i:s');
        if ($resetExpiration <= $currentTime) {
            exit('The password reset link has expired. Please request a new password.<a href="ForgotPasswordEmailPage.php">Request</a>');
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="newPassword">
    <h1>Confirm New Password</h1>
    <form action="NewPassword.php" method="post">
        <label for="newPassword">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="newPassword" placeholder="New Password" id="newPassword" required>
        <label for="confirm_newPassword">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="confirm_newPassword" placeholder="Confirm New Password" id="confirm_newPassword" required>
        <p><?php echo $_SESSION['security_question']; ?></p>
        <input type="text" name="security_answer" placeholder="Security Answer" id="security_answer" required>
        <!-- carries the CSRF session token to the next page -->
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <input type="submit" value="Confirm">
    </form>
</div>
</body>
</html>

