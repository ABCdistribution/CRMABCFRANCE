#!/bin/bash

# Script de démarrage rapide pour tester l'API CRM
# Usage: ./start-testing.sh [swagger|postman|insomnia|test]

echo "🚀 Démarrage des outils de test de l'API CRM"
echo "=============================================="

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les options
show_help() {
    echo -e "${BLUE}Options disponibles:${NC}"
    echo "  swagger    - Ouvrir Swagger UI (http://localhost:3001/api-docs)"
    echo "  postman    - Instructions pour importer la collection Postman"
    echo "  insomnia   - Instructions pour importer la collection Insomnia"
    echo "  test       - Exécuter les tests en ligne de commande"
    echo "  all        - Afficher toutes les options"
    echo ""
    echo -e "${YELLOW}Usage:${NC}"
    echo "  ./start-testing.sh swagger"
    echo "  ./start-testing.sh test"
    echo "  ./start-testing.sh all"
}

# Vérifier si le serveur est démarré
check_server() {
    echo -e "${BLUE}🔍 Vérification du serveur...${NC}"
    if curl -s http://localhost:3001/api/health > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Serveur démarré et accessible${NC}"
        return 0
    else
        echo -e "${RED}❌ Serveur non accessible sur http://localhost:3001${NC}"
        echo -e "${YELLOW}💡 Démarrez le serveur avec: npm start${NC}"
        return 1
    fi
}

# Ouvrir Swagger UI
open_swagger() {
    echo -e "${BLUE}📚 Ouverture de Swagger UI...${NC}"
    if check_server; then
        echo -e "${GREEN}🌐 Swagger UI disponible sur: http://localhost:3001/api-docs${NC}"
        echo -e "${YELLOW}💡 Ouvrez cette URL dans votre navigateur${NC}"
        
        # Essayer d'ouvrir automatiquement (Linux)
        if command -v xdg-open > /dev/null; then
            xdg-open http://localhost:3001/api-docs
        elif command -v open > /dev/null; then
            open http://localhost:3001/api-docs
        fi
    fi
}

# Instructions Postman
show_postman() {
    echo -e "${BLUE}📮 Instructions pour Postman:${NC}"
    echo "1. Ouvrez Postman"
    echo "2. Cliquez sur 'Import'"
    echo "3. Sélectionnez le fichier: $(pwd)/postman-collection.json"
    echo "4. La collection 'API CRM - Gescom' sera importée"
    echo "5. Configurez la variable 'baseUrl' si nécessaire"
    echo "6. Exécutez le test 'Login' pour obtenir un token"
    echo ""
    echo -e "${GREEN}📁 Fichier de collection: $(pwd)/postman-collection.json${NC}"
}

# Instructions Insomnia
show_insomnia() {
    echo -e "${BLUE}💤 Instructions pour Insomnia:${NC}"
    echo "1. Ouvrez Insomnia"
    echo "2. Cliquez sur 'Import'"
    echo "3. Sélectionnez le fichier: $(pwd)/insomnia-collection.json"
    echo "4. La collection 'API CRM - Gescom' sera importée"
    echo "5. Configurez l'environnement 'Base Environment'"
    echo "6. Testez les endpoints"
    echo ""
    echo -e "${GREEN}📁 Fichier de collection: $(pwd)/insomnia-collection.json${NC}"
}

# Exécuter les tests
run_tests() {
    echo -e "${BLUE}🧪 Exécution des tests...${NC}"
    if check_server; then
        echo ""
        echo -e "${YELLOW}Test basique:${NC}"
        node test-api.js
        echo ""
        echo -e "${YELLOW}Test avancé:${NC}"
        node test-api-advanced.js
    fi
}

# Afficher toutes les options
show_all() {
    echo -e "${BLUE}🛠️ Tous les outils de test disponibles:${NC}"
    echo ""
    
    echo -e "${GREEN}1. 📚 Swagger UI (Recommandé)${NC}"
    echo "   URL: http://localhost:3001/api-docs"
    echo "   Interface web interactive pour tester l'API"
    echo ""
    
    echo -e "${GREEN}2. 📮 Postman${NC}"
    echo "   Collection: postman-collection.json"
    echo "   Client desktop pour tester les APIs"
    echo ""
    
    echo -e "${GREEN}3. 💤 Insomnia${NC}"
    echo "   Collection: insomnia-collection.json"
    echo "   Alternative moderne à Postman"
    echo ""
    
    echo -e "${GREEN}4. 🖥️ Tests en ligne de commande${NC}"
    echo "   Scripts: test-api.js, test-api-advanced.js"
    echo "   Tests automatisés avec statistiques"
    echo ""
    
    echo -e "${GREEN}5. 📖 Documentation${NC}"
    echo "   Guide: TESTING_GUIDE.md"
    echo "   Documentation complète des tests"
    echo ""
    
    echo -e "${YELLOW}💡 Pour utiliser un outil spécifique:${NC}"
    echo "   ./start-testing.sh swagger"
    echo "   ./start-testing.sh postman"
    echo "   ./start-testing.sh insomnia"
    echo "   ./start-testing.sh test"
}

# Point d'entrée principal
case "${1:-all}" in
    "swagger")
        open_swagger
        ;;
    "postman")
        show_postman
        ;;
    "insomnia")
        show_insomnia
        ;;
    "test")
        run_tests
        ;;
    "all"|"")
        show_all
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        echo -e "${RED}❌ Option inconnue: $1${NC}"
        show_help
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}🎉 Prêt à tester votre API !${NC}"





