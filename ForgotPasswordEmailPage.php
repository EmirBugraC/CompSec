<?php
session_start();
//CSRF session token generation
$_SESSION['session_token'] = bin2hex(random_bytes(16));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Confirmation</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="Style.css" rel="stylesheet" type="text/css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onSubmit(token) {
            document.getElementById("confirm_2fa").submit();
        }
    </script>
</head>
<body>
<div class="confirmEmail">
    <h1>Confirm Your Email</h1>
    <form action="ForgotPasswordEmail.php" id = 'confirm_2fa' method="post">
        <label for="confirmationEmail">
            <i class="fas fa-envelope"></i>
        </label>
        <input type="text" name="confirmationEmail" placeholder="Confirm Email" id="confirmationEmail" required>
        <!-- carries the CSRF session token to the next page -->
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <!-- reCaptchaV3 implementation for the submit button using site key provided-->
        <button class="g-recaptcha"
                data-sitekey="6Le8YSMpAAAAAEgYXyO29ZKNairGMpyaRR3TSQ1o"
                data-callback='onSubmit'
                data-action='submit'>Confirm</button>
        <form action="LogInPage.php" method="get">
            <input type="submit" value="Go Back">
        </form>
    </form>
</div>
</body>
</html>

