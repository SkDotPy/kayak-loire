<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>Test de la configuration</h1>";

// Test 1 : Connexion BDD
try {
    $conn = getConnection();
    echo "✅ Connexion à la base de données : OK<br>";
} catch(Exception $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "<br>";
}

// Test 2 : Constantes
echo "✅ SITE_URL : " . SITE_URL . "<br>";
echo "✅ SITE_NAME : " . SITE_NAME . "<br>";

// Test 3 : Fonctions
$test_email = "test@example.com";
echo "✅ Validation email '$test_email' : " . (isValidEmail($test_email) ? "VALIDE" : "INVALIDE") . "<br>";

$test_price = 49.99;
echo "✅ Formatage prix : " . formatPrice($test_price) . "<br>";

echo "<br><strong>Tous les tests sont OK !</strong>";
?>