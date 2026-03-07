<?php
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception as MongoDriverException;

class MongoManager {
    
    private static ?Client $client = null;
    private const DEFAULT_URI = "mongodb+srv://AlexDes_db_user:qw47dopAqjQ2oceb@cluster0.n1cceax.mongodb.net/?appName=Cluster0";
    private const DB_NAME = 'vite_et_gourmand_stats';
    private const COLLECTION_STATS = 'order_stats';

    public static function getDb(): ?Database {
        if (self::$client === null) {
            try {
                // Connexion à MongoDB en Lazy Loading (pour économie de ressources)
                $uri = getenv('MONGO_URL') ?: self::DEFAULT_URI;
                self::$client = new Client($uri);
                self::ensureSchemaExists(self::$client->selectDatabase(self::DB_NAME));
            } catch (Exception $e) {
                error_log("[CRITICAL] MongoDB Connection Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$client->selectDatabase(self::DB_NAME);
    }

    private static function ensureSchemaExists(Database $db): void {
        $collName = self::COLLECTION_STATS;
        
        // Vérification de l'existence de la collection
        $collections = $db->listCollections(['filter' => ['name' => $collName]]);
        $exists = false;
        foreach ($collections as $col) { $exists = true; }

        if (!$exists) {
            try {
                $db->createCollection($collName);
            } catch (MongoDriverException $e) {
         
            }
        }
        
        // Création des index pour optimiser les requêtes
        try {
            $db->selectCollection($collName)->createIndexes([
                ['key' => ['menu_id' => 1], 'name' => 'menu_idx', 'background' => true],
                ['key' => ['order_date' => 1], 'name' => 'date_idx', 'background' => true]
            ]);
        } catch (Exception $e) { 
        // ignorer si l'index existe  
        }
    }

    /*Agrégation de données, Filtre les commandes sur une période, les regroupe par menu, calcule les revenus/quantités, et trie par CA décroissant.*/
    public static function getRevenueByDuration(?string $startStr = null, ?string $endStr = null): array {
        $db = self::getDb();
        if (!$db) return [];

        try {
            $collection = $db->selectCollection(self::COLLECTION_STATS);

            // Définition de la plage de dates
            $startTime = $startStr ? strtotime($startStr) : strtotime(date('Y-m-01 00:00:00'));
            $endTime = $endStr ? strtotime($endStr . ' 23:59:59') : time();

            // Pipeline : filtrage par date, regroupement par menu et tri par CA
            $pipeline = [
                [
                    '$match' => [
                        'order_date' => [
                            '$gte' => new UTCDateTime($startTime * 1000),
                            '$lte' => new UTCDateTime($endTime * 1000)
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$menu_id', 
                        'total_revenue' => ['$sum' => '$total_revenue'], 
                        'order_count' => ['$sum' => 1] 
                    ]
                ],
                ['$sort' => ['total_revenue' => -1]]
            ];

            return $collection->aggregate($pipeline)->toArray();
        } catch (Exception $e) {
            error_log("MongoDB Duration Error: " . $e->getMessage());
            return [];
        }
    }

    /* Log les événements avec horodatage pour faire des stats*/
    public static function logEvent(array $data): bool {
        $db = self::getDb();
        if (!$db) return false;
        try {
            // Ajout de la date actuelle
            $data['order_date'] = new UTCDateTime(); 
            $db->selectCollection(self::COLLECTION_STATS)->insertOne($data);
            return true;
        } catch (Exception $e) {
            error_log("MongoDB Insert Error: " . $e->getMessage());
            return false;
        }
    }
}
?>