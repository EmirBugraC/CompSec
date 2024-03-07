<?php
session_start();
//CSRF session token generation
$_SESSION['session_token'] = bin2hex(random_bytes(16));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="Style.css" rel="stylesheet" type="text/css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onSubmit(token) {
            document.getElementById("sign_up").submit();
        }
    </script>
</head>
<body>
<div class="signup">
    <h1>Sign Up</h1>
    <form action="SignUp.php" id = 'sign_up' method="post">
        <label for="username">
            <i class="fas fa-user"></i>
        </label>
        <input type="text" name="username" placeholder="Username" id="username" required>
        <label for="password">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="password" placeholder="Password" id="password" required>
        <label for="confirm_password">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirm_password" required>
        <label for="email">
            <i class="fas fa-envelope"></i>
        </label>
        <input type="email" name="email" placeholder="Email" id="email" required>
        <label for="phone_number">
            <i class="fas fa-phone"></i>
        </label>
        <input type="tel" name="phone_number" pattern="^[0-9\s()+-]*$" placeholder="e.g., +447123456789 or 07123 456 789" id="phone_number" required>
        <label for="security_question">
            <i class="fas fa-question"></i>
        </label>
        <select name="security_question" id="security_question" required>
            <option value="" disabled selected>Select Security Question</option>
            <option value="Maiden name of your mother">Your mother's maiden name</option>
            <option value="Name of your first pet">Your first pet's name</option>
            <option value="Your childhood nickname">Your childhood nickname</option>
            <option value="Name of your 1st grade teacher">Your first teacher's name</option>
        </select>
        <label for="security_answer">
            <i class="fas fa-key"></i>
        </label>
        <input type="text" name="security_answer" placeholder="Security Answer" id="security_answer" required>
        <!-- carries the CSRF session token to the next page -->
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <!-- reCaptchaV3 implementation for the submit button using site key provided-->
        <button class="g-recaptcha"
                data-sitekey="6Le8YSMpAAAAAEgYXyO29ZKNairGMpyaRR3TSQ1o"
                data-callback='onSubmit'
                data-action='submit'>Create Account</button>
    </form>
    <form action="LogInPage.php" method="get">
        <input type="submit" value="Log In">
    </form>
</div>
</body>
</html>