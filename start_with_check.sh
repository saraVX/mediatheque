#!/bin/bash
echo "🚀 DÉMARRAGE APPLICATION MÉDIATHÈQUE"
echo "====================================="

# Vérifier MySQL
echo "1. VÉRIFICATION MYSQL..."
if ! systemctl is-active --quiet mysql 2>/dev/null; then
    echo "⚠️  MySQL n'est pas démarré"
    echo "   Tentative de démarrage..."
    sudo systemctl start mysql 2>/dev/null || echo "❌ Impossible de démarrer MySQL"
fi

# Vérifier la connexion
echo "2. TEST CONNEXION DB..."
mysql -u root -psara -e "SELECT 1" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "❌ Connexion MySQL échouée"
    echo "   Essayez manuellement: mysql -u root -psara"
    echo ""
    echo "📋 Solutions possibles:"
    echo "   1. Vérifier le mot de passe: essayez 'sara'"
    echo "   2. Créer la base: CREATE DATABASE mediatheque_db;"
    echo "   3. Redémarrer MySQL: sudo systemctl restart mysql"
    echo ""
    read -p "Continuer quand même? (o/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Oo]$ ]]; then
        exit 1
    fi
else
    echo "✅ MySQL accessible"
    
    # Vérifier/Créer la base
    echo "3. VÉRIFICATION BASE DE DONNÉES..."
    mysql -u root -psara -e "CREATE DATABASE IF NOT EXISTS mediatheque_db;" 2>/dev/null
    echo "✅ Base mediatheque_db prête"
fi

# Arrêter les anciens processus
echo "4. NETTOYAGE PROCESSUS..."
pkill -f "php -S" 2>/dev/null

# Démarrer le serveur
echo "5. DÉMARRAGE SERVEUR PHP..."
cd ~/mediatheque_project/public

# Créer un lien symbolique pour catalogue_fixed.php
ln -sf catalogue_fixed.php catalogue.php 2>/dev/null

php -S 0.0.0.0:8080 > server.log 2>&1 &
SERVER_PID=$!

sleep 3

if ps -p $SERVER_PID > /dev/null; then
    echo "✅ SERVEUR DÉMARRÉ SUR LE PORT 8080"
    echo ""
    echo "🌐 URL D'ACCÈS :"
    echo "   http://localhost:8080/"
    echo ""
    echo "🔧 PAGES DE TEST :"
    echo "   http://localhost:8080/test_db.php  - Test base de données"
    echo "   http://localhost:8080/catalogue_fixed.php - Catalogue"
    echo ""
    echo "🔑 COMPTES TEST :"
    echo "   Admin: admin@mediatheque.fr / admin123"
    echo "   Adhérent: jean.martin@email.com / admin123"
    echo ""
    echo "📋 PAGES PRINCIPALES :"
    echo "   /index.php          - Accueil"
    echo "   /login.php          - Connexion"
    echo "   /admin_panel.php    - Panel admin"
    echo "   /profil.php         - Profil utilisateur"
    echo ""
    echo "📝 LOGS : tail -f server.log"
    echo "🛑 POUR ARRÊTER : pkill -f 'php -S'"
else
    echo "❌ ERREUR LORS DU DÉMARRAGE"
    echo "Vérifiez les logs : cat server.log"
fi
