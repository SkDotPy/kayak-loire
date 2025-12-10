<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Modifier une étape';

$errors = [];

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/etapes/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer l'étape
$etape = query("SELECT * FROM etapes WHERE id = ?", [$id]);

if (empty($etape)) {
    setErrorMessage("Cette étape n'existe pas.");
    redirect(SITE_URL . '/admin/etapes/liste.php');
}

$etape = $etape[0];

// Initialiser les variables avec les valeurs actuelles
$nom = $etape['nom'];
$description = $etape['description'];
$ville = $etape['ville'];
$latitude = $etape['latitude'];
$longitude = $etape['longitude'];
$distance_precedente = $etape['distance_precedente'];
$ordre = $etape['ordre'];
$actif = $etape['actif'];
$image_actuelle = $etape['image'];

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
    
    // Vérifier si l'ordre existe déjà (sauf pour cette étape)
    if (empty($errors)) {
        $ordre_existe = query("SELECT id FROM etapes WHERE ordre = ? AND id != ?", [$ordre, $id]);
        if (!empty($ordre_existe)) {
            $errors[] = "Cet ordre est déjà utilisé par une autre étape.";
        }
    }
    
    // Upload de la nouvelle image (optionnel)
    $nouvelle_image = $image_actuelle;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image'], UPLOAD_PATH . '/etapes');
        if ($uploaded) {
            // Supprimer l'ancienne image si elle existe
            if ($image_actuelle && file_exists(UPLOAD_PATH . '/etapes/' . $image_actuelle)) {
                unlink(UPLOAD_PATH . '/etapes/' . $image_actuelle);
            }
            $nouvelle_image = $uploaded;
        } else {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, mettre à jour en BDD
    if (empty($errors)) {
        $sql = "UPDATE etapes SET 
                nom = ?, 
                description = ?, 
                ville = ?, 
                latitude = ?, 
                longitude = ?, 
                distance_precedente = ?, 
                ordre = ?, 
                image = ?, 
                actif = ? 
                WHERE id = ?";
        
        $result = execute($sql, [
            $nom,
            $description,
            $ville,
            $latitude ?: null,
            $longitude ?: null,
            $distance_precedente,
            $ordre,
            $nouvelle_image,
            $actif,
            $id
        ]);
        
        if ($result) {
            setSuccessMessage("L'étape a été modifiée avec succès !");
            redirect(SITE_URL . '/admin/etapes/liste.php');
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
            <h1>Modifier l'étape : <?php echo htmlspecialchars($etape['nom']); ?></h1>
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
                        <input type="number" id="ordre" name="ordre" value="<?php echo $ordre; ?>" min="1" required>
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
                    <label for="image">Changer l'image</label>
                    <?php if ($image_actuelle): ?>
                        <div class="current-image">
                            <p><strong>Image actuelle :</strong></p>
                            <img src="<?php echo UPLOAD_URL; ?>/etapes/<?php echo $image_actuelle; ?>" alt="Image actuelle" style="max-width: 200px; margin: 10px 0;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Laissez vide pour conserver l'image actuelle</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Étape active (visible sur le site)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="<?php echo SITE_URL; ?>/admin/etapes/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>