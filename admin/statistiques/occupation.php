<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Statistiques d\'occupation';

// ============================================
// 1. RÉCUPÉRATION DES FILTRES
// ============================================
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-01');
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-t');
$type_filtre = isset($_GET['type']) ? $_GET['type'] : '';

// ============================================
// 2. CALCUL DU NOMBRE DE JOURS
// ============================================
$debut = new DateTime($date_debut);
$fin = new DateTime($date_fin);
$nb_jours = $debut->diff($fin)->days + 1;

// ============================================
// 3. RÉCUPÉRER LES HÉBERGEMENTS ACTIFS
// ============================================
$sql = "SELECT h.id, h.nom, h.type, e.nom as etape 
        FROM hebergements h
        INNER JOIN etapes e ON h.etape_id = e.id
        WHERE h.actif = 1";

if (!empty($type_filtre)) {
    $sql .= " AND h.type = ?";
    $hebergements = query($sql, [$type_filtre]);
} else {
    $hebergements = query($sql);
}

// ============================================
// 4. COMPTER LES RÉSERVATIONS POUR CHAQUE HÉBERGEMENT
// ============================================
foreach ($hebergements as &$h) {
    $sql_count = "SELECT COUNT(*) as total
                  FROM reservation_hebergements rh
                  INNER JOIN reservations r ON rh.reservation_id = r.id
                  WHERE rh.hebergement_id = ?
                  AND r.statut IN ('confirmee', 'terminee')
                  AND rh.date_arrivee <= ?
                  AND rh.date_depart >= ?";
    
    $result = query($sql_count, [$h['id'], $date_fin, $date_debut]);
    $h['nb_reservations'] = $result[0]['total'];
    $h['taux'] = round(($h['nb_reservations'] / $nb_jours) * 100, 2);
}

// ============================================
// 5. CALCULER LES STATISTIQUES GLOBALES
// ============================================
$total_reservations = 0;
$somme_taux = 0;
$nb_bien_occupes = 0;
$nb_peu_occupes = 0;

foreach ($hebergements as $h) {
    $total_reservations += $h['nb_reservations'];
    $somme_taux += $h['taux'];
    if ($h['taux'] >= 80) $nb_bien_occupes++;
    if ($h['taux'] < 50) $nb_peu_occupes++;
}

$taux_moyen = count($hebergements) > 0 ? round($somme_taux / count($hebergements), 2) : 0;

// ============================================
// 6. PRÉPARER LES DONNÉES POUR LE GRAPHIQUE
// ============================================
$data_graphique = array_map(function($h) {
    return ['nom' => $h['nom'], 'taux' => $h['taux']];
}, $hebergements);

include '../../includes/header.php';
?>

<!-- Inclure Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Statistiques d'occupation</h1>
        </div>

        <!-- FORMULAIRE DE FILTRES -->
        <div class="filters-box">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label>Date début :</label>
                    <input type="date" name="date_debut" value="<?php echo $date_debut; ?>" required>
                </div>

                <div class="filter-group">
                    <label>Date fin :</label>
                    <input type="date" name="date_fin" value="<?php echo $date_fin; ?>" required>
                </div>

                <div class="filter-group">
                    <label>Type :</label>
                    <select name="type">
                        <option value="">Tous les types</option>
                        <option value="hotel" <?php echo $type_filtre === 'hotel' ? 'selected' : ''; ?>>Hôtel</option>
                        <option value="camping" <?php echo $type_filtre === 'camping' ? 'selected' : ''; ?>>Camping</option>
                        <option value="gite" <?php echo $type_filtre === 'gite' ? 'selected' : ''; ?>>Gîte</option>
                        <option value="chambre_hote" <?php echo $type_filtre === 'chambre_hote' ? 'selected' : ''; ?>>Chambre d'hôtes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Générer le graphique</button>
            </form>
        </div>

        <!-- INFORMATIONS SUR LA PÉRIODE -->
        <div class="period-info">
            <p><strong>Période analysée :</strong> du <?php echo formatDate($date_debut); ?> au <?php echo formatDate($date_fin); ?></p>
            <p><strong>Nombre de jours :</strong> <?php echo $nb_jours; ?> jours</p>
            <p><strong>Hébergements analysés :</strong> <?php echo count($hebergements); ?></p>
        </div>

        <!-- STATISTIQUES GLOBALES -->
        <div class="stats-summary">
            <h2>Résumé global</h2>
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-value"><?php echo $taux_moyen; ?>%</div>
                    <div class="summary-label">Taux moyen d'occupation</div>
                </div>

                <div class="summary-card">
                    <div class="summary-value"><?php echo $total_reservations; ?></div>
                    <div class="summary-label">Total réservations</div>
                </div>

                <div class="summary-card">
                    <div class="summary-value"><?php echo $nb_bien_occupes; ?></div>
                    <div class="summary-label">Hébergements bien occupés (≥80%)</div>
                </div>

                <div class="summary-card">
                    <div class="summary-value"><?php echo $nb_peu_occupes; ?></div>
                    <div class="summary-label">Hébergements peu occupés (&lt;50%)</div>
                </div>
            </div>
        </div>

        <!-- GRAPHIQUE -->
        <div class="chart-container">
            <canvas id="graphique"></canvas>
        </div>

        <!-- TABLEAU DÉTAILLÉ -->
        <div class="table-container">
            <h2>Détails par hébergement</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Hébergement</th>
                        <th>Type</th>
                        <th>Étape</th>
                        <th>Réservations</th>
                        <th>Taux d'occupation</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($hebergements as $h): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($h['nom']); ?></strong></td>
                            <td>
                                <?php 
                                $types = [
                                    'hotel' => 'Hôtel',
                                    'camping' => 'Camping',
                                    'gite' => 'Gîte',
                                    'chambre_hote' => 'Chambre d\'hôtes'
                                ];
                                echo $types[$h['type']];
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($h['etape']); ?></td>
                            <td><?php echo $h['nb_reservations']; ?></td>
                            <td>
                                <strong style="color: <?php 
                                    if ($h['taux'] >= 80) echo '#059669';
                                    elseif ($h['taux'] >= 50) echo '#d97706';
                                    else echo '#dc2626';
                                ?>">
                                    <?php echo $h['taux']; ?>%
                                </strong>
                            </td>
                            <td>
                                <?php if ($h['taux'] >= 80): ?>
                                    <span class="badge badge-success">Très bien</span>
                                <?php elseif ($h['taux'] >= 50): ?>
                                    <span class="badge badge-warning">Moyen</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Faible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<!-- SCRIPT CHART.JS -->
<script>
const donnees = <?php echo json_encode($data_graphique); ?>;

const noms = donnees.map(d => d.nom);
const taux = donnees.map(d => d.taux);

const couleurs = taux.map(t => {
    if (t >= 80) return 'rgba(5, 150, 105, 0.7)';
    if (t >= 50) return 'rgba(217, 119, 6, 0.7)';
    return 'rgba(220, 38, 38, 0.7)';
});

const ctx = document.getElementById('graphique').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: noms,
        datasets: [{
            label: 'Taux d\'occupation (%)',
            data: taux,
            backgroundColor: couleurs,
            borderColor: couleurs.map(c => c.replace('0.7', '1')),
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Taux d\'occupation des hébergements',
                font: { size: 18, weight: 'bold' }
            },
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: value => value + '%'
                },
                title: {
                    display: true,
                    text: 'Taux d\'occupation (%)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Hébergements'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>