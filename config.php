<?php
/**
 * Fichier de configuration principal
 * Contient tous les paramètres du site
 */

// AFFICHER LES ERREURS (uniquement en développement)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Éviter les doubles inclusions avec defined()
if (!defined('DB_HOST')) {
    // ======================================
    // CONFIGURATION BASE DE DONNÉES
    // ======================================
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'kayak_loire');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    // ======================================
    // CONFIGURATION DU SITE
    // ======================================
    define('SITE_URL', 'http://http://51.178.44.228/');
    define('SITE_NAME', 'Kayak Loire');
    define('SITE_EMAIL', 'contact@kayak-loire.fr');

    // ======================================
    // CHEMINS DES DOSSIERS
    // ======================================
    define('ROOT_PATH', __DIR__);
    define('INCLUDES_PATH', ROOT_PATH . '/includes');
    define('UPLOAD_PATH', ROOT_PATH . '/uploads');
    define('UPLOAD_URL', SITE_URL . '/uploads');

    // ======================================
    // CONFIGURATION DES UPLOADS
    // ======================================
    define('MAX_FILE_SIZE', 5242880);
    define('ALLOWED_EXTENSIONS', serialize(['jpg', 'jpeg', 'png', 'gif', 'webp']));

    // ======================================
    // CONFIGURATION EMAIL
    // ======================================
    define('MAIL_FROM', 'noreply@kayak-loire.fr');
    define('MAIL_FROM_NAME', 'Kayak Loire');

    // ======================================
    // PARAMÈTRES DE SÉCURITÉ
    // ======================================
    define('PASSWORD_MIN_LENGTH', 8);
    define('SESSION_LIFETIME', 7200);

    // ======================================
    // TIMEZONE
    // ======================================
    date_default_timezone_set('Europe/Paris');
}

// ======================================
// DÉMARRAGE DE SESSION
// ======================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>