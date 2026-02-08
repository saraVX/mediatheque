<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // Compter les livres
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM document WHERE type = 'livre' AND disponible = 1");
    $livres = $stmt->fetchColumn();
    
    // Compter les CD
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM document WHERE type = 'cd' AND disponible = 1");
    $cd = $stmt->fetchColumn();
    
    // Compter les DVD
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM document WHERE type = 'dvd' AND disponible = 1");
    $dvd = $stmt->fetchColumn();
    
    // Compter les adhérents actifs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM adherent WHERE statut = 'actif'");
    $adherents = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'livres' => (int)$livres,
            'cd' => (int)$cd,
            'dvd' => (int)$dvd,
            'adherents' => (int)$adherents
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
