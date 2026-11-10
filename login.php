<?php
require_once 'Config/db.php';
session_start();

// Récupérer la redirection demandée
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'lobby.php';

if (isset($_SESSION['joueur'])) {
    header('Location: ' . $redirect);
    exit;
}

$erreur = null;
$succes = null;

if (isset($_POST['login'], $_POST['mot_de_passe'])) {
    $login = trim($_POST['login']);
    $mdp   = $_POST['mot_de_passe'];
    $pdo   = DB::getInstance();
    $stmt  = $pdo->prepare('SELECT * FROM joueur WHERE login = ?');
    $stmt->execute([$login]);
    $joueur = $stmt->fetch();
    if ($joueur && password_verify($mdp, $joueur['mot_de_passe'])) {
        $_SESSION['joueur'] = $joueur;
        header('Location: ' . $redirect);
        exit;
    } else {
        $erreur = "Login ou mot de passe incorrect.";
    }
}

if (isset($_POST['nouveau_login'], $_POST['nouveau_mdp'])) {
    $login = trim($_POST['nouveau_login']);
    $mdp   = $_POST['nouveau_mdp'];
    if (!preg_match('/^[a-zA-Z0-9]{5,10}$/', $login)) {
        $erreur = "Login : 5 à 10 caractères sans ponctuation.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Mot de passe : minimum 6 caractères.";
    } else {
        $pdo  = DB::getInstance();
        $stmt = $pdo->prepare('SELECT id_joueur FROM joueur WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $erreur = "Ce login est déjà pris.";
        } else {
            $pdo->prepare('INSERT INTO joueur (login, mot_de_passe, date_inscription) VALUES (?, ?, NOW())')->execute([$login, password_hash($mdp, PASSWORD_DEFAULT)]);
            $succes = "Compte créé ! Tu peux te connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fish & Ships — Connexion</title>
    <link rel="stylesheet" href="/bataille_navale/assets/style.css">
    <style>
        .contenu { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 92px); padding: 40px 20px; gap: 30px; flex-wrap: wrap; }
        .card { background: #0d1f3c; border-radius: 16px; padding: 36px; width: 340px; border: 1px solid #1a3a6b; }
        .card h2 { font-size: 20px; color: #3b9eff; margin-bottom: 24px; text-align: center; }
        label { font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #1a3a6b; background: #0a1628; color: white; font-size: 14px; margin-bottom: 18px; outline: none; }
        input:focus { border-color: #3b9eff; }
        .btn-connexion { width: 100%; padding: 13px; background: #3b9eff; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .btn-connexion:hover { background: #2a7fd4; }
        .btn-inscription { width: 100%; padding: 13px; background: transparent; color: #3b9eff; border: 2px solid #3b9eff; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .btn-inscription:hover { background: #3b9eff; color: white; }
        .separateur { width: 1px; background: #1a3a6b; height: 300px; align-self: center; }
        .erreur { color: #e74c3c; font-size: 13px; margin-top: 12px; text-align: center; }
        .succes { color: #2ecc71; font-size: 13px; margin-top: 12px; text-align: center; }
        .retour { display: block; text-align: center; margin-top: 16px; color: #aaa; font-size: 13px; text-decoration: none; }
        .retour:hover { color: white; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="contenu">
    <div class="card">
        <h2>Se connecter</h2>
        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <label>Votre surnom</label>
            <input type="text" name="login" placeholder="MonPseudo" required>
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" placeholder="••••••" required>
            <button type="submit" class="btn-connexion">Connexion</button>
        </form>
        <?php if ($erreur && isset($_POST['login'])): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <a href="index.php" class="retour">← Retour à l'accueil</a>
    </div>

    <div class="separateur"></div>

    <div class="card">
        <h2>Créer un compte</h2>
        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <label>Choisir un surnom (5 à 10 caractères)</label>
            <input type="text" name="nouveau_login" minlength="5" maxlength="10" placeholder="MonPseudo" required>
            <label>Mot de passe (min. 6 caractères)</label>
            <input type="password" name="nouveau_mdp" minlength="6" placeholder="••••••" required>
            <button type="submit" class="btn-inscription">Créer mon compte</button>
        </form>
        <?php if ($succes): ?>
            <p class="succes"><?= htmlspecialchars($succes) ?></p>
        <?php elseif ($erreur && isset($_POST['nouveau_login'])): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <a href="index.php" class="retour">← Retour à l'accueil</a>
    </div>
</div>

</body>
</html>