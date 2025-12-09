<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'Connexion';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . '/admin/index.php');
    } else {
        redirect(SITE_URL . '/user/dashboard.php');
    }
}

$email = '';
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    }
    
    // Si pas d'erreurs, vérifier les identifiants
    if (empty($errors)) {
        $user = query("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!empty($user)) {
            $user = $user[0];
            
            // Vérifier le mot de passe
            if (password_verify($password, $user['password'])) {
                // Vérifier si l'email est vérifié
                if ($user['email_verified'] == 0) {
                    $errors[] = "Veuillez vérifier votre adresse email avant de vous connecter.";
                } else {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Si "Se souvenir de moi", créer un cookie (optionnel)
                    if ($remember) {
                        setcookie('user_remember', $user['id'], time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    setSuccessMessage("Bienvenue " . $user['prenom'] . " !");
                    
                    // Rediriger selon le rôle
                    if ($user['role'] === 'admin') {
                        redirect(SITE_URL . '/admin/index.php');
                    } else {
                        redirect(SITE_URL . '/user/dashboard.php');
                    }
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="container">
        <div class="auth-box">
            <h2>Se connecter</h2>
            <p class="auth-subtitle">Accédez à votre espace personnel</p>
            
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div class="alert alert-success">
                    Vous avez été déconnecté avec succès.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group-checkbox">
                    <label>
                        <input type="checkbox" name="remember"> Se souvenir de moi
                    </label>
                    <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php" class="forgot-link">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
            
            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="<?php echo SITE_URL; ?>/auth/register.php">S'inscrire</a></p>
            </div>
            
            <!-- Comptes de test (pour développement) -->
            <div class="test-accounts">
                <h4>Comptes de test :</h4>
                <p><strong>Admin :</strong> admin@kayak-loire.fr / Admin123!</p>
                <p><strong>Client :</strong> client@test.fr / Client123!</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>