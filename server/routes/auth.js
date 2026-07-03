const express = require('express');
const router = express.Router();
const { query } = require('../config/database');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');

// Configuration JWT
const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '30d';

/**
 * @swagger
 * /api/auth/login:
 *   post:
 *     summary: Authentification utilisateur
 *     tags: [Authentification]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - login
 *               - pass
 *             properties:
 *               login:
 *                 type: string
 *                 description: Nom d'utilisateur
 *                 example: "utilisateur"
 *               pass:
 *                 type: string
 *                 description: Mot de passe
 *                 example: "motdepasse"
 *               device:
 *                 type: string
 *                 description: Informations sur l'appareil (optionnel)
 *                 example: "iPhone 12"
 *     responses:
 *       200:
 *         description: Authentification réussie
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 token:
 *                   type: string
 *                   description: Token JWT
 *                 user:
 *                   $ref: '#/components/schemas/User'
 *       400:
 *         description: Données manquantes
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/Error'
 *       401:
 *         description: Identifiants erronés
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/Error'
 */
router.post('/login', async (req, res) => {
  try {
    const { login, pass, device } = req.body;

    if (!login || !pass) {
      return res.status(400).json({
        error: true,
        errorMsg: 'Identifiants manquants'
      });
    }

    // Recherche de l'utilisateur (simulation de la logique LDAP)
    const users = await query(
      'SELECT * FROM user WHERE login = ? AND deleted = 0',
      [login]
    );

    if (users.length === 0) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Identifiants erronés'
      });
    }

    const user = users[0];
    
    // Vérification du mot de passe (à adapter selon votre système)
    // Pour l'instant, on simule la vérification
    const isValidPassword = true; // À remplacer par la vraie vérification

    if (!isValidPassword) {
      return res.status(401).json({
        error: true,
        errorMsg: 'Identifiants erronés'
      });
    }

    // Génération du token
    const token = jwt.sign(
      { 
        userId: user.id,
        login: user.login,
        id_profile: user.id_profile
      },
      JWT_SECRET,
      { expiresIn: JWT_EXPIRES_IN }
    );

    // Mise à jour du token en base
    await query(
      'UPDATE user SET token = ?, api_query = NOW() WHERE id = ?',
      [token, user.id]
    );

    // Récupération des profils de sécurité
    let profiles = [];
    let userProfiles = [];
    
    try {
      profiles = await query('SELECT * FROM secu_profile WHERE deleted = 0');
      userProfiles = profiles.filter(p => 
        p.id === user.id_profile || user.id_profile === 2
      );
    } catch (error) {
      console.log('Table secu_profile non trouvée, utilisation de profils par défaut');
      // Si la table n'existe pas, on utilise des profils par défaut
      profiles = [
        { id: 1, libelle: 'Administrateur' },
        { id: 2, libelle: 'Commercial' }
      ];
      userProfiles = profiles.filter(p => 
        p.id === user.id_profile || user.id_profile === 2
      );
    }

    const response = {
      token,
      user: {
        id: user.id,
        login: user.login,
        nom: user.nom,
        prenom: user.prenom,
        mail: user.mail,
        id_profile: user.id_profile,
        id_repr: user.id_repr,
        security: {
          profiles: profiles,
          user: userProfiles.map(p => p.id)
        }
      }
    };

    res.json(response);

  } catch (error) {
    console.error('Erreur authentification:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur serveur lors de l\'authentification'
    });
  }
});

/**
 * @swagger
 * /api/auth/check-token:
 *   post:
 *     summary: Vérification de la validité du token
 *     tags: [Authentification]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - token
 *             properties:
 *               token:
 *                 type: string
 *                 description: Token JWT à vérifier
 *     responses:
 *       200:
 *         description: Token valide ou expiré
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 expire:
 *                   type: boolean
 *                   description: true si le token est expiré
 *                 user:
 *                   $ref: '#/components/schemas/User'
 *                   description: Informations utilisateur (si token valide)
 */
router.post('/check-token', async (req, res) => {
  try {
    const { token } = req.body;

    if (!token) {
      return res.status(400).json({
        error: true,
        errorMsg: 'Token manquant'
      });
    }

    // Vérification du token
    const decoded = jwt.verify(token, JWT_SECRET);
    
    // Vérification en base
    const users = await query(
      'SELECT * FROM user WHERE id = ? AND token = ? AND deleted = 0',
      [decoded.userId, token]
    );

    if (users.length === 0) {
      return res.json({ expire: true });
    }

    const user = users[0];

    res.json({
      expire: false,
      user: {
        id: user.id,
        login: user.login,
        nom: user.nom,
        prenom: user.prenom,
        mail: user.mail,
        id_profile: user.id_profile,
        id_repr: user.id_repr
      }
    });

  } catch (error) {
    console.error('Erreur vérification token:', error);
    res.json({ expire: true });
  }
});

/**
 * @swagger
 * /api/auth/logout:
 *   post:
 *     summary: Déconnexion utilisateur
 *     tags: [Authentification]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required:
 *               - token
 *             properties:
 *               token:
 *                 type: string
 *                 description: Token JWT à invalider
 *     responses:
 *       200:
 *         description: Déconnexion réussie
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                 message:
 *                   type: string
 */
router.post('/logout', async (req, res) => {
  try {
    const { token } = req.body;

    if (token) {
      await query(
        'UPDATE user SET token = NULL WHERE token = ?',
        [token]
      );
    }

    res.json({ success: true, message: 'Déconnexion réussie' });

  } catch (error) {
    console.error('Erreur déconnexion:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la déconnexion'
    });
  }
});

module.exports = router;
