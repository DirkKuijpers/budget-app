<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Uitloggen...</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div id="success-popup" class="popup">
    <i class="fas fa-check-circle"></i> Succesvol uitgelogd!
</div>

<script>
    const popup = document.getElementById('success-popup');
    popup.classList.add('show');

    setTimeout(() => {
        popup.classList.remove('show');
        window.location.href = "login.php";
    },500);
</script>

</body>
</html>
