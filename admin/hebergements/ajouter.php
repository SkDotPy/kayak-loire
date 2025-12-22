<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Ajouter un hébergement';

$errors = [];
$etape_id = '';
$nom = '';
$type = 'hotel';
$description = '';
$adresse = '';
$telephone = '';
$email = '';
$capacite = '';
$prix_par_nuit = '';
$actif = 1;
$equipements = [];

// Récupérer toutes les étapes pour le select
$etapes = query("SELECT * FROM etapes WHERE actif = 1 ORDER BY ordre ASC");

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etape_id = (int)$_POST['etape_id'];
    $nom = clean($_POST['nom']);
    $type = clean($_POST['type']);
    $description = clean($_POST['description']);
    $adresse = clean($_POST['adresse']);
    $telephone = clean($_POST['telephone']);
    $email = clean($_POST['email']);
    $capacite = (int)$_POST['capacite'];
    $prix_par_nuit = (float)$_POST['prix_par_nuit'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    $equipements = isset($_POST['equipements']) ? $_POST['equipements'] : [];
    
    // Validation
    if (empty($etape_id)) {
        $errors[] = "Vous devez sélectionner une étape.";
    }
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($adresse)) {
        $errors[] = "L'adresse est obligatoire.";
    }
    
    if (empty($capacite) || $capacite < 1) {
        $errors[] = "La capacité doit être supérieure à 0.";
    }
    
    if (empty($prix_par_nuit) || $prix_par_nuit < 0) {
        $errors[] = "Le prix doit être supérieur ou égal à 0.";
    }
    
    if (!empty($email) && !isValidEmail($email)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    // Upload de l'image (optionnel)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image'], UPLOAD_PATH . '/hebergements');
        if (!$image) {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        // Convertir les équipements en JSON
        $equipements_json = json_encode($equipements);
        
        $sql = "INSERT INTO hebergements (etape_id, nom, type, description, adresse, telephone, email, capacite, prix_par_nuit, image, equipements, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $result = execute($sql, [
            $etape_id,
            $nom,
            $type,
            $description,
            $adresse,
            $telephone,
            $email,
            $capacite,
            $prix_par_nuit,
            $image,
            $equipements_json,
            $actif
        ]);
        
        if ($result) {
            setSuccessMessage("L'hébergement a été ajouté avec succès !");
            redirect(SITE_URL . '/admin/hebergements/liste.php');
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
            <h1>Ajouter un hébergement</h1>
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
                
                <h3>Informations générales</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="etape_id">Étape *</label>
                        <select id="etape_id" name="etape_id" required>
                            <option value="">-- Sélectionner une étape --</option>
                            <?php foreach($etapes as $etape): ?>
                                <option value="<?php echo $etape['id']; ?>" <?php echo ($etape_id == $etape['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($etape['nom']); ?> (<?php echo htmlspecialchars($etape['ville']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="hotel" <?php echo ($type === 'hotel') ? 'selected' : ''; ?>>Hôtel</option>
                            <option value="camping" <?php echo ($type === 'camping') ? 'selected' : ''; ?>>Camping</option>
                            <option value="gite" <?php echo ($type === 'gite') ? 'selected' : ''; ?>>Gîte</option>
                            <option value="chambre_hote" <?php echo ($type === 'chambre_hote') ? 'selected' : ''; ?>>Chambre d'hôtes</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nom">Nom de l'hébergement *</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <h3>Coordonnées</h3>

                <div class="form-group">
                    <label for="adresse">Adresse complète *</label>
                    <textarea id="adresse" name="adresse" rows="2" required><?php echo htmlspecialchars($adresse); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" placeholder="02 38 00 00 00">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="contact@hebergement.fr">
                    </div>
                </div>

                <h3>Tarification et capacité</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacite">Capacité (nombre de personnes) *</label>
                        <input type="number" id="capacite" name="capacite" value="<?php echo $capacite; ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="prix_par_nuit">Prix par nuit (€) *</label>
                        <input type="number" id="prix_par_nuit" name="prix_par_nuit" value="<?php echo $prix_par_nuit; ?>" min="0" step="0.01" required>
                    </div>
                </div>

                <h3>Équipements</h3>

                <div class="form-group">
                    <label>Sélectionnez les équipements disponibles :</label>
                    <div class="checkboxes-grid">
                        <label><input type="checkbox" name="equipements[]" value="wifi" <?php echo in_array('wifi', $equipements) ? 'checked' : ''; ?>> WiFi</label>
                        <label><input type="checkbox" name="equipements[]" value="parking" <?php echo in_array('parking', $equipements) ? 'checked' : ''; ?>> Parking</label>
                        <label><input type="checkbox" name="equipements[]" value="petit_dejeuner" <?php echo in_array('petit_dejeuner', $equipements) ? 'checked' : ''; ?>> Petit-déjeuner</label>
                        <label><input type="checkbox" name="equipements[]" value="climatisation" <?php echo in_array('climatisation', $equipements) ? 'checked' : ''; ?>> Climatisation</label>
                        <label><input type="checkbox" name="equipements[]" value="cuisine" <?php echo in_array('cuisine', $equipements) ? 'checked' : ''; ?>> Cuisine équipée</label>
                        <label><input type="checkbox" name="equipements[]" value="terrasse" <?php echo in_array('terrasse', $equipements) ? 'checked' : ''; ?>> Terrasse</label>
                        <label><input type="checkbox" name="equipements[]" value="jardin" <?php echo in_array('jardin', $equipements) ? 'checked' : ''; ?>> Jardin</label>
                        <label><input type="checkbox" name="equipements[]" value="piscine" <?php echo in_array('piscine', $equipements) ? 'checked' : ''; ?>> Piscine</label>
                        <label><input type="checkbox" name="equipements[]" value="spa" <?php echo in_array('spa', $equipements) ? 'checked' : ''; ?>> Spa</label>
                        <label><input type="checkbox" name="equipements[]" value="restaurant" <?php echo in_array('restaurant', $equipements) ? 'checked' : ''; ?>> Restaurant</label>
                        <label><input type="checkbox" name="equipements[]" value="lave_linge" <?php echo in_array('lave_linge', $equipements) ? 'checked' : ''; ?>> Lave-linge</label>
                        <label><input type="checkbox" name="equipements[]" value="sanitaires" <?php echo in_array('sanitaires', $equipements) ? 'checked' : ''; ?>> Sanitaires modernes</label>
                    </div>
                </div>

                <h3>Média</h3>

                <div class="form-group">
                    <label for="image">Photo principale</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Formats acceptés : JPG, PNG, GIF, WEBP - Max 5 Mo</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Hébergement actif (visible sur le site)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Ajouter l'hébergement</button>
                    <a href="<?php echo SITE_URL; ?>/admin/hebergements/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>