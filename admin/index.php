<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Protéger la page (accessible uniquement si admin)
requireAdmin();

$page_title = 'Dashboard Admin';

// Récupérer les statistiques
$total_etapes = query("SELECT COUNT(*) as total FROM etapes");
$total_etapes = $total_etapes[0]['total'];

$total_hebergements = query("SELECT COUNT(*) as total FROM hebergements");
$total_hebergements = $total_hebergements[0]['total'];

$total_reservations = query("SELECT COUNT(*) as total FROM reservations");
$total_reservations = $total_reservations[0]['total'];

$total_ca = query("SELECT SUM(prix_total_ttc) as total FROM reservations WHERE statut != 'annulee'");
$total_ca = $total_ca[0]['total'] ?? 0;

$reservations_mois = query("SELECT COUNT(*) as total FROM reservations WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$reservations_mois = $reservations_mois[0]['total'];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <!-- Header -->
        <div class="admin-header">
            <h1>Tableau de bord administrateur</h1>
            <p>Gérez votre site Kayak Loire</p>
        </div>

        <!-- Statistiques globales -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_etapes; ?></div>
                    <div class="stat-label">Étapes</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_hebergements; ?></div>
                    <div class="stat-label">Hébergements</div>
                </div>
            </div>

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
                    <div class="stat-label">Réservations</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo formatPrice($total_ca); ?></div>
                    <div class="stat-label">Chiffre d'affaires</div>
                </div>
            </div>
        </div>

        <!-- Menu de navigation rapide -->
        <div class="admin-menu">
            <h2>Gestion du site</h2>
            <div class="menu-grid">
                <a href="<?php echo SITE_URL; ?>/admin/etapes/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3>Étapes</h3>
                    <p>Gérer les points d'arrêt</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/hebergements/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <h3>Hébergements</h3>
                    <p>Gérer les logements</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/services/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <h3>Services</h3>
                    <p>Gérer les services complémentaires</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/packs/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <h3>Packs</h3>
                    <p>Gérer les parcours prédéfinis</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/promotions/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                    </div>
                    <h3>Promotions</h3>
                    <p>Gérer les codes promo</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/tarifs/gerer-plages.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <h3>Tarifs</h3>
                    <p>Gérer les plages tarifaires</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/reservations/liste.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <h3>Réservations</h3>
                    <p>Gérer les réservations clients</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/statistiques/occupation.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </div>
                    <h3>Statistiques</h3>
                    <p>Graphiques d'occupation</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/messagerie/chat-admin.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3>Messagerie</h3>
                    <p>Répondre aux clients</p>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="menu-card">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>Newsletter</h3>
                    <p>Gérer les abonnés</p>
                </a>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="recent-activity">
            <h2>Activité récente</h2>
            <div class="activity-info">
                <p>Réservations ce mois-ci : <strong><?php echo $reservations_mois; ?></strong></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>