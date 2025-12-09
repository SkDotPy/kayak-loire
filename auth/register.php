<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'Inscription';

// Variables pour conserver les valeurs du formulaire
$email = '';
$nom = '';
$prenom = '';
$telephone = '';
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $nom = clean($_POST['nom']);
    $prenom = clean($_POST['prenom']);
    $telephone = clean($_POST['telephone']);
    
    // Validation
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!isValidEmail($email)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (!isValidPassword($password)) {
        $errors[] = "Le mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères.";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        $existing_user = query("SELECT id FROM users WHERE email = ?", [$email]);
        if (!empty($existing_user)) {
            $errors[] = "Cette adresse email est déjà utilisée.";
        }
    }
    
    // Si pas d'erreurs, créer le compte
    if (empty($errors)) {
        // Hasher le mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Générer un token de vérification
        $verification_token = bin2hex(random_bytes(32));
        
        // Insérer l'utilisateur
        $sql = "INSERT INTO users (email, password, nom, prenom, telephone, verification_token, role, email_verified, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'client', 0, NOW())";
        
        $result = execute($sql, [$email, $password_hash, $nom, $prenom, $telephone, $verification_token]);
        
        if ($result) {
            // Envoyer l'email de vérification
            $verification_link = SITE_URL . "/auth/verify-email.php?token=" . $verification_token;
            
            // Stocker le lien pour l'afficher (uniquement pour tests)
            $_SESSION['verification_link'] = $verification_link;
            $_SESSION['verification_email'] = $email;
            
            // Rediriger vers une page qui affiche le lien
            redirect(SITE_URL . '/auth/email-sent.php');
        } else {
            $errors[] = "Une erreur est survenue lors de la création du compte.";
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="container">
        <div class="auth-box">
            <h2>Créer un compte</h2>
            <p class="auth-subtitle">Rejoignez-nous pour réserver votre aventure sur la Loire</p>
            
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" placeholder="06 12 34 56 78">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                        <small>Au moins <?php echo PASSWORD_MIN_LENGTH; ?> caractères</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmer le MDP</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
            </form>
            
            <div class="auth-footer">
                <p>Vous avez déjà un compte ? <a href="<?php echo SITE_URL; ?>/auth/login.php">Se connecter</a></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>