<?php // header.php ?>
<style>
    header {
        background: #5B8DB8;
        padding: 10px 40px;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        border-bottom: none;
        font-family: 'krabby patty', Arial, sans-serif;
        position: relative;
    }

    header .logo {
        justify-self: start;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    header .logo img { height: 70px; }

    .logo-titre {
        color: #FFEDAB;
        font-size: 22px;
        font-weight: bold;
        font-family: 'krabby patty', Arial, sans-serif;
        letter-spacing: 1px;
    }

    .logo-centre {
        justify-self: center;
    }

    .logo-centre img { height: 70px; }

    header nav {
        justify-self: end;
        display: flex;
        align-items: center;
    }

    nav a {
        color: #FFEDAB;
        text-decoration: none;
        margin-left: 30px;
        font-size: 15px;
        font-weight: bold;
        font-family: 'krabby patty', Arial, sans-serif;
    }

    nav a:hover { color: white; }

    .header-bordure {
        width: 100%;
        height: 35px;
        background-image: url('/bataille_navale/assets/bordure_coquilllage-removebg-preview_2.png');
        background-repeat: repeat-x;
        background-size: auto 100%;
    }

    footer {
        margin-top: 0;
        border-top: none;
        padding: 0;
        background: transparent;
    }

    .footer-vague {
        width: 100%;
        background-image: url('/bataille_navale/assets/vague_figma-removebg-preview_7.png');
        background-repeat: repeat-x;
        background-size: auto 100%;
        height: 80px;
    }

    .footer-texte {
        background: #1a9bbf;
        text-align: center;
        padding: 10px;
        color: white;
        font-size: 13px;
        font-family: 'krabby patty', Arial, sans-serif;
    }
</style>

<header>
    <a href="index.php" class="logo">
        <span class="logo-titre">Fish & Ships</span>
    </a>
    <a href="index.php" class="logo-centre">
        <img src="/bataille_navale/assets/logo_couleurOG.png" alt="Fish & Ships">
    </a>
    <nav>
        <a href="index.php#aide">Aide</a>
        <?php if (isset($_SESSION['joueur'])): ?>
            <a href="lobby.php">Lobby</a>
            <a href="scores.php">Scores</a>
            <form method="POST" action="deconnexion.php" style="display:inline;">
                <button type="submit" style="background:transparent; border:none; color:#FFEDAB; font-size:15px; font-weight:bold; cursor:pointer; margin-left:30px; font-family:'krabby patty', Arial, sans-serif;">
                    Se deconnecter
                </button>
            </form>
        <?php else: ?>
            <a href="login.php">Se connecter</a>
            <a href="login.php?redirect=scores.php">Scores</a>
        <?php endif; ?>
    </nav>
</header>
<div class="header-bordure"></div>