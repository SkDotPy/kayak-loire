<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/packs/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer le pack
$pack = query("SELECT * FROM packs WHERE id = ?", [$id]);

if (empty($pack)) {
    setErrorMessage("Ce pack n'existe pas.");
    redirect(SITE_URL . '/admin/packs/liste.php');
}

$pack = $pack[0];

// Récupérer la composition du pack pour l'afficher
$composition = query("SELECT pe.*, e.nom as etape_nom, h.nom as hebergement_nom 
                      FROM pack_etapes pe
                      INNER JOIN etapes e ON pe.etape_id = e.id
                      LEFT JOIN hebergements h ON pe.hebergement_id = h.id
                      WHERE pe.pack_id = ?
                      ORDER BY pe.jour ASC", [$id]);

// Vérifier si le pack est utilisé dans des réservations (à implémenter plus tard)
// $reservations_liees = query("SELECT COUNT(*) as total FROM reservations WHERE pack_id = ?", [$id]);
// $nb_reservations = $reservations_liees[0]['total'];

// Si confirmation de suppression
if (isset($_GET['confirmer']) && $_GET['confirmer'] === 'oui') {
    // Supprimer l'image si elle existe
    if ($pack['image'] && file_exists(UPLOAD_PATH . '/packs/' . $pack['image'])) {
        unlink(UPLOAD_PATH . '/packs/' . $pack['image']);
    }
    
    // Supprimer la composition (pack_etapes)
    execute("DELETE FROM pack_etapes WHERE pack_id = ?", [$id]);
    
    // Supprimer le pack
    $result = execute("DELETE FROM packs WHERE id = ?", [$id]);
    
    if ($result) {
        setSuccessMessage("Le pack a été supprimé avec succès.");
    } else {
        setErrorMessage("Une erreur est survenue lors de la suppression.");
    }
    
    redirect(SITE_URL . '/admin/packs/liste.php');
}

$page_title = 'Supprimer un pack';
include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Supprimer un pack</h1>
        </div>

        <div class="confirmation-box">
            <div class="alert alert-warning">
                <h3>Attention !</h3>
                <p>Vous êtes sur le point de supprimer le pack :</p>
                <p><strong><?php echo htmlspecialchars($pack['nom']); ?></strong></p>
            </div>

            <div class="etape-info">
                <h4>Informations du pack :</h4>
                <ul>
                    <li><strong>Durée :</strong> <?php echo $pack['duree_jours']; ?> jour(s)</li>
                    <li><strong>Prix :</strong> <?php echo formatPrice($pack['prix_base']); ?></li>
                    <li><strong>Difficulté :</strong> 
                        <?php 
                        $difficultes = ['facile' => 'Facile', 'moyen' => 'Moyen', 'difficile' => 'Difficile'];
                        echo $difficultes[$pack['difficulte']];
                        ?>
                    </li>
                    <li><strong>Statut :</strong> <?php echo $pack['actif'] ? 'Actif' : 'Inactif'; ?></li>
                </ul>

                <h4>Composition du parcours :</h4>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    <?php foreach($composition as $comp_jour): ?>
                        <div style="padding: 10px; border-left: 3px solid var(--primary-color); margin-bottom: 10px; background: white;">
                            <strong>Jour <?php echo $comp_jour['jour']; ?> :</strong> 
                            <?php echo htmlspecialchars($comp_jour['etape_nom']); ?>
                            <?php if ($comp_jour['hebergement_nom']): ?>
                                → <em><?php echo htmlspecialchars($comp_jour['hebergement_nom']); ?></em>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($pack['image']): ?>
                    <div class="current-image">
                        <p><strong>Image :</strong></p>
                        <img src="<?php echo UPLOAD_URL; ?>/packs/<?php echo $pack['image']; ?>" alt="Image du pack" style="max-width: 300px; border-radius: 8px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-error" style="margin-top: 20px;">
                <p><strong>Cette action supprimera également :</strong></p>
                <ul>
                    <li>La composition du pack (<?php echo count($composition); ?> jour(s) configuré(s))</li>
                    <li>L'image du pack</li>
                </ul>
            </div>

            <div class="form-actions" style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/admin/packs/supprimer.php?id=<?php echo $id; ?>&confirmer=oui" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')">Confirmer la suppression</a>
                <a href="<?php echo SITE_URL; ?>/admin/packs/liste.php" class="btn btn-primary">Annuler</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>