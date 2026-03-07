<?php
define('ROOT_PATH', dirname(__DIR__)); 

// Sécurité contre le Clickjacking, le reniflage MIME et les attaques XSS
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Protection contre le vol de cookies 
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/ViteEtGourmand/public'); 

// Chargement de l'autoloader (dépendance externe comme MongoDB)
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Les variables d'environnement/mots de passe sont stockés en dehors du dossier WWW de WAMP
$secretsPath = dirname(ROOT_PATH, 2) . '/secrets_config.php';
if (file_exists($secretsPath)) {
    require_once $secretsPath;
} else {
    die("Fichier de configuration critique introuvable dans : " . $secretsPath);
}

require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/config/mysql_conn.php';
require_once ROOT_PATH . '/config/MongoManager.php';
require_once ROOT_PATH . '/includes/MailerService.php';
require_once ROOT_PATH . '/includes/Maps.php'; 
?>