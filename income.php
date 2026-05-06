<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Laatste betaling
$stmt = $pdo->prepare("SELECT amount, date FROM income WHERE user_id = ? ORDER BY date DESC LIMIT 1");
$stmt->execute([$user_id]);
$last_income = $stmt->fetch();

// Totaal ontvangen
$stmt = $pdo->prepare("SELECT SUM(amount) AS total FROM income WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_income = $stmt->fetchColumn();

// Data voor grafiek (laatste 6 maanden)
$stmt = $pdo->prepare("
    SELECT maand, SUM(amount) AS totaal
    FROM (
        SELECT 
            amount,
            DATE_FORMAT(date, '%b %Y') AS maand,
            YEAR(date) AS jaar,
            MONTH(date) AS maand_num
        FROM income
        WHERE user_id = ?
    ) AS sub
    GROUP BY maand, jaar
    ORDER BY jaar ASC, MONTH(STR_TO_DATE(maand, '%b')) ASC
");
$stmt->execute([$user_id]);
$income_data = $stmt->fetchAll(PDO::FETCH_ASSOC);



$stmt->execute([$user_id]);
$income_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn Inkomsten - MyBudgetApp</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h1>Inkomsten</h1>

        <button class="btn" id="addIncomeBtn"><i class="fas fa-plus"></i> Nieuwe betaling</button>

        <div class="cards-container">
            <div class="card">
                <h3>Laatste betaling</h3>
                <?php if($last_income): ?>
                    <p><strong>€<?= number_format($last_income['amount'], 2, ',', '.') ?></strong></p>
                    <p><?= date('d-m-Y', strtotime($last_income['date'])) ?></p>
                <?php else: ?>
                    <p>Geen betalingen gevonden.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Totaal ontvangen</h3>
                <p><strong>€<?= number_format($total_income ?? 0, 2, ',', '.') ?></strong></p>
            </div>

            <div class="card">
                <h3>Inkomstenoverzicht</h3>
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Popup voor nieuwe betaling -->
<div id="incomePopup" class="popup-form">
    <h3>Nieuwe betaling toevoegen</h3>
    <form method="POST" action="save_income.php">
        <input type="number" step="0.01" name="amount" placeholder="Bedrag (€)" required>
        <input type="date" name="date" required>
        <input type="text" name="description" placeholder="Omschrijving (optioneel)">
        <button type="submit" class="btn">Opslaan</button>
        <button type="button" class="btn" id="closePopup">Annuleren</button>
    </form>
</div>

<script>
const labels = <?= json_encode(array_column($income_data, 'maand')) ?>;
const data = <?= json_encode(array_column($income_data, 'totaal')) ?>;

const ctx = document.getElementById('incomeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Inkomsten (€)',
            data: data,
            backgroundColor: '#0b1d51'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Popup logica
document.getElementById('addIncomeBtn').addEventListener('click', () => {
    document.getElementById('incomePopup').classList.add('show');
});
document.getElementById('closePopup').addEventListener('click', () => {
    document.getElementById('incomePopup').classList.remove('show');
});
</script>

</body>
</html>
