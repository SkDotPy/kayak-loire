<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Ajouter un pack';

$errors = [];
$nom = '';
$description = '';
$duree_jours = 3;
$prix_base = '';
$difficulte = 'facile';
$actif = 1;

// Récupérer toutes les étapes et hébergements pour les dropdowns
$etapes = query("SELECT * FROM etapes WHERE actif = 1 ORDER BY ordre ASC");
$hebergements = query("SELECT h.id, h.nom, h.type, e.nom as etape_nom 
                       FROM hebergements h 
                       INNER JOIN etapes e ON h.etape_id = e.id 
                       WHERE h.actif = 1 
                       ORDER BY e.ordre ASC, h.nom ASC");

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
    
    // Vérifier la composition du pack (au moins une étape)
    $composition_valide = true;
    for ($jour = 1; $jour <= $duree_jours; $jour++) {
        if (empty($_POST['etape_jour_' . $jour])) {
            $errors[] = "Vous devez sélectionner une étape pour le jour " . $jour . ".";
            $composition_valide = false;
            break;
        }
    }
    
    // Upload de l'image (optionnel)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image'], UPLOAD_PATH . '/packs');
        if (!$image) {
            $errors[] = "Erreur lors de l'upload de l'image.";
        }
    }
    
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        // Insérer le pack
        $sql = "INSERT INTO packs (nom, description, duree_jours, prix_base, difficulte, image, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $result = execute($sql, [$nom, $description, $duree_jours, $prix_base, $difficulte, $image, $actif]);
        
        if ($result) {
            $pack_id = lastInsertId();
            
            // Insérer la composition du pack (étapes + hébergements)
            $composition_ok = true;
            for ($jour = 1; $jour <= $duree_jours; $jour++) {
                $etape_id = (int)$_POST['etape_jour_' . $jour];
                $hebergement_id = !empty($_POST['hebergement_jour_' . $jour]) ? (int)$_POST['hebergement_jour_' . $jour] : null;
                
            $sql_etape = "INSERT INTO pack_etapes (pack_id, etape_id, jour, hebergement_id) 
                        VALUES (?, ?, ?, ?)";
                
                $result_etape = execute($sql_etape, [$pack_id, $etape_id, $jour, $hebergement_id]);
                
                if (!$result_etape) {
                    $composition_ok = false;
                    break;
                }
            }
            
            if ($composition_ok) {
                setSuccessMessage("Le pack a été créé avec succès !");
                redirect(SITE_URL . '/admin/packs/liste.php');
            } else {
                // Supprimer le pack si la composition a échoué
                execute("DELETE FROM packs WHERE id = ?", [$pack_id]);
                $errors[] = "Erreur lors de la création de la composition du pack.";
            }
        } else {
            $errors[] = "Une erreur est survenue lors de la création du pack.";
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Créer un nouveau pack</h1>
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
            <form method="POST" enctype="multipart/form-data" class="admin-form" id="packForm">
                
                <h3>Informations générales</h3>
                
                <div class="form-group">
                    <label for="nom">Nom du pack *</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required placeholder="Ex: Loire Découverte 3 jours">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Décrivez le pack..."><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duree_jours">Durée (jours) *</label>
                        <input type="number" id="duree_jours" name="duree_jours" value="<?php echo $duree_jours; ?>" min="1" max="30" required>
                    </div>

                    <div class="form-group">
                        <label for="prix_base">Prix de base (€) *</label>
                        <input type="number" id="prix_base" name="prix_base" value="<?php echo $prix_base; ?>" min="0" step="0.01" required placeholder="299.00">
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
                    <label for="image">Image du pack</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Formats acceptés : JPG, PNG, GIF, WEBP - Max 5 Mo</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Pack actif (visible sur le site)
                    </label>
                </div>

                <h3>Composition du pack</h3>
                <p class="info-text">Sélectionnez une étape et un hébergement pour chaque jour du parcours.</p>

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
                                            <option value="<?php echo $etape['id']; ?>">
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
                                            <option value="<?php echo $heb['id']; ?>">
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
                    <button type="submit" class="btn btn-primary">Créer le pack</button>
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

<script>
// Script pour régénérer la composition si on change la durée
document.getElementById('duree_jours').addEventListener('change', function() {
    alert('⚠️ Si vous changez la durée, vous devrez soumettre le formulaire pour voir les nouveaux jours.');
});
</script>

<?php include '../../includes/footer.php'; ?>