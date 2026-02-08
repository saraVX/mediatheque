<?php
session_start();
require_once '../src/auth/guard.php';
require_once '../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="terminal">
    <h1>Bienvenue <?= $_SESSION['user_prenom'] ?></h1>

    <ul class="menu">
        <li><a href="profil.php">[ Profil ]</a></li>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="../admin/users.php">[ Gérer utilisateurs ]</a></li>
            <li><a href="../admin/documents.php">[ Catalogue ]</a></li>
            <li><a href="../admin/emprunts.php">[ Emprunts ]</a></li>
        <?php endif; ?>

        <li><a href="logout.php">[ Déconnexion ]</a></li>
    </ul>
</div>

</body>
</html>
