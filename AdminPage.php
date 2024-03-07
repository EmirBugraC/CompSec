<?php

require 'connect_db.php';

session_start();

if(!isset($_GET['session_token'], $_SESSION['session_token']) || $_GET['session_token'] != $_SESSION['session_token']){
    header('Location: index.html');
    exit();
}

//if the user is not saved in the session as admin it takes the user back to index page
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1){
    header('Location: index.html');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Page</title>
  <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="adminPage">
  <h1>Admin Page</h1>
  <form action="RequestEvaluationAdmin.php" method="get">
      <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
      <input type="submit" value="Evaluate Requests">
  </form>
</div>
</body>
</html>