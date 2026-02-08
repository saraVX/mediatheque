<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Vérifier la connexion
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

// Vérifier le rôle
if ($_SESSION['role'] !== 'adherent') {
    echo json_encode(['success' => false, 'message' => 'Seuls les adhérents peuvent emprunter']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$id_document = $_POST['id_document'] ?? 0;

if (empty($id_document)) {
    echo json_encode(['success' => false, 'message' => 'Document non spécifié']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // 1. Vérifier si le document existe et est disponible
    $stmt = $pdo->prepare("SELECT * FROM document WHERE id_document = ?");
    $stmt->execute([$id_document]);
    $document = $stmt->fetch();
    
    if (!$document) {
        echo json_encode(['success' => false, 'message' => 'Document non trouvé']);
        exit;
    }
    
    if (!$document['disponible'] || $document['quantite_disponible'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Document indisponible pour le moment']);
        exit;
    }
    
    // 2. Récupérer l'ID de l'adhérent
    $stmt = $pdo->prepare("SELECT id_adherent FROM adherent WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $adherent = $stmt->fetch();
    
    if (!$adherent) {
        echo json_encode(['success' => false, 'message' => 'Adhérent non trouvé']);
        exit;
    }
    
    $id_adherent = $adherent['id_adherent'];
    
    // 3. Vérifier si l'adhérent n'a pas déjà emprunté ce document
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprunt WHERE id_adherent = ? AND id_document = ? AND statut = 'en_cours'");
    $stmt->execute([$id_adherent, $id_document]);
    $alreadyBorrowed = $stmt->fetchColumn();
    
    if ($alreadyBorrowed > 0) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà emprunté ce document']);
        exit;
    }
    
    // 4. Vérifier si l'adhérent n'a pas trop d'emprunts en cours (max 5)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprunt WHERE id_adherent = ? AND statut = 'en_cours'");
    $stmt->execute([$id_adherent]);
    $currentBorrows = $stmt->fetchColumn();
    
    if ($currentBorrows >= 5) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà 5 emprunts en cours. Veuillez en retourner avant d\'en emprunter de nouveaux.']);
        exit;
    }
    
    // 5. Récupérer un bibliothécaire
    $stmt = $pdo->query("SELECT id_bibliothecaire FROM bibliothecaire LIMIT 1");
    $bibliothecaire = $stmt->fetch();
    
    if (!$bibliothecaire) {
        echo json_encode(['success' => false, 'message' => 'Aucun bibliothécaire disponible']);
        exit;
    }
    
    $id_bibliothecaire = $bibliothecaire['id_bibliothecaire'];
    
    // 6. Dates
    $date_emprunt = date('Y-m-d');
    $date_retour_prevue = date('Y-m-d', strtotime('+21 days')); // 3 semaines
    
    // 7. Démarrer une transaction
    $pdo->beginTransaction();
    
    try {
        // 8. Insérer l'emprunt
        $stmt = $pdo->prepare("
            INSERT INTO emprunt (date_emprunt, date_retour_prevue, id_adherent, id_document, id_bibliothecaire) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$date_emprunt, $date_retour_prevue, $id_adherent, $id_document, $id_bibliothecaire]);
        
        // 9. Mettre à jour la quantité disponible
        $stmt = $pdo->prepare("
            UPDATE document 
            SET quantite_disponible = quantite_disponible - 1 
            WHERE id_document = ?
        ");
        $stmt->execute([$id_document]);
        
        // 10. Si quantité disponible = 0, marquer comme indisponible
        $stmt = $pdo->prepare("
            UPDATE document 
            SET disponible = FALSE 
            WHERE id_document = ? AND quantite_disponible <= 0
        ");
        $stmt->execute([$id_document]);
        
        // 11. Valider la transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Document emprunté avec succès ! Date de retour prévue: ' . date('d/m/Y', strtotime($date_retour_prevue))
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'emprunt: ' . $e->getMessage()]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
