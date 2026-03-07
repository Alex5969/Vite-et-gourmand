<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification de la session avec jeton et de la validité du formulaire
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        set_error("Session expirée.");
        redirect('dashboard_user');
    }
    if (!is_logged_in()) redirect('login');

    $orderId = (int)$_POST['order_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    $userId = $_SESSION['user_id'];

    $pdo = MySQLClient::getInstance();
    
    //l'utilisateur peut noter une commande qui lui appartient ET étant terminée
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'completed'");
    $stmt->execute([$orderId, $userId]);

    if ($stmt->fetch()) {
        // Insertion de l'avis avec un statut "non validé" par défaut pour modération
        $ins = $pdo->prepare("INSERT INTO reviews (user_id, order_id, rating, comment, is_validated, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $ins->execute([$userId, $orderId, $rating, $comment]);
        
        header("Location: ../public/index.php?page=dashboard_user&success=1");
        exit;
    } else {
        set_error("Commande invalide pour cet avis.");
    }
}
redirect('dashboard_user');