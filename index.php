<?php
require_once 'includes/config.php';
$pageTitle = "Accueil";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="jumbotron">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Bienvenue à la <?php echo APP_NAME; ?></h1>
        <p class="lead mb-4">Découvrez notre vaste collection de livres, CD, DVD et magazines. Empruntez en ligne et profitez de la culture où que vous soyez.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="catalogue.php" class="btn btn-light btn-lg">
                <i class="fas fa-search me-2"></i>Explorer le catalogue
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>S'inscrire gratuitement
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-5">
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-book fa-3x text-primary mb-3"></i>
                <h3 class="card-title" id="stats-livres">0</h3>
                <p class="card-text">Livres disponibles</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-music fa-3x text-primary mb-3"></i>
                <h3 class="card-title" id="stats-cd">0</h3>
                <p class="card-text">CD musicaux</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-film fa-3x text-primary mb-3"></i>
                <h3 class="card-title" id="stats-dvd">0</h3>
                <p class="card-text">DVD & Blu-ray</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h3 class="card-title" id="stats-adherents">0</h3>
                <p class="card-text">Adhérents actifs</p>
            </div>
        </div>
    </div>
</div>

<!-- Nouveautés -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-star me-2"></i>Dernières acquisitions</h4>
            </div>
            <div class="card-body">
                <div class="row" id="nouveautes-container">
                    <!-- Les nouveautés seront chargées ici -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des nouveautés...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="icon-service mb-3">
                    <i class="fas fa-laptop-house fa-3x text-primary"></i>
                </div>
                <h4 class="card-title">Réservation en ligne</h4>
                <p class="card-text">Réservez vos documents en ligne et venez les chercher à la médiathèque à votre convenance.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="icon-service mb-3">
                    <i class="fas fa-clock fa-3x text-primary"></i>
                </div>
                <h4 class="card-title">Prolongation à distance</h4>
                <p class="card-text">Prolongez vos emprunts directement depuis votre espace personnel en ligne.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="icon-service mb-3">
                    <i class="fas fa-bell fa-3x text-primary"></i>
                </div>
                <h4 class="card-title">Alertes par email</h4>
                <p class="card-text">Recevez des notifications pour vos retours et les nouvelles acquisitions.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Charger les statistiques
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour charger les statistiques
    function loadStats() {
        fetch('api/get_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stats-livres').textContent = data.stats.livres;
                    document.getElementById('stats-cd').textContent = data.stats.cd;
                    document.getElementById('stats-dvd').textContent = data.stats.dvd;
                    document.getElementById('stats-adherents').textContent = data.stats.adherents;
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
    
    // Fonction pour charger les nouveautés
    function loadNouveautes() {
        fetch('api/get_nouveautes.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('nouveautes-container');
                if (!data.success || data.documents.length === 0) {
                    container.innerHTML = '<p class="text-center">Aucune nouveauté pour le moment.</p>';
                    return;
                }
                
                let html = '';
                data.documents.forEach(doc => {
                    html += `
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card document-card h-100">
                            <div class="position-relative">
                                <img src="${doc.image_url || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300'}" 
                                     class="card-img-top" 
                                     alt="${doc.titre}"
                                     style="height: 150px; object-fit: cover;">
                                <span class="badge ${doc.disponible && doc.quantite_disponible > 0 ? 'bg-success' : 'bg-danger'} availability-badge">
                                    ${doc.disponible && doc.quantite_disponible > 0 ? 'Disponible' : 'Indisponible'}
                                </span>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title" style="font-size: 0.9rem;">${doc.titre}</h6>
                                <p class="card-text text-muted" style="font-size: 0.8rem;">
                                    ${doc.auteur || 'Auteur inconnu'}
                                </p>
                                <small class="text-muted">${doc.type}</small>
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('nouveautes-container').innerHTML = 
                    '<p class="text-center text-danger">Erreur lors du chargement des nouveautés.</p>';
            });
    }
    
    // Charger les données
    loadStats();
    loadNouveautes();
});
</script>

<?php require_once 'includes/footer.php'; ?>
