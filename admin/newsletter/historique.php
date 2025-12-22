<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Historique des newsletters';

// Récupérer l'historique avec le nom de l'admin
$historique = query("SELECT ne.*, u.prenom, u.nom 
                     FROM newsletter_envois ne
                     LEFT JOIN users u ON ne.envoye_par = u.id
                     ORDER BY ne.date_envoi DESC");

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>📋 Historique des newsletters</h1>
            <a href="<?php echo SITE_URL; ?>/admin/newsletter/composer.php" class="btn btn-primary">✉️ Envoyer une newsletter</a>
        </div>

        <?php if (empty($historique)): ?>
            <div class="empty-state">
                <p>Aucune newsletter envoyée pour le moment.</p>
                <a href="<?php echo SITE_URL; ?>/admin/newsletter/composer.php" class="btn btn-primary">Envoyer la première newsletter</a>
            </div>
        <?php else: ?>
            <div class="historique-container">
                <?php foreach($historique as $envoi): ?>
                    <div class="historique-item">
                        <div class="historique-header">
                            <div class="historique-date">
                                📅 <?php echo formatDate($envoi['date_envoi']); ?>
                            </div>
                            <div class="historique-stats">
                                <span class="badge badge-info"><?php echo $envoi['nb_destinataires']; ?> destinataires</span>
                            </div>
                        </div>
                        
                        <div class="historique-sujet">
                            <strong><?php echo htmlspecialchars($envoi['sujet']); ?></strong>
                        </div>
                        
                        <div class="historique-contenu">
                            <?php 
                            $apercu = strip_tags($envoi['contenu']);
                            $apercu = substr($apercu, 0, 200);
                            echo nl2br(htmlspecialchars($apercu));
                            if (strlen($envoi['contenu']) > 200) echo '...';
                            ?>
                        </div>
                        
                        <div class="historique-footer">
                            <?php if ($envoi['prenom'] && $envoi['nom']): ?>
                                <small>Envoyée par <?php echo htmlspecialchars($envoi['prenom'] . ' ' . $envoi['nom']); ?></small>
                            <?php else: ?>
                                <small>Envoyée par un administrateur</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="table-info">
                <p><strong><?php echo count($historique); ?></strong> newsletter(s) envoyée(s)</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="btn btn-outline">Voir les abonnés</a>
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<style>
.historique-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.historique-item {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}

.historique-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f3f4f6;
}

.historique-date {
    font-weight: 600;
    color: var(--dark);
}

.historique-sujet {
    font-size: 18px;
    margin-bottom: 12px;
    color: var(--primary-color);
}

.historique-contenu {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 15px;
}

.historique-footer {
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.historique-footer small {
    color: #9ca3af;
}
</style>

<?php include '../../includes/footer.php'; ?>