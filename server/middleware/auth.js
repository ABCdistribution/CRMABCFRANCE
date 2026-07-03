const jwt = require('jsonwebtoken');
const { query } = require('../config/database');

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';

/**
 * Middleware d'authentification JWT
 */
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Token d\'accès requis'
      });
    }

    // Vérification du token JWT
    const decoded = jwt.verify(token, JWT_SECRET);

    // Vérification en base de données
    const users = await query(
      'SELECT * FROM user WHERE id = ? AND deleted = 0',
      [decoded.userId]
    );

    if (users.length === 0) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Utilisateur non trouvé'
      });
    }

    // Vérification optionnelle du token en base (si la colonne existe)
    if (users[0].token && users[0].token !== token) {
      console.log('Token en base différent du token envoyé, mais on continue...');
    }

    // Ajout de l'utilisateur à la requête
    req.user = users[0];
    next();

  } catch (error) {
    console.error('Erreur authentification:', error);
    
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        error: true,
        errorMsg: 'Token invalide'
      });
    }
    
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        error: true,
        errorMsg: 'Token expiré'
      });
    }

    return res.status(500).json({
      error: true,
      errorMsg: 'Erreur serveur lors de l\'authentification'
    });
  }
};

/**
 * Middleware d'authentification optionnelle
 * (pour les routes qui peuvent fonctionner avec ou sans authentification)
 */
const optionalAuth = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (token) {
      const decoded = jwt.verify(token, JWT_SECRET);
      const users = await query(
        'SELECT * FROM user WHERE id = ? AND token = ? AND deleted = 0',
        [decoded.userId, token]
      );

      if (users.length > 0) {
        req.user = users[0];
      }
    }

    next();
  } catch (error) {
    // En cas d'erreur, on continue sans authentification
    next();
  }
};

/**
 * Middleware de vérification des permissions
 */
const checkPermission = (requiredProfile) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Authentification requise'
      });
    }

    // Vérification du profil utilisateur
    if (req.user.id_profile !== requiredProfile && req.user.id_profile !== 2) {
      return res.status(403).json({
        error: true,
        errorMsg: 'Permissions insuffisantes'
      });
    }

    next();
  };
};

/**
 * Middleware de vérification des permissions multiples
 */
const checkAnyPermission = (requiredProfiles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Authentification requise'
      });
    }

    // Admin a tous les droits
    if (req.user.id_profile === 2) {
      return next();
    }

    // Vérification si l'utilisateur a au moins un des profils requis
    if (!requiredProfiles.includes(req.user.id_profile)) {
      return res.status(403).json({
        error: true,
        errorMsg: 'Permissions insuffisantes'
      });
    }

    next();
  };
};

module.exports = {
  authenticateToken,
  optionalAuth,
  checkPermission,
  checkAnyPermission
};
