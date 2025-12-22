<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des promotions';

// Récupérer toutes les promotions
$promotions = query("SELECT * FROM promotions ORDER BY created_at DESC");

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>🎫 Gestion des promotions</h1>
            <a href="<?php echo SITE_URL; ?>/admin/promotions/ajouter.php" class="btn btn-primary">Créer un code promo</a>
        </div>

        <?php if (empty($promotions)): ?>
            <div class="empty-state">
                <p>Aucune promotion créée pour le moment.</p>
                <a href="<?php echo SITE_URL; ?>/admin/promotions/ajouter.php" class="btn btn-primary">Créer le premier code promo</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Réduction</th>
                            <th>Validité</th>
                            <th>Utilisation</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($promotions as $promo): ?>
                            <?php
                            // Vérifier si la promo est expirée
                            $now = new DateTime();
                            $date_fin = $promo['date_fin'] ? new DateTime($promo['date_fin']) : null;
                            $est_expiree = $date_fin && $date_fin < $now;
                            
                            // Vérifier si les utilisations sont épuisées
                            $utilisations_epuisees = $promo['utilisation_max'] && $promo['utilisation_count'] >= $promo['utilisation_max'];
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-family: monospace; font-size: 16px; color: var(--primary-color);">
                                        <?php echo htmlspecialchars($promo['code']); ?>
                                    </strong>
                                </td>
                                <td><?php echo htmlspecialchars($promo['description']); ?></td>
                                <td>
                                    <?php if ($promo['type'] === 'pourcentage'): ?>
                                        <span class="badge badge-info">Pourcentage</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Montant fixe</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: #059669;">
                                        <?php 
                                        if ($promo['type'] === 'pourcentage') {
                                            echo $promo['valeur'] . '%';
                                        } else {
                                            echo formatPrice($promo['valeur']);
                                        }
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if ($promo['date_debut'] && $promo['date_fin']): ?>
                                        <?php echo formatDate($promo['date_debut']); ?><br>
                                        <small>au <?php echo formatDate($promo['date_fin']); ?></small>
                                    <?php elseif ($promo['date_debut']): ?>
                                        Dès le <?php echo formatDate($promo['date_debut']); ?>
                                    <?php elseif ($promo['date_fin']): ?>
                                        Jusqu'au <?php echo formatDate($promo['date_fin']); ?>
                                    <?php else: ?>
                                        <span style="color: #6b7280;">Illimitée</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $promo['utilisation_count']; ?>>
                                    <?php if ($promo['utilisation_max']): ?>
                                        / <?php echo $promo['utilisation_max']; ?>
                                    <?php else: ?>
                                        / ∞
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$promo['actif']): ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php elseif ($est_expiree): ?>
                                        <span class="badge badge-warning">Expirée</span>
                                    <?php elseif ($utilisations_epuisees): ?>
                                        <span class="badge badge-warning">Épuisée</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/promotions/modifier.php?id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/promotions/supprimer.php?id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce code promo ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($promotions); ?></strong> promotion(s) affichée(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>