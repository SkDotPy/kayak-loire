<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Ajouter un service';

$errors = [];
$nom = '';
$description = '';
$prix = '';
$type = 'materiel';
$actif = 1;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = clean($_POST['nom']);
    $description = clean($_POST['description']);
    $prix = (float)$_POST['prix'];
    $type = clean($_POST['type']);
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($prix) || $prix < 0) {
        $errors[] = "Le prix doit être supérieur ou égal à 0.";
    }
    
    if (!in_array($type, ['materiel', 'prestation', 'nourriture', 'autre'])) {
        $errors[] = "Le type sélectionné n'est pas valide.";
    }
    
    // Upload de l'image (optionnel)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image'], UPLOAD_PATH . '/services');
        if (!$image) {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        $sql = "INSERT INTO services_complementaires (nom, description, prix, type, image, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $result = execute($sql, [$nom, $description, $prix, $type, $image, $actif]);
        
        if ($result) {
            setSuccessMessage("Le service a été ajouté avec succès !");
            redirect(SITE_URL . '/admin/services/liste.php');
        } else {
            $errors[] = "Une erreur est survenue lors de l'ajout.";
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Ajouter un service complémentaire</h1>
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

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom du service *</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required placeholder="Ex: Location de vélo">
                    </div>

                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="materiel" <?php echo ($type === 'materiel') ? 'selected' : ''; ?>>Matériel</option>
                            <option value="prestation" <?php echo ($type === 'prestation') ? 'selected' : ''; ?>>Prestation</option>
                            <option value="nourriture" <?php echo ($type === 'nourriture') ? 'selected' : ''; ?>>Nourriture</option>
                            <option value="autre" <?php echo ($type === 'autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                        <small>Matériel : vélo, kayak, etc. | Prestation : guide, transfert, etc. | Nourriture : repas, panier, etc.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Décrivez le service..."><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="prix">Prix (€) *</label>
                    <input type="number" id="prix" name="prix" value="<?php echo $prix; ?>" min="0" step="0.01" required placeholder="15.00">
                    <small>Prix par unité (jour, personne, etc.)</small>
                </div>

                <div class="form-group">
                    <label for="image">Image du service</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Formats acceptés : JPG, PNG, GIF, WEBP - Max 5 Mo</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Service actif (visible sur le site)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Ajouter le service</button>
                    <a href="<?php echo SITE_URL; ?>/admin/services/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>