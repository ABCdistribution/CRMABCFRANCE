# 🧪 Guide de test de l'API CRM

Ce guide vous explique comment tester votre API CRM avec différents outils et méthodes.

## 🚀 Démarrage rapide

### 1. Démarrer le serveur
```bash
cd server
npm install
npm start
# ou pour le développement
npm run dev
```

### 2. Vérifier que l'API fonctionne
```bash
curl http://localhost:3001/api/health
```

## 🛠️ Outils de test disponibles

### 1. 📚 Swagger UI (Recommandé)

**URL :** http://localhost:3001/api-docs

Swagger UI fournit une interface web interactive pour tester tous vos endpoints.

**Avantages :**
- Interface graphique intuitive
- Documentation automatique
- Test direct dans le navigateur
- Gestion de l'authentification JWT
- Exemples de requêtes

**Comment utiliser :**
1. Ouvrez http://localhost:3001/api-docs
2. Cliquez sur "Authorize" en haut à droite
3. Entrez votre token JWT (obtenu via `/api/auth/login`)
4. Testez les endpoints directement

### 2. 🚀 Postman

**Fichier :** `postman-collection.json`

**Avantages :**
- Collection complète pré-configurée
- Variables d'environnement
- Tests automatisés
- Sauvegarde des tokens automatiquement

**Comment utiliser :**
1. Ouvrez Postman
2. Importez le fichier `postman-collection.json`
3. Configurez la variable `baseUrl` si nécessaire
4. Exécutez le test "Login" pour obtenir un token
5. Les autres requêtes utiliseront automatiquement le token

### 3. 💤 Insomnia

**Fichier :** `insomnia-collection.json`

**Avantages :**
- Interface moderne et rapide
- Gestion des environnements
- Variables dynamiques
- Tests de performance

**Comment utiliser :**
1. Ouvrez Insomnia
2. Importez le fichier `insomnia-collection.json`
3. Configurez l'environnement "Base Environment"
4. Testez les endpoints

### 4. 🖥️ Scripts de test en ligne de commande

#### Test basique
```bash
node test-api.js
```

#### Test avancé (avec couleurs et statistiques)
```bash
node test-api-advanced.js
```

#### Test d'un endpoint spécifique
```bash
node test-api-advanced.js --endpoint=health --verbose
```

## 📋 Endpoints disponibles

### 🔐 Authentification
- `POST /api/auth/login` - Connexion
- `POST /api/auth/check-token` - Vérification du token
- `POST /api/auth/logout` - Déconnexion

### 🏥 Santé du système
- `GET /api/health` - État du serveur
- `GET /api/test-db` - Test de la base de données

### 👥 Clients
- `GET /api/clients` - Liste des clients
- `GET /api/clients/:id` - Détails d'un client
- `GET /api/clients/:id/stats` - Statistiques client
- `POST /api/clients/:id/contacts` - Ajouter un contact

### 📦 Commandes
- `GET /api/commandes` - Liste des commandes
- `GET /api/commandes/:id` - Détails d'une commande
- `POST /api/commandes` - Créer une commande
- `PUT /api/commandes/:id/status` - Mettre à jour le statut

### 🏢 Visites
- `GET /api/visites/client/:clientId` - Visites d'un client
- `GET /api/visites/futures` - Futures visites
- `POST /api/visites` - Créer une visite

## 🔧 Configuration

### Variables d'environnement
```bash
# .env
DB_HOST=localhost
DB_USER=your_user
DB_PASS=your_password
DB_NAME=gescom
PORT=3001
JWT_SECRET=your-secret-key
JWT_EXPIRES_IN=30d
CORS_ORIGIN=http://localhost:3000
```

### Authentification
Tous les endpoints (sauf `/api/auth/*`) nécessitent un token JWT dans l'en-tête :
```
Authorization: Bearer <your-jwt-token>
```

## 🧪 Tests automatisés

### Exécuter tous les tests
```bash
# Test basique
node test-api.js

# Test avancé avec statistiques
node test-api-advanced.js
```

### Tests spécifiques
```bash
# Test de santé uniquement
node test-api-advanced.js --endpoint=health

# Mode verbeux
node test-api-advanced.js --verbose
```

## 📊 Monitoring et logs

### Logs du serveur
Les logs sont affichés dans la console avec Morgan :
- Requêtes HTTP
- Erreurs
- Temps de réponse

### Métriques de performance
Le script de test avancé inclut :
- Tests de charge
- Temps de réponse
- Taux de réussite

## 🐛 Dépannage

### Problèmes courants

1. **Erreur 401 Unauthorized**
   - Vérifiez que vous avez un token valide
   - Le token peut être expiré

2. **Erreur 500 Internal Server Error**
   - Vérifiez la connexion à la base de données
   - Consultez les logs du serveur

3. **Erreur de connexion**
   - Vérifiez que le serveur est démarré
   - Vérifiez l'URL de base

### Debug
```bash
# Mode debug avec plus de détails
DEBUG=* node test-api-advanced.js

# Vérifier l'état du serveur
curl -v http://localhost:3001/api/health
```

## 📈 Améliorations futures

- [ ] Tests d'intégration avec Jest
- [ ] Tests de charge avec Artillery
- [ ] Monitoring avec Prometheus
- [ ] Documentation OpenAPI complète
- [ ] Tests de sécurité automatisés

## 🤝 Contribution

Pour ajouter de nouveaux tests :
1. Modifiez `test-api-advanced.js`
2. Ajoutez la documentation Swagger
3. Mettez à jour les collections Postman/Insomnia
4. Testez vos modifications

---

**Note :** Ce guide est mis à jour régulièrement. Consultez la documentation Swagger pour les détails les plus récents des endpoints.





