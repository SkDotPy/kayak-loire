<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des étapes';

// Récupérer toutes les étapes
$etapes = query("SELECT * FROM etapes ORDER BY ordre ASC");

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Gestion des étapes</h1>
            <a href="<?php echo SITE_URL; ?>/admin/etapes/ajouter.php" class="btn btn-primary">Ajouter une étape</a>
        </div>

        <?php if (empty($etapes)): ?>
            <div class="empty-state">
                <p>Aucune étape pour le moment.</p>
                <a href="<?php echo SITE_URL; ?>/admin/etapes/ajouter.php" class="btn btn-primary">Ajouter la première étape</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ordre</th>
                            <th>Nom</th>
                            <th>Ville</th>
                            <th>Distance (km)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($etapes as $etape): ?>
                            <tr>
                                <td><strong><?php echo $etape['ordre']; ?></strong></td>
                                <td><?php echo htmlspecialchars($etape['nom']); ?></td>
                                <td><?php echo htmlspecialchars($etape['ville']); ?></td>
                                <td><?php echo $etape['distance_precedente']; ?> km</td>
                                <td>
                                    <?php if ($etape['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/etapes/modifier.php?id=<?php echo $etape['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/etapes/supprimer.php?id=<?php echo $etape['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette étape ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>