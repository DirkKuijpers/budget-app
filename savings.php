<?php
session_start();
include 'db.php';

// Check of gebruiker is ingelogd
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

// Backend acties
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'];

    if ($_POST['action'] === 'delete_saving') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM savings WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success'=>true]);
        exit();
    }

    if ($_POST['action'] === 'add_saving') {
        $name = $_POST['name'];
        $goal = str_replace(',', '.', $_POST['goal']); // Komma -> punt
        $stmt = $pdo->prepare("INSERT INTO savings (user_id, name, goal, current) VALUES (?, ?, ?, 0)");
        $stmt->execute([$user_id, $name, $goal]);
        echo json_encode(['success'=>true]);
        exit();
    }

    if ($_POST['action'] === 'edit_saving') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $goal = str_replace(',', '.', $_POST['goal']); // Komma -> punt
        $stmt = $pdo->prepare("UPDATE savings SET name = ?, goal = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $goal, $id, $user_id]);
        echo json_encode(['success'=>true]);
        exit();
    }

    if ($_POST['action'] === 'add_money') {
        $id = $_POST['id'];
        $amount = str_replace(',', '.', $_POST['amount']); // Komma -> punt
        $stmt = $pdo->prepare("UPDATE savings SET current = current + ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$amount, $id, $user_id]);
        echo json_encode(['success'=>true]);
        exit();
    }
}

// Spaarpotjes ophalen
$stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? AND current < goal");
$stmt->execute([$_SESSION['user_id']]);
$activeSavings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? AND current >= goal");
$stmt->execute([$_SESSION['user_id']]);
$completedSavings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Spaarpotjes - MyBudgetApp</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h1>Spaarpotjes</h1>

        <!-- Knop nieuw spaarpotje -->
        <button class="btn" id="addSavingBtn"><i class="fas fa-plus"></i> Nieuw Spaarpotje</button>

        <!-- Actieve spaarpotjes -->
        <h2>Actieve Spaarpotjes</h2>
        <div class="savings-list">
            <?php foreach($activeSavings as $saving): ?>
            <div class="saving-card">
                <p><strong><?= htmlspecialchars($saving['name']) ?></strong></p>
                <p><?= number_format($saving['current'],2,',','.') ?> / <?= number_format($saving['goal'],2,',','.') ?> €</p>
                <div class="progress-bar">
                    <div class="progress" style="width: <?= ($saving['current']/$saving['goal'])*100 ?>%"></div>
                </div>
                <div class="card-buttons">
                    <button class="btn edit-btn" data-id="<?= $saving['id'] ?>"><i class="fas fa-edit"></i> Aanpassen</button>
                    <button class="btn add-money-btn" data-id="<?= $saving['id'] ?>"><i class="fas fa-coins"></i> Bedrag toevoegen</button>
                    <button class="btn delete-btn" data-id="<?= $saving['id'] ?>"><i class="fas fa-trash"></i> Verwijderen</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Voltooide spaarpotjes -->
        <h2>Voltooide Spaarpotjes</h2>
        <div class="savings-list">
            <?php foreach($completedSavings as $saving): ?>
            <div class="saving-card completed">
                <p><strong><?= htmlspecialchars($saving['name']) ?></strong></p>
                <p><?= number_format($saving['current'],2,',','.') ?> / <?= number_format($saving['goal'],2,',','.') ?> €</p>
                <div class="progress-bar">
                    <div class="progress" style="width:100%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Popups -->
<div id="addSavingPopup" class="popup-form">
    <h3>Nieuw Spaarpotje</h3>
    <form id="addSavingForm">
        <input type="text" name="name" placeholder="Naam spaarpotje" required>
        <input type="number" name="goal" placeholder="Doelbedrag (€)" step="0.01" required>
        <button type="submit"><i class="fas fa-plus"></i> Toevoegen</button>
        <button type="button" class="close-popup">Annuleren</button>
    </form>
</div>

<div id="editSavingPopup" class="popup-form">
    <h3>Spaarpotje aanpassen</h3>
    <form id="editSavingForm">
        <input type="hidden" name="id">
        <input type="text" name="name" placeholder="Naam spaarpotje" required>
        <input type="number" name="goal" placeholder="Doelbedrag (€)" step="0.01" required>
        <button type="submit"><i class="fas fa-save"></i> Opslaan</button>
        <button type="button" class="close-popup">Annuleren</button>
    </form>
</div>

<div id="addMoneyPopup" class="popup-form">
    <h3>Bedrag toevoegen</h3>
    <form id="addMoneyForm">
        <input type="hidden" name="id">
        <input type="number" name="amount" placeholder="Bedrag (€)" step="0.01" required>
        <button type="submit"><i class="fas fa-coins"></i> Toevoegen</button>
        <button type="button" class="close-popup">Annuleren</button>
    </form>
</div>

<script src="js/script.js"></script>
<script>
    
</script>

</body>
</html>
