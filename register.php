<?php
require_once 'includes/config.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($nom)) $errors[] = 'Le nom est requis';
    if (empty($prenom)) $errors[] = 'Le prénom est requis';
    if (empty($email)) $errors[] = 'L\'email est requis';
    if (empty($password)) $errors[] = 'Le mot de passe est requis';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Les mots de passe ne correspondent pas';
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà utilisé';
            } else {
                // Créer l'utilisateur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO user (nom, prenom, email, mot_de_passe, role) 
                    VALUES (?, ?, ?, ?, 'adherent')
                ");
                $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();
                
                // Créer l'adhérent associé
                $stmt = $pdo->prepare("
                    INSERT INTO adherent (nom, prenom, email, user_id) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$nom, $prenom, $email, $userId]);
                
                // Connecter automatiquement l'utilisateur
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['role'] = 'adherent';
                
                $_SESSION['success'] = 'Compte créé avec succès ! Bienvenue ' . $prenom . ' !';
                redirect('index.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du compte: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Inscription";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Créer un compte</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo old('nom'); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="<?php echo old('prenom'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo old('email'); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimum 6 caractères</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">conditions d'utilisation</a> *
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p>Déjà inscrit ? <a href="login.php" class="text-decoration-none">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des conditions -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conditions d'utilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>En vous inscrivant à la médiathèque, vous acceptez :</p>
                <ul>
                    <li>De respecter les délais de retour des documents</li>
                    <li>De prendre soin des documents empruntés</li>
                    <li>De signaler tout document endommagé</li>
                    <li>De payer les frais de retard éventuels</li>
                    <li>De maintenir vos informations à jour</li>
                </ul>
                <p>L'adhésion est gratuite et valable un an.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Les mots de passe ne correspondent pas'
        });
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Le mot de passe doit contenir au moins 6 caractères'
        });
        return false;
    }
    
    return true;
});
</script>

<?php require_once 'includes/footer.php'; ?>
