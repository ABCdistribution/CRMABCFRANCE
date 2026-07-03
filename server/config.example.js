// Configuration d'exemple pour le serveur
// Copiez ce fichier vers config.js et adaptez les valeurs

module.exports = {
  // Base de données (même que le CRM PHP)
  database: {
    host: 'localhost',
    user: 'abcdistribution',
    password: 'i_89UYT_Op0#qsd',
    database: 'gescom',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
  },

  // Serveur
  server: {
    port: process.env.PORT || 3001,
    environment: process.env.NODE_ENV || 'development'
  },

  // JWT
  jwt: {
    secret: process.env.JWT_SECRET || 'your-super-secret-jwt-key-change-this-in-production',
    expiresIn: process.env.JWT_EXPIRES_IN || '30d'
  },

  // CORS
  cors: {
    origin: process.env.CORS_ORIGIN || 'http://localhost:3000',
    credentials: true
  },

  // Upload
  upload: {
    maxFileSize: '10mb',
    allowedTypes: ['image/jpeg', 'image/png', 'image/gif']
  }
};
