<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/promotions/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer la promotion
$promo = query("SELECT * FROM promotions WHERE id = ?", [$id]);

if (empty($promo)) {
    setErrorMessage("Ce code promo n'existe pas.");
    redirect(SITE_URL . '/admin/promotions/liste.php');
}

$promo = $promo[0];

// Si confirmation de suppression
if (isset($_GET['confirmer']) && $_GET['confirmer'] === 'oui') {
    $result = execute("DELETE FROM promotions WHERE id = ?", [$id]);
    
    if ($result) {
        setSuccessMessage("Le code promo a été supprimé avec succès.");
    } else {
        setErrorMessage("Une erreur est survenue lors de la suppression.");
    }
    
    redirect(SITE_URL . '/admin/promotions/liste.php');
}

$page_title = 'Supprimer un code promo';
include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Supprimer un code promo</h1>
        </div>

        <div class="confirmation-box">
            <div class="alert alert-warning">
                <h3>⚠️ Attention !</h3>
                <p>Vous êtes sur le point de supprimer le code promo :</p>
                <p style="font-family: monospace; font-size: 24px; color: var(--primary-color); margin: 20px 0;">
                    <strong><?php echo htmlspecialchars($promo['code']); ?></strong>
                </p>
            </div>

            <div class="etape-info">
                <h4>📋 Informations du code promo :</h4>
                <ul>
                    <li><strong>Description :</strong> <?php echo htmlspecialchars($promo['description']); ?></li>
                    <li><strong>Type :</strong> 
                        <?php echo $promo['type'] === 'pourcentage' ? 'Pourcentage' : 'Montant fixe'; ?>
                    </li>
                    <li><strong>Réduction :</strong> 
                        <?php 
                        if ($promo['type'] === 'pourcentage') {
                            echo $promo['valeur'] . '%';
                        } else {
                            echo formatPrice($promo['valeur']);
                        }
                        ?>
                    </li>
                    <li><strong>Période :</strong> 
                        <?php if ($promo['date_debut'] && $promo['date_fin']): ?>
                            Du <?php echo formatDate($promo['date_debut']); ?> au <?php echo formatDate($promo['date_fin']); ?>
                        <?php else: ?>
                            Aucune limitation
                        <?php endif; ?>
                    </li>
                    <li><strong>Utilisations :</strong> 
                        <?php echo $promo['utilisation_count']; ?>
                        <?php if ($promo['utilisation_max']): ?>
                            / <?php echo $promo['utilisation_max']; ?>
                        <?php endif; ?>
                    </li>
                    <li><strong>Statut :</strong> <?php echo $promo['actif'] ? 'Actif' : 'Inactif'; ?></li>
                </ul>
            </div>

            <?php if ($promo['utilisation_count'] > 0): ?>
                <div class="alert alert-error">
                    <h4>⚠️ Ce code a déjà été utilisé</h4>
                    <p>Ce code promo a été utilisé <strong><?php echo $promo['utilisation_count']; ?> fois</strong>. 
                    La suppression n'affectera pas les réservations existantes qui l'ont utilisé.</p>
                </div>
            <?php endif; ?>

            <div class="form-actions" style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/admin/promotions/supprimer.php?id=<?php echo $id; ?>&confirmer=oui" class="btn btn-danger" onclick="return confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')">Confirmer la suppression</a>
                <a href="<?php echo SITE_URL; ?>/admin/promotions/liste.php" class="btn btn-primary">Annuler</a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>