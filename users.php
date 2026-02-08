<?php
session_start();
require_once '../src/auth/guard.php';
require_once '../src/config/database.php';

if ($_SESSION['role'] !== 'admin') {
    die("Accès refusé");
}

$db = (new Database())->getConnection();
$users = $db->query("SELECT id, nom, prenom, email, role FROM utilisateurs")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/style.css">
<title>Admin - Utilisateurs</title>
</head>
<body>
<div class="terminal">
<h2>Gestion des utilisateurs</h2>

<table>
<tr><th>Nom</th><th>Email</th><th>Rôle</th></tr>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['prenom']." ".$u['nom'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['role'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<a href="../public/dashboard.php">[ Retour ]</a>
</div>
</body>
</html>
