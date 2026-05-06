<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Huidig wachtwoord ophalen
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user || !password_verify($current, $user['password'])){
    $_SESSION['error'] = "Huidig wachtwoord klopt niet!";
    header('Location: settings.php');
    exit();
}

if($new !== $confirm){
    $_SESSION['error'] = "Nieuwe wachtwoorden komen niet overeen!";
    header('Location: settings.php');
    exit();
}

// Wachtwoord updaten
$hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hash, $user_id]);

$_SESSION['success'] = "Wachtwoord succesvol gewijzigd!";
header('Location: settings.php');
exit();
