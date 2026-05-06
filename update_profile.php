<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Data van het formulier
$username = trim($_POST['username']);
$email = trim($_POST['email']);

// Basis validatie
if(empty($username) || empty($email)){
    $_SESSION['error'] = "Vul alle velden in.";
    header("Location: settings.php");
    exit();
}

// Profielfoto uploaden
$profile_pic = null;
if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK){
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileSize = $_FILES['profile_pic']['size'];
    $fileType = $_FILES['profile_pic']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedExtensions = ['jpg','jpeg','png','gif'];

    if(in_array($fileExtension, $allowedExtensions)){
        $newFileName = "profile_" . $user_id . "." . $fileExtension;
        $uploadFileDir = "uploads/";
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)){
            $profile_pic = $newFileName;
        } else {
            $_SESSION['error'] = "Er is een fout opgetreden bij het uploaden van de foto.";
            header("Location: settings.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Alleen jpg, jpeg, png en gif zijn toegestaan.";
        header("Location: settings.php");
        exit();
    }
}

try {
    // Database update
    if($profile_pic){
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_pic = ? WHERE id = ?");
        $stmt->execute([$username, $email, $profile_pic, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user_id]);
    }

    $_SESSION['success'] = "Profiel succesvol bijgewerkt!";
    header("Location: settings.php");
    exit();

} catch(PDOException $e){
    $_SESSION['error'] = "Database fout: " . $e->getMessage();
    header("Location: settings.php");
    exit();
}
