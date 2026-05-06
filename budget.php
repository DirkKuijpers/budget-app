<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Voeg nieuw budget toe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    $category = trim($_POST['category']);
    $amount = str_replace(',', '.', $_POST['amount']);
    $spent = 0;

    if (!empty($category) && is_numeric($amount) && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO budget (user_id, category, amount, spent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $category, $amount, $spent]);
    }
    header('Location: budget.php');
    exit();
}

// Verwijder budget
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete']; // Veilige cast naar integer
    if($id > 0){
        $stmt = $pdo->prepare("DELETE FROM budget WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
    header('Location: budget.php');
    exit();
}

// Haal budgetten op
$stmt = $pdo->prepare("SELECT * FROM budget WHERE user_id = ?");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bereken totaal
$total = $totalSpent = 0;
foreach ($budgets as $b) {
    $total += $b['amount'] ?? 0;
    $totalSpent += $b['spent'] ?? 0;
}
$total = round($total, 2);
$totalSpent = round($totalSpent, 2);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Budgettering - MyBudgetApp</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashboard-container { display:flex; }
        .main-content { flex:1; padding:2rem; }
        .budget-container { background:#f9f9fc; border-radius:15px; padding:2rem; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
        .budget-overview { display:flex; justify-content:space-between; flex-wrap:wrap; margin-bottom:2rem; }
        .budget-stat { background:#fff; padding:1rem 2rem; border-radius:10px; margin:0.5rem; flex:1; text-align:center; min-width:220px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
        .budget-list { margin-top:2rem; }
        .budget-item { background:#fff; padding:1rem; border-radius:12px; box-shadow:0 3px 8px rgba(0,0,0,0.08); margin-bottom:1rem; }
        .progress-bar { height:12px; background:#ddd; border-radius:10px; margin-top:0.5rem; overflow:hidden; }
        .progress { height:100%; background:linear-gradient(90deg, #0b1d51, #3a86ff); }
        .add-budget { background:#fff; padding:1.5rem; border-radius:15px; box-shadow:0 3px 10px rgba(0,0,0,0.1); margin-bottom:2rem; }
        input { width:100%; padding:0.6rem; margin-bottom:1rem; border-radius:8px; border:1px solid #ccc; }
        button { background:#0b1d51; color:#fff; border:none; padding:0.7rem 1.5rem; border-radius:8px; cursor:pointer; transition:0.2s; }
        button:hover { background:#1b2e7c; }
        .delete-btn { background:#e63946; margin-left:1rem; }
        .delete-btn:hover { background:#d62828; }
    </style>
</head>
<body>
<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h1>Budgettering</h1>

        <div class="budget-container">

            <div class="budget-overview">
                <div class="budget-stat">
                    <h3>Totaal budget</h3>
                    <p>€ <?= number_format($total,2,',','.') ?></p>
                </div>
                <div class="budget-stat">
                    <h3>Totaal uitgegeven</h3>
                    <p>€ <?= number_format($totalSpent,2,',','.') ?></p>
                </div>
                <div class="budget-stat">
                    <h3>Resterend</h3>
                    <p>€ <?= number_format($total - $totalSpent,2,',','.') ?></p>
                </div>
            </div>

            <div class="add-budget">
                <h3>Nieuw budget toevoegen</h3>
                <form method="POST">
                    <input type="text" name="category" placeholder="Categorie (bijv. boodschappen)" required>
                    <input type="number" step="0.01" min="0.01" name="amount" placeholder="Bedrag (€)" required>
                    <button type="submit" name="add_budget"><i class="fas fa-plus"></i> Toevoegen</button>
                </form>
            </div>

            <div class="budget-list">
                <h3>Mijn budgetten</h3>
                <?php if(count($budgets) > 0): ?>
                    <?php foreach($budgets as $b):
                        $amount = $b['amount'] ?? 0;
                        $spent = $b['spent'] ?? 0;
                        $percentage = ($amount > 0) ? ($spent / $amount) * 100 : 0;
                        $percentage = min(100,max(0,$percentage));
                    ?>
                        <div class="budget-item">
                            <h3><?= htmlspecialchars($b['category']) ?></h3>
                            <p>Uitgegeven: €<?= number_format($spent,2,',','.') ?> / €<?= number_format($amount,2,',','.') ?></p>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <div style="margin-top:0.7rem;">
                                <a href="edit_budget.php?id=<?= (int)$b['id'] ?>"><button><i class="fas fa-edit"></i> Bewerken</button></a>
                                <a href="?delete=<?= (int)$b['id'] ?>" onclick="return confirm('Weet je zeker dat je dit budget wilt verwijderen?');"><button class="delete-btn"><i class="fas fa-trash"></i> Verwijderen</button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Je hebt nog geen budgetten toegevoegd.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>
