<?php

require_once __DIR__ . '/../config/mysql_conn.php';
require_once __DIR__ . '/../includes/bootstrap.php';

class AdminController {

    // Création d'un compte employé par l'administrateur
    public static function createEmployee() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Session expirée. Veuillez réessayer.");
                redirect('admin_create_employee');
            }

            $first_name = sanitize($_POST['first_name']);
            $last_name = sanitize($_POST['last_name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone_gsm']);
            $address = sanitize($_POST['address_postale']);
            $city = sanitize($_POST['city']);
            $password = $_POST['password']; 

            // sécurité : mot de passe fort exigé
            if (strlen($password) < 10) {
                set_error("Le mot de passe doit faire 10 caractères minimum.");
                redirect('admin_create_employee');
            }

            // Vérification de l'unicité de l'email
            $pdo = MySQLClient::getInstance();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                set_error("Cet email est déjà utilisé.");
                redirect('admin_create_employee');
            }

            $hash = hashPassword($password);

            try {
                // Insertion en forçant le rôle 'employee' et statut actif
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password_hash, phone_gsm, address_postale, city, role, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'employee', 1)
                ");
                $stmt->execute([$first_name, $last_name, $email, $hash, $phone, $address, $city]);

                set_error("Le compte employé a été créé avec succès.");
                redirect('admin');
            } catch (Exception $e) {
                set_error("Erreur technique lors de la création.");
                redirect('admin_create_employee');
            }
        }
    }

    // Activation / Désactivation d'un compte employé
    public static function toggleEmployeeStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Action non autorisée.");
                redirect('admin');
            }

            $user_id = (int)$_POST['user_id'];
            $new_status = (int)$_POST['new_status'];
            $pdo = MySQLClient::getInstance();
            
            //Donne l'acces qu'aux comptes employés pour empêcher l'admin de se bloquer lui-même
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role = 'employee'");
            $stmt->execute([$new_status, $user_id]);

            redirect('admin');
        }
    }
}
?>