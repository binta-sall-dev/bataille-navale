<?php
session_start();

$joueur = $_SESSION['joueur'] ?? null;
session_destroy();

session_start();
if ($joueur) {
    $_SESSION['joueur'] = $joueur;
}

header('Location: lobby.php');
exit;