<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Créer un code promo';

$errors = [];
$code = '';
$description = '';
$type = 'pourcentage';
$valeur = '';
$date_debut = '';
$date_fin = '';
$utilisation_max = '';
$montant_min = '';
$premiere_reservation_seulement = 0;
$actif = 1;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(clean($_POST['code'])); // Toujours en majuscules
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
    
    // Vérifier que le code n'existe pas déjà
    $code_existant = query("SELECT id FROM promotions WHERE code = ?", [$code]);
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
    
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        $sql = "INSERT INTO promotions (code, description, type, valeur, date_debut, date_fin, utilisation_max, utilisation_count, montant_min, premiere_reservation_seulement, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, NOW())";
        
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
            $actif
        ]);
        
        if ($result) {
            setSuccessMessage("Le code promo a été créé avec succès !");
            redirect(SITE_URL . '/admin/promotions/liste.php');
        } else {
            $errors[] = "Une erreur est survenue lors de la création.";
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>🎫 Créer un code promo</h1>
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

        <!-- INFO BOX -->
        <div class="info-box">
            <h3>💡 Conseils pour créer un bon code promo</h3>
            <ul>
                <li><strong>Code court et mémorable :</strong> NOEL2024, BIENVENUE, ETE50</li>
                <li><strong>Pourcentage pour les grosses commandes :</strong> -20% sur les packs</li>
                <li><strong>Montant fixe pour inciter :</strong> -50€ sur la première réservation</li>
                <li><strong>Date limite pour créer l'urgence :</strong> Valable jusqu'au 31/12</li>
            </ul>
        </div>

        <div class="form-container">
            <form method="POST" class="admin-form">
                
                <h3>Informations du code promo</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Code promo *</label>
                        <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($code); ?>" required placeholder="Ex: NOEL2024" style="text-transform: uppercase; font-family: monospace; font-size: 18px;">
                        <small>Lettres majuscules et chiffres uniquement (min 3 caractères)</small>
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
                        <input type="number" id="valeur" name="valeur" value="<?php echo $valeur; ?>" min="0.01" step="0.01" required placeholder="20">
                        <small id="type-hint">Ex: 20 = -20%</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="2" required placeholder="Ex: Promotion de Noël 2024"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <h3>Période de validité</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_debut">Date de début (optionnel)</label>
                        <input type="date" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
                        <small>Laissez vide pour une validité immédiate</small>
                    </div>

                    <div class="form-group">
                        <label for="date_fin">Date de fin (optionnel)</label>
                        <input type="date" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
                        <small>Laissez vide pour une validité illimitée</small>
                    </div>
                </div>

                <h3>Conditions d'utilisation</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="utilisation_max">Nombre d'utilisations max (optionnel)</label>
                        <input type="number" id="utilisation_max" name="utilisation_max" value="<?php echo $utilisation_max; ?>" min="1" placeholder="100">
                        <small>Laissez vide pour illimité</small>
                    </div>

                    <div class="form-group">
                        <label for="montant_min">Montant minimum de commande (optionnel)</label>
                        <input type="number" id="montant_min" name="montant_min" value="<?php echo $montant_min; ?>" min="0" step="0.01" placeholder="200.00">
                        <small>Laissez vide si aucun minimum</small>
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
                        Code promo actif (utilisable immédiatement)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer le code promo</button>
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
    margin: 0 0 15px 0;
    color: #1e40af;
}

.info-box ul {
    margin: 10px 0;
    padding-left: 20px;
}

.info-box li {
    margin: 8px 0;
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

// Forcer les majuscules en temps réel
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include '../../includes/footer.php'; ?>