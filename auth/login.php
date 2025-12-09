<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Détruire le cookie "Se souvenir de moi"
if (isset($_COOKIE['user_remember'])) {
    setcookie('user_remember', '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: ' . SITE_URL . '/auth/login.php?logout=success');
exit();
?>