<?php
//regex
define('REGEX_EMAIL', '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/');
define('REGEX_PASSWORD', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/');

// Échappe les caractères spéciaux avant affichage HTML ou insertion SQL
function escape(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Génère et vérifie un jeton CSRF unique par session pour sécuriser les soumissions de champs formulaires
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Chiffrement et hachage de données et mot de passe
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function encryptData(string $data): string {
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData(string $data): string {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

 
//Vérifie si une session utilisateur est active.
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function set_error(string $message): void {
    $_SESSION['error'] = $message;
}

function redirect(string $page): void {
    header("Location: " . BASE_URL . "/index.php?page=" . $page);
    exit;
}

// Fonction pour valider le format des dates
function isValidDate($date, $format = 'Y-m-d'): bool {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>