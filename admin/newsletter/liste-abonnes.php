<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Liste des abonnés newsletter';

// Filtre
$filtre_statut = isset($_GET['statut']) ? clean($_GET['statut']) : '';

// Construire la requête
$sql = "SELECT * FROM newsletter_abonnes WHERE 1=1";
$params = [];

if ($filtre_statut === 'actif') {
    $sql .= " AND actif = 1";
} elseif ($filtre_statut === 'inactif') {
    $sql .= " AND actif = 0";
}

$sql .= " ORDER BY date_inscription DESC";

$abonnes = query($sql, $params);

// Statistiques
$total = count(query("SELECT id FROM newsletter_abonnes"));
$actifs = count(query("SELECT id FROM newsletter_abonnes WHERE actif = 1"));
$inactifs = $total - $actifs;

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>📧 Newsletter - Abonnés</h1>
            <a href="<?php echo SITE_URL; ?>/admin/newsletter/composer.php" class="btn btn-primary">✉️ Envoyer une newsletter</a>
        </div>

        <!-- Statistiques -->
        <div class="stats-summary">
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-value"><?php echo $total; ?></div>
                    <div class="summary-label">Total abonnés</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value" style="color: #059669;"><?php echo $actifs; ?></div>
                    <div class="summary-label">Actifs</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value" style="color: #dc2626;"><?php echo $inactifs; ?></div>
                    <div class="summary-label">Désabonnés</div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-box">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="statut">Filtrer par statut :</label>
                    <select name="statut" id="statut">
                        <option value="">Tous</option>
                        <option value="actif" <?php echo ($filtre_statut === 'actif') ? 'selected' : ''; ?>>Actifs uniquement</option>
                        <option value="inactif" <?php echo ($filtre_statut === 'inactif') ? 'selected' : ''; ?>>Désabonnés uniquement</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline">Filtrer</button>
                <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <?php if (empty($abonnes)): ?>
            <div class="empty-state">
                <p>Aucun abonné trouvé.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th>Date désabonnement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($abonnes as $abonne): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($abonne['email']); ?></strong></td>
                                <td><?php echo htmlspecialchars($abonne['nom'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($abonne['prenom'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($abonne['actif']): ?>
                                        <span class="badge badge-success">✓ Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">✗ Désabonné</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($abonne['date_inscription']); ?></td>
                                <td>
                                    <?php if ($abonne['date_desinscription']): ?>
                                        <?php echo formatDate($abonne['date_desinscription']); ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($abonnes); ?></strong> abonné(s) affiché(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/newsletter/historique.php" class="btn btn-outline">📋 Voir l'historique des envois</a>
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>