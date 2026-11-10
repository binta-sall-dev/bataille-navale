<?php
require_once 'Class/Navires/Navire.php';
require_once 'Class/Navires/Baleine.php';
require_once 'Class/Navires/Anguille.php';
require_once 'Class/Navires/Requin.php';
require_once 'Class/Navires/Colore.php';
require_once 'Class/Navires/Clown.php';
require_once 'Class/Plateau/Case.php';
require_once 'Class/Plateau/Mer.php';

session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['reset'])) {
    $joueur = $_SESSION['joueur'];
    $idPartie = $_SESSION['id_partie'] ?? null;
    session_destroy();
    session_start();
    $_SESSION['joueur'] = $joueur;
    if ($idPartie) $_SESSION['id_partie'] = $idPartie;
    header("Location: placement.php");
    exit;
}

if (!isset($_SESSION['mer']) || !($_SESSION['mer'] instanceof Mer)) {
    $_SESSION['mer'] = new Mer();
}

if (!isset($_SESSION['navires'])) {
    $_SESSION['navires'] = [
        'baleine'  => new Baleine(),
        'anguille' => new Anguille(),
        'requin'   => new Requin(),
        'colore'   => new Colore(),
        'clown'    => new Clown(),
    ];
}

$poissons = [
    'baleine'  => ['nom' => 'Baleine',  'img' => '/bataille_navale/assets/baleine.png',  'couleur' => '#2980b9'],
    'anguille' => ['nom' => 'Anguille', 'img' => '/bataille_navale/assets/anguille.png', 'couleur' => '#8e44ad'],
    'requin'   => ['nom' => 'Requin',   'img' => '/bataille_navale/assets/requin.png',   'couleur' => '#27ae60'],
    'colore'   => ['nom' => 'Coloré',   'img' => '/bataille_navale/assets/colore.png',   'couleur' => '#f39c12'],
    'clown'    => ['nom' => 'Clown',    'img' => '/bataille_navale/assets/clown.png',    'couleur' => '#e74c3c'],
];

$navireSelectionne = $_SESSION['navire_selectionne'] ?? null;
$orientation       = $_SESSION['orientation'] ?? 'horizontal';

if (isset($_POST['selectionner'])) {
    $navireSelectionne = $_POST['selectionner'];
    $_SESSION['navire_selectionne'] = $navireSelectionne;
}

if (isset($_POST['orientation'])) {
    $orientation = $_POST['orientation'];
    $_SESSION['orientation'] = $orientation;
}

if (isset($_POST['ligne'], $_POST['colonne']) && $navireSelectionne) {
    $id     = $navireSelectionne;
    $navire = $_SESSION['navires'][$id];
    $ok     = $_SESSION['mer']->placerNavire($id, (int)$_POST['ligne'], (int)$_POST['colonne'], $navire->taille, $orientation);
    if ($ok) {
        $_SESSION['navires'][$id]->place = true;
        $_SESSION['navire_selectionne']  = null;
        $navireSelectionne               = null;

        $tousPlaces = true;
        foreach ($_SESSION['navires'] as $n) {
            if (!$n->place) { $tousPlaces = false; break; }
        }
        if ($tousPlaces) {
            header('Location: lobby.php');
            exit;
        }
    } else {
        $_SESSION['erreur'] = "Impossible de placer ici !";
    }
}

$navires = $_SESSION['navires'];
$mer     = $_SESSION['mer'];
$erreur  = $_SESSION['erreur'] ?? null;
unset($_SESSION['erreur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Placement — Fish & Ships</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            background: #0a1628 !important;
            color: white !important;
            padding: 0;
        }

        .contenu-placement {
            display: flex;
            gap: 40px;
            padding: 30px 40px;
            align-items: flex-start;
        }

        .liste-navires {
            background: #16213e;
            border-radius: 10px;
            padding: 20px;
            width: 200px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-shrink: 0;
        }

        .liste-navires h2 {
            font-size: 14px;
            color: #aaa;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .navire-item {
            border-radius: 8px;
            padding: 10px;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .navire-item.disponible { opacity: 1; }
        .navire-item.disponible:hover { border-color: white; }
        .navire-item.selectionne { border-color: white; background: #0f3460; }
        .navire-item.place { opacity: 0.4; }

        .navire-nom {
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }

        .navire-item.place .navire-nom::after {
            content: ' ✓';
            color: #2ecc71;
        }

        .poisson-img {
            width: 100%;
            height: 40px;
            object-fit: contain;
            display: block;
            mix-blend-mode: lighten;
        }

        .couleur-barre {
            display: flex;
            gap: 3px;
            margin-top: 4px;
        }

        .couleur-case {
            height: 8px;
            border-radius: 2px;
            flex: 1;
        }

        .orientation {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .orientation button {
            flex: 1;
            padding: 8px;
            border: 2px solid #444;
            background: #16213e;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .orientation button.actif { border-color: white; background: #0f3460; }

        .btn-reset {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
            font-size: 13px;
        }

        .erreur { color: #e74c3c; font-size: 12px; margin-top: 8px; }

        .zone-jeu h2 {
            font-size: 14px;
            color: #aaa;
            margin-bottom: 16px;
        }

        table { border-collapse: collapse; }

        td, th {
            width: 44px;
            height: 44px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
        }

        th { color: #aaa; font-weight: bold; font-size: 13px; }
        td { border: 1px solid #1e3a5f; background: #0d1f3c; }
        td.cliquable { cursor: crosshair; }
        td.cliquable:hover { background: #1a4a7a; }
        td.occupe { cursor: default; }

        td button {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            cursor: crosshair;
            display: block;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="contenu-placement">

    <!-- LISTE POISSONS -->
    <div class="liste-navires">
        <h2>Poissons</h2>

        <?php foreach ($navires as $id => $navire):
            $p = $poissons[$id];
        ?>
            <?php if (!$navire->place): ?>
                <form method="POST" style="margin:0;">
                    <button type="submit" name="selectionner" value="<?= $id ?>"
                        style="background:none;border:none;width:100%;padding:0;cursor:pointer;">
                        <div class="navire-item disponible <?= $navireSelectionne === $id ? 'selectionne' : '' ?>">
                            <img src="<?= $p['img'] ?>" alt="<?= $p['nom'] ?>" class="poisson-img">
                            <div class="navire-nom"><?= $p['nom'] ?> (<?= $navire->taille ?> cases)</div>
                            <div class="couleur-barre">
                                <?php for ($i = 0; $i < $navire->taille; $i++): ?>
                                    <div class="couleur-case" style="background:<?= $p['couleur'] ?>"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </button>
                </form>
            <?php else: ?>
                <div class="navire-item place">
                    <img src="<?= $p['img'] ?>" alt="<?= $p['nom'] ?>" class="poisson-img">
                    <div class="navire-nom"><?= $p['nom'] ?> (<?= $navire->taille ?> cases)</div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="orientation">
            <form method="POST">
                <button type="submit" name="orientation" value="horizontal"
                    class="<?= $orientation === 'horizontal' ? 'actif' : '' ?>">→ H</button>
            </form>
            <form method="POST">
                <button type="submit" name="orientation" value="vertical"
                    class="<?= $orientation === 'vertical' ? 'actif' : '' ?>">↓ V</button>
            </form>
        </div>

        <form method="POST">
            <button type="submit" name="reset" class="btn-reset">Réinitialiser</button>
        </form>

        <?php if ($erreur): ?>
            <p class="erreur"><?= $erreur ?></p>
        <?php endif; ?>
    </div>

    <!-- GRILLE -->
    <div class="zone-jeu">
        <h2>
            <?php if ($navireSelectionne): ?>
                Placer : <?= $poissons[$navireSelectionne]['nom'] ?> (<?= $orientation ?>)
            <?php else: ?>
                Sélectionnez un poisson
            <?php endif; ?>
        </h2>

        <table>
            <tr>
                <th></th>
                <?php for ($c = 0; $c < Mer::TAILLE; $c++): ?>
                    <th><?= $c + 1 ?></th>
                <?php endfor; ?>
            </tr>

            <?php
            $lettres = ['A','B','C','D','E','F','G','H','I','J'];
            for ($l = 0; $l < Mer::TAILLE; $l++):
            ?>
            <tr>
                <th><?= $lettres[$l] ?></th>
                <?php for ($c = 0; $c < Mer::TAILLE; $c++):
                    $cellule = $mer->grille[$l][$c];
                ?>
                    <?php if ($cellule->idNavire): ?>
                        <td class="occupe" style="background:<?= $poissons[$cellule->idNavire]['couleur'] ?>"></td>

                    <?php elseif ($navireSelectionne): ?>
                        <td class="cliquable" style="padding:0;">
                            <form method="POST" style="width:100%;height:100%;">
                                <input type="hidden" name="ligne"   value="<?= $l ?>">
                                <input type="hidden" name="colonne" value="<?= $c ?>">
                                <button type="submit">&nbsp;</button>
                            </form>
                        </td>

                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>

                <?php endfor; ?>
            </tr>
            <?php endfor; ?>
        </table>
    </div>

</div>

</body>
</html>