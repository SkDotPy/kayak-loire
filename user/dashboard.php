<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Protéger la page (accessible uniquement si connecté)
requireLogin();

$page_title = 'Mon tableau de bord';

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM users WHERE id = ?", [$user_id]);

if (empty($user)) {
    redirect(SITE_URL . '/auth/logout.php');
}

$user = $user[0];

// Récupérer les statistiques de l'utilisateur
$total_reservations = query("SELECT COUNT(*) as total FROM reservations WHERE user_id = ?", [$user_id]);
$total_reservations = $total_reservations[0]['total'];

$reservations_en_cours = query("SELECT COUNT(*) as total FROM reservations WHERE user_id = ? AND statut IN ('en_attente', 'confirmee')", [$user_id]);
$reservations_en_cours = $reservations_en_cours[0]['total'];

// Récupérer les dernières réservations
$dernieres_reservations = query("SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC LIMIT 3", [$user_id]);

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container">
        <div class="dashboard-header">
            <h1>Bienvenue <?php echo htmlspecialchars($user['prenom']); ?> !</h1>
            <p>Gérez vos réservations et votre profil</p>
        </div>

        <!-- Statistiques rapides -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_reservations; ?></div>
                    <div class="stat-label">Réservation(s) au total</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $reservations_en_cours; ?></div>
                    <div class="stat-label">Réservation(s) en cours</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number">Client</div>
                    <div class="stat-label">Votre statut</div>
                </div>
            </div>
        </div>

        <!-- Menu rapide -->
        <div class="quick-actions">
            <h2>Actions rapides</h2>
            <div class="actions-grid">
                <a href="<?php echo SITE_URL; ?>/pages/composer-parcours.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </div>
                    <h3>Nouvelle réservation</h3>
                    <p>Composer votre parcours sur la Loire</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/user/mes-reservations.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>Mes réservations</h3>
                    <p>Consulter l'historique de vos réservations</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/user/profil.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h3>Mon profil</h3>
                    <p>Gérer vos informations personnelles</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/user/mes-messages.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3>Messages</h3>
                    <p>Discuter avec le service commercial</p>
                </a>
            </div>
        </div>

        <!-- Dernières réservations -->
        <div class="recent-bookings">
            <div class="section-header">
                <h2>Mes dernières réservations</h2>
                <a href="<?php echo SITE_URL; ?>/user/mes-reservations.php" class="btn btn-outline">Voir tout</a>
            </div>

            <?php if (empty($dernieres_reservations)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <h3>Aucune réservation</h3>
                    <p>Vous n'avez pas encore effectué de réservation.</p>
                    <a href="<?php echo SITE_URL; ?>/pages/composer-parcours.php" class="btn btn-primary">Réserver maintenant</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach($dernieres_reservations as $reservation): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <span class="booking-number"><?php echo htmlspecialchars($reservation['numero_reservation']); ?></span>
                                <span class="booking-status status-<?php echo $reservation['statut']; ?>">
                                    <?php 
                                    $statuts = [
                                        'en_attente' => 'En attente',
                                        'confirmee' => 'Confirmée',
                                        'annulee' => 'Annulée',
                                        'terminee' => 'Terminée'
                                    ];
                                    echo $statuts[$reservation['statut']];
                                    ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <p><strong>Dates :</strong> <?php echo formatDate($reservation['date_debut']); ?> au <?php echo formatDate($reservation['date_fin']); ?></p>
                                <p><strong>Personnes :</strong> <?php echo $reservation['nb_personnes']; ?></p>
                                <p><strong>Total :</strong> <?php echo formatPrice($reservation['prix_total_ttc']); ?></p>
                            </div>
                            <div class="booking-actions">
                                <a href="<?php echo SITE_URL; ?>/user/mes-reservations.php?id=<?php echo $reservation['id']; ?>" class="btn btn-outline">Voir les détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>