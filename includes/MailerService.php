<?php
class MailerService {
    
    // Configuration
    private const FROM_EMAIL = "no-reply@viteetgourmand.fr";
    private const FROM_NAME = "Vite & Gourmand";

    //Fonction interne pour envoyer le mail
    private static function send(string $to, string $subject, string $htmlContent): bool {
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . self::FROM_NAME . " <" . self::FROM_EMAIL . ">" . "\r\n";
        
        return mail($to, $subject, $htmlContent, $headers);
    }

    //Mail de Bienvenue
    public static function sendWelcome(string $email, string $name): void {
        self::send(
            $email, 
            "Bienvenue chez Vite & Gourmand", 
            "<h1>Bonjour $name !</h1><p>Votre compte a été créé avec succès.</p>"
        );
    }

    //Mail de Confirmation Commande
    public static function sendOrderConfirmation(string $email, int $orderId, float $amount): void {
        self::send(
            $email, 
            "Confirmation Commande #$orderId", 
            "<h1>Merci !</h1><p>Votre commande d'un montant de <strong>$amount €</strong> est validée.</p>"
        );
    }

    //Mail d'Alerte Matériel
    public static function sendMaterialAlert(string $email, int $orderId): void {
        self::send(
            $email,
            "URGENT : Restitution matériel commande #$orderId",
            "<h1 style='color:red;'>Rappel Important</h1>
             <p>Veuillez nous contacter pour organiser la restitution du matériel lié à votre commande.</p>
             <p>Conformément à nos CGV, une pénalité de 600€ s'appliquera sous 10 jours ouvrés sans nouvelles de votre part.</p>"
        );
    }

    //Mail de Réinitialisation mot de passe 
    public static function sendPasswordReset(string $email, string $token): void {
        $resetLink = BASE_URL . "/index.php?page=password_recovery&token=" . $token;
        
        self::send(
            $email, 
            "Réinitialisation de votre mot de passe", 
            "<h1>Mot de passe oublié ?</h1>
             <p>Cliquez sur le lien ci-dessous pour créer un nouveau mot de passe sécurisé :</p>
             <p><a href='$resetLink' style='background-color:#EA2B1F; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Créer un nouveau mot de passe</a></p>
             <p><em>Attention : Ce lien n'est valable qu'une heure.</em></p>"
        );
    }
}
?>