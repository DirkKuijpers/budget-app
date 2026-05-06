<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $amount = str_replace(',', '.', $_POST['amount']); // Komma -> punt
    $date = $_POST['date'];
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO income (user_id, amount, date, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $amount, $date, $description]);
}

header('Location: income.php');
exit();
