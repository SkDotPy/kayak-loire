<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'Vérification email';

$message = '';
$success = false;

// Vérifier si un token est fourni
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = clean($_GET['token']);
    
    // Chercher l'utilisateur avec ce token
    $user = query("SELECT id, email, email_verified FROM users WHERE verification_token = ?", [$token]);
    
    if (!empty($user)) {
        $user = $user[0];
        
        // Vérifier si l'email n'est pas déjà vérifié
        if ($user['email_verified'] == 0) {
            // Activer le compte
            $result = execute("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?", [$user['id']]);
            
            if ($result) {
                $message = "Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.";
                $success = true;
            } else {
                $message = "Une erreur est survenue lors de la vérification de votre email.";
            }
        } else {
            $message = "Votre email a déjà été vérifié. Vous pouvez vous connecter.";
            $success = true;
        }
    } else {
        $message = "Le lien de vérification est invalide ou a expiré.";
    }
} else {
    $message = "Aucun token de vérification fourni.";
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="container">
        <div class="auth-box text-center">
            <h2>Vérification de votre email</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-primary">Se connecter</a>
            <?php else: ?>
                <div class="alert alert-error">
                    <?php echo $message; ?>
                </div>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary">Créer un nouveau compte</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>