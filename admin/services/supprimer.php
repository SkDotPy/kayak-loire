<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/services/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer le service
$service = query("SELECT * FROM services_complementaires WHERE id = ?", [$id]);

if (empty($service)) {
    setErrorMessage("Ce service n'existe pas.");
    redirect(SITE_URL . '/admin/services/liste.php');
}

$service = $service[0];

// Vérifier si le service est utilisé dans des réservations
$reservations_liees = query("SELECT COUNT(*) as total FROM reservation_services WHERE service_id = ?", [$id]);
$nb_reservations = $reservations_liees[0]['total'];

// Si confirmation de suppression
if (isset($_GET['confirmer']) && $_GET['confirmer'] === 'oui') {
    // Supprimer l'image si elle existe
    if ($service['image'] && file_exists(UPLOAD_PATH . '/services/' . $service['image'])) {
        unlink(UPLOAD_PATH . '/services/' . $service['image']);
    }
    
    // Supprimer le service
    $result = execute("DELETE FROM services_complementaires WHERE id = ?", [$id]);
    
    if ($result) {
        setSuccessMessage("Le service a été supprimé avec succès.");
    } else {
        setErrorMessage("Une erreur est survenue lors de la suppression.");
    }
    
    redirect(SITE_URL . '/admin/services/liste.php');
}

$page_title = 'Supprimer un service';
include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Supprimer un service</h1>
        </div>

        <div class="confirmation-box">
            <div class="alert alert-warning">
                <h3>Attention !</h3>
                <p>Vous êtes sur le point de supprimer le service :</p>
                <p><strong><?php echo htmlspecialchars($service['nom']); ?></strong></p>
            </div>

            <?php if ($nb_reservations > 0): ?>
                <div class="alert alert-error">
                    <h4>Ce service est utilisé :</h4>
                    <p><strong><?php echo $nb_reservations; ?></strong> réservation(s) liée(s)</p>
                    <p><strong>Ces liaisons seront également supprimées !</strong></p>
                </div>
            <?php endif; ?>

            <div class="etape-info">
                <h4>Informations du service :</h4>
                <ul>
                    <li><strong>Type :</strong> 
                        <?php 
                        $types = [
                            'materiel' => 'Matériel',
                            'prestation' => 'Prestation',
                            'nourriture' => 'Nourriture',
                            'autre' => 'Autre'
                        ];
                        echo $types[$service['type']] ?? $service['type'];
                        ?>
                    </li>
                    <li><strong>Prix :</strong> <?php echo formatPrice($service['prix']); ?></li>
                    <li><strong>Statut :</strong> <?php echo $service['actif'] ? 'Actif' : 'Inactif'; ?></li>
                </ul>
                
                <?php if ($service['image']): ?>
                    <div class="current-image">
                        <p><strong>Image :</strong></p>
                        <img src="<?php echo UPLOAD_URL; ?>/services/<?php echo $service['image']; ?>" alt="Image du service" style="max-width: 300px; border-radius: 8px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/admin/services/supprimer.php?id=<?php echo $id; ?>&confirmer=oui" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')">Confirmer la suppression</a>
                <a href="<?php echo SITE_URL; ?>/admin/services/liste.php" class="btn btn-primary">Annuler</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>