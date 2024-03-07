<?php
require 'connect_db.php';

session_start();

if(!isset($_GET['session_token'], $_SESSION['session_token']) || $_GET['session_token'] != $_SESSION['session_token']){
    header('Location: index.html');
    exit();
}

//if the user is not saved in the session as admin it takes the user back to index page
if ($_SESSION['is_admin'] != 1) {
    header('Location: index.html');
    exit();
}

//gets all the data from requests table 
$stmt = $con->prepare('SELECT requestID, id, request, contact, contact_type, posted_at, images FROM requests');
$stmt->execute();
$stmt->bind_result($requestID, $userID, $request, $contact, $contactType, $postedAt, $images);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request List - Admin</title>
    <link href="Style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="adminRequestEvaluation">
    <h1>Request List - Admin</h1>
    <table>
        <thead>
        <tr>
            <th>Request ID</th>
            <th>User ID</th>
            <th>Request</th>
            <th>Contact</th>
            <th>Contact Type</th>
            <th>Posted At</th>
            <th>Images</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($stmt->fetch()){
            echo "<tr>";
            echo "<td>" . htmlspecialchars($requestID) . "</td>";
            echo "<td>" . htmlspecialchars($userID) . "</td>";
            echo "<td>" . htmlspecialchars($request) . "</td>";
            echo "<td>" . htmlspecialchars($contact) . "</td>";
            echo "<td>" . htmlspecialchars($contactType) . "</td>";
            echo "<td>" . htmlspecialchars($postedAt) . "</td>";
            echo "<td><img src='" . htmlspecialchars($images) . "'></td>";
            echo "</tr>";
        }
        $stmt->close();
        $con->close();
        ?>
        </tbody>
    </table>
    <form action="AdminPage.php" method="get">
        <input type="hidden" name= "session_token" value = "<?php echo isset($_SESSION['session_token']) ? $_SESSION['session_token'] : ''; ?>" >
        <input type="submit" value="Go Back">
    </form>
</div>
</body>
</html>