<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Titre de la page
$page_title = 'Accueil';

// Récupérer quelques données pour afficher
$etapes_count = query("SELECT COUNT(*) as total FROM etapes WHERE actif = 1");
$total_etapes = $etapes_count[0]['total'];

$packs = query("SELECT * FROM packs WHERE actif = 1 LIMIT 3");

// Inclure le header
include 'includes/header.php';
?>

<!-- Section Hero (Bannière principale) -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Vivez l'aventure sur la Loire</h1>
        <p>Découvrez le dernier fleuve sauvage d'Europe en kayak</p>
        <div class="hero-buttons">
            <a href="<?php echo SITE_URL; ?>/pages/packs.php" class="btn btn-primary">Voir nos packs</a>
            <a href="<?php echo SITE_URL; ?>/pages/composer-parcours.php" class="btn btn-secondary">Composer mon parcours</a>
        </div>
    </div>
</section>

<!-- Section Chiffres clés -->
<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_etapes; ?></div>
                <div class="stat-label">Points d'arrêt</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">21</div>
                <div class="stat-label">Hébergements</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">7</div>
                <div class="stat-label">Services</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Packs disponibles</div>
            </div>
        </div>
    </div>
</section>

<!-- Section Comment ça marche -->
<section class="how-it-works">
    <div class="container">
        <h2>Comment ça marche ?</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Choisissez votre parcours</h3>
                <p>Sélectionnez un pack prédéfini ou composez votre propre itinéraire en choisissant vos étapes.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Réservez vos hébergements</h3>
                <p>Choisissez vos hébergements à chaque étape : hôtel, camping, gîte ou chambre d'hôtes.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Ajoutez des services</h3>
                <p>Transport de bagages, paniers garnis, location de matériel... On s'occupe de tout !</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Partez à l'aventure</h3>
                <p>Recevez votre feuille de route et profitez de votre voyage sur la Loire en toute sérénité.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Nos packs -->
<section class="packs-preview">
    <div class="container">
        <h2>Nos packs populaires</h2>
        <div class="packs-grid">
            <?php foreach($packs as $pack): ?>
                <div class="pack-card">
                    <div class="pack-header">
                        <h3><?php echo htmlspecialchars($pack['nom']); ?></h3>
                        <span class="pack-duration"><?php echo $pack['duree_jours']; ?> jours</span>
                    </div>
                    <div class="pack-body">
                        <p><?php echo htmlspecialchars($pack['description']); ?></p>
                        <div class="pack-info">
                            <span class="pack-difficulty">
                                <?php 
                                $difficulte_label = [
                                    'facile' => 'Facile',
                                    'moyen' => 'Moyen',
                                    'difficile' => 'Difficile'
                                ];
                                echo $difficulte_label[$pack['difficulte']];
                                ?>
                            </span>
                            <span class="pack-price">À partir de <?php echo formatPrice($pack['prix_base']); ?></span>
                        </div>
                    </div>
                    <div class="pack-footer">
                        <a href="<?php echo SITE_URL; ?>/pages/packs.php?id=<?php echo $pack['id']; ?>" class="btn btn-outline">Voir les détails</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/pages/packs.php" class="btn btn-primary">Voir tous les packs</a>
        </div>
    </div>
</section>

<?php
// Inclure le footer
include 'includes/footer.php';
?>