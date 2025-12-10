<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Ajouter une étape';

$errors = [];
$nom = '';
$description = '';
$ville = '';
$latitude = '';
$longitude = '';
$distance_precedente = 0;
$ordre = '';
$actif = 1;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = clean($_POST['nom']);
    $description = clean($_POST['description']);
    $ville = clean($_POST['ville']);
    $latitude = clean($_POST['latitude']);
    $longitude = clean($_POST['longitude']);
    $distance_precedente = (int)$_POST['distance_precedente'];
    $ordre = (int)$_POST['ordre'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($ville)) {
        $errors[] = "La ville est obligatoire.";
    }
    
    if (empty($ordre) || $ordre < 1) {
        $errors[] = "L'ordre doit être un nombre supérieur à 0.";
    }
    
    // Vérifier si l'ordre existe déjà
    if (empty($errors)) {
        $ordre_existe = query("SELECT id FROM etapes WHERE ordre = ?", [$ordre]);
        if (!empty($ordre_existe)) {
            $errors[] = "Cet ordre est déjà utilisé par une autre étape.";
        }
    }
    
    // Upload de l'image (optionnel)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image'], UPLOAD_PATH . '/etapes');
        if (!$image) {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        $sql = "INSERT INTO etapes (nom, description, ville, latitude, longitude, distance_precedente, ordre, image, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $result = execute($sql, [
            $nom,
            $description,
            $ville,
            $latitude ?: null,
            $longitude ?: null,
            $distance_precedente,
            $ordre,
            $image,
            $actif
        ]);
        
        if ($result) {
            setSuccessMessage("L'étape a été ajoutée avec succès !");
            redirect(SITE_URL . '/admin/etapes/liste.php');
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
            <h1>Ajouter une étape</h1>
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
                <div class="form-group">
                    <label for="nom">Nom de l'étape *</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($ville); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ordre">Ordre *</label>
                        <input type="number" id="ordre" name="ordre" value="<?php echo htmlspecialchars($ordre); ?>" min="1" required>
                        <small>Position de l'étape dans le parcours (1, 2, 3...)</small>
                    </div>

                    <div class="form-group">
                        <label for="distance_precedente">Distance depuis l'étape précédente (km)</label>
                        <input type="number" id="distance_precedente" name="distance_precedente" value="<?php echo $distance_precedente; ?>" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude">Latitude (optionnel)</label>
                        <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($latitude); ?>" placeholder="Ex: 47.0052">
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude (optionnel)</label>
                        <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($longitude); ?>" placeholder="Ex: 3.1231">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Image de l'étape</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Formats acceptés : JPG, PNG, GIF, WEBP - Max 5 Mo</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Étape active (visible sur le site)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Ajouter l'étape</button>
                    <a href="<?php echo SITE_URL; ?>/admin/etapes/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>