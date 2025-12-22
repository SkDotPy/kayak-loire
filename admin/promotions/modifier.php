<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Modifier un code promo';

$errors = [];

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect(SITE_URL . '/admin/promotions/liste.php');
}

$id = (int)$_GET['id'];

// Récupérer la promotion
$promo = query("SELECT * FROM promotions WHERE id = ?", [$id]);

if (empty($promo)) {
    setErrorMessage("Ce code promo n'existe pas.");
    redirect(SITE_URL . '/admin/promotions/liste.php');
}

$promo = $promo[0];

// Initialiser les variables
$code = $promo['code'];
$description = $promo['description'];
$type = $promo['type'];
$valeur = $promo['valeur'];
$date_debut = $promo['date_debut'];
$date_fin = $promo['date_fin'];
$utilisation_max = $promo['utilisation_max'];
$montant_min = $promo['montant_min'];
$premiere_reservation_seulement = $promo['premiere_reservation_seulement'];
$actif = $promo['actif'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(clean($_POST['code']));
    $description = clean($_POST['description']);
    $type = clean($_POST['type']);
    $valeur = (float)$_POST['valeur'];
    $date_debut = !empty($_POST['date_debut']) ? clean($_POST['date_debut']) : null;
    $date_fin = !empty($_POST['date_fin']) ? clean($_POST['date_fin']) : null;
    $utilisation_max = !empty($_POST['utilisation_max']) ? (int)$_POST['utilisation_max'] : null;
    $montant_min = !empty($_POST['montant_min']) ? (float)$_POST['montant_min'] : null;
    $premiere_reservation_seulement = isset($_POST['premiere_reservation_seulement']) ? 1 : 0;
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($code)) {
        $errors[] = "Le code est obligatoire.";
    } elseif (strlen($code) < 3) {
        $errors[] = "Le code doit contenir au moins 3 caractères.";
    } elseif (!preg_match('/^[A-Z0-9]+$/', $code)) {
        $errors[] = "Le code ne peut contenir que des lettres majuscules et des chiffres.";
    }
    
    // Vérifier que le code n'existe pas déjà (sauf pour cette promo)
    $code_existant = query("SELECT id FROM promotions WHERE code = ? AND id != ?", [$code, $id]);
    if (!empty($code_existant)) {
        $errors[] = "Ce code promo existe déjà.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est obligatoire.";
    }
    
    if (!in_array($type, ['pourcentage', 'montant_fixe'])) {
        $errors[] = "Le type sélectionné n'est pas valide.";
    }
    
    if (empty($valeur) || $valeur <= 0) {
        $errors[] = "La valeur doit être supérieure à 0.";
    }
    
    if ($type === 'pourcentage' && $valeur > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%.";
    }
    
    if ($date_debut && $date_fin && $date_debut > $date_fin) {
        $errors[] = "La date de début doit être antérieure à la date de fin.";
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        $sql = "UPDATE promotions SET 
                code = ?, 
                description = ?, 
                type = ?, 
                valeur = ?, 
                date_debut = ?, 
                date_fin = ?, 
                utilisation_max = ?, 
                montant_min = ?, 
                premiere_reservation_seulement = ?, 
                actif = ? 
                WHERE id = ?";
        
        $result = execute($sql, [
            $code, 
            $description, 
            $type, 
            $valeur, 
            $date_debut, 
            $date_fin, 
            $utilisation_max, 
            $montant_min, 
            $premiere_reservation_seulement, 
            $actif,
            $id
        ]);
        
        if ($result) {
            setSuccessMessage("Le code promo a été modifié avec succès !");
            redirect(SITE_URL . '/admin/promotions/liste.php');
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
            <h1>🎫 Modifier : <?php echo htmlspecialchars($promo['code']); ?></h1>
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

        <!-- INFO UTILISATION -->
        <div class="info-box" style="background: #fef3c7; border-left-color: #f59e0b;">
            <h3>📊 Utilisation actuelle</h3>
            <p><strong><?php echo $promo['utilisation_count']; ?></strong> utilisation(s) 
            <?php if ($promo['utilisation_max']): ?>
                sur <?php echo $promo['utilisation_max']; ?> maximum
            <?php endif; ?>
            </p>
            <small>Vous pouvez modifier les paramètres mais pas réinitialiser le compteur d'utilisation.</small>
        </div>

        <div class="form-container">
            <form method="POST" class="admin-form">
                
                <h3>Informations du code promo</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Code promo *</label>
                        <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($code); ?>" required style="text-transform: uppercase; font-family: monospace; font-size: 18px;">
                        <small>Lettres majuscules et chiffres uniquement</small>
                    </div>

                    <div class="form-group">
                        <label for="type">Type de réduction *</label>
                        <select id="type" name="type" required onchange="updateTypeHint()">
                            <option value="pourcentage" <?php echo ($type === 'pourcentage') ? 'selected' : ''; ?>>Pourcentage (%)</option>
                            <option value="montant_fixe" <?php echo ($type === 'montant_fixe') ? 'selected' : ''; ?>>Montant fixe (€)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="valeur">Valeur *</label>
                        <input type="number" id="valeur" name="valeur" value="<?php echo $valeur; ?>" min="0.01" step="0.01" required>
                        <small id="type-hint">
                            <?php echo $type === 'pourcentage' ? 'Ex: 20 = -20%' : 'Ex: 50 = -50€'; ?>
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="2" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <h3>Période de validité</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_debut">Date de début (optionnel)</label>
                        <input type="date" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_fin">Date de fin (optionnel)</label>
                        <input type="date" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
                    </div>
                </div>

                <h3>Conditions d'utilisation</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="utilisation_max">Nombre d'utilisations max (optionnel)</label>
                        <input type="number" id="utilisation_max" name="utilisation_max" value="<?php echo $utilisation_max; ?>" min="1">
                    </div>

                    <div class="form-group">
                        <label for="montant_min">Montant minimum de commande (optionnel)</label>
                        <input type="number" id="montant_min" name="montant_min" value="<?php echo $montant_min; ?>" min="0" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="premiere_reservation_seulement" <?php echo $premiere_reservation_seulement ? 'checked' : ''; ?>>
                        Réservé aux nouveaux clients (première réservation uniquement)
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" <?php echo $actif ? 'checked' : ''; ?>>
                        Code promo actif
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="<?php echo SITE_URL; ?>/admin/promotions/liste.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
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
</style>

<script>
function updateTypeHint() {
    const type = document.getElementById('type').value;
    const hint = document.getElementById('type-hint');
    
    if (type === 'pourcentage') {
        hint.textContent = 'Ex: 20 = -20%';
    } else {
        hint.textContent = 'Ex: 50 = -50€';
    }
}

document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include '../../includes/footer.php'; ?>