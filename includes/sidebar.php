<div class="sidebar">
    <div class="profile">
        <?php
        if(isset($_SESSION['user_id'])){
            include 'db.php'; // alleen nodig als $pdo nog niet bestaat
            $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $profile_img = !empty($user_data['profile_pic']) ? 'uploads/' . $user_data['profile_pic'] : 'images/profile.png';
            $username = htmlspecialchars($user_data['username']);
        } else {
            $profile_img = 'images/profile.png';
            $username = 'Gast';
        }
        ?>
        <img src="<?= $profile_img ?>" alt="Profielfoto" class="profile-img">
        <p><?= $username ?></p>
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
