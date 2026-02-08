<?php
require_once 'includes/config.php';
$pageTitle = "Catalogue";
require_once 'includes/header.php';

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$genre = $_GET['genre'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDBConnection();
    
    // Construction de la requête
    $sql = "SELECT * FROM document WHERE 1=1";
    $countSql = "SELECT COUNT(*) FROM document WHERE 1=1";
    $params = [];
    $types = [];
    $genres = [];
    
    if (!empty($search)) {
        $sql .= " AND (titre LIKE ? OR auteur LIKE ? OR description LIKE ?)";
        $countSql .= " AND (titre LIKE ? OR auteur LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types[] = 's';
        $types[] = 's';
        $types[] = 's';
    }
    
    if (!empty($type)) {
        $sql .= " AND type = ?";
        $countSql .= " AND type = ?";
        $params[] = $type;
        $types[] = 's';
    }
    
    if (!empty($genre)) {
        $sql .= " AND genre = ?";
        $countSql .= " AND genre = ?";
        $params[] = $genre;
        $types[] = 's';
    }
    
    $sql .= " ORDER BY titre LIMIT ? OFFSET ?";
    $countParams = $params;
    
    // Compter le total
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($countParams);
    $totalDocuments = $stmt->fetchColumn();
    $totalPages = ceil($totalDocuments / $limit);
    
    // Récupérer les documents
    $params[] = $limit;
    $params[] = $offset;
    $types[] = 'i';
    $types[] = 'i';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
    
    // Récupérer les types et genres uniques pour les filtres
    $stmtTypes = $pdo->query("SELECT DISTINCT type FROM document WHERE type IS NOT NULL ORDER BY type");
    $allTypes = $stmtTypes->fetchAll(PDO::FETCH_COLUMN);
    
    $stmtGenres = $pdo->query("SELECT DISTINCT genre FROM document WHERE genre IS NOT NULL ORDER BY genre");
    $allGenres = $stmtGenres->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = "Erreur lors du chargement du catalogue: " . $e->getMessage();
    $documents = [];
    $allTypes = [];
    $allGenres = [];
    $totalPages = 1;
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-filter me-2"></i>Rechercher dans le catalogue</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Titre, auteur, description...">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Tous les types</option>
                            <?php foreach ($allTypes as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo $type === $t ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($t); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="genre" class="form-label">Genre</label>
                        <select class="form-select" id="genre" name="genre">
                            <option value="">Tous les genres</option>
                            <?php foreach ($allGenres as $g): ?>
                                <option value="<?php echo $g; ?>" <?php echo $genre === $g ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Catalogue des documents</h3>
            <span class="badge bg-primary fs-6">
                <?php echo number_format($totalDocuments, 0, ',', ' '); ?> document(s)
            </span>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (empty($documents)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Aucun document ne correspond à vos critères de recherche.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($documents as $doc): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card document-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($doc['image_url'] ?: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300'); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($doc['titre']); ?>"
                                     style="height: 250px; object-fit: cover;"
                                     onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300'">
                                <span class="badge <?php echo ($doc['disponible'] && $doc['quantite_disponible'] > 0) ? 'bg-success' : 'bg-danger'; ?> availability-badge">
                                    <?php echo ($doc['disponible'] && $doc['quantite_disponible'] > 0) ? 'Disponible' : 'Indisponible'; ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($doc['titre']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($doc['auteur'] ?: 'Auteur non spécifié'); ?>
                                </p>
                                <p class="card-text small flex-grow-1">
                                    <?php 
                                    $description = $doc['description'] ?: 'Pas de description disponible';
                                    echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description);
                                    ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo ucfirst($doc['type']); ?>
                                            <?php if ($doc['genre']): ?>
                                                • <?php echo htmlspecialchars($doc['genre']); ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if ($doc['annee_publication']): ?>
                                            <small class="text-muted">
                                                <?php echo $doc['annee_publication']; ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-secondary">
                                            <?php echo $doc['quantite_disponible']; ?> / <?php echo $doc['quantite_total']; ?> exemplaires
                                        </span>
                                        <?php if (isLoggedIn() && $_SESSION['role'] === 'adherent' && $doc['disponible'] && $doc['quantite_disponible'] > 0): ?>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="emprunterDocument(<?php echo $doc['id_document']; ?>)">
                                                <i class="fas fa-bookmark me-1"></i>Emprunter
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function emprunterDocument(idDocument) {
    Swal.fire({
        title: 'Emprunter ce document ?',
        text: 'Voulez-vous vraiment emprunter ce document ?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, emprunter',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/emprunter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_document=' + idDocument
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur réseau: ' + error
                });
            });
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
