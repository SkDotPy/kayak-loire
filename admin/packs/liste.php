<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des packs';

// Récupérer tous les packs
$packs = query("SELECT * FROM packs ORDER BY duree_jours ASC, nom ASC");

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Gestion des packs</h1>
            <a href="<?php echo SITE_URL; ?>/admin/packs/ajouter.php" class="btn btn-primary">Ajouter un pack</a>
        </div>

        <?php if (empty($packs)): ?>
            <div class="empty-state">
                <p>Aucun pack créé pour le moment.</p>
                <a href="<?php echo SITE_URL; ?>/admin/packs/ajouter.php" class="btn btn-primary">Créer le premier pack</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Durée</th>
                            <th>Difficulté</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($packs as $pack): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pack['nom']); ?></strong></td>
                                <td><?php echo $pack['duree_jours']; ?> jour(s)</td>
                                <td>
                                    <?php
                                    $difficultes = [
                                        'facile' => '🟢 Facile',
                                        'moyen' => '🟡 Moyen',
                                        'difficile' => '🔴 Difficile'
                                    ];
                                    echo $difficultes[$pack['difficulte']] ?? $pack['difficulte'];
                                    ?>
                                </td>
                                <td><?php echo formatPrice($pack['prix_base']); ?></td>
                                <td>
                                    <?php if ($pack['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/packs/modifier.php?id=<?php echo $pack['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/packs/supprimer.php?id=<?php echo $pack['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pack ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($packs); ?></strong> pack(s) affiché(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>