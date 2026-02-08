#!/bin/bash
echo "🚀 Démarrage de l'Application Médiathèque Complète"
echo "===================================================="

# Arrêter les anciens processus
echo "1. Nettoyage des processus..."
pkill -f "php -S" 2>/dev/null

# Démarrer MySQL si nécessaire
echo "2. Vérification de MySQL..."
sudo systemctl start mysql 2>/dev/null || echo "MySQL déjà démarré ou non installé"

# Aller dans le dossier
cd ~/mediatheque_project/public

# Démarrer le serveur
echo "3. Démarrage du serveur PHP..."
echo "   Port: 8080"
echo "   URL: http://localhost:8080/app.php"
echo ""

php -S 0.0.0.0:8080 &
SERVER_PID=$!

sleep 2

if ps -p $SERVER_PID > /dev/null; then
    echo "✅ Serveur démarré avec succès!"
    echo ""
    echo "🌐 ACCÈS :"
    echo "   http://localhost:8080/app.php"
    echo "   http://127.0.0.1:8080/app.php"
    echo ""
    echo "🔑 COMPTES DE TEST :"
    echo "   1. Email: admin@mediatheque.fr"
    echo "      Mot de passe: admin123"
    echo "      Rôle: Administrateur"
    echo ""
    echo "   2. Email: jean@email.com"
    echo "      Mot de passe: password123"
    echo "      Rôle: Adhérent"
    echo ""
    echo "📱 FONCTIONNALITÉS :"
    echo "   • Tableau de bord interactif"
    echo "   • Catalogue de documents"
    echo "   • Gestion des emprunts"
    echo "   • Profil utilisateur"
    echo "   • Interface responsive"
    echo "   • Design moderne"
    echo ""
    echo "⚠️  Pour arrêter: pkill -f 'php -S'"
else
    echo "❌ Erreur lors du démarrage"
    echo "Essaie sur le port 8000..."
    php -S localhost:8000 &
    echo "🌐 Essaie: http://localhost:8000/app.php"
fi
