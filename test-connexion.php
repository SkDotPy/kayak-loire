<?php
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=kayak_loire;charset=utf8mb4",
        "root",
        ""
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie !";
} catch(PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>