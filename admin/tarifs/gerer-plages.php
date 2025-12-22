<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Gestion des tarifs saisonniers';

$errors = [];
$success = '';

// ============================================
// TRAITEMENT : AJOUTER UNE PLAGE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = clean($_POST['nom']);
    $date_debut = clean($_POST['date_debut']);
    $date_fin = clean($_POST['date_fin']);
    $coefficient = (float)$_POST['coefficient'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($date_debut) || empty($date_fin)) {
        $errors[] = "Les dates de début et de fin sont obligatoires.";
    }
    
    if ($date_debut > $date_fin) {
        $errors[] = "La date de début doit être antérieure à la date de fin.";
    }
    
    if ($coefficient <= 0) {
        $errors[] = "Le coefficient doit être supérieur à 0.";
    }
    
    // Vérifier les chevauchements de dates
    $chevauchement = query("SELECT * FROM plages_tarifaires 
                            WHERE actif = 1 
                            AND (
                                (date_debut <= ? AND date_fin >= ?)
                                OR (date_debut <= ? AND date_fin >= ?)
                                OR (date_debut >= ? AND date_fin <= ?)
                            )", [$date_debut, $date_debut, $date_fin, $date_fin, $date_debut, $date_fin]);
    
    if (!empty($chevauchement)) {
        $errors[] = "Cette plage chevauche une autre plage tarifaire active : " . $chevauchement[0]['nom'];
    }
    
    // Si pas d'erreurs, insérer
    if (empty($errors)) {
        $sql = "INSERT INTO plages_tarifaires (nom, date_debut, date_fin, coefficient, actif, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $result = execute($sql, [$nom, $date_debut, $date_fin, $coefficient, $actif]);
        
        if ($result) {
            $success = "La plage tarifaire a été ajoutée avec succès !";
        } else {
            $errors[] = "Une erreur est survenue lors de l'ajout.";
        }
    }
}

// ============================================
// TRAITEMENT : MODIFIER UNE PLAGE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id = (int)$_POST['id'];
    $nom = clean($_POST['nom']);
    $date_debut = clean($_POST['date_debut']);
    $date_fin = clean($_POST['date_fin']);
    $coefficient = (float)$_POST['coefficient'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($date_debut) || empty($date_fin)) {
        $errors[] = "Les dates sont obligatoires.";
    }
    
    if ($date_debut > $date_fin) {
        $errors[] = "La date de début doit être antérieure à la date de fin.";
    }
    
    if ($coefficient <= 0) {
        $errors[] = "Le coefficient doit être supérieur à 0.";
    }
    
    // Vérifier les chevauchements (sauf pour cette plage elle-même)
    $chevauchement = query("SELECT * FROM plages_tarifaires 
                            WHERE actif = 1 
                            AND id != ?
                            AND (
                                (date_debut <= ? AND date_fin >= ?)
                                OR (date_debut <= ? AND date_fin >= ?)
                                OR (date_debut >= ? AND date_fin <= ?)
                            )", [$id, $date_debut, $date_debut, $date_fin, $date_fin, $date_debut, $date_fin]);
    
    if (!empty($chevauchement)) {
        $errors[] = "Cette plage chevauche : " . $chevauchement[0]['nom'];
    }
    
    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        $sql = "UPDATE plages_tarifaires 
                SET nom = ?, date_debut = ?, date_fin = ?, coefficient = ?, actif = ? 
                WHERE id = ?";
        
        $result = execute($sql, [$nom, $date_debut, $date_fin, $coefficient, $actif, $id]);
        
        if ($result) {
            $success = "La plage tarifaire a été modifiée avec succès !";
        } else {
            $errors[] = "Une erreur est survenue lors de la modification.";
        }
    }
}

// ============================================
// TRAITEMENT : SUPPRIMER UNE PLAGE
// ============================================
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    
    $result = execute("DELETE FROM plages_tarifaires WHERE id = ?", [$id]);
    
    if ($result) {
        $success = "La plage tarifaire a été supprimée avec succès !";
    } else {
        $errors[] = "Une erreur est survenue lors de la suppression.";
    }
}

// ============================================
// RÉCUPÉRER TOUTES LES PLAGES
// ============================================
$plages = query("SELECT * FROM plages_tarifaires ORDER BY date_debut ASC");

// Récupérer la plage à modifier si demandé
$plage_a_modifier = null;
if (isset($_GET['modifier'])) {
    $id_modifier = (int)$_GET['modifier'];
    $plage_result = query("SELECT * FROM plages_tarifaires WHERE id = ?", [$id_modifier]);
    if (!empty($plage_result)) {
        $plage_a_modifier = $plage_result[0];
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>💰 Gestion des tarifs saisonniers</h1>
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
            </div>
        <?php endif; ?>

        <!-- INFO BOX -->
        <div class="info-box">
            <h3>ℹ️ Comment ça marche ?</h3>
            <p>Les <strong>coefficients multiplicateurs</strong> s'appliquent aux prix de base :</p>
            <ul>
                <li><strong>Coefficient 1.5</strong> = Prix × 1.5 = +50% (haute saison)</li>
                <li><strong>Coefficient 1.0</strong> = Prix normal (pas de modification)</li>
                <li><strong>Coefficient 0.8</strong> = Prix × 0.8 = -20% (basse saison)</li>
            </ul>
            <p><strong>Exemple :</strong> Hébergement à 80€/nuit avec coefficient 1.5 = 120€/nuit</p>
        </div>

        <!-- FORMULAIRE D'AJOUT/MODIFICATION -->
        <div class="form-section">
            <h3><?php echo $plage_a_modifier ? '✏️ Modifier une plage tarifaire' : '➕ Ajouter une plage tarifaire'; ?></h3>
            
            <form method="POST" class="tarif-form">
                <input type="hidden" name="action" value="<?php echo $plage_a_modifier ? 'modifier' : 'ajouter'; ?>">
                <?php if ($plage_a_modifier): ?>
                    <input type="hidden" name="id" value="<?php echo $plage_a_modifier['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom de la plage *</label>
                        <input type="text" id="nom" name="nom" 
                               value="<?php echo $plage_a_modifier ? htmlspecialchars($plage_a_modifier['nom']) : ''; ?>" 
                               required placeholder="Ex: Haute saison été">
                    </div>

                    <div class="form-group">
                        <label for="coefficient">Coefficient *</label>
                        <input type="number" id="coefficient" name="coefficient" 
                               value="<?php echo $plage_a_modifier ? $plage_a_modifier['coefficient'] : '1.0'; ?>" 
                               min="0.1" max="5" step="0.1" required>
                        <small>1.0 = prix normal | 1.5 = +50% | 0.8 = -20%</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_debut">Date de début *</label>
                        <input type="date" id="date_debut" name="date_debut" 
                               value="<?php echo $plage_a_modifier ? $plage_a_modifier['date_debut'] : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="date_fin">Date de fin *</label>
                        <input type="date" id="date_fin" name="date_fin" 
                               value="<?php echo $plage_a_modifier ? $plage_a_modifier['date_fin'] : ''; ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="actif" 
                               <?php echo (!$plage_a_modifier || $plage_a_modifier['actif']) ? 'checked' : ''; ?>>
                        Plage active
                    </label>
                    <small>Les plages inactives ne sont pas appliquées aux calculs de prix</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $plage_a_modifier ? 'Enregistrer les modifications' : 'Ajouter la plage'; ?>
                    </button>
                    <?php if ($plage_a_modifier): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/tarifs/gerer-plages.php" class="btn btn-outline">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- LISTE DES PLAGES -->
        <div class="table-section">
            <h3>📋 Plages tarifaires existantes</h3>

            <?php if (empty($plages)): ?>
                <div class="empty-state">
                    <p>Aucune plage tarifaire configurée pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Durée</th>
                                <th>Coefficient</th>
                                <th>Impact</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($plages as $plage): ?>
                                <?php
                                $date_debut = new DateTime($plage['date_debut']);
                                $date_fin = new DateTime($plage['date_fin']);
                                $duree = $date_debut->diff($date_fin)->days + 1;
                                
                                $impact = (($plage['coefficient'] - 1) * 100);
                                $impact_text = $impact > 0 ? '+' . round($impact) . '%' : round($impact) . '%';
                                $impact_color = $impact > 0 ? '#059669' : ($impact < 0 ? '#dc2626' : '#6b7280');
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($plage['nom']); ?></strong></td>
                                    <td><?php echo formatDate($plage['date_debut']); ?></td>
                                    <td><?php echo formatDate($plage['date_fin']); ?></td>
                                    <td><?php echo $duree; ?> jour(s)</td>
                                    <td><strong><?php echo $plage['coefficient']; ?>x</strong></td>
                                    <td style="color: <?php echo $impact_color; ?>; font-weight: 600;">
                                        <?php echo $impact_text; ?>
                                    </td>
                                    <td>
                                        <?php if ($plage['actif']): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="<?php echo SITE_URL; ?>/admin/tarifs/gerer-plages.php?modifier=<?php echo $plage['id']; ?>" class="btn btn-sm btn-outline">Modifier</a>
                                        <a href="<?php echo SITE_URL; ?>/admin/tarifs/gerer-plages.php?supprimer=<?php echo $plage['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette plage ?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- EXEMPLE DE CALCUL -->
        <div class="example-box">
            <h3>🧮 Exemple de calcul</h3>
            <div class="example-content">
                <div class="example-item">
                    <div class="example-label">Prix de base :</div>
                    <div class="example-value">80,00 €</div>
                </div>
                <div class="example-separator">×</div>
                <div class="example-item">
                    <div class="example-label">Coefficient haute saison :</div>
                    <div class="example-value">1.5</div>
                </div>
                <div class="example-separator">=</div>
                <div class="example-item highlight">
                    <div class="example-label">Prix final :</div>
                    <div class="example-value">120,00 €</div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
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

.form-section, .table-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.form-section h3, .table-section h3 {
    margin: 0 0 20px 0;
    color: var(--dark);
}

.tarif-form {
    max-width: 100%;
}

.example-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    color: white;
}

.example-box h3 {
    margin: 0 0 20px 0;
}

.example-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.example-item {
    background: rgba(255,255,255,0.2);
    padding: 20px 30px;
    border-radius: 8px;
    text-align: center;
}

.example-item.highlight {
    background: rgba(255,255,255,0.3);
    border: 2px solid white;
}

.example-label {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 8px;
}

.example-value {
    font-size: 24px;
    font-weight: 700;
}

.example-separator {
    font-size: 32px;
    font-weight: 700;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .example-content {
        flex-direction: column;
    }
    
    .example-separator {
        transform: rotate(90deg);
    }
}
</style>

<?php include '../../includes/footer.php'; ?>