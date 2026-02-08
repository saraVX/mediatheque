#!/bin/bash
echo "🚀 Démarrage de l'application Médiathèque"
echo "========================================="

# Vérifier la base de données
echo "1. Vérification de la base de données..."
mysql -u root -psara -e "USE mediatheque_db; SHOW TABLES;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "❌ Base de données inaccessible"
    echo "   Essayez: mysql -u root -psara"
    echo "   Puis: CREATE DATABASE IF NOT EXISTS mediatheque_db;"
    exit 1
else
    echo "✅ Base de données accessible"
fi

# Arrêter les anciens processus
echo "2. Nettoyage des anciens processus..."
pkill -f "php -S" 2>/dev/null

# Démarrer le serveur
echo "3. Démarrage du serveur PHP..."
cd public
php -S 0.0.0.0:8080 > server.log 2>&1 &
SERVER_PID=$!

sleep 3

if ps -p $SERVER_PID > /dev/null; then
    echo "✅ Serveur démarré sur le port 8080 (PID: $SERVER_PID)"
    echo ""
    echo "🌐 URL d'accès :"
    echo "   http://localhost:8080/"
    echo ""
    echo "🔑 Comptes de test :"
    echo "   Admin: admin@mediatheque.fr / admin123"
    echo "   Adhérent: jean.martin@email.com / admin123"
    echo ""
    echo "📋 Pages principales :"
    echo "   /index.php          - Accueil"
    echo "   /login.php          - Connexion"
    echo "   /admin_panel.php    - Panel admin (admin seulement)"
    echo "   /profil.php         - Profil utilisateur"
    echo ""
    echo "📝 Logs : tail -f server.log"
    echo "🛑 Pour arrêter : pkill -f 'php -S'"
else
    echo "❌ Erreur lors du démarrage du serveur"
    echo "Vérifiez les logs : cat server.log"
fi
