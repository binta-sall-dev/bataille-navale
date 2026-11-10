<?php
require_once 'Config/DB.php';
session_start();

// Vider toutes les variables de partie
unset($_SESSION['mer']);
unset($_SESSION['navires']);
unset($_SESSION['navire_selectionne']);
unset($_SESSION['orientation']);
unset($_SESSION['id_partie']);
unset($_SESSION['ordre']);

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    exit;
}

$pdo = DB::getInstance();
$stmt = $pdo->query('
    SELECT j.login, s.points, s.resultat, s.nb_tirs, s.nb_touches, p.date_debut
    FROM score s
    JOIN joueur j ON s.id_joueur = j.id_joueur
    JOIN partie p ON s.id_partie = p.id_partie
    ORDER BY p.date_debut DESC
');
$scores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scores — Fish & Ships</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
    <style>
        body { padding: 0; background: #0a1628; }
        .contenu { padding: 40px; }
        h1 { text-align: center; margin-bottom: 30px; color: #FFEDAB; }
        .retour { display: inline-block; margin-bottom: 20px; color: #aaa; text-decoration: none; font-size: 14px; }
        .retour:hover { color: white; }
        table { width: 100%; border-collapse: collapse; background: #16213e; border-radius: 10px; overflow: hidden; }
        th { background: #0053F1; padding: 12px 16px; text-align: left; font-size: 14px; color: #FFEDAB; }
        td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #0a1628; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #1a2a4a; }
        .gagnant { color: #2ecc71; font-weight: bold; }
        .perdant  { color: #FF454A; }
        .abandon  { color: #aaa; }
        .vide { text-align: center; padding: 40px; color: #aaa; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="contenu">
    <a class="retour" href="lobby.php">← Retour au lobby</a>
    <h1> Tableau des scores</h1>
    <table>
        <thead>
            <tr>
                <th>Joueur</th>
                <th>Résultat</th>
                <th>Points</th>
                <th>Tirs</th>
                <th>Touches</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($scores)): ?>
                <tr><td colspan="6" class="vide">Aucune partie jouée pour l'instant.</td></tr>
            <?php else: ?>
                <?php foreach ($scores as $score): ?>
                <tr>
                    <td><?= htmlspecialchars($score['login']) ?></td>
                    <td class="<?= $score['resultat'] ?>"><?= ucfirst(htmlspecialchars($score['resultat'])) ?></td>
                    <td><?= $score['points'] ?></td>
                    <td><?= $score['nb_tirs'] ?></td>
                    <td><?= $score['nb_touches'] ?></td>
                    <td><?= $score['date_debut'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>