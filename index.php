<?php
require_once 'Config/db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fish & Ships</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="hero">
    <img src="/bataille_navale/assets/logo_couleurOG.png" alt="Fish & Ships">
    <h1>Fish & Ships</h1>
    <p>Trouvez et pêchez tous les poissons de votre adversaire !</p>
    <div class="boutons">
        <a href="login.php" class="btn-jouer">Jouer</a>
        <a href="scores.php" class="btn-scores">Scores</a>
    </div>
</div>

<div class="aide-wrapper" id="aide">
    <div class="aide">
        <h2>Comment jouer à Fish & Ships ?</h2>
        <p>Fish & Ships est un jeu de stratégie dans lequel deux joueurs s'affrontent sur une grille de 10x10 cases. Chaque joueur dispose d'une flotte de 5 navires qu'il doit placer stratégiquement avant la bataille.</p>

        <h3>Avant la partie</h3>
        <p>Chaque joueur crée son compte puis place sa flotte sur sa grille. Les navires ne peuvent pas se toucher — il y a toujours une case vide autour de chaque navire.</p>

        <h3>Pendant la partie</h3>
        <p>Les joueurs jouent au tour par tour. Le joueur qui a la main clique sur une case de la grille adverse pour tirer :</p>
        <ul>
            <li>Si la case est vide → c'est <strong>raté</strong>, c'est au tour de l'adversaire.</li>
            <li>Si la case contient un navire → c'est <strong>touché</strong>, le même joueur continue de tirer.</li>
            <li>Si toutes les cases d'un navire sont touchées → le navire est <strong>coulé</strong> !</li>
        </ul>

        <h3>Fin de partie</h3>
        <p>La partie se termine quand tous les navires d'un joueur sont coulés, ou quand un joueur abandonne.</p>
        <ul>
            <li>Le gagnant obtient : <strong>100 - nombre de tirs</strong> points.</li>
            <li>Le perdant obtient : son <strong>nombre de touches</strong> comme points.</li>
            <li>Un abandon ne donne pas de score.</li>
        </ul>

        <h3>La flotte</h3>
        <ul>
            <li>1 Porte-avions — <strong>5 cases</strong></li>
            <li>1 Cuirassé — <strong>4 cases</strong></li>
            <li>1 Destroyer — <strong>3 cases</strong></li>
            <li>1 Sous-marin — <strong>3 cases</strong></li>
            <li>1 Patrouilleur — <strong>2 cases</strong></li>
        </ul>
    </div>
</div>

<footer>
    Fish & Ships — IUT Bobigny — Université Sorbonne Paris Nord
</footer>

</body>
</html>