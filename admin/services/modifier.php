<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Modifier un service';

$errors = [];

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/services/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer le service
$service = query("SELECT * FROM services_complementaires WHERE id = ?", [$id]);

if (empty($service)) {
    setErrorMessage("Ce service n'existe pas.");
    redirect(SITE_URL . '/admin/services/liste.php');
}

$service = $service[0];

// Initialiser les variables avec les valeurs actuelles
$nom = $service['nom'];
$description = $service['description'];
$prix = $service['prix'];
$type = $service['type'];
$actif = $service['actif'];
$image_actuelle = $service['image'];

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
    
    // Upload de la nouvelle image (optionnel)
    $nouvelle_image = $image_actuelle;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image'], UPLOAD_PATH . '/services');
        if ($uploaded) {
            // Supprimer l'ancienne image si elle existe
            if ($image_actuelle && file_exists(UPLOAD_PATH . '/services/' . $image_actuelle)) {
                unlink(UPLOAD_PATH . '/services/' . $image_actuelle);
            }
            $nouvelle_image = $uploaded;
        } else {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, mettre à jour en BDD
    if (empty($errors)) {
        $sql = "UPDATE services_complementaires SET 
                nom = ?, 
                description = ?, 
                prix = ?, 
                type = ?, 
                image = ?, 
                actif = ? 
                WHERE id = ?";
        
        $result = execute($sql, [$nom, $description, $prix, $type, $nouvelle_image, $actif, $id]);
        
        if ($result) {
            setSuccessMessage("Le service a été modifié avec succès !");
            redirect(SITE_URL . '/admin/services/liste.php');
        } else {
            $errors[] = "Une erreur est survenue lors de la modification.";
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Modifier : <?php echo htmlspecialchars($service['nom']); ?></h1>
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
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="materiel" <?php echo ($type === 'materiel') ? 'selected' : ''; ?>>Matériel</option>
                            <option value="prestation" <?php echo ($type === 'prestation') ? 'selected' : ''; ?>>Prestation</option>
                            <option value="nourriture" <?php echo ($type === 'nourriture') ? 'selected' : ''; ?>>Nourriture</option>
                            <option value="autre" <?php echo ($type === 'autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="prix">Prix (€) *</label>
                    <input type="number" id="prix" name="prix" value="<?php echo $prix; ?>" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="image">Changer l'image</label>
                    <?php if ($image_actuelle): ?>
                        <div class="current-image">
                            <p><strong>Image actuelle :</strong></p>
                            <img src="<?php echo UPLOAD_URL; ?>/services/<?php echo $image_actuelle; ?>" alt="Image actuelle" style="max-width: 200px; border-radius: 8px; margin: 10px 0;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Laissez vide pour conserver l'image actuelle</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Service actif (visible sur le site)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="<?php echo SITE_URL; ?>/admin/services/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>