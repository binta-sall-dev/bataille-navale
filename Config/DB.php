<?php

class DB
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // 1. On définit d'abord les configurations selon l'environnement
            // On détecte si on est en local (localhost / 127.0.0.1) ou sur le serveur de l'université
            $isLocalhost = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

            if (!$isLocalhost) {
                // --- CONFIGURATION SERVEUR UNIVERSITÉ (LAMP-EDAGO) ---
                // Le port standard 3306 est utilisé par défaut si on ne spécifie rien
                $dsn = 'mysql:host=localhost;dbname=bataille_navale;charset=utf8';
                $user = 'root';      
                $pass = 'root';      
            } else {
                // --- CONFIGURATION TON PC LOCAL (MAMP / XAMPP avec port 3307) ---
                $dsn = 'mysql:host=localhost;dbname=bataille_navale;charset=utf8;port=3307';
                $user = 'root';
                $pass = 'root';
            }

            try {
                self::$instance = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // Si la première tentative échoue, on tente un dernier fallback universel standard (port 3306)
                try {
                    self::$instance = new PDO(
                        'mysql:host=localhost;dbname=bataille_navale;charset=utf8',
                        'root',
                        '', // souvent vide sous Windows / XAMPP standard
                        [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]
                    );
                } catch (PDOException $fallbackException) {
                    die('Erreur de connexion base de données : ' . $e->getMessage());
                }
            }
        }
        return self::$instance;
    }
}

// --- BLOC DE TEST POUR LE PROFESSEUR (Lien depuis test.php) ---
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    try {
        DB::getInstance();
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { background: #0a1628; color: white; font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h2 { color: #2ecc71; margin-top: 0; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h2> Connexion Réussie !</h2>
                <p>La liaison avec la base de données fonctionne parfaitement.</p>
                <p style='font-size: 12px; color: #aaa;'>Environnement détecté : " . ($_SERVER['SERVER_NAME'] === 'localhost' ? ' Local (Port 3307)' : 'Serveur Université (Standard)') . "</p>
                <br>
                <a href='../test.php' style='color:#3498db; text-decoration:none; font-weight:bold;'>⬅ Retour au Tableau de bord</a>
            </div>
        </body>
        </html>";
    } catch (Exception $e) {
        // L'erreur est déjà interceptée par le die() au-dessus
    }
}