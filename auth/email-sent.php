<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'Email envoyé';

// Vérifier qu'il y a bien un lien en session
if (!isset($_SESSION['verification_link'])) {
    redirect(SITE_URL . '/auth/register.php');
}

$verification_link = $_SESSION['verification_link'];
$email = $_SESSION['verification_email'] ?? '';

// Nettoyer la session
unset($_SESSION['verification_link']);
unset($_SESSION['verification_email']);

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="container">
        <div class="auth-box text-center">
            <h2>Vérifiez votre email</h2>
            
            <div class="alert alert-success">
                <p>Votre compte a été créé avec succès !</p>
                <p>Un email de vérification a été envoyé à <strong><?php echo htmlspecialchars($email); ?></strong></p>
            </div>
            
            <div class="verification-info">
                <p>Cliquez sur le lien dans l'email pour activer votre compte.</p>
                
                <!-- Pour les tests en développement, afficher le lien directement -->
                <div class="test-accounts" style="margin-top: 30px;">
                    <h4>Mode développement - Lien de vérification :</h4>
                    <p><a href="<?php echo $verification_link; ?>" class="btn btn-primary">Vérifier mon email maintenant</a></p>
                    <small style="display: block; margin-top: 10px; color: var(--gray);">
                        Dans un environnement de production, ce lien serait envoyé par email.
                    </small>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/auth/login.php">Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>