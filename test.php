<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répartition des tâches - Bataille Navale</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; vertical-align: top; }
        th { background-color: #eee; font-weight: bold; }
        .fini { color: #27ae60; font-weight: bold; text-decoration: none; }
        .fini:hover { text-decoration: underline; }
        .en-cours { color: #7f8c8d; font-style: italic; }
        .termine { color: #27ae60; font-style: italic; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>

    <h1>Tableau de bord - Test des fonctionnalités</h1>
    <p>Ce tableau permet de tester chaque module du projet en cliquant sur les liens terminés.</p>

    <table>
        <thead>
            <tr>
                <th>Membre</th>
                <th>Rôle Principal</th>
                <th>Tâches & Tests (Cliquer pour tester)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Binta</strong></td>
                <td>Cheffe de Projet / Dev</td>
                <td>
                    <ul>
                        <li><a class="fini" href="login.php">Page Login / Inscription (PHP + PDO)</a></li>
                        <li><a class="fini" href="Config/DB.php">Connexion Base de données</a></li>
                        <li><a class="fini" href="placement.php">Grille 10x10 & Placement navires</a></li>
                        <li><a class="fini" href="lobby.php">Lobby — Créer / Rejoindre une partie</a></li>
        
                        <li><a class="fini" href="scores.php">Tableau des scores</a></li>
                        <li class="termine">Modélisation BDD (Terminé)</li>
                        <li class="termine">Système de matchmaking multijoueur (Terminé)</li>
                        <li class="termine">Logique de fin de partie & calcul des points (Terminé)</li>
                        <li class="termine">Correction bug zones tampon (Terminé)</li>
                        <li class="termine">Correction bug etat NAVIRE dans Mer.php (Terminé)</li>
                    </ul>
                </td>
            </tr>

            <tr>
                <td><strong>Maëlys</strong></td>
                <td>Responsable Dev</td>
                <td>
                    <ul>
                        <li><a class="fini" href="jeu.php">Système de tour & Page de jeu</a></li>
                        <li><a class="fini" href="Class/Plateau/Mer.php">Logique Cellule/Mer (POO)</a></li>
                        <li><a class="fini" href="scores.php">Logique de fin de partie & Scores</a></li>
                        <li class="termine">Système de tour entre joueurs (Terminé)</li>
                    </ul>
                </td>
            </tr>

            <tr>
                <td><strong>Sarrah</strong></td>
                <td>Responsable UX/UI</td>
                <td>
                    <ul>
                        <li><a class="fini" href="Asset/maquette.pdf">Maquettes & Wireframes</a></li>
                        <li class="en-cours">Style CSS global (En cours)</li>
                        <li class="en-cours">Benchmark concurrentiel</li>
                    </ul>
                </td>
            </tr>

            <tr>
                <td><strong>Kemie</strong></td>
                <td>Création Numérique</td>
                <td>
                    <ul>
                        <li><a class="fini" href="Class/Navires/Portavion.php">Classes des Navires (Logique)</a></li>
                        <li class="termine">Zone tampon & Collisions (Terminé)</li>
                        <li class="en-cours">Dessins des poissons & Charte graphique</li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>