const express = require('express');
const router = express.Router();
const { query } = require('../config/database');

/**
 * Récupération des visites d'un client
 * GET /api/visites/client/:clientId
 */
router.get('/client/:clientId', async (req, res) => {
  try {
    const clientId = req.params.clientId;
    const userId = req.user.id;
    const { limit = 20, offset = 0 } = req.query;

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id_as400 FROM ref_client 
      WHERE id_as400 = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [clientId, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    const visites = await query(`
      SELECT 
        v.id,
        v.id_visite,
        v.id_client,
        v.date_creation,
        v.pem,
        v.alerte_raison,
        v.alerte_obs,
        v.pmc_state,
        v.pmc_coms,
        v.dn_abc,
        v.dn_concurence,
        u.nom as user_nom,
        u.prenom as user_prenom
      FROM visite v
      LEFT JOIN user u ON v.id_user = u.id
      WHERE v.id_client = ? AND v.deleted = 0
      ORDER BY v.date_creation DESC
      LIMIT ? OFFSET ?
    `, [clientId, parseInt(limit), parseInt(offset)]);

    // Récupération des photos pour chaque visite
    for (const visite of visites) {
      const photos = await query(`
        SELECT id, file, size, app_name, date_creation
        FROM visite_photo 
        WHERE id_visite = ? AND deleted = 0
        ORDER BY date_creation ASC
      `, [visite.id]);
      visite.photos = photos;
    }

    res.json({
      success: true,
      data: visites,
      count: visites.length
    });

  } catch (error) {
    console.error('Erreur récupération visites client:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des visites'
    });
  }
});

/**
 * Récupération des photos d'une visite
 * GET /api/visites/:id/photos
 */
router.get('/:id/photos', async (req, res) => {
  try {
    const visiteId = req.params.id;
    const userId = req.user.id;

    // Vérification que la visite appartient à l'utilisateur
    const visiteCheck = await query(`
      SELECT v.id FROM visite v
      LEFT JOIN ref_client cl ON v.id_client = cl.id_as400
      WHERE v.id = ? AND v.deleted = 0
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
    `, [visiteId, userId, userId]);

    if (visiteCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Visite non trouvée'
      });
    }

    const photos = await query(`
      SELECT 
        id,
        file,
        size,
        app_name,
        date_creation
      FROM visite_photo 
      WHERE id_visite = ? AND deleted = 0
      ORDER BY date_creation ASC
    `, [visiteId]);

    res.json({
      success: true,
      data: photos,
      count: photos.length
    });

  } catch (error) {
    console.error('Erreur récupération photos visite:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des photos'
    });
  }
});

/**
 * Création d'une nouvelle visite
 * POST /api/visites
 */
router.post('/', async (req, res) => {
  try {
    const userId = req.user.id;
    const {
      id_client,
      id_commande,
      no_cmd,
      no_cmd_reason,
      pmc_state,
      pmc_coms,
      dn_abc,
      dn_concurence,
      pem,
      alerte_raison,
      alerte_obs,
      is_juva = 0
    } = req.body;

    // Validation des données
    if (!id_client) {
      return res.status(400).json({
        error: true,
        errorMsg: 'ID client manquant'
      });
    }

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id_as400 FROM ref_client 
      WHERE id_as400 = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [id_client, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé ou non autorisé'
      });
    }

    // Génération d'un ID de visite unique
    const id_visite = 'VIS_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

    // Insertion de la visite
    const visiteResult = await query(`
      INSERT INTO visite 
      (id_user, id_commande, id_client, id_visite, no_cmd, no_cmd_reason, 
       pmc_state, pmc_coms, dn_abc, dn_concurence, pem, alerte_raison, 
       alerte_obs, is_juva, date_creation)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    `, [
      userId, id_commande, id_client, id_visite, no_cmd, no_cmd_reason,
      pmc_state, pmc_coms, dn_abc, dn_concurence, pem ? 1 : 0,
      Array.isArray(alerte_raison) ? alerte_raison.join(',') : alerte_raison,
      alerte_obs, is_juva
    ]);

    const visiteId = visiteResult.insertId;

    res.json({
      success: true,
      data: { id: visiteId, id_visite },
      message: 'Visite créée avec succès'
    });

  } catch (error) {
    console.error('Erreur création visite:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la création de la visite'
    });
  }
});

/**
 * Upload d'une photo de visite
 * POST /api/visites/:id/photos
 */
router.post('/:id/photos', async (req, res) => {
  try {
    const visiteId = req.params.id;
    const userId = req.user.id;
    const { photo, name } = req.body;

    // Vérification que la visite appartient à l'utilisateur
    const visiteCheck = await query(`
      SELECT v.id FROM visite v
      LEFT JOIN ref_client cl ON v.id_client = cl.id_as400
      WHERE v.id = ? AND v.deleted = 0
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
    `, [visiteId, userId, userId]);

    if (visiteCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Visite non trouvée'
      });
    }

    // Vérification que la photo n'existe pas déjà
    const existingPhoto = await query(`
      SELECT id FROM visite_photo WHERE app_name = ?
    `, [name]);

    if (existingPhoto.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: 'Cette photo existe déjà'
      });
    }

    // Décodage de la photo base64
    const photoBuffer = Buffer.from(photo, 'base64');
    const photoSize = photoBuffer.length;

    // Génération du nom de fichier
    const timestamp = Date.now();
    const random = Math.floor(Math.random() * 9000) + 1000;
    const filename = `photo-${timestamp}-${random}.jpg`;

    // Chemin de stockage (à adapter selon votre structure)
    const filePath = `/var/www/gescom/datas/visites/${visiteId}/${filename}`;

    // Création du dossier si nécessaire
    const fs = require('fs');
    const path = require('path');
    const dir = path.dirname(filePath);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    // Sauvegarde du fichier
    fs.writeFileSync(filePath, photoBuffer);

    // Insertion en base
    const photoResult = await query(`
      INSERT INTO visite_photo 
      (id_visite, file, size, app_name, date_creation)
      VALUES (?, ?, ?, ?, NOW())
    `, [visiteId, filename, photoSize, name]);

    res.json({
      success: true,
      data: { id: photoResult.insertId },
      message: 'Photo uploadée avec succès'
    });

  } catch (error) {
    console.error('Erreur upload photo:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de l\'upload de la photo'
    });
  }
});

/**
 * Récupération des futures visites
 * GET /api/visites/futures
 */
router.get('/futures', async (req, res) => {
  try {
    const userId = req.user.id;
    const { limit = 20, offset = 0 } = req.query;

    // Récupération des visites futures (planning)
    const visites = await query(`
      SELECT 
        p.id,
        p.id_client,
        p.date_visite,
        p.heure_debut,
        p.heure_fin,
        p.commentaire,
        p.statut,
        cl.enseigne as client_nom,
        cl.adresse as client_adresse,
        cl.ville as client_ville
      FROM planning p
      LEFT JOIN ref_client cl ON p.id_client = cl.id_as400
      WHERE p.id_user = ? AND p.deleted = 0
        AND p.date_visite >= CURDATE()
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
      ORDER BY p.date_visite ASC, p.heure_debut ASC
      LIMIT ? OFFSET ?
    `, [userId, userId, userId, parseInt(limit), parseInt(offset)]);

    res.json({
      success: true,
      data: visites,
      count: visites.length
    });

  } catch (error) {
    console.error('Erreur récupération futures visites:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des futures visites'
    });
  }
});

module.exports = router;
