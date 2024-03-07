<?php

require 'connect_db.php';

session_start();
//if the user has not logged in to the session takes the user back to index page
if ($_SESSION['loggedin'] != true){
    header('Location: index.html');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Home Page</title>
    <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="homePage">
    <h1>Home Page <br> Welcome to Lovejoy's Antiques </h1>
    <form action="RequestEvaluationPage.php" method="get">
        <input type="submit" value="Request Evaluation">
    </form>
</div>
</body>
</html>
