<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>Test des données</h1>";

// Test 1 : Compter les étapes
$etapes = query("SELECT COUNT(*) as total FROM etapes");
echo "✅ Nombre d'étapes : " . $etapes[0]['total'] . "<br>";

// Test 2 : Compter les hébergements
$hebergements = query("SELECT COUNT(*) as total FROM hebergements");
echo "✅ Nombre d'hébergements : " . $hebergements[0]['total'] . "<br>";

// Test 3 : Compter les services
$services = query("SELECT COUNT(*) as total FROM services_complementaires");
echo "✅ Nombre de services : " . $services[0]['total'] . "<br>";

// Test 4 : Compter les packs
$packs = query("SELECT COUNT(*) as total FROM packs");
echo "✅ Nombre de packs : " . $packs[0]['total'] . "<br>";

// Test 5 : Afficher les 3 premières étapes
echo "<h2>Premières étapes :</h2>";
$premieres_etapes = query("SELECT nom, ville, distance_precedente FROM etapes ORDER BY ordre LIMIT 3");
foreach($premieres_etapes as $etape) {
    echo "📍 " . $etape['nom'] . " (" . $etape['ville'] . ") - " . $etape['distance_precedente'] . " km<br>";
}

echo "<br><strong>Base de données remplie avec succès !</strong>";
?>