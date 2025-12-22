<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Modifier un pack';

$errors = [];

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/packs/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer le pack
$pack = query("SELECT * FROM packs WHERE id = ?", [$id]);

if (empty($pack)) {
    setErrorMessage("Ce pack n'existe pas.");
    redirect(SITE_URL . '/admin/packs/liste.php');
}

$pack = $pack[0];

// Récupérer la composition actuelle du pack
$composition = query("SELECT * FROM pack_etapes WHERE pack_id = ? ORDER BY jour ASC", [$id]);

// Créer un tableau associatif pour faciliter l'accès
$composition_par_jour = [];
foreach ($composition as $comp) {
    $composition_par_jour[$comp['jour']] = [
        'etape_id' => $comp['etape_id'],
        'hebergement_id' => $comp['hebergement_id']
    ];
}

// Récupérer toutes les étapes et hébergements
$etapes = query("SELECT * FROM etapes WHERE actif = 1 ORDER BY ordre ASC");
$hebergements = query("SELECT h.id, h.nom, h.type, e.nom as etape_nom 
                       FROM hebergements h 
                       INNER JOIN etapes e ON h.etape_id = e.id 
                       WHERE h.actif = 1 
                       ORDER BY e.ordre ASC, h.nom ASC");

// Initialiser les variables
$nom = $pack['nom'];
$description = $pack['description'];
$duree_jours = $pack['duree_jours'];
$prix_base = $pack['prix_base'];
$difficulte = $pack['difficulte'];
$actif = $pack['actif'];
$image_actuelle = $pack['image'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = clean($_POST['nom']);
    $description = clean($_POST['description']);
    $duree_jours = (int)$_POST['duree_jours'];
    $prix_base = (float)$_POST['prix_base'];
    $difficulte = clean($_POST['difficulte']);
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if ($duree_jours < 1 || $duree_jours > 30) {
        $errors[] = "La durée doit être entre 1 et 30 jours.";
    }
    
    if (empty($prix_base) || $prix_base < 0) {
        $errors[] = "Le prix doit être supérieur ou égal à 0.";
    }
    
    if (!in_array($difficulte, ['facile', 'moyen', 'difficile'])) {
        $errors[] = "La difficulté sélectionnée n'est pas valide.";
    }
    
    // Vérifier la composition
    for ($jour = 1; $jour <= $duree_jours; $jour++) {
        if (empty($_POST['etape_jour_' . $jour])) {
            $errors[] = "Vous devez sélectionner une étape pour le jour " . $jour . ".";
            break;
        }
    }
    
    // Upload de la nouvelle image (optionnel)
    $nouvelle_image = $image_actuelle;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image'], UPLOAD_PATH . '/packs');
        if ($uploaded) {
            if ($image_actuelle && file_exists(UPLOAD_PATH . '/packs/' . $image_actuelle)) {
                unlink(UPLOAD_PATH . '/packs/' . $image_actuelle);
            }
            $nouvelle_image = $uploaded;
        } else {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        // Mettre à jour le pack
        $sql = "UPDATE packs SET 
                nom = ?, 
                description = ?, 
                duree_jours = ?, 
                prix_base = ?, 
                difficulte = ?, 
                image = ?, 
                actif = ? 
                WHERE id = ?";
        
        $result = execute($sql, [$nom, $description, $duree_jours, $prix_base, $difficulte, $nouvelle_image, $actif, $id]);
        
        if ($result) {
            // Supprimer l'ancienne composition
            execute("DELETE FROM pack_etapes WHERE pack_id = ?", [$id]);
            
            // Réinsérer la nouvelle composition
            $composition_ok = true;
            for ($jour = 1; $jour <= $duree_jours; $jour++) {
                $etape_id = (int)$_POST['etape_jour_' . $jour];
                $hebergement_id = !empty($_POST['hebergement_jour_' . $jour]) ? (int)$_POST['hebergement_jour_' . $jour] : null;
                
                $sql_etape = "INSERT INTO pack_etapes (pack_id, etape_id, jour, hebergement_id) 
                              VALUES (?, ?, ?, ?)";
                
                $result_etape = execute($sql_etape, [$id, $etape_id, $jour, $hebergement_id]);
                
                if (!$result_etape) {
                    $composition_ok = false;
                    break;
                }
            }
            
            if ($composition_ok) {
                setSuccessMessage("Le pack a été modifié avec succès !");
                redirect(SITE_URL . '/admin/packs/liste.php');
            } else {
                $errors[] = "Erreur lors de la mise à jour de la composition du pack.";
            }
        } else {
            $errors[] = "Une erreur est survenue lors de la modification du pack.";
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Modifier : <?php echo htmlspecialchars($pack['nom']); ?></h1>
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
                
                <div class="form-group">
                    <label for="nom">Nom du pack *</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duree_jours">Durée (jours) *</label>
                        <input type="number" id="duree_jours" name="duree_jours" value="<?php echo $duree_jours; ?>" min="1" max="30" required>
                        <small>Modifier la durée supprimera la composition actuelle</small>
                    </div>

                    <div class="form-group">
                        <label for="prix_base">Prix de base (€) *</label>
                        <input type="number" id="prix_base" name="prix_base" value="<?php echo $prix_base; ?>" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="difficulte">Difficulté *</label>
                        <select id="difficulte" name="difficulte" required>
                            <option value="facile" <?php echo ($difficulte === 'facile') ? 'selected' : ''; ?>>Facile</option>
                            <option value="moyen" <?php echo ($difficulte === 'moyen') ? 'selected' : ''; ?>>Moyen</option>
                            <option value="difficile" <?php echo ($difficulte === 'difficile') ? 'selected' : ''; ?>>Difficile</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Changer l'image</label>
                    <?php if ($image_actuelle): ?>
                        <div class="current-image">
                            <p><strong>Image actuelle :</strong></p>
                            <img src="<?php echo UPLOAD_URL; ?>/packs/<?php echo $image_actuelle; ?>" alt="Image actuelle" style="max-width: 300px; border-radius: 8px; margin: 10px 0;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Laissez vide pour conserver l'image actuelle</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Pack actif (visible sur le site)
                    </label>
                </div>

                <h3>Composition du pack</h3>
                <p class="info-text">Modifiez les étapes et hébergements pour chaque jour du parcours.</p>

                <div id="composition-container">
                    <?php for ($jour = 1; $jour <= $duree_jours; $jour++): ?>
                        <div class="jour-composition">
                            <h4>Jour <?php echo $jour; ?></h4>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="etape_jour_<?php echo $jour; ?>">Étape *</label>
                                    <select name="etape_jour_<?php echo $jour; ?>" id="etape_jour_<?php echo $jour; ?>" required>
                                        <option value="">-- Sélectionner une étape --</option>
                                        <?php foreach($etapes as $etape): ?>
                                            <option value="<?php echo $etape['id']; ?>"
                                                <?php 
                                                if (isset($composition_par_jour[$jour]) && $composition_par_jour[$jour]['etape_id'] == $etape['id']) {
                                                    echo 'selected';
                                                }
                                                ?>>
                                                <?php echo htmlspecialchars($etape['nom']); ?> (<?php echo htmlspecialchars($etape['ville']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="hebergement_jour_<?php echo $jour; ?>">Hébergement (optionnel)</label>
                                    <select name="hebergement_jour_<?php echo $jour; ?>" id="hebergement_jour_<?php echo $jour; ?>">
                                        <option value="">-- Aucun hébergement --</option>
                                        <?php foreach($hebergements as $heb): ?>
                                            <option value="<?php echo $heb['id']; ?>"
                                                <?php 
                                                if (isset($composition_par_jour[$jour]) && $composition_par_jour[$jour]['hebergement_id'] == $heb['id']) {
                                                    echo 'selected';
                                                }
                                                ?>>
                                                <?php echo htmlspecialchars($heb['nom']); ?> (<?php echo htmlspecialchars($heb['etape_nom']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="<?php echo SITE_URL; ?>/admin/packs/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.info-text {
    background: #dbeafe;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    color: #1e40af;
}

.jour-composition {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid var(--primary-color);
}

.jour-composition h4 {
    margin: 0 0 15px 0;
    color: var(--primary-color);
    font-size: 18px;
}
</style>

<?php include '../../includes/footer.php'; ?>