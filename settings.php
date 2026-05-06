<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Feedback berichten
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Haal user data op
$stmt = $pdo->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die("Gebruiker niet gevonden.");
}

$profile_pic = !empty($user['profile_pic']) ? 'uploads/' . $user['profile_pic'] : 'images/profile.png';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Instellingen - MyBudgetApp</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .settings-tabs { display:flex; gap:1rem; margin-bottom:1.5rem; }
        .tab-btn { padding:0.5rem 1rem; cursor:pointer; border:none; border-radius:5px; background:#ddd; transition: all 0.3s ease; }
        .tab-btn.active { background:#0b1d51; color:white; }
        .tab { display:none; }
        .tab.active { display:block; }

        .popup-form { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:2rem; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:1000; width:100%; max-width:400px; box-sizing:border-box; }
        .popup-form input, .popup-form label, .popup-form button { width:100%; box-sizing:border-box; }

        .message { padding:0.5rem; border-radius:8px; margin-bottom:1rem; font-size:0.9rem; }
        .message.error { background-color:#e63946; color:white; }
        .message.success { background-color:#0b1d51; color:white; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile">
            <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profielfoto" class="profile-img">
            <p><?= htmlspecialchars($user['username']) ?></p>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Uitloggen</a>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="savings.php" class="<?= basename($_SERVER['PHP_SELF'])=='savings.php' ? 'active':'' ?>"><i class="fas fa-piggy-bank"></i> Spaarpotjes</a></li>
                <li><a href="income.php" class="<?= basename($_SERVER['PHP_SELF'])=='income.php' ? 'active':'' ?>"><i class="fas fa-wallet"></i> Inkomen</a></li>
                <li><a href="budget.php" class="<?= basename($_SERVER['PHP_SELF'])=='budget.php' ? 'active':'' ?>"><i class="fas fa-chart-pie"></i> Budgetering</a></li>
                <li><a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php' ? 'active':'' ?>"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <h1>Instellingen</h1>

        <!-- Feedback -->
        <?php if($success): ?>
            <p class="message success"><?= $success ?></p>
        <?php endif; ?>
        <?php if($error): ?>
            <p class="message error"><?= $error ?></p>
        <?php endif; ?>

        <div class="settings-tabs">
            <button class="tab-btn active" data-tab="profile">Profiel</button>
            <button class="tab-btn" data-tab="password">Wachtwoord wijzigen</button>
            <button class="tab-btn" data-tab="delete">Account verwijderen</button>
        </div>

        <div class="settings-content">
            <!-- Profiel -->
            <div class="tab active" id="profile">
                <div class="card">
                    <h3>Profiel</h3>
                    <img src="<?= htmlspecialchars($profile_pic) ?>" width="100" height="100" class="profile-img">
                    <p><strong>Gebruikersnaam:</strong> <?= htmlspecialchars($user['username']); ?></p>
                    <p><strong>E-mail:</strong> <?= htmlspecialchars($user['email']); ?></p>
                    <button class="btn" id="editProfileBtn">Profiel bewerken</button>
                </div>
            </div>

            <!-- Wachtwoord wijzigen -->
            <div class="tab" id="password">
                <div class="card">
                    <h3>Wachtwoord wijzigen</h3>
                    <form method="POST" action="update_password.php">
                        <input type="password" name="current_password" placeholder="Huidig wachtwoord" required><br><br>
                        <input type="password" name="new_password" placeholder="Nieuw wachtwoord" required><br><br>
                        <input type="password" name="confirm_password" placeholder="Bevestig nieuw wachtwoord" required><br><br>
                        <button type="submit" class="btn">Opslaan</button>
                    </form>
                </div>
            </div>

            <!-- Account verwijderen -->
            <div class="tab" id="delete">
                <div class="card">
                    <h3>Account verwijderen</h3>
                    <p>Weet je zeker dat je je account wilt verwijderen? Dit kan niet ongedaan worden gemaakt!</p>
                    <button class="btn btn-danger" id="deleteAccountBtn">Account verwijderen</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popups -->
<div id="editProfilePopup" class="popup-form">
    <h3>Profiel bewerken</h3>
    <form method="POST" action="update_profile.php" enctype="multipart/form-data">
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required><br><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required><br><br>
        <label>Profielfoto:</label>
        <input type="file" name="profile_pic" accept="image/*"><br><br>
        <button type="submit" class="btn">Opslaan</button>
        <button type="button" class="btn close-popup">Annuleren</button>
    </form>
</div>

<div id="deletePopup" class="popup-form">
    <h3>Account verwijderen</h3>
    <p>Weet je zeker dat je je account wilt verwijderen? Dit kan niet ongedaan worden gemaakt.</p>
    <form method="POST" action="delete_account.php">
        <button type="submit" class="btn btn-danger">Ja, verwijderen</button>
        <button type="button" class="btn close-popup">Annuleren</button>
    </form>
</div>

<script>
    // Tabs functionaliteit
    const tabs = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.dataset.tab;
            tabContents.forEach(c => c.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
    });

    // Popups
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfilePopup = document.getElementById('editProfilePopup');
    const deleteBtn = document.getElementById('deleteAccountBtn');
    const deletePopup = document.getElementById('deletePopup');

    document.querySelectorAll('.close-popup').forEach(btn => {
        btn.addEventListener('click', () => {
            editProfilePopup.classList.remove('show');
            deletePopup.classList.remove('show');
        });
    });

    if(editProfileBtn) editProfileBtn.addEventListener('click', () => editProfilePopup.classList.add('show'));
    if(deleteBtn) deleteBtn.addEventListener('click', () => deletePopup.classList.add('show'));
</script>

</body>
</html>
