# 📚 Documentation API CRM - Node.js

## 🚀 Vue d'ensemble

Cette API Node.js est une refonte complète de votre API PHP existante, utilisant la même base de données MySQL. Elle fournit des endpoints RESTful pour gérer les clients, commandes, visites et l'authentification.

## 🔐 Authentification

L'API utilise JWT (JSON Web Tokens) pour l'authentification. Tous les endpoints (sauf `/api/auth/*`) nécessitent un token valide dans l'en-tête `Authorization: Bearer <token>`.

## 📋 Endpoints disponibles

### 🔑 Authentification

#### POST `/api/auth/login`
Authentification utilisateur
```json
{
  "login": "utilisateur",
  "pass": "motdepasse",
  "device": "optional_device_info"
}
```

**Réponse :**
```json
{
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "login": "utilisateur",
    "nom": "Nom",
    "prenom": "Prénom",
    "mail": "email@example.com",
    "id_profile": 1,
    "id_repr": 123,
    "security": {
      "profiles": [...],
      "user": [1, 2]
    }
  }
}
```

#### POST `/api/auth/check-token`
Vérification de la validité du token
```json
{
  "token": "jwt_token_here"
}
```

#### POST `/api/auth/logout`
Déconnexion utilisateur

### 👥 Clients

#### GET `/api/clients`
Récupération de la liste des clients
**Query params :** `limit`, `offset`

#### GET `/api/clients/:id`
Récupération des détails d'un client
**Réponse :** Client avec contacts, centrales et visites récentes

#### GET `/api/clients/:id/stats`
Statistiques d'un client (visites, commandes, dernière visite)

#### POST `/api/clients/:id/contacts`
Ajout d'un contact client
```json
{
  "nom": "Nom du contact",
  "prenom": "Prénom",
  "fonction": "Fonction",
  "telephone": "0123456789",
  "mail": "contact@example.com"
}
```

#### PUT `/api/clients/:id/contacts/:contactId`
Mise à jour d'un contact

#### DELETE `/api/clients/:id/contacts/:contactId`
Suppression d'un contact

### 📦 Commandes

#### GET `/api/commandes`
Récupération de l'historique des commandes
**Query params :** `limit`, `offset`, `client_id`

#### GET `/api/commandes/:id`
Récupération des détails d'une commande avec ses lignes

#### POST `/api/commandes`
Création d'une nouvelle commande
```json
{
  "id_client": "CLIENT123",
  "date_livraison": "2025-09-10",
  "commentaire": "Commentaire optionnel",
  "lignes": [
    {
      "id_produit": 1,
      "quantite": 10
    }
  ]
}
```

#### PUT `/api/commandes/:id/status`
Mise à jour du statut d'une commande
```json
{
  "statut": "CONFIRMEE"
}
```
**Statuts valides :** `EN_ATTENTE`, `CONFIRMEE`, `EN_PREPARATION`, `LIVREE`, `ANNULEE`

#### GET `/api/commandes/juva`
Récupération des commandes JUVA

### 🏢 Visites

#### GET `/api/visites/client/:clientId`
Récupération des visites d'un client
**Query params :** `limit`, `offset`

#### GET `/api/visites/:id/photos`
Récupération des photos d'une visite

#### POST `/api/visites`
Création d'une nouvelle visite
```json
{
  "id_client": "CLIENT123",
  "id_commande": "CMD123",
  "no_cmd": "Numéro commande",
  "pmc_state": "État PMC",
  "pmc_coms": "Commentaires PMC",
  "pem": true,
  "alerte_raison": ["Raison1", "Raison2"],
  "alerte_obs": "Observations"
}
```

#### POST `/api/visites/:id/photos`
Upload d'une photo de visite
```json
{
  "photo": "base64_encoded_image",
  "name": "photo_name"
}
```

#### GET `/api/visites/futures`
Récupération des futures visites (planning)

## 🔧 Configuration

### Variables d'environnement
```bash
DB_HOST=localhost
DB_USER=abcdistribution
DB_PASS=i_89UYT_Op0#qsd
DB_NAME=gescom
PORT=3001
JWT_SECRET=your-secret-key
JWT_EXPIRES_IN=30d
```

### Structure des réponses

#### Succès
```json
{
  "success": true,
  "data": {...},
  "message": "Message optionnel"
}
```

#### Erreur
```json
{
  "error": true,
  "errorMsg": "Description de l'erreur"
}
```

#### Pagination
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 100,
    "limit": 20,
    "offset": 0,
    "hasMore": true
  }
}
```

## 🛡️ Sécurité

- **JWT Authentication** : Tous les endpoints protégés
- **Helmet.js** : En-têtes de sécurité
- **CORS** : Configuration des origines autorisées
- **Validation** : Validation des données d'entrée
- **Permissions** : Vérification des profils utilisateur

## 📊 Codes de statut HTTP

- `200` : Succès
- `201` : Création réussie
- `400` : Données invalides
- `401` : Non authentifié
- `403` : Permissions insuffisantes
- `404` : Ressource non trouvée
- `500` : Erreur serveur

## 🚀 Démarrage

```bash
cd server
npm install
npm start          # Production
npm run dev        # Développement avec nodemon
```

## 📝 Notes importantes

1. **Base de données** : Utilise la même base que votre CRM PHP
2. **Compatibilité** : Structure compatible avec votre application mobile
3. **Performance** : Pool de connexions MySQL optimisé
4. **Logs** : Logging complet avec Morgan
5. **Validation** : Validation robuste des données d'entrée

## 🔄 Migration depuis l'API PHP

L'API Node.js est conçue pour être un remplacement direct de votre API PHP :
- Mêmes endpoints (adaptés en REST)
- Même logique métier
- Même base de données
- Compatible avec vos applications existantes
