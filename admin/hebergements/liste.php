<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des hébergements';

// Filtres
$filtre_etape = isset($_GET['etape']) ? (int)$_GET['etape'] : 0;
$filtre_type = isset($_GET['type']) ? clean($_GET['type']) : '';

// Construire la requête avec filtres
$sql = "SELECT h.*, e.nom as etape_nom 
        FROM hebergements h 
        INNER JOIN etapes e ON h.etape_id = e.id 
        WHERE 1=1";
$params = [];

if ($filtre_etape > 0) {
    $sql .= " AND h.etape_id = ?";
    $params[] = $filtre_etape;
}

if (!empty($filtre_type)) {
    $sql .= " AND h.type = ?";
    $params[] = $filtre_type;
}

$sql .= " ORDER BY e.ordre ASC, h.nom ASC";

$hebergements = query($sql, $params);

// Récupérer toutes les étapes pour le filtre
$etapes = query("SELECT * FROM etapes ORDER BY ordre ASC");

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Gestion des hébergements</h1>
            <a href="<?php echo SITE_URL; ?>/admin/hebergements/ajouter.php" class="btn btn-primary">Ajouter un hébergement</a>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="etape">Filtrer par étape :</label>
                    <select name="etape" id="etape">
                        <option value="0">Toutes les étapes</option>
                        <?php foreach($etapes as $etape): ?>
                            <option value="<?php echo $etape['id']; ?>" <?php echo ($filtre_etape == $etape['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($etape['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="type">Filtrer par type :</label>
                    <select name="type" id="type">
                        <option value="">Tous les types</option>
                        <option value="hotel" <?php echo ($filtre_type === 'hotel') ? 'selected' : ''; ?>>Hôtel</option>
                        <option value="camping" <?php echo ($filtre_type === 'camping') ? 'selected' : ''; ?>>Camping</option>
                        <option value="gite" <?php echo ($filtre_type === 'gite') ? 'selected' : ''; ?>>Gîte</option>
                        <option value="chambre_hote" <?php echo ($filtre_type === 'chambre_hote') ? 'selected' : ''; ?>>Chambre d'hôtes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline">Filtrer</button>
                <a href="<?php echo SITE_URL; ?>/admin/hebergements/liste.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <?php if (empty($hebergements)): ?>
            <div class="empty-state">
                <p>Aucun hébergement trouvé.</p>
                <a href="<?php echo SITE_URL; ?>/admin/hebergements/ajouter.php" class="btn btn-primary">Ajouter le premier hébergement</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Étape</th>
                            <th>Capacité</th>
                            <th>Prix/nuit</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($hebergements as $hebergement): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hebergement['nom']); ?></strong></td>
                                <td>
                                    <?php
                                    $types = [
                                        'hotel' => 'Hôtel',
                                        'camping' => 'Camping',
                                        'gite' => 'Gîte',
                                        'chambre_hote' => 'Chambre d\'hôtes'
                                    ];
                                    echo $types[$hebergement['type']];
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($hebergement['etape_nom']); ?></td>
                                <td><?php echo $hebergement['capacite']; ?> pers.</td>
                                <td><?php echo formatPrice($hebergement['prix_par_nuit']); ?></td>
                                <td>
                                    <?php if ($hebergement['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/hebergements/modifier.php?id=<?php echo $hebergement['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/hebergements/supprimer.php?id=<?php echo $hebergement['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet hébergement ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($hebergements); ?></strong> hébergement(s) affiché(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>