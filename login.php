<?php
require_once 'includes/config.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Pour la démo, accepter les mots de passe en clair
            if ($user) {
                // Vérifier le mot de passe
                $isValid = false;
                
                // Si c'est un hash bcrypt
                if (password_verify($password, $user['mot_de_passe'])) {
                    $isValid = true;
                }
                // Si c'est un mot de passe en clair (pour la démo)
                elseif (strpos($user['mot_de_passe'], '$2y$') === false && $user['mot_de_passe'] === $password) {
                    $isValid = true;
                }
                
                if ($isValid) {
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['role'] = $user['role'];
                    
                    $_SESSION['success'] = 'Connexion réussie !';
                    
                    // Redirection selon le rôle
                    switch ($user['role']) {
                        case 'admin':
                            redirect('admin_dashboard.php');
                            break;
                        case 'bibliothecaire':
                            redirect('bibliothecaire_dashboard.php');
                            break;
                        default:
                            redirect('index.php');
                    }
                } else {
                    $error = 'Email ou mot de passe incorrect';
                }
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Connexion";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo old('email'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">Pas encore de compte ? 
                        <a href="register.php" class="text-decoration-none">S'inscrire</a>
                    </p>
                    <p class="mb-0">
                        <a href="forgot_password.php" class="text-decoration-none">Mot de passe oublié ?</a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Comptes de démonstration -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Comptes de démonstration</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <strong>Administrateur</strong><br>
                        <small>admin@mediatheque.com</small><br>
                        <small>admin123</small>
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Bibliothécaire</strong><br>
                        <small>bibliothecaire@mediatheque.com</small><br>
                        <small>biblio123</small>
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Adhérent</strong><br>
                        <small>adherent@mediatheque.com</small><br>
                        <small>adherent123</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
