<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Haal één random actief spaarpotje op voor deze gebruiker
$stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? AND current < goal ORDER BY RAND() LIMIT 1");
$stmt->execute([$user_id]);
$activeSaving = $stmt->fetch(PDO::FETCH_ASSOC);

// Haal laatste 12 inkomens op
$stmt = $pdo->prepare("SELECT amount, date FROM income WHERE user_id = ? ORDER BY date DESC LIMIT 12");
$stmt->execute([$user_id]);
$previousSalaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Budgetering
$stmt = $pdo->prepare("SELECT SUM(amount) as spent FROM budget WHERE user_id = ?");
$stmt->execute([$user_id]);
$spent = $stmt->fetchColumn() ?? 0;

// Totaal budget (pas aan naar jouw logica)
$totalBudget = 1000;
$remainingBudget = max($totalBudget - $spent, 0);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>MyBudgetApp - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h1>Dashboard</h1>

        <div class="cards-container">

            <!-- Vorige uitbetaling -->
            <div class="card">
                <h3>Vorige uitbetaling</h3>
                <?php if(!empty($previousSalaries)): ?>
                    <p>€ <?= number_format($previousSalaries[0]['amount'], 2) ?> (<?= htmlspecialchars($previousSalaries[0]['date']) ?>)</p>
                <?php else: ?>
                    <p>€ - </p>
                <?php endif; ?>
            </div>

            <!-- Salarissen staafdiagram -->
            <div class="card">
                <h3>Vorige Salarissen</h3>
                <canvas id="salaryChart"></canvas>
            </div>

            <!-- Actief spaarpotje -->
            <div class="card">
                <h3>Actief Spaarpotje</h3>
                <?php if($activeSaving): ?>
                    <p><strong><?= htmlspecialchars($activeSaving['name']) ?></strong></p>
                    <p><?= number_format($activeSaving['current'],2) ?> / <?= number_format($activeSaving['goal'],2) ?> €</p>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= ($activeSaving['current']/$activeSaving['goal'])*100 ?>%"></div>
                    </div>
                <?php else: ?>
                    <p>Geen actief spaarpotje</p>
                    <div class="progress-bar">
                        <div class="progress" style="width:0%"></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Budgetering cirkeldiagram -->
            <div class="card">
                <h3>Budgetering</h3>
                <canvas id="budgetChart"></canvas>
            </div>

        </div>
    </div>

</div>

<script>
    // Salaris staafdiagram
    const salaryCtx = document.getElementById('salaryChart').getContext('2d');
    const salaryData = <?php echo json_encode(array_column($previousSalaries, 'amount')); ?>.reverse();
    const salaryLabels = <?php echo json_encode(array_column($previousSalaries, 'date')); ?>.reverse();
    const salaryChart = new Chart(salaryCtx, {
        type: 'bar',
        data: {
            labels: salaryLabels,
            datasets: [{
                label: 'Salaris (€)',
                data: salaryData,
                backgroundColor: '#0b1d51'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Budgetering cirkeldiagram
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'doughnut',
        data: {
            labels: ['Nog te besteden', 'Uitgegeven'],
            datasets: [{
                data: [<?= $remainingBudget ?>, <?= $spent ?>],
                backgroundColor: ['#0b1d51', '#00aaff']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

</body>
</html>
