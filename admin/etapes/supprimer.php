<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/etapes/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer l'étape
$etape = query("SELECT * FROM etapes WHERE id = ?", [$id]);

if (empty($etape)) {
    setErrorMessage("Cette étape n'existe pas.");
    redirect(SITE_URL . '/admin/etapes/liste.php');
}

$etape = $etape[0];

// Vérifier si l'étape est utilisée dans des hébergements
$hebergements_lies = query("SELECT COUNT(*) as total FROM hebergements WHERE etape_id = ?", [$id]);
$nb_hebergements = $hebergements_lies[0]['total'];

// Vérifier si l'étape est utilisée dans des packs
$packs_lies = query("SELECT COUNT(*) as total FROM pack_etapes WHERE etape_id = ?", [$id]);
$nb_packs = $packs_lies[0]['total'];

// Si confirmation de suppression
if (isset($_GET['confirmer']) && $_GET['confirmer'] === 'oui') {
    // Supprimer l'image si elle existe
    if ($etape['image'] && file_exists(UPLOAD_PATH . '/etapes/' . $etape['image'])) {
        unlink(UPLOAD_PATH . '/etapes/' . $etape['image']);
    }
    
    // Supprimer l'étape (les hébergements et pack_etapes seront supprimés en cascade grâce aux clés étrangères)
    $result = execute("DELETE FROM etapes WHERE id = ?", [$id]);
    
    if ($result) {
        setSuccessMessage("L'étape a été supprimée avec succès.");
    } else {
        setErrorMessage("Une erreur est survenue lors de la suppression.");
    }
    
    redirect(SITE_URL . '/admin/etapes/liste.php');
}

$page_title = 'Supprimer une étape';
include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Supprimer une étape</h1>
        </div>

        <div class="confirmation-box">
            <div class="alert alert-warning">
                <h3>Attention !</h3>
                <p>Vous êtes sur le point de supprimer l'étape :</p>
                <p><strong><?php echo htmlspecialchars($etape['nom']); ?></strong> (<?php echo htmlspecialchars($etape['ville']); ?>)</p>
            </div>

            <?php if ($nb_hebergements > 0 || $nb_packs > 0): ?>
                <div class="alert alert-error">
                    <h4>Cette étape est utilisée :</h4>
                    <ul>
                        <?php if ($nb_hebergements > 0): ?>
                            <li><strong><?php echo $nb_hebergements; ?></strong> hébergement(s) lié(s)</li>
                        <?php endif; ?>
                        <?php if ($nb_packs > 0): ?>
                            <li><strong><?php echo $nb_packs; ?></strong> pack(s) lié(s)</li>
                        <?php endif; ?>
                    </ul>
                    <p><strong>Ces éléments seront également supprimés !</strong></p>
                </div>
            <?php endif; ?>

            <div class="etape-info">
                <h4>Informations de l'étape :</h4>
                <ul>
                    <li><strong>Ordre :</strong> <?php echo $etape['ordre']; ?></li>
                    <li><strong>Distance :</strong> <?php echo $etape['distance_precedente']; ?> km</li>
                    <li><strong>Statut :</strong> <?php echo $etape['actif'] ? 'Actif' : 'Inactif'; ?></li>
                </ul>
                
                <?php if ($etape['image']): ?>
                    <div class="current-image">
                        <p><strong>Image :</strong></p>
                        <img src="<?php echo UPLOAD_URL; ?>/etapes/<?php echo $etape['image']; ?>" alt="Image de l'étape" style="max-width: 300px; border-radius: 8px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/admin/etapes/supprimer.php?id=<?php echo $id; ?>&confirmer=oui" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')">Confirmer la suppression</a>
                <a href="<?php echo SITE_URL; ?>/admin/etapes/liste.php" class="btn btn-primary">Annuler</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>