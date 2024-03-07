<?php
session_start();

include_once 'connect_db.php';

//if the user has not logged in to the session takes the user back to index page
if ($_SESSION['loggedin'] != true){
    header('Location: index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    //XSS implementation
    $request = isset($_POST['request']) ? htmlspecialchars($_POST['request']) : '';
    $label = isset($_POST['label']) ? htmlspecialchars($_POST['label']) : '';
    $userId = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    $contactType = '';
    $contact = '';
    $target_dir = "images/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    //according to user's choice of contact get's their data from the database
    if ($label === 'phone'){
        $contactType = 'phone';
        $phoneContactQuery = $con->prepare('SELECT phone_number FROM accounts WHERE id = ?');
        $phoneContactQuery->bind_param('i', $userId);
        $phoneContactQuery->execute();
        $phoneContactQuery->bind_result($userPhone);
        $phoneContactQuery->fetch();
        $phoneContactQuery->close();
        $contact = $userPhone;
    } elseif ($label === 'email'){
        $contactType = 'email';
        $emailContactQuery = $con->prepare('SELECT email FROM accounts WHERE id = ?');
        $emailContactQuery->bind_param('i', $userId);
        $emailContactQuery->execute();
        $emailContactQuery->bind_result($userEmail);
        $emailContactQuery->fetch();
        $emailContactQuery->close();
        $contact = $userEmail;
    }

    //file upload
    $target_dir = "images/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadable = true;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    if(isset($_POST["submit"])){
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false){
            $uploadable = true;
        } else{
            echo "File is not an image.";
            $uploadable = false;
        }
    }

    if (file_exists($target_file)){
        echo "Sorry, file already exists.";
        $uploadable = false;
    }

    if ($_FILES["fileToUpload"]["size"] > 5000000){
        echo "Sorry, your file is too large.";
        $uploadable = false;
    }

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"){
        echo "Sorry, only JPG, JPEG & PNG files are allowed.";
        $uploadable = false;
    }

    if ($uploadable == false) {
        echo "Your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)){
            $stmt = $con->prepare('INSERT INTO requests (id, request, contact, contact_type, images, posted_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('issss', $userId, $request, $contact, $contactType, $target_file);
            $stmt->execute();
            echo "The file ". htmlspecialchars(basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            if ($stmt->affected_rows > 0){
                echo ' Request submitted successfully!';
            } else{
                echo ' Error submitting the request.';
            } 
            $stmt->close();
            $con->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

} else{
    header('Location: index.html');
    exit();
}

