<?php
session_start();
//CSRF session token generation
$_SESSION['session_token'] = bin2hex(random_bytes(16));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log In</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="Style.css" rel="stylesheet" type="text/css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onSubmit(token) {
            document.getElementById("log_in").submit();
        }
    </script>
</head>
<body>
<div class="login">
    <h1>Log In</h1>
    <form action="LogIn.php" id = 'log_in' method="post">
        <label for="username">
            <i class="fas fa-user"></i>
        </label>
        <input type="text" name="username" placeholder="Username" id="username" required>
        <label for="password">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="password" placeholder="Password" id="password" required>
        <!-- carries the CSRF session token to the next page -->
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <!-- reCaptchaV3 implementation for the submit button using site key provided-->
        <button class="g-recaptcha"
                data-sitekey="6Le8YSMpAAAAAEgYXyO29ZKNairGMpyaRR3TSQ1o"
                data-callback='onSubmit'
                data-action='submit'>Log In</button>
    </form>
    <form action="SignUpPage.php" method="get">
        <input type="submit" value="Sign Up">
    </form>
    <p> <a href="ForgotPasswordEmailPage.php">Forgot your password?</a></p>
</div>
</body>
</html>

