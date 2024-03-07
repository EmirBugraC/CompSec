<?php

session_start();

//CSRF session token generation
$_SESSION['session_token'] = bin2hex(random_bytes(16));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>2FA Confirmation</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="authentication">
    <h1>2FA Confirmation</h1>
    <form action="Authentication.php" method="post">
        <label for="authenticationCode">
            <i class="fas fa-key"></i>
        </label>
        <input type="text" name="authenticationCode" placeholder="2FA Code" id="authenticationCode" required>
        <!-- carries the CSRF session token to the next page -->
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <input type="submit" value="Confirm">
    </form>
</div>
</body>
</html>