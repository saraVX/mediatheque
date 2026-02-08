<?php
require_once 'src/config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h1>Test Base de données Médiathèque</h1>";

try {
    // Compter les tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>✅ Connexion réussie</p>";
    echo "<p>Tables trouvées : " . count($tables) . "</p>";
    
    // Afficher les statistiques
    $stats = [
        'utilisateurs' => 'SELECT COUNT(*) FROM utilisateurs',
        'documents' => 'SELECT COUNT(*) FROM documents',
        'emprunts' => 'SELECT COUNT(*) FROM emprunts',
        'reservations' => 'SELECT COUNT(*) FROM reservations'
    ];
    
    echo "<h2>📊 Statistiques :</h2>";
    foreach ($stats as $label => $query) {
        $count = $db->query($query)->fetchColumn();
        echo "<p>" . ucfirst($label) . " : $count</p>";
    }
    
    // Afficher quelques documents
    echo "<h2>📚 Documents disponibles :</h2>";
    $docs = $db->query("SELECT titre, auteur, type_document, disponible FROM documents LIMIT 5")->fetchAll();
    foreach ($docs as $doc) {
        echo "<p>" . htmlspecialchars($doc['titre']) . " - " . htmlspecialchars($doc['auteur']) . 
             " (" . $doc['type_document'] . ") - Disponible : " . $doc['disponible'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Accéder à l'application</a></p>";
?>
