<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des réservations';

// Filtres
$filtre_statut = isset($_GET['statut']) ? clean($_GET['statut']) : '';
$filtre_mois = isset($_GET['mois']) ? clean($_GET['mois']) : '';

// Construire la requête avec filtres
$sql = "SELECT r.*, u.prenom, u.nom, u.email 
        FROM reservations r
        INNER JOIN users u ON r.user_id = u.id
        WHERE 1=1";
$params = [];

if (!empty($filtre_statut)) {
    $sql .= " AND r.statut = ?";
    $params[] = $filtre_statut;
}

if (!empty($filtre_mois)) {
    // Format attendu : 2024-12
    $sql .= " AND DATE_FORMAT(r.date_debut, '%Y-%m') = ?";
    $params[] = $filtre_mois;
}

$sql .= " ORDER BY r.created_at DESC";

$reservations = query($sql, $params);

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Gestion des réservations</h1>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="statut">Filtrer par statut :</label>
                    <select name="statut" id="statut">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?php echo ($filtre_statut === 'en_attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="confirmee" <?php echo ($filtre_statut === 'confirmee') ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="terminee" <?php echo ($filtre_statut === 'terminee') ? 'selected' : ''; ?>>Terminée</option>
                        <option value="annulee" <?php echo ($filtre_statut === 'annulee') ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="mois">Filtrer par mois :</label>
                    <input type="month" name="mois" id="mois" value="<?php echo $filtre_mois; ?>">
                </div>

                <button type="submit" class="btn btn-outline">Filtrer</button>
                <a href="<?php echo SITE_URL; ?>/admin/reservations/liste.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-summary" style="margin-bottom: 30px;">
            <?php
            $stats = [
                'total' => count(query("SELECT id FROM reservations")),
                'en_attente' => count(query("SELECT id FROM reservations WHERE statut = 'en_attente'")),
                'confirmee' => count(query("SELECT id FROM reservations WHERE statut = 'confirmee'")),
                'ca_total' => query("SELECT SUM(prix_total_ttc) as total FROM reservations WHERE statut IN ('confirmee', 'terminee')")[0]['total'] ?? 0
            ];
            ?>
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-value"><?php echo $stats['total']; ?></div>
                    <div class="summary-label">Total réservations</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo $stats['en_attente']; ?></div>
                    <div class="summary-label">En attente</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo $stats['confirmee']; ?></div>
                    <div class="summary-label">Confirmées</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo formatPrice($stats['ca_total']); ?></div>
                    <div class="summary-label">CA Total</div>
                </div>
            </div>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <p>Aucune réservation trouvée.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>N° Réservation</th>
                            <th>Client</th>
                            <th>Dates</th>
                            <th>Nb Pers.</th>
                            <th>Total TTC</th>
                            <th>Statut</th>
                            <th>Créée le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($reservation['numero_reservation']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?><br>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($reservation['email']); ?></small>
                                </td>
                                <td>
                                    <?php echo formatDate($reservation['date_debut']); ?><br>
                                    au <?php echo formatDate($reservation['date_fin']); ?>
                                </td>
                                <td><?php echo $reservation['nb_personnes']; ?> pers.</td>
                                <td><strong><?php echo formatPrice($reservation['prix_total_ttc']); ?></strong></td>
                                <td>
                                    <?php
                                    $statuts_badges = [
                                        'en_attente' => 'warning',
                                        'confirmee' => 'success',
                                        'terminee' => 'info',
                                        'annulee' => 'danger'
                                    ];
                                    $statuts_labels = [
                                        'en_attente' => 'En attente',
                                        'confirmee' => 'Confirmée',
                                        'terminee' => 'Terminée',
                                        'annulee' => 'Annulée'
                                    ];
                                    $badge_type = $statuts_badges[$reservation['statut']] ?? 'info';
                                    $statut_label = $statuts_labels[$reservation['statut']] ?? $reservation['statut'];
                                    ?>
                                    <span class="badge badge-<?php echo $badge_type; ?>"><?php echo $statut_label; ?></span>
                                </td>
                                <td><?php echo formatDate($reservation['created_at']); ?></td>
                                <td class="actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/reservations/details.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">Voir détails</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($reservations); ?></strong> réservation(s) affichée(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>