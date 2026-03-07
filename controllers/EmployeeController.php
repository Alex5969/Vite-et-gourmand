<?php
require_once __DIR__ . '/../config/mysql_conn.php';
require_once __DIR__ . '/../includes/bootstrap.php';

class EmployeeController {
    
    // Modération des avis clients par les employés
    public static function moderateReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                set_error("Action non autorisée.");
                redirect('dashboard_employee');
            }

            $review_id = (int)$_POST['review_id'];
            $action = $_POST['moderation_action']; // Action ciblée : accepte ou refuse l'avis
            $pdo = MySQLClient::getInstance();

            if ($action === 'approve') {
                // Validation de l'avis : il devient visible sur le site
                $stmt = $pdo->prepare("UPDATE reviews SET is_validated = 1 WHERE id = ?");
                $stmt->execute([$review_id]);
            } elseif ($action === 'reject') {
                // Refus de l'avis : suppression de l'avis sur la base de données
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$review_id]);
            }

            redirect('dashboard_employee');
        }
    }
}
?>