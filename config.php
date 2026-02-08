<?php
// ==============================================
// CONFIGURATION DE L'APPLICATION MÉDIATHÈQUE
// ==============================================

// 1. Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'mediatheque_db');
define('DB_USER', 'mediatheque_user');
define('DB_PASS', 'sara');

// 2. Configuration de l'application
define('APP_NAME', 'Médiathèque Municipale');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/index.php', '', $_SERVER['PHP_SELF']));
define('SITE_ROOT', realpath(dirname(__FILE__) . '/../'));

// 3. Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Fonction de connexion à la base de données
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>
            <h3>Erreur de connexion à la base de données</h3>
            <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p><strong>Vérifiez:</strong></p>
            <ul>
                <li>Que MySQL est démarré</li>
                <li>Que la base 'mediatheque_db' existe</li>
                <li>Que l'utilisateur 'mediatheque_user' a le bon mot de passe</li>
            </ul>
        </div>");
    }
}

// 5. Fonction de redirection
function redirect($url) {
    header("Location: $url");
    exit();
}

// 6. Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 7. Vérifier le rôle de l'utilisateur
function checkRole($requiredRole) {
    if (!isLoggedIn() || $_SESSION['role'] !== $requiredRole) {
        $_SESSION['error'] = 'Accès non autorisé';
        redirect('login.php');
    }
}

// 8. Hachage des mots de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// 9. Vérification du mot de passe
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// 10. Fonction pour afficher les messages flash
function displayFlashMessages() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['success']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['info'])) {
        echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['info']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['info']);
    }
}

// 11. Fonction pour pré-remplir les champs de formulaire
function old($field) {
    return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
}

// 12. Fonction pour vérifier les champs requis
function validateRequired($fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = "Le champ " . ucfirst($field) . " est requis";
        }
    }
    return $errors;
}
