<?php
session_start();
include 'db.php';

$message = '';
$login_success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Haal gebruiker op uit DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        // Login succesvol
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $login_success = true;
    } else {
        $message = 'Gebruikersnaam of wachtwoord incorrect!';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>MyBudgetApp - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<!-- Succes popup -->
<div id="success-popup" class="popup">
    <i class="fas fa-check-circle"></i> Succesvol ingelogd!
</div>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="app-title">MyBudgetApp</h1>

        <?php if($message): ?>
            <p class="message error"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">
            <div class="input-group">
                <input type="text" name="username" placeholder="Gebruikersnaam" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Wachtwoord" id="password" required>
                <button type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
            </div>
            <button type="submit">Inloggen!</button>
        </form>

        <p><a href="register.php">Geen account? Registreren</a></p>
    </div>
</div>

<?php if($login_success): ?>
<script>
    const popup = document.getElementById('success-popup');
    popup.classList.add('show');
    setTimeout(() => {
        popup.classList.remove('show');
        window.location.href = "dashboard.php";
    }, 1500);
</script>
<?php endif; ?>

<script>
    // Toggle wachtwoord
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        if(passwordInput.type === 'password'){
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
        togglePassword.querySelector('i').classList.toggle('fa-eye');
        togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>
