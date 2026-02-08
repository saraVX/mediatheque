<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // Récupérer les 8 derniers documents ajoutés
    $stmt = $pdo->query("
        SELECT * FROM document 
        ORDER BY id_document DESC 
        LIMIT 8
    ");
    $documents = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
