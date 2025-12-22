<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des services';

// Filtre par type
$filtre_type = isset($_GET['type']) ? clean($_GET['type']) : '';

// Récupérer tous les services
$sql = "SELECT * FROM services_complementaires WHERE 1=1";
$params = [];

if (!empty($filtre_type)) {
    $sql .= " AND type = ?";
    $params[] = $filtre_type;
}

$sql .= " ORDER BY nom ASC";

$services = query($sql, $params);

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Gestion des services complémentaires</h1>
            <a href="<?php echo SITE_URL; ?>/admin/services/ajouter.php" class="btn btn-primary">Ajouter un service</a>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" class="filters-form">
                <div class="filter-group">
    <label for="type">Filtrer par type :</label>
    <select name="type" id="type">
        <option value="">Tous les types</option>
        <option value="materiel" <?php echo ($filtre_type === 'materiel') ? 'selected' : ''; ?>>Matériel</option>
        <option value="prestation" <?php echo ($filtre_type === 'prestation') ? 'selected' : ''; ?>>Prestation</option>
        <option value="nourriture" <?php echo ($filtre_type === 'nourriture') ? 'selected' : ''; ?>>Nourriture</option>
        <option value="autre" <?php echo ($filtre_type === 'autre') ? 'selected' : ''; ?>>Autre</option>
    </select>
</div>

                <button type="submit" class="btn btn-outline">Filtrer</button>
                <a href="<?php echo SITE_URL; ?>/admin/services/liste.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <?php if (empty($services)): ?>
            <div class="empty-state">
                <p>Aucun service trouvé.</p>
                <a href="<?php echo SITE_URL; ?>/admin/services/ajouter.php" class="btn btn-primary">Ajouter le premier service</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $service): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($service['nom']); ?></strong></td>
                                <td>
    <?php
    $types = [
        'materiel' => 'Matériel',
        'prestation' => 'Prestation',
        'nourriture' => 'Nourriture',
        'autre' => 'Autre'
    ];
    echo $types[$service['type']] ?? 'Non défini';
    ?>
</td>
                                <td><?php echo formatPrice($service['prix']); ?></td>
                                <td>
                                    <?php if ($service['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/services/modifier.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/services/supprimer.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($services); ?></strong> service(s) affiché(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>