<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Vérification du role admin pour acceder a cette page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Accès refusé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    //sécurité CRSF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("Erreur de sécurité (CSRF).");
    }

    $userId = (int)$_POST['user_id'];
    $newStatus = (int)$_POST['new_status']; 

    try {
        $pdo = MySQLClient::getInstance();
        
        //ici admin peut modifier que le statut des employés, if un autre role cela ne marchera pas
        $checkStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $checkStmt->execute([$userId]);
        $user = $checkStmt->fetch();
        
        if ($user && $user['role'] === 'employee') {
            $updateStmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $updateStmt->execute([$newStatus, $userId]);
            
            header("Location: ../public/index.php?page=admin&success=1");
            exit;
        } else {
            die("Cible invalide.");
        }

    } catch (PDOException $e) {
        die("Erreur technique : " . $e->getMessage());
    }
}
header("Location: ../public/index.php?page=admin");
exit;