<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!is_logged_in()) {
    redirect('login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        set_error("Session expirée. Veuillez réessayer.");
        redirect('dashboard_user');
    }
    //requete préparé pour éviter les injections SQL
    $id = $_SESSION['user_id'];
    $fname = sanitize($_POST['first_name']);
    $lname = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $addr  = sanitize($_POST['address']);

    $pdo = MySQLClient::getInstance();
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone_gsm=?, address_postale=? WHERE id=?");
        $stmt->execute([$fname, $lname, $email, $phone, $addr, $id]);
        
        // Actualisation immédiate de l'affichage
        $_SESSION['user_name'] = $fname;
        
        header("Location: ../public/index.php?page=dashboard_user&success=1");
        exit;

    } catch (PDOException $e) {
        // Gestion du code erreur SQLSTATE 23000
        if ($e->getCode() == 23000) {
            set_error("Cet email est déjà utilisé par un autre compte.");
        } else {
            set_error("Erreur technique lors de la mise à jour.");
        }
        redirect('dashboard_user');
    }
}
redirect('dashboard_user');