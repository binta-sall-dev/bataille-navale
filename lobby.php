<?php
require_once 'Class/Navires/Navire.php';
require_once 'Class/Navires/Baleine.php';
require_once 'Class/Navires/Anguille.php';
require_once 'Class/Navires/Requin.php';
require_once 'Class/Navires/Colore.php';
require_once 'Class/Navires/Clown.php';
require_once 'Class/Plateau/Case.php';
require_once 'Class/Plateau/Mer.php';
require_once 'Config/db.php';

session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    exit;
}

$joueur   = $_SESSION['joueur'];
$idJoueur = $joueur['id_joueur'];
$pdo      = DB::getInstance();

// ACTION D'ABANDON PENDANT L'ATTENTE ADVERSE
if (isset($_POST['abandonner_attente'])) {
    $idPartieEnCours = $_SESSION['id_partie'] ?? null;
    if ($idPartieEnCours) {
        $pdo->prepare('UPDATE partie SET statut = "terminee", date_fin = NOW() WHERE id_partie = ?')->execute([$idPartieEnCours]);
        $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) VALUES (?, ?, 0, "abandon", 0, 0)')->execute([$idJoueur, $idPartieEnCours]);
    }
    unset($_SESSION['id_partie'], $_SESSION['ordre']);
    header('Location: lobby.php');
    exit;
}

// SAUVEGARDER LA GRILLE si placement terminé
if (isset($_SESSION['mer'], $_SESSION['id_partie'])) {
    $tousPlaces = true;
    if (isset($_SESSION['navires'])) {
        foreach ($_SESSION['navires'] as $n) {
            if (!$n->place) { $tousPlaces = false; break; }
        }
    }

    if ($tousPlaces) {
        $grilleSerialisee = serialize($_SESSION['mer']);
        
        $stmt = $pdo->prepare('SELECT grille FROM participer WHERE id_joueur = ? AND id_partie = ?');
        $stmt->execute([$idJoueur, $_SESSION['id_partie']]);
        $row = $stmt->fetch();

        if ($row && $row['grille'] === null) {
            $stmt = $pdo->prepare('UPDATE participer SET grille = ? WHERE id_joueur = ? AND id_partie = ?');
            $stmt->execute([$grilleSerialisee, $idJoueur, $_SESSION['id_partie']]);
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) as prets FROM participer WHERE id_partie = ? AND grille IS NOT NULL');
        $stmt->execute([$_SESSION['id_partie']]);
        $result = $stmt->fetch();

        if ($result['prets'] == 2) {
            $stmt = $pdo->prepare('SELECT id_joueur FROM participer WHERE id_partie = ? ORDER BY RAND() LIMIT 1');
            $stmt->execute([$_SESSION['id_partie']]);
            $premier = $stmt->fetch();

            $stmt = $pdo->prepare('UPDATE partie SET statut = "en_cours", id_joueur_actif = ? WHERE id_partie = ?');
            $stmt->execute([$premier['id_joueur'], $_SESSION['id_partie']]);

            header('Location: jeu.php');
            exit;
        }

        // --- ICI : AFFICHAGE DE L'ÉCRAN D'ATTENTE AVEC LE HEADER ---
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Fish & Ships — En attente</title>
            <link rel="stylesheet" href="/bataille_navale/assets/style.css">
            <meta http-equiv="refresh" content="3">
            <style>
                body { text-align: center; background: #0a1628; color: white; padding: 0; }
                .zone-attente { padding: 80px 20px; }
                h2 { color: #2ecc71; margin-bottom: 20px; font-family: krabby patty; }
                p { color: #aaa; font-size: 16px; margin-bottom: 25px; }
                .btn-quitter { background: #c0392b; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; }
                .btn-quitter:hover { background: #a93226; }
            </style>
        </head>
        <body>';
        
        include 'header.php'; // On injecte ton vrai header ici
        
        echo '<div class="zone-attente">
            <h2>✅ Navires placés !</h2>
            <p>En attente que l\'adversaire place ses navires...</p>
            <form method="POST">
                <button type="submit" name="abandonner_attente" class="btn-quitter"> Abandonner la partie</button>
            </form>
            <p style="font-size:12px; margin-top:20px; color:#555;">La page se rafraîchit automatiquement.</p>
        </div>
        </body>
        </html>';
        exit;
    }
}

// CRÉER UNE PARTIE
if (isset($_POST['creer'])) {
    unset($_SESSION['mer'], $_SESSION['navires'], $_SESSION['navire_selectionne'], $_SESSION['orientation']);

    $stmt = $pdo->prepare('INSERT INTO partie (date_debut, statut) VALUES (NOW(), "en_attente")');
    $stmt->execute();
    $idPartie = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO participer (id_joueur, id_partie, ordre) VALUES (?, ?, 1)')->execute([$idJoueur, $idPartie]);

    $_SESSION['id_partie'] = $idPartie;
    $_SESSION['ordre']     = 1;

    header('Location: placement.php');
    exit;
}

// REJOINDRE UNE PARTIE
if (isset($_POST['rejoindre'])) {
    unset($_SESSION['mer'], $_SESSION['navires'], $_SESSION['navire_selectionne'], $_SESSION['orientation']);

    $idPartie = (int)$_POST['id_partie'];

    $stmt = $pdo->prepare('SELECT id_joueur FROM participer WHERE id_joueur = ? AND id_partie = ?');
    $stmt->execute([$idJoueur, $idPartie]);
    if ($stmt->fetch()) {
        header('Location: lobby.php');
        exit;
    }

    $pdo->prepare('UPDATE partie SET statut = "placement" WHERE id_partie = ?')->execute([$idPartie]);
    $pdo->prepare('INSERT INTO participer (id_joueur, id_partie, ordre) VALUES (?, ?, 2)')->execute([$idJoueur, $idPartie]);

    $_SESSION['id_partie'] = $idPartie;
    $_SESSION['ordre']     = 2;

    header('Location: placement.php');
    exit;
}

// PARTIES EN ATTENTE
$stmt = $pdo->query('
    SELECT p.id_partie, j.login AS joueur1
    FROM partie p
    JOIN participer pa ON pa.id_partie = p.id_partie AND pa.ordre = 1
    JOIN joueur j ON j.id_joueur = pa.id_joueur
    WHERE p.statut = "en_attente"
');
$parties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lobby — Bataille Navale</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
    <style>
        body { padding: 0; }
        .contenu { 
    padding: 40px; 
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
        h1 { color: #e74c3c; margin-bottom: 10px;  font-family: krabby patty; }
        .bienvenue { color: #aaa; margin-bottom: 30px; font-size: 14px; }
        .btn-creer { background: #F25E2E; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 16px; margin-bottom: 40px; display: inline-block; }
        .btn-creer:hover { background: #cc4e24; }
        h2 { margin-bottom: 16px; color: #aaa; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;font-family: krabby patty; }
        table { 
    width: 100%; 
    max-width: 500px; 
    border-collapse: collapse; 
    margin: 0 auto;
}
        th { color: #aaa; font-size: 13px; padding: 10px 16px; text-align: left; }
        td { background: #16213e; padding: 12px 16px; border-bottom: 1px solid #0a1628; }
        .btn-rejoindre { background: #3498db; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; }
        .btn-rejoindre:hover { background: #2980b9; }
        .aucune { color: #555; font-size: 14px; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="contenu">
    <h1>Bataille Navale</h1>
    <p class="bienvenue">Bonjour <strong><?= htmlspecialchars($joueur['login']) ?></strong> !</p>

    <form method="POST">
        <button class="btn-creer" type="submit" name="creer">+ Créer une partie</button>
    </form>

    <h2>Parties en attente</h2>

    <?php if (empty($parties)): ?>
        <p class="aucune">Aucune partie en attente pour le moment.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Partie</th>
                <th>Joueur 1</th>
                <th>Action</th>
            </tr>
            <?php foreach ($parties as $partie): ?>
            <tr>
                <td>#<?= $partie['id_partie'] ?></td>
                <td><?= htmlspecialchars($partie['joueur1']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id_partie" value="<?= $partie['id_partie'] ?>">
                        <button class="btn-rejoindre" type="submit" name="rejoindre">Rejoindre</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>