const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const { testConnection } = require('./config/database');
const { swaggerUi, specs } = require('./swagger');

// Import des routes
const authRoutes = require('./routes/auth');
const clientRoutes = require('./routes/clients');
const commandeRoutes = require('./routes/commandes');
const visiteRoutes = require('./routes/visites');

// Import des middlewares
const { authenticateToken } = require('./middleware/auth');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware de sécurité
app.use(helmet());

// Middleware CORS
app.use(cors({
  origin: process.env.CORS_ORIGIN || 'http://localhost:3000',
  credentials: true
}));

// Middleware de logging
app.use(morgan('combined'));

// Middleware pour parser le JSON
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Route de test
app.get('/', (req, res) => {
  res.json({
    message: '🚀 Serveur Express CRM - API Ready!',
    timestamp: new Date().toISOString(),
    version: '1.0.0'
  });
});

// Route de test de la base de données
app.get('/api/health', async (req, res) => {
  try {
    const isConnected = await testConnection();
    res.json({
      status: isConnected ? 'healthy' : 'unhealthy',
      database: isConnected ? 'connected' : 'disconnected',
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    res.status(500).json({
      status: 'error',
      message: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

// Route pour tester une requête simple
app.get('/api/test-db', async (req, res) => {
  try {
    const { query } = require('./config/database');
    const result = await query('SELECT 1 as test');
    res.json({
      success: true,
      data: result,
      message: 'Connexion à la base de données fonctionnelle'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message,
      message: 'Erreur de connexion à la base de données'
    });
  }
});

// Documentation Swagger
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(specs, {
  explorer: true,
  customCss: '.swagger-ui .topbar { display: none }',
  customSiteTitle: 'API CRM - Documentation'
}));

// Routes API
app.use('/api/auth', authRoutes);
app.use('/api/clients', authenticateToken, clientRoutes);
app.use('/api/commandes', authenticateToken, commandeRoutes);
app.use('/api/visites', authenticateToken, visiteRoutes);

// Gestion des erreurs 404
app.use((req, res) => {
  res.status(404).json({
    error: 'Route non trouvée',
    path: req.originalUrl,
    method: req.method,
    timestamp: new Date().toISOString()
  });
});

// Gestionnaire d'erreurs global
app.use((error, req, res, next) => {
  console.error('Erreur serveur:', error);
  res.status(500).json({
    error: 'Erreur interne du serveur',
    message: process.env.NODE_ENV === 'development' ? error.message : 'Une erreur est survenue',
    timestamp: new Date().toISOString()
  });
});

// Démarrage du serveur
app.listen(PORT, async () => {
  console.log(`🚀 Serveur Express démarré sur le port ${PORT}`);
  console.log(`📊 Environnement: ${process.env.NODE_ENV || 'development'}`);
  console.log(`🌐 URL: http://localhost:${PORT}`);
  
  // Test de la connexion à la base de données
  await testConnection();
});

// Gestion gracieuse de l'arrêt
process.on('SIGTERM', () => {
  console.log('🛑 Arrêt du serveur...');
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('🛑 Arrêt du serveur...');
  process.exit(0);
});
