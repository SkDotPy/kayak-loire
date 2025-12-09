<?php
/**
 * Gestion de la connexion à la base de données
 */

// Inclure la configuration
require_once __DIR__ . '/../config.php';

/**
 * Fonction pour obtenir la connexion à la base de données
 * Utilise le pattern Singleton (une seule instance)
 * @return PDO Objet de connexion PDO
 */
function getConnection() {
    static $conn = null;
    
    // Si la connexion n'existe pas encore, on la crée
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    // Mode d'erreur : exceptions
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        } catch(PDOException $e) {
            // En cas d'erreur, arrêter le script et afficher l'erreur
            die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    return $conn;
}

/**
 * Fonction pour exécuter une requête SELECT
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête (optionnel)
 * @return array Le résultat de la requête
 */
function query($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour exécuter une requête INSERT/UPDATE/DELETE
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return bool True si succès, False sinon
 */
function execute($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Fonction pour récupérer le dernier ID inséré
 * @return int Le dernier ID
 */
function lastInsertId() {
    $conn = getConnection();
    return $conn->lastInsertId();
}

?>