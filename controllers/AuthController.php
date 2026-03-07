<?php
require_once __DIR__ . '/../includes/bootstrap.php';

class AuthController {

    const MIN_PWD_LENGTH = 10;

    //  CONNEXION 
    public static function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Session expirée. Veuillez réessayer.");
                redirect('login');
            }

            $email = sanitize($_POST['email']);
            $password = $_POST['password'];

            $pdo = MySQLClient::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Vérification du mot de passe et User
            if ($user && verifyPassword($password, $user['password_hash'])) {
                
                // Blocage si le compte a été suspendu
                if ($user['is_active'] == 0) {
                    set_error("Ce compte est désactivé.");
                    redirect('login');
                }

                // Initialisation de la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'];

                // Redirection selon le rôle
                if ($user['role'] === 'admin') redirect('admin');
                if ($user['role'] === 'employee') redirect('dashboard_employee');
                redirect('dashboard_user');
            } else {
                set_error("Identifiants incorrects.");
                redirect('login');
            }
        }
    }

    //  INSCRIPTION CLIENT 
    public static function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Session expirée.");
                redirect('register');
            }

            $first_name = sanitize($_POST['first_name']);
            $last_name = sanitize($_POST['last_name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone']);
            $city = sanitize($_POST['city']);
            $address = sanitize($_POST['address']);
            $pwd = $_POST['password'];

            $pdo = MySQLClient::getInstance();
            
            // Empêche les doublons d'email
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                set_error("Cet email est déjà utilisé.");
                redirect('register');
            }

            $hash = hashPassword($pwd);

            try {
                // Insertion avec le rôle client pour raison de sécurité
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password_hash, phone_gsm, city, address_postale, role, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'client', 1)
                ");
                $stmt->execute([$first_name, $last_name, $email, $hash, $phone, $city, $address]);

                // Auto-connexion après inscription réussie
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_role'] = 'client';
                $_SESSION['user_name'] = $first_name;

                if (class_exists('MailerService')) {
                    MailerService::sendWelcome($email, $first_name);
                }

                redirect('menus');
            } catch (Exception $e) {
                set_error("Erreur technique lors de l'inscription.");
                redirect('register');
            }
        }
    }
  
    public static function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?page=home");
        exit;
    }

    //  MOT DE PASSE OUBLIÉ  
    public static function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Session expirée.");
                redirect('password_recovery');
            }

            $email = sanitize($_POST['email']);
            $pdo = MySQLClient::getInstance();

            // On vérifie que le compte existe et est actif
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $ins = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $ins->execute([$email, $token, $expiresAt]);

                MailerService::sendPasswordReset($email, $token);
            }

            $_SESSION['success'] = "Si votre email existe, un lien de réinitialisation vient de vous être envoyé.";
            header("Location: ../public/index.php?page=password_recovery");
            exit;
        }
    }
 
    public static function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Session expirée.");
                redirect('login');
            }

            $token = $_POST['token'];
            $newPassword = $_POST['password'];

            $pdo = MySQLClient::getInstance();
            
            // Vérification de la validité et de l'expiration du jeton
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch();

            if ($resetRequest) {
                $hash = hashPassword($newPassword);
                
                // Mise à jour du mot de passe
                $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $upd->execute([$hash, $resetRequest['email']]);

                // Suppression du jeton consommé
                $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->execute([$resetRequest['email']]);

                $_SESSION['success'] = "Mot de passe mis à jour. Vous pouvez vous connecter.";
                redirect('login');
            } else {
                set_error("Ce lien est invalide ou a expiré.");
                redirect('login');
            }
        }
    }
}
?>