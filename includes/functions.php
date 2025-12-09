<?php
/**
 * Fonctions utilitaires du site
 */

// ======================================
// FONCTIONS DE SÉCURITÉ
// ======================================

/**
 * Nettoyer une chaîne de caractères
 * @param string $data La chaîne à nettoyer
 * @return string La chaîne nettoyée
 */
function clean($data) {
    $data = trim($data); // Enlève les espaces
    $data = stripslashes($data); // Enlève les antislashs
    $data = htmlspecialchars($data); // Convertit les caractères spéciaux
    return $data;
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool True si connecté, False sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est administrateur
 * @return bool True si admin, False sinon
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Rediriger vers une page
 * @param string $url L'URL de destination
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Protéger une page (accessible uniquement si connecté)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * Protéger une page admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/index.php');
    }
}

// ======================================
// FONCTIONS DE VALIDATION
// ======================================

/**
 * Valider un email
 * @param string $email L'email à valider
 * @return bool True si valide, False sinon
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Valider un mot de passe
 * @param string $password Le mot de passe
 * @return bool True si valide, False sinon
 */
function isValidPassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

// ======================================
// FONCTIONS DE FORMATAGE
// ======================================

/**
 * Formater un prix en euros
 * @param float $price Le prix
 * @return string Le prix formaté
 */
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

/**
 * Formater une date en français
 * @param string $date La date (format SQL)
 * @return string La date formatée
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Formater une date avec heure
 * @param string $datetime La date et heure
 * @return string La date formatée
 */
function formatDateTime($datetime) {
    $timestamp = strtotime($datetime);
    return date('d/m/Y à H:i', $timestamp);
}

// ======================================
// FONCTIONS POUR LES MESSAGES
// ======================================

/**
 * Définir un message de succès
 * @param string $message Le message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Définir un message d'erreur
 * @param string $message Le message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Afficher les messages (succès ou erreur)
 * @return string Le HTML des messages
 */
function displayMessages() {
    $html = '';
    
    if (isset($_SESSION['success_message'])) {
        $html .= '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $html .= '<div class="alert alert-error">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    
    return $html;
}

// ======================================
// FONCTIONS POUR LES FICHIERS
// ======================================

/**
 * Uploader une image
 * @param array $file Le fichier uploadé ($_FILES['nom'])
 * @param string $destination Le dossier de destination
 * @return string|false Le nom du fichier ou false si erreur
 */
function uploadImage($file, $destination) {
    // Vérifier si un fichier a été uploadé
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Vérifier la taille
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    // Générer un nom unique
    $filename = uniqid() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

?>