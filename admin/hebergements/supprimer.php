<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/hebergements/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer l'hébergement
$hebergement = query("SELECT h.*, e.nom as etape_nom FROM hebergements h INNER JOIN etapes e ON h.etape_id = e.id WHERE h.id = ?", [$id]);

if (empty($hebergement)) {
    setErrorMessage("Cet hébergement n'existe pas.");
    redirect(SITE_URL . '/admin/hebergements/liste.php');
}

$hebergement = $hebergement[0];

// Vérifier si l'hébergement est utilisé dans des réservations
$reservations_liees = query("SELECT COUNT(*) as total FROM reservation_hebergements WHERE hebergement_id = ?", [$id]);
$nb_reservations = $reservations_liees[0]['total'];

// Vérifier si l'hébergement est utilisé dans des packs
$packs_lies = query("SELECT COUNT(*) as total FROM pack_etapes WHERE hebergement_id = ?", [$id]);
$nb_packs = $packs_lies[0]['total'];

// Si confirmation de suppression
if (isset($_GET['confirmer']) && $_GET['confirmer'] === 'oui') {
    // Supprimer l'image si elle existe
    if ($hebergement['image'] && file_exists(UPLOAD_PATH . '/hebergements/' . $hebergement['image'])) {
        unlink(UPLOAD_PATH . '/hebergements/' . $hebergement['image']);
    }
    
    // Supprimer l'hébergement
    $result = execute("DELETE FROM hebergements WHERE id = ?", [$id]);
    
    if ($result) {
        setSuccessMessage("L'hébergement a été supprimé avec succès.");
    } else {
        setErrorMessage("Une erreur est survenue lors de la suppression.");
    }
    
    redirect(SITE_URL . '/admin/hebergements/liste.php');
}

$page_title = 'Supprimer un hébergement';
include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Supprimer un hébergement</h1>
        </div>

        <div class="confirmation-box">
            <div class="alert alert-warning">
                <h3>Attention !</h3>
                <p>Vous êtes sur le point de supprimer l'hébergement :</p>
                <p><strong><?php echo htmlspecialchars($hebergement['nom']); ?></strong></p>
                <p>Type : <?php 
                    $types = [
                        'hotel' => 'Hôtel',
                        'camping' => 'Camping',
                        'gite' => 'Gîte',
                        'chambre_hote' => 'Chambre d\'hôtes'
                    ];
                    echo $types[$hebergement['type']];
                ?> - Étape : <?php echo htmlspecialchars($hebergement['etape_nom']); ?></p>
            </div>

            <?php if ($nb_reservations > 0 || $nb_packs > 0): ?>
                <div class="alert alert-error">
                    <h4>Cet hébergement est utilisé :</h4>
                    <ul>
                        <?php if ($nb_reservations > 0): ?>
                            <li><strong><?php echo $nb_reservations; ?></strong> réservation(s) liée(s)</li>
                        <?php endif; ?>
                        <?php if ($nb_packs > 0): ?>
                            <li><strong><?php echo $nb_packs; ?></strong> pack(s) lié(s)</li>
                        <?php endif; ?>
                    </ul>
                    <p><strong>Ces liaisons seront également supprimées !</strong></p>
                </div>
            <?php endif; ?>

            <div class="etape-info">
                <h4>Informations de l'hébergement :</h4>
                <ul>
                    <li><strong>Capacité :</strong> <?php echo $hebergement['capacite']; ?> personnes</li>
                    <li><strong>Prix par nuit :</strong> <?php echo formatPrice($hebergement['prix_par_nuit']); ?></li>
                    <li><strong>Adresse :</strong> <?php echo htmlspecialchars($hebergement['adresse']); ?></li>
                    <li><strong>Statut :</strong> <?php echo $hebergement['actif'] ? 'Actif' : 'Inactif'; ?></li>
                </ul>
                
                <?php if ($hebergement['image']): ?>
                    <div class="current-image">
                        <p><strong>Photo :</strong></p>
                        <img src="<?php echo UPLOAD_URL; ?>/hebergements/<?php echo $hebergement['image']; ?>" alt="Photo de l'hébergement" style="max-width: 300px; border-radius: 8px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/admin/hebergements/supprimer.php?id=<?php echo $id; ?>&confirmer=oui" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')">Confirmer la suppression</a>
                <a href="<?php echo SITE_URL; ?>/admin/hebergements/liste.php" class="btn btn-primary">Annuler</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>