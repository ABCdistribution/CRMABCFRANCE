# 🚀 Serveur Express CRM

Serveur API Express.js pour le CRM, connecté à la même base de données que le système PHP existant.

## 📋 Prérequis

- Node.js (version 16+)
- MySQL (base de données `gescom`)
- Accès à la base de données avec les identifiants du CRM

## 🛠️ Installation

```bash
cd server
npm install
```

## 🚀 Démarrage

### Mode développement (avec rechargement automatique)
```bash
npm run dev
```

### Mode production
```bash
npm start
```

Le serveur sera accessible sur `http://localhost:3001`

## 📊 Endpoints disponibles

### Test de base
- `GET /` - Page d'accueil de l'API
- `GET /api/health` - Vérification de l'état du serveur et de la BDD
- `GET /api/test-db` - Test de connexion à la base de données

## 🗄️ Configuration de la base de données

Le serveur utilise la même configuration que le CRM PHP :
- **Host**: localhost
- **Database**: gescom
- **User**: abcdistribution
- **Password**: i_89UYT_Op0#qsd

## 🔧 Structure du projet

```
server/
├── config/
│   └── database.js    # Configuration de la base de données
├── index.js           # Serveur principal
├── package.json       # Dépendances et scripts
└── README.md         # Documentation
```

## 🛡️ Sécurité

- Helmet.js pour les en-têtes de sécurité
- CORS configuré
- Validation des entrées
- Gestion des erreurs

## 📝 Prochaines étapes

- [ ] Créer les routes pour les clients
- [ ] Créer les routes pour les commandes
- [ ] Ajouter l'authentification
- [ ] Créer les middlewares de validation
- [ ] Ajouter la documentation API (Swagger)
