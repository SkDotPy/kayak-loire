<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Composer une newsletter';

$errors = [];
$success = '';
$sujet = '';
$contenu = '';

// Compter les destinataires
$nb_destinataires = count(query("SELECT id FROM newsletter_abonnes WHERE actif = 1"));

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = clean($_POST['sujet']);
    $contenu = $_POST['contenu']; // On garde le HTML
    
    // Validation
    if (empty($sujet)) {
        $errors[] = "Le sujet est obligatoire.";
    }
    
    if (empty($contenu)) {
        $errors[] = "Le contenu est obligatoire.";
    }
    
    if ($nb_destinataires == 0) {
        $errors[] = "Aucun abonné actif pour l'envoi.";
    }
    
    // Si pas d'erreurs, envoyer
    if (empty($errors)) {
        // Récupérer tous les abonnés actifs
        $destinataires = query("SELECT email, prenom, nom FROM newsletter_abonnes WHERE actif = 1");
        
        $nb_envoyes = 0;
        
        // Envoyer l'email à chaque abonné
        foreach ($destinataires as $dest) {
            $email = $dest['email'];
            $nom_complet = trim(($dest['prenom'] ?? '') . ' ' . ($dest['nom'] ?? ''));
            
            // Personnaliser le contenu
            $contenu_personnalise = str_replace('[NOM]', $nom_complet, $contenu);
            $contenu_personnalise = str_replace('[EMAIL]', $email, $contenu_personnalise);
            
            // Headers pour HTML
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Kayak Loire <noreply@kayak-loire.fr>' . "\r\n";
            
            // Envoyer l'email
            if (mail($email, $sujet, $contenu_personnalise, $headers)) {
                $nb_envoyes++;
            }
        }
        
        // Enregistrer l'envoi dans l'historique
        $sql = "INSERT INTO newsletter_envois (sujet, contenu, nb_destinataires, date_envoi, envoye_par) 
                VALUES (?, ?, ?, NOW(), ?)";
        
        $admin_id = $_SESSION['user_id'] ?? null;
        execute($sql, [$sujet, $contenu, $nb_envoyes, $admin_id]);
        
        $success = "Newsletter envoyée avec succès à {$nb_envoyes} abonné(s) !";
        
        // Réinitialiser le formulaire
        $sujet = '';
        $contenu = '';
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>✉️ Composer une newsletter</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div style="margin-top: 15px;">
                    <a href="<?php echo SITE_URL; ?>/admin/newsletter/historique.php" class="btn btn-outline">Voir l'historique</a>
                    <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="btn btn-outline">Voir les abonnés</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- INFO BOX -->
        <div class="info-box">
            <h3>📊 Destinataires</h3>
            <p><strong><?php echo $nb_destinataires; ?></strong> abonné(s) actif(s) recevront cette newsletter.</p>
            
            <h3 style="margin-top: 20px;">💡 Variables disponibles</h3>
            <ul>
                <li><code>[NOM]</code> - Remplacé par le nom complet de l'abonné</li>
                <li><code>[EMAIL]</code> - Remplacé par l'email de l'abonné</li>
            </ul>
        </div>

        <?php if ($nb_destinataires > 0): ?>
            <div class="form-container">
                <form method="POST" class="admin-form">
                    
                    <div class="form-group">
                        <label for="sujet">Sujet de l'email *</label>
                        <input type="text" id="sujet" name="sujet" value="<?php echo htmlspecialchars($sujet); ?>" required placeholder="Ex: Offre spéciale été 2025 - 20% de réduction">
                    </div>

                    <div class="form-group">
                        <label for="contenu">Contenu de l'email *</label>
                        <textarea id="contenu" name="contenu" rows="15" required placeholder="Bonjour [NOM],

Nous sommes ravis de vous annoncer...

Cordialement,
L'équipe Kayak Loire"><?php echo htmlspecialchars($contenu); ?></textarea>
                        <small>Vous pouvez utiliser du HTML simple (balises p, strong, a, etc.)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir envoyer cette newsletter à <?php echo $nb_destinataires; ?> abonné(s) ?')">
                            📤 Envoyer la newsletter
                        </button>
                        <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="btn btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <p><strong>Aucun abonné actif.</strong> Vous ne pouvez pas envoyer de newsletter.</p>
                <a href="<?php echo SITE_URL; ?>/admin/newsletter/liste-abonnes.php" class="btn btn-outline">Voir les abonnés</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.info-box {
    background: #dbeafe;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    border-left: 4px solid #3b82f6;
}

.info-box h3 {
    margin: 0 0 10px 0;
    color: #1e40af;
}

.info-box ul {
    margin: 10px 0;
    padding-left: 20px;
}

.info-box code {
    background: rgba(59, 130, 246, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
    color: #1e40af;
}
</style>

<?php include '../../includes/footer.php'; ?>