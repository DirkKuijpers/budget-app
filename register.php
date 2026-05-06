<?php
session_start();
include 'db.php';

$message = '';
$register_success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check of gebruikersnaam of e-mail al bestaat
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

    if($userExists){
        $message = 'Gebruikersnaam of e-mail bestaat al!';
    } else {
        // Hash wachtwoord
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Voeg gebruiker toe aan database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if($stmt->execute([$username, $email, $password_hash])){
            $register_success = true;
        } else {
            $message = 'Er is iets misgegaan. Probeer het opnieuw.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>MyBudgetApp - Registreren</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Succes popup -->
<div id="success-popup" class="popup">
    <i class="fas fa-check-circle"></i> Account succesvol aangemaakt!
</div>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="app-title">MyBudgetApp</h1>

        <?php if($message): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">
            <div class="input-group">
                <input type="text" name="username" placeholder="Gebruikersnaam" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="E-mailadres" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Wachtwoord" id="passwordReg" required>
                <button type="button" id="togglePasswordReg"><i class="fas fa-eye"></i></button>
            </div>
            <button type="submit">Registreren</button>
        </form>

        <p><a href="login.php">Al een account? Inloggen</a></p>
    </div>
</div>

<?php if($register_success): ?>
<script>
    const popup = document.getElementById('success-popup');
    popup.classList.add('show');

    // Popup verdwijnt na 1,5 seconde en redirect naar login
    setTimeout(() => {
        popup.classList.remove('show');
        window.location.href = "login.php";
    }, 1500);
</script>
<?php endif; ?>

<script>
// Wachtwoord toggle
const toggleBtn = document.getElementById('togglePasswordReg');
const passwordInput = document.getElementById('passwordReg');
if(toggleBtn){
    toggleBtn.addEventListener('click', () => {
        if(passwordInput.type === 'password'){
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
}
</script>

</body>
</html>
