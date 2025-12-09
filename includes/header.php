<?php
// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'URL actuelle pour mettre en surbrillance le menu actif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Kayak Loire</title>
    <meta name="description" content="Réservez votre aventure en kayak sur la Loire. Parcours personnalisés et packs tout compris.">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>/index.php">
                        <h1>Kayak Loire</h1>
                    </a>
                </div>

                <!-- Menu de navigation -->
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/etapes.php" class="<?php echo ($current_page == 'etapes.php') ? 'active' : ''; ?>">Étapes</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/packs.php" class="<?php echo ($current_page == 'packs.php') ? 'active' : ''; ?>">Nos Packs</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/composer-parcours.php" class="<?php echo ($current_page == 'composer-parcours.php') ? 'active' : ''; ?>">Composer mon parcours</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/services.php" class="<?php echo ($current_page == 'services.php') ? 'active' : ''; ?>">Services</a></li>
                    </ul>
                </nav>

                <!-- Menu utilisateur -->
                <div class="user-menu">
                    <?php if (isLoggedIn()): ?>
                        <!-- Si connecté -->
                        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="btn-user">
                            Mon compte
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn-admin">
                                Admin
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn-logout">
                            Déconnexion
                        </a>
                    <?php else: ?>
                        <!-- Si non connecté -->
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-login">
                            Connexion
                        </a>
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn-register">
                            Inscription
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Bouton menu mobile (hamburger) -->
                <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Début du contenu principal -->
    <main class="main-content">