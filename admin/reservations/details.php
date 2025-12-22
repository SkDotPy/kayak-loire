<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Détails de la réservation';

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/reservations/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer la réservation avec les infos du client
$reservation = query("SELECT r.*, u.prenom, u.nom, u.email, u.telephone 
                      FROM reservations r
                      INNER JOIN users u ON r.user_id = u.id
                      WHERE r.id = ?", [$id]);

if (empty($reservation)) {
    setErrorMessage("Cette réservation n'existe pas.");
    redirect(SITE_URL . '/admin/reservations/liste.php');
}

$reservation = $reservation[0];

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_statut'])) {
    $nouveau_statut = clean($_POST['nouveau_statut']);
    
    if (in_array($nouveau_statut, ['en_attente', 'confirmee', 'terminee', 'annulee'])) {
        $sql = "UPDATE reservations SET statut = ? WHERE id = ?";
        $result = execute($sql, [$nouveau_statut, $id]);
        
        if ($result) {
            setSuccessMessage("Le statut de la réservation a été modifié avec succès !");
            redirect(SITE_URL . '/admin/reservations/details.php?id=' . $id);
        } else {
            setErrorMessage("Erreur lors de la modification du statut.");
        }
    }
}

// Récupérer les hébergements de la réservation
$hebergements = query("SELECT rh.*, h.nom as hebergement_nom, e.nom as etape_nom
                       FROM reservation_hebergements rh
                       INNER JOIN hebergements h ON rh.hebergement_id = h.id
                       INNER JOIN etapes e ON h.etape_id = e.id
                       WHERE rh.reservation_id = ?
                       ORDER BY rh.date_arrivee ASC", [$id]);

// Récupérer les services de la réservation
$services = query("SELECT rs.*, s.nom as service_nom, s.type
                   FROM reservation_services rs
                   INNER JOIN services_complementaires s ON rs.service_id = s.id
                   WHERE rs.reservation_id = ?", [$id]);

// Calculer la durée du séjour
$date_debut = new DateTime($reservation['date_debut']);
$date_fin = new DateTime($reservation['date_fin']);
$duree = $date_debut->diff($date_fin)->days;

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Réservation <?php echo htmlspecialchars($reservation['numero_reservation']); ?></h1>
        </div>

        <div class="reservation-details">
            
            <!-- Informations client -->
            <div class="detail-section">
                <h3>👤 Informations client</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Nom :</span>
                        <span class="detail-value"><?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email :</span>
                        <span class="detail-value">
                            <a href="mailto:<?php echo htmlspecialchars($reservation['email']); ?>">
                                <?php echo htmlspecialchars($reservation['email']); ?>
                            </a>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone :</span>
                        <span class="detail-value">
                            <?php echo $reservation['telephone'] ? htmlspecialchars($reservation['telephone']) : 'Non renseigné'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Informations du séjour -->
            <div class="detail-section">
                <h3>📅 Informations du séjour</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Date de début :</span>
                        <span class="detail-value"><?php echo formatDate($reservation['date_debut']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date de fin :</span>
                        <span class="detail-value"><?php echo formatDate($reservation['date_fin']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Durée :</span>
                        <span class="detail-value"><?php echo $duree; ?> jour(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nombre de personnes :</span>
                        <span class="detail-value"><?php echo $reservation['nb_personnes']; ?> personne(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Créée le :</span>
                        <span class="detail-value"><?php echo formatDate($reservation['created_at']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Hébergements réservés -->
            <?php if (!empty($hebergements)): ?>
                <div class="detail-section">
                    <h3>🏨 Hébergements réservés</h3>
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Hébergement</th>
                                <th>Étape</th>
                                <th>Arrivée</th>
                                <th>Départ</th>
                                <th>Nuits</th>
                                <th>Prix total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($hebergements as $heb): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($heb['hebergement_nom']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($heb['etape_nom']); ?></td>
                                    <td><?php echo formatDate($heb['date_arrivee']); ?></td>
                                    <td><?php echo formatDate($heb['date_depart']); ?></td>
                                    <td><?php echo $heb['nb_nuits']; ?></td>
                                    <td><?php echo formatPrice($heb['prix_total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Services complémentaires -->
            <?php if (!empty($services)): ?>
                <div class="detail-section">
                    <h3>🛠️ Services complémentaires</h3>
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Prix unitaire</th>
                                <th>Prix total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($services as $service): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($service['service_nom']); ?></strong></td>
                                    <td>
                                        <?php 
                                        $types = ['materiel' => 'Matériel', 'prestation' => 'Prestation', 'nourriture' => 'Nourriture', 'autre' => 'Autre'];
                                        echo $types[$service['type']] ?? $service['type'];
                                        ?>
                                    </td>
                                    <td><?php echo $service['quantite']; ?></td>
                                    <td><?php echo formatPrice($service['prix_unitaire']); ?></td>
                                    <td><?php echo formatPrice($service['prix_total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Récapitulatif des prix -->
            <div class="detail-section">
                <h3>💰 Récapitulatif des prix</h3>
                <div class="price-summary">
                    <div class="price-line">
                        <span>Prix total HT :</span>
                        <span><?php echo formatPrice($reservation['prix_total_ht']); ?></span>
                    </div>
                    <div class="price-line total">
                        <span><strong>Prix total TTC :</strong></span>
                        <span><strong><?php echo formatPrice($reservation['prix_total_ttc']); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Gestion du statut -->
            <div class="detail-section">
                <h3>🔄 Changer le statut</h3>
                <form method="POST" class="status-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nouveau_statut">Statut actuel : 
                                <?php
                                $statuts_labels = [
                                    'en_attente' => '⏳ En attente',
                                    'confirmee' => '✅ Confirmée',
                                    'terminee' => '🏁 Terminée',
                                    'annulee' => '❌ Annulée'
                                ];
                                echo $statuts_labels[$reservation['statut']] ?? $reservation['statut'];
                                ?>
                            </label>
                            <select name="nouveau_statut" id="nouveau_statut" class="form-control">
                                <option value="en_attente" <?php echo ($reservation['statut'] === 'en_attente') ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmee" <?php echo ($reservation['statut'] === 'confirmee') ? 'selected' : ''; ?>>Confirmée</option>
                                <option value="terminee" <?php echo ($reservation['statut'] === 'terminee') ? 'selected' : ''; ?>>Terminée</option>
                                <option value="annulee" <?php echo ($reservation['statut'] === 'annulee') ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Mettre à jour le statut</button>
                    </div>
                </form>
            </div>

        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/reservations/liste.php" class="btn btn-outline">← Retour à la liste</a>
        </div>
    </div>
</div>

<style>
.reservation-details {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.detail-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f3f4f6;
}

.detail-section:last-child {
    border-bottom: none;
}

.detail-section h3 {
    color: var(--primary-color);
    margin-bottom: 20px;
    font-size: 20px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-label {
    font-weight: 600;
    color: #6b7280;
    font-size: 14px;
}

.detail-value {
    color: #1f2937;
    font-size: 16px;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table th,
.detail-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.detail-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.detail-table tr:hover {
    background: #f9fafb;
}

.price-summary {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
}

.price-line {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.price-line.total {
    border-top: 2px solid #d1d5db;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 18px;
}

.status-form {
    max-width: 600px;
}

.status-form .form-row {
    display: flex;
    gap: 20px;
    align-items: flex-end;
}

.status-form .form-group {
    flex: 1;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
}
</style>

<?php include '../../includes/footer.php'; ?>