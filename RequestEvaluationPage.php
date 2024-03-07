<?php
session_start();

include_once 'connect_db.php';
//if the user has not logged in to the session takes the user back to index page
if ($_SESSION['loggedin'] != true){
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request Evaluation</title>
    <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="requestEvaluation">
    <h1>Request Evaluation</h1>
    <form action="RequestEvaluation.php" method="post" enctype="multipart/form-data">
        <label for="request">Comment your request:</label>
        <textarea name="request" id="request" required></textarea>
        <label for="label">Request Type:</label>
        <select name="label" id="label">
            <option value="phone">Phone Number</option>
            <option value="email">Email Address</option>
        </select>
        <input type="file" name="fileToUpload" id="fileToUpload" enctype="multipart/form-data" required>
        <input type="submit" value="Upload Request" name="submit">
    </form>
    <form action="HomePage.php" method="get">
        <input type="submit" value="Go Back">
    </form>
</div>
</body>
</html>

