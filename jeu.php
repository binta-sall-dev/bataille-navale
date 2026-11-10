<?php
// ====================================================
// 1. INCLUSIONS & SESSIONS
// ====================================================
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

$poissons = [
    'baleine'  => '/bataille_navale/assets/baleine.png',
    'anguille' => '/bataille_navale/assets/anguille.png',
    'requin'   => '/bataille_navale/assets/requin.png',
    'colore'   => '/bataille_navale/assets/colore.png',
    'clown'    => '/bataille_navale/assets/clown.png',
];

if (!isset($_SESSION['joueur'])) { header('Location: login.php'); exit; }

$pdo      = DB::getInstance();
$idJoueur = $_SESSION['joueur']['id_joueur'];
$idPartie = $_SESSION['id_partie'] ?? null;

if (!$idPartie) { header('Location: lobby.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM partie WHERE id_partie = ?');
$stmt->execute([$idPartie]);
$partie = $stmt->fetch();
if (!$partie) { header('Location: lobby.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM participer WHERE id_partie = ? ORDER BY ordre ASC');
$stmt->execute([$idPartie]);
$participants = $stmt->fetchAll();

// ====================================================
// GESTION DE L'ABANDON (Placé tôt pour fonctionner aussi en attente)
// ====================================================
if (isset($_POST['abandonner'])) {
    $pdo->prepare('UPDATE partie SET statut = "terminee", date_fin = NOW() WHERE id_partie = ?')->execute([$idPartie]);
    
    // Si l'adversaire est là, on lui donne ses points, sinon on insère juste mon abandon
    if (count($participants) == 2) {
        foreach ($participants as $p) {
            if ($p['id_joueur'] == $idJoueur) $monP = $p;
            else $sonP = $p;
        }
        $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) VALUES (?, ?, 0, "abandon", ?, ?)')->execute([$idJoueur, $idPartie, $monP['nb_tirs'], $monP['nb_touches']]);
        $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) VALUES (?, ?, ?, "gagnant", ?, ?)')->execute([$sonP['id_joueur'], $idPartie, $sonP['nb_touches'], $sonP['nb_tirs'], $sonP['nb_touches']]);
    } else {
        $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) VALUES (?, ?, 0, "abandon", 0, 0)')->execute([$idJoueur, $idPartie]);
    }
    unset($_SESSION['id_partie'], $_SESSION['ordre']);
    header('Location: lobby.php');
    exit;
}

// Configuration des variables de base pour l'affichage des templates
$lettres  = ['A','B','C','D','E','F','G','H','I','J'];
$cellSize = 38;

// ====================================================
// ÉCRANS D'ATTENTE COMPLETS (Avec Header et bouton Abandon)
// ====================================================

// Cas 1 : Moins de 2 joueurs dans la partie
if (count($participants) < 2) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Fish & Ships — En attente</title>
        <link rel="stylesheet" href="/bataille_navale/assets/style.css">
        <meta http-equiv="refresh" content="3">
        <style>body{text-align:center;background:#0a1628;color:white;}.zone-attente{padding:80px 20px;}h2{color:#3b9eff;margin-bottom:15px;}.btn-abandon{background:#c0392b;color:white;border:none;padding:10px 24px;border-radius:6px;cursor:pointer;font-weight:bold;margin-top:20px;}</style>
    </head>
    <body>
        <?php include 'header.php'; ?>
        <div class="zone-attente">
            <h2> En attente d'un adversaire...</h2>
            <p>La page se rafraîchit automatiquement dès qu'un joueur rejoint la partie.</p>
            <form method="POST"><button type="submit" name="abandonner" class="btn-abandon">🏳 Quitter la partie</button></form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$monParticipant = null;
$sonParticipant = null;
foreach ($participants as $p) {
    if ($p['id_joueur'] == $idJoueur) $monParticipant = $p;
    else $sonParticipant = $p;
}

$maGrille = unserialize($monParticipant['grille']);
$saGrille = unserialize($sonParticipant['grille']);

// Cas 2 : L'adversaire n'a pas encore fini de placer ses poissons
if (!$maGrille || !$saGrille) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Fish & Ships — Placement</title>
        <link rel="stylesheet" href="/bataille_navale/assets/style.css">
        <meta http-equiv="refresh" content="3">
        <style>body{text-align:center;background:#0a1628;color:white;}.zone-attente{padding:80px 20px;}h2{color:#2ecc71;margin-bottom:15px;}.btn-abandon{background:#c0392b;color:white;border:none;padding:10px 24px;border-radius:6px;cursor:pointer;font-weight:bold;margin-top:20px;}</style>
    </head>
    <body>
        <?php include 'header.php'; ?>
        <div class="zone-attente">
            <h2> Vos poissons sont placés !</h2>
            <p>En attente que l'adversaire (<strong><?= htmlspecialchars($sonParticipant['id_joueur']) ?></strong>) termine son placement...</p>
            <p style="color:#555;font-size:12px;margin-top:5px;">La page se rafraîchit automatiquement.</p>
            <form method="POST"><button type="submit" name="abandonner" class="btn-abandon">🏳 Abandonner la partie</button></form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$cEstMonTour = ($partie['id_joueur_actif'] == $idJoueur);

// ====================================================
// 2. TRAITEMENT DU TIR MULTIJOUEUR
// ====================================================
if ($cEstMonTour && isset($_POST['tir_l'], $_POST['tir_c']) && $partie['statut'] === 'en_cours') {
    $l = (int)$_POST['tir_l'];
    $c = (int)$_POST['tir_c'];
    $resultat = $saGrille->recevoirTir($l, $c);
    
    // --- LES CROIX AUTOMATIQUES DANS LA ZONE TAMPON QUAND UN POISSON EST COULÉ ---
    if ($resultat === Cellule::COULE) {
        $idNavireCoule = $saGrille->grille[$l][$c]->idNavire;
        
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $cell = $saGrille->grille[$i][$j];
                // Si la case fait partie du contour (estZoneTampon) et qu'il n'y a pas d'autre poisson dessus
                if ($cell->idNavire === null && $cell->estZoneTampon) {
                    // On vérifie si elle touche directement une des cases du poisson qui vient de couler
                    for ($dl = -1; $dl <= 1; $dl++) {
                        for ($dc = -1; $dc <= 1; $dc++) {
                            if (isset($saGrille->grille[$i+$dl][$j+$dc])) {
                                if ($saGrille->grille[$i+$dl][$j+$dc]->idNavire === $idNavireCoule) {
                                    $cell->etat = Cellule::RATE; // Devient une croix '✕'
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $pdo->prepare('UPDATE participer SET grille = ? WHERE id_joueur = ? AND id_partie = ?')->execute([serialize($saGrille), $sonParticipant['id_joueur'], $idPartie]);
    $pdo->prepare('UPDATE participer SET nb_tirs = nb_tirs + 1 WHERE id_joueur = ? AND id_partie = ?')->execute([$idJoueur, $idPartie]);

    if ($resultat === Cellule::TOUCHE || $resultat === Cellule::COULE) {
        $pdo->prepare('UPDATE participer SET nb_touches = nb_touches + 1 WHERE id_joueur = ? AND id_partie = ?')->execute([$idJoueur, $idPartie]);
        if ($saGrille->estPerdu()) {
            $pdo->prepare('UPDATE partie SET statut = "terminee", date_fin = NOW() WHERE id_partie = ?')->execute([$idPartie]);
            $stmt = $pdo->prepare('SELECT nb_tirs FROM participer WHERE id_joueur = ? AND id_partie = ?');
            $stmt->execute([$idJoueur, $idPartie]);
            $nb  = $stmt->fetch();
            $pts = max(1, 100 - $nb['nb_tirs']);
            $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) SELECT id_joueur, id_partie, ?, "gagnant", nb_tirs, nb_touches FROM participer WHERE id_joueur = ? AND id_partie = ?')->execute([$pts, $idJoueur, $idPartie]);
            $pdo->prepare('INSERT INTO score (id_joueur, id_partie, points, resultat, nb_tirs, nb_touches) SELECT id_joueur, id_partie, nb_touches, "perdant", nb_tirs, nb_touches FROM participer WHERE id_joueur = ? AND id_partie = ?')->execute([$sonParticipant['id_joueur'], $idPartie]);
        }
    } else {
        $pdo->prepare('UPDATE partie SET id_joueur_actif = ? WHERE id_partie = ?')->execute([$sonParticipant['id_joueur'], $idPartie]);
    }
    header('Location: jeu.php');
    exit;
}

// ============================================================
// PRÉCALCULER LES NAVIRES COULÉS
// ============================================================
function calculerNaviresCoulesInfo($grille) {
    $info = [];
    foreach ($grille->grille as $rangee) {
        foreach ($rangee as $cell) {
            if ($cell->etat === Cellule::COULE && $cell->idNavire) {
                $id = $cell->idNavire;
                if (!isset($info[$id])) $info[$id] = ['cases' => []];
                $info[$id]['cases'][] = [$cell->ligne, $cell->colonne];
            }
        }
    }
    foreach ($info as $id => &$data) {
        $cases = $data['cases'];
        usort($cases, fn($a, $b) => $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0]);
        $data['cases']      = $cases;
        $data['premiere']   = $cases[0];
        $data['taille']     = count($cases);
        $data['horizontal'] = ($cases[0][0] === $cases[count($cases)-1][0]);
    }
    unset($data);
    return $info;
}

$naviresCoulesEnnemi = calculerNaviresCoulesInfo($saGrille);
$naviresCoulesPerso  = calculerNaviresCoulesInfo($maGrille);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fish & Ships — Jeu</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
    <?php if (!$cEstMonTour && $partie['statut'] === 'en_cours'): ?>
    <meta http-equiv="refresh" content="3">
    <?php endif; ?>
    <style>
        body { text-align: center; padding: 0; background: #0a1628; }
        .contenu-jeu { padding: 20px; }
        h1 { color: #e74c3c; margin-bottom: 10px; }
        .info { color: #aaa; margin-bottom: 10px; font-size: 14px; }
        .tour { font-size: 20px; font-weight: bold; margin-bottom: 15px; padding: 10px 24px; border-radius: 8px; display: inline-block; }
        .tour.mon-tour  { background: rgba(46,204,113,0.15); color: #2ecc71; border: 1px solid #2ecc71; }
        .tour.leur-tour { background: rgba(231,76,60,0.15);  color: #e74c3c; border: 1px solid #e74c3c; }
        .page-layout { display: flex; justify-content: center; align-items: flex-start; gap: 40px; max-width: 1300px; margin: 0 auto; padding: 20px; }
        .arena { display: flex; gap: 40px; flex-wrap: wrap; }
        .plateau h4 { margin-bottom: 12px; font-size: 13px; color: #aaa; letter-spacing: 2px; text-transform: uppercase; }
        table { border-collapse: collapse; }
        td, th { width: <?= $cellSize ?>px; height: <?= $cellSize ?>px; border: 1px solid #1e3a5f; text-align: center; vertical-align: middle; font-size: 12px; position: relative; padding: 0; overflow: visible; }
        th { color: #aaa; font-weight: normal; overflow: hidden; }
        .ma-grille td { background: #0d1f3c; }
        .ma-grille td.navire { background: #2c5f9e; }
        .ma-grille td.touche { background: #e67e22; }
        .ma-grille td.coule  { background: #0d1f3c; }
        .ma-grille td.rate   { background: #1a2a3a; }
        .grille-ennemie td { background: #0d1f3c; }
        .grille-ennemie td.cible { background: #0d2a4a; cursor: crosshair; }
        .grille-ennemie td.cible:hover { background: #1a4a7a; }
        .grille-ennemie td.touche { background: #e67e22; }
        .grille-ennemie td.coule  { background: #0d1f3c; }
        .grille-ennemie td.rate   { background: #1a2a3a; }
        .marqueur { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 20px; pointer-events: none; z-index: 5; }
        .croix { font-size: 18px; font-weight: bold; color: #5dade2; }
        .btn-tir { position: absolute; inset: 0; width: 100%; height: 100%; background: transparent; border: none; cursor: crosshair; padding: 0; margin: 0; z-index: 2; }
        .btn-abandon { background: #c0392b; color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; margin-bottom: 15px; font-size: 14px; }
        .btn-abandon:hover { background: #a93226; }
        .gagne { color: #2ecc71; font-size: 28px; font-weight: bold; margin: 20px 0; }
        .perdu { color: #e74c3c; font-size: 28px; font-weight: bold; margin: 20px 0; }
        .btn-lien { display: inline-block; margin: 8px 5px 0; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; color: white; }
        .poisson-bg { position: absolute; inset: 0; background-repeat: no-repeat; pointer-events: none; z-index: 1; }
        .poisson-vertical { transform: rotate(90deg); transform-origin: center; }
        .sidebar-legende { background: #16213e; border-radius: 12px; padding: 20px; width: 220px; display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); text-align: left; }
        .sidebar-legende h3 { font-size: 13px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #1e3a5f; padding-bottom: 8px; margin-bottom: 5px; text-align: center;}
        .legende-item { display: flex; flex-direction: column; gap: 4px; background: rgba(13,31,60,0.5); padding: 8px; border-radius: 6px; }
        .legende-img { height: 32px; object-fit: contain; width: 100%; mix-blend-mode: lighten; }
        .legende-info { font-size: 12px; font-weight: bold; color: #fff; display: flex; justify-content: space-between; }
        .legende-statut { font-size: 11px; color: #2ecc71; font-style: italic; }
        .legende-statut.en-mer { color: #5dade2; }
        .barre-vie { display: flex; gap: 2px; margin-top: 2px; }
        .barre-case { height: 5px; flex: 1; border-radius: 1px; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="contenu-jeu">
<h1>Fish & Ships</h1>
<p class="info">Joueur : <strong><?= htmlspecialchars($_SESSION['joueur']['login']) ?></strong></p>

<?php if ($partie['statut'] === 'terminee'): ?>
    <?php
    $stmt = $pdo->prepare('SELECT resultat FROM score WHERE id_joueur = ? AND id_partie = ?');
    $stmt->execute([$idJoueur, $idPartie]);
    $monScore = $stmt->fetch();
    ?>
    <?php if ($monScore && $monScore['resultat'] === 'gagnant'): ?>
        <p class="gagne"> VOUS AVEZ GAGNÉ !</p>
    <?php elseif ($monScore && $monScore['resultat'] === 'abandon'): ?>
        <p class="perdu"> Vous avez abandonné.</p>
    <?php else: ?>
        <p class="perdu">💀 VOUS AVEZ PERDU !</p>
    <?php endif; ?>
    <a href="scores.php"       class="btn-lien" style="background:#3498db;"> Voir les scores</a>
    <a href="reset_partie.php" class="btn-lien" style="background:#2ecc71;">Nouvelle partie</a>

<?php else: ?>

    <p>
        <span class="tour <?= $cEstMonTour ? 'mon-tour' : 'leur-tour' ?>">
            <?= $cEstMonTour ? ' C\'est votre tour !' : ' En attente de l\'adversaire...' ?>
        </span>
    </p>
    <?php if (!$cEstMonTour): ?>
        <p style="color:#555; font-size:12px; margin-bottom:8px;">La page se rafraîchit automatiquement.</p>
    <?php endif; ?>

    <form method="POST" style="margin-bottom:10px;">
        <button type="submit" name="abandonner" class="btn-abandon">Abandonner</button>
    </form>

    <div class="page-layout">
        <div class="arena">

            <div class="plateau">
                <h4> Votre flotte</h4>
                <table class="ma-grille">
                    <tr><th></th><?php for ($i=1;$i<=10;$i++) echo "<th>$i</th>"; ?></tr>
                    <?php for ($l = 0; $l < 10; $l++): ?>
                    <tr>
                        <th><?= $lettres[$l] ?></th>
                        <?php for ($c = 0; $c < 10; $c++):
                            $cell  = $maGrille->grille[$l][$c];
                            $class = '';
                            if     ($cell->etat === Cellule::TOUCHE) $class = 'touche';
                            elseif ($cell->etat === Cellule::COULE)  $class = 'coule';
                            elseif ($cell->etat === Cellule::RATE)   $class = 'rate';
                            elseif ($cell->idNavire)                 $class = 'navire';
                        ?>
                        <td class="<?= $class ?>">
                            <?php if ($cell->etat === Cellule::TOUCHE): ?>
                                <div class="marqueur">🔥</div>
                            <?php elseif ($cell->etat === Cellule::COULE): ?>
                                <?php
                                $id   = $cell->idNavire;
                                $info = $naviresCoulesPerso[$id] ?? null;
                                if ($info) {
                                    $pos = 0; foreach ($info['cases'] as $i => $cas) { if ($cas[0] === $l && $cas[1] === $c) { $pos = $i; break; } }
                                    $bgSize = ($info['taille'] * $cellSize) . 'px ' . $cellSize . 'px';
                                    $bgPosX = -($pos * $cellSize) . 'px';
                                    ?>
                                    <div class="poisson-bg <?= $info['horizontal'] ? '' : 'poisson-vertical' ?>" style="background-image: url('<?= $poissons[$id] ?>'); background-size: <?= $bgSize ?>; background-position: <?= $bgPosX ?> 0px;"></div>
                                <?php } ?>
                            <?php elseif ($cell->etat === Cellule::RATE): ?>
                                <div class="marqueur"><span class="croix">✕</span></div>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>

            <div class="plateau">
                <h4> Flotte ennemie</h4>
                <table class="grille-ennemie">
                    <tr><th></th><?php for ($i=1;$i<=10;$i++) echo "<th>$i</th>"; ?></tr>
                    <?php for ($l = 0; $l < 10; $l++): ?>
                    <tr>
                        <th><?= $lettres[$l] ?></th>
                        <?php for ($c = 0; $c < 10; $c++):
                            $cell     = $saGrille->grille[$l][$c];
                            $estRate  = ($cell->etat === Cellule::RATE);
                            $dejaTire = ($cell->etat === Cellule::TOUCHE || $cell->etat === Cellule::COULE || $estRate);
                            $class = '';
                            if     ($cell->etat === Cellule::TOUCHE) $class = 'touche';
                            elseif ($cell->etat === Cellule::COULE)  $class = 'coule';
                            elseif ($estRate)                        $class = 'rate';
                            elseif (!$dejaTire && $cEstMonTour)      $class = 'cible';
                        ?>
                        <td class="<?= $class ?>">
                            <?php if ($cell->etat === Cellule::TOUCHE): ?>
                                <div class="marqueur">🔥</div>
                            <?php elseif ($cell->etat === Cellule::COULE): ?>
                                <?php
                                $id = $cell->idNavire; $info = $naviresCoulesEnnemi[$id] ?? null;
                                if ($info) {
                                    $pos = 0; foreach ($info['cases'] as $i => $cas) { if ($cas[0] === $l && $cas[1] === $c) { $pos = $i; break; } }
                                    $bgSize = ($info['taille'] * $cellSize) . 'px ' . $cellSize . 'px';
                                    $bgPosX = -($pos * $cellSize) . 'px';
                                    ?>
                                    <div class="poisson-bg <?= $info['horizontal'] ? '' : 'poisson-vertical' ?>" style="background-image: url('<?= $poissons[$id] ?>'); background-size: <?= $bgSize ?>; background-position: <?= $bgPosX ?> 0px;"></div>
                                <?php } ?>
                            <?php elseif ($estRate): ?>
                                <div class="marqueur"><span class="croix">✕</span></div>
                            <?php elseif (!$dejaTire && $cEstMonTour): ?>
                                <form method="POST">
                                    <input type="hidden" name="tir_l" value="<?= $l ?>">
                                    <input type="hidden" name="tir_c" value="<?= $c ?>">
                                    <button type="submit" class="btn-tir" title="Tirer en <?= $lettres[$l].($c+1) ?>"></button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="sidebar-legende">
            <h3>Poissons Ennemis</h3>
            <?php
            $listeAffichage = [
                'baleine'  => ['nom' => 'Baleine', 'taille' => 5, 'c' => '#2980b9'],
                'anguille' => ['nom' => 'Anguille','taille' => 4, 'c' => '#8e44ad'],
                'requin'   => ['nom' => 'Requin',  'taille' => 3, 'c' => '#27ae60'],
                'colore'   => ['nom' => 'Coloré',  'taille' => 3, 'c' => '#f39c12'],
                'clown'    => ['nom' => 'Clown',   'taille' => 2, 'c' => '#e74c3c'],
            ];

            foreach ($listeAffichage as $id => $data):
                $estCoule = isset($naviresCoulesEnnemi[$id]);
            ?>
                <div class="legende-item" style="border-left: 4px solid <?= $data['c'] ?>;">
                    <div class="legende-info">
                        <span><?= $data['nom'] ?></span>
                        <span><?= $data['taille'] ?> c.</span>
                    </div>
                    <img src="<?= $poissons[$id] ?>" class="legende-img" style="<?= $estCoule ? 'filter: grayscale(100%) opacity(40%);' : '' ?>">
                    <div class="legende-statut <?= $estCoule ? '' : 'en-mer' ?>">
                        <?= $estCoule ? ' Coulé !' : ' En mer' ?>
                    </div>
                    <div class="barre-vie">
                        <?php for ($i=0; $i<$data['taille']; $i++): ?>
                            <div class="barre-case" style="background: <?= $estCoule ? '#7f8c8d' : $data['c'] ?>;"></div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php endif; ?>
</div>

</body>
</html>