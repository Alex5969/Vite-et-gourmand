<?php

class MySQLClient {
    //Pattern Singleton pour limiter l'application à une seule connexion PDO
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                // Configuration globale de PDO, (ex donner des tableaux associatifs, forcer les requetes préparées)
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, 
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("MySQL Error: " . $e->getMessage());
                die("Service momentanément indisponible.");
            }
        }
        return self::$instance;
    }
}