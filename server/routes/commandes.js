const express = require('express');
const router = express.Router();
const { query } = require('../config/database');

/**
 * Récupération de l'historique des commandes
 * GET /api/commandes
 */
router.get('/', async (req, res) => {
  try {
    const userId = req.user.id;
    const { limit = 50, offset = 0, client_id } = req.query;

    let whereClause = 'c.deleted = 0';
    let params = [userId, userId];

    if (client_id) {
      whereClause += ' AND c.id_magasin = ?';
      params.push(client_id);
    }

    const commandes = await query(`
      SELECT 
        c.id,
        c.id_apk as id_commande,
        c.id_magasin as id_client,
        c.date_creation,
        c.date_liv_estimee as date_livraison,
        c.total as montant_total,
        c.statut,
        c.no_cmd_client as commentaire,
        cl.enseigne as client_nom,
        cl.id_as400 as client_code
      FROM commande_apk c
      LEFT JOIN ref_client cl ON c.id_magasin = cl.id_as400
      WHERE ${whereClause}
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
      ORDER BY c.date_creation DESC
      LIMIT ? OFFSET ?
    `, [...params, parseInt(limit), parseInt(offset)]);

    // Récupération du nombre total
    const totalResult = await query(`
      SELECT COUNT(*) as total
      FROM commande_apk c
      LEFT JOIN ref_client cl ON c.id_magasin = cl.id_as400
      WHERE ${whereClause}
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
    `, [...params]);

    res.json({
      success: true,
      data: commandes,
      pagination: {
        total: totalResult[0].total,
        limit: parseInt(limit),
        offset: parseInt(offset),
        hasMore: (parseInt(offset) + parseInt(limit)) < totalResult[0].total
      }
    });

  } catch (error) {
    console.error('Erreur récupération commandes:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des commandes'
    });
  }
});

/**
 * Récupération des détails d'une commande
 * GET /api/commandes/:id
 */
router.get('/:id', async (req, res) => {
  try {
    const commandeId = req.params.id;
    const userId = req.user.id;

    // Récupération de la commande
    const commandes = await query(`
      SELECT 
        c.*,
        cl.enseigne as client_nom,
        cl.id_as400 as client_code,
        cl.adresse1 as client_adresse,
        cl.ville as client_ville,
        cl.code_postal as client_cp
      FROM commande_apk c
      LEFT JOIN ref_client cl ON c.id_magasin = cl.id_as400
      WHERE c.id = ? AND c.deleted = 0
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
    `, [commandeId, userId, userId]);

    if (commandes.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Commande non trouvée'
      });
    }

    const commande = commandes[0];

    // Récupération des lignes de commande
    const lignes = await query(`
      SELECT 
        cl.*,
        p.libelle as produit_libelle,
        p.prix_unitaire,
        p.code_produit
      FROM commande_ligne cl
      LEFT JOIN ref_produit p ON cl.id_produit = p.id
      WHERE cl.id_commande = ? AND cl.deleted = 0
      ORDER BY cl.numero_ligne ASC
    `, [commande.id]);

    commande.lignes = lignes;

    res.json({
      success: true,
      data: commande
    });

  } catch (error) {
    console.error('Erreur récupération commande:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération de la commande'
    });
  }
});

/**
 * Création d'une nouvelle commande
 * POST /api/commandes
 */
router.post('/', async (req, res) => {
  try {
    const userId = req.user.id;
    const {
      id_client,
      date_livraison,
      commentaire,
      lignes
    } = req.body;

    // Validation des données
    if (!id_client || !lignes || !Array.isArray(lignes) || lignes.length === 0) {
      return res.status(400).json({
        error: true,
        errorMsg: 'Données de commande invalides'
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

    // Génération d'un ID de commande unique
    const id_commande = 'CMD_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

    // Calcul du montant total
    let montant_total = 0;
    for (const ligne of lignes) {
      const produit = await query(
        'SELECT prix_unitaire FROM ref_produit WHERE id = ?',
        [ligne.id_produit]
      );
      if (produit.length > 0) {
        montant_total += produit[0].prix_unitaire * ligne.quantite;
      }
    }

    // Insertion de la commande
    const commandeResult = await query(`
      INSERT INTO commande 
      (id_commande, id_client, id_user, date_livraison, montant_total, commentaire, statut, date_creation)
      VALUES (?, ?, ?, ?, ?, ?, 'EN_ATTENTE', NOW())
    `, [id_commande, id_client, userId, date_livraison, montant_total, commentaire]);

    const commandeId = commandeResult.insertId;

    // Insertion des lignes de commande
    for (let i = 0; i < lignes.length; i++) {
      const ligne = lignes[i];
      await query(`
        INSERT INTO commande_ligne 
        (id_commande, numero_ligne, id_produit, quantite, prix_unitaire)
        VALUES (?, ?, ?, ?, 
          (SELECT prix_unitaire FROM ref_produit WHERE id = ?)
        )
      `, [commandeId, i + 1, ligne.id_produit, ligne.quantite, ligne.id_produit]);
    }

    res.json({
      success: true,
      data: { id: commandeId, id_commande },
      message: 'Commande créée avec succès'
    });

  } catch (error) {
    console.error('Erreur création commande:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la création de la commande'
    });
  }
});

/**
 * Mise à jour du statut d'une commande
 * PUT /api/commandes/:id/status
 */
router.put('/:id/status', async (req, res) => {
  try {
    const commandeId = req.params.id;
    const userId = req.user.id;
    const { statut } = req.body;

    const statutsValides = ['EN_ATTENTE', 'CONFIRMEE', 'EN_PREPARATION', 'LIVREE', 'ANNULEE'];
    
    if (!statutsValides.includes(statut)) {
      return res.status(400).json({
        error: true,
        errorMsg: 'Statut invalide'
      });
    }

    // Vérification que la commande appartient à l'utilisateur
    const commandeCheck = await query(`
      SELECT c.id FROM commande c
      LEFT JOIN ref_client cl ON c.id_client = cl.id_as400
      WHERE c.id = ? AND c.deleted = 0
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
    `, [commandeId, userId, userId]);

    if (commandeCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Commande non trouvée'
      });
    }

    // Mise à jour du statut
    await query(`
      UPDATE commande 
      SET statut = ?, date_modification = NOW()
      WHERE id = ?
    `, [statut, commandeId]);

    res.json({
      success: true,
      message: 'Statut de la commande mis à jour'
    });

  } catch (error) {
    console.error('Erreur mise à jour statut commande:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la mise à jour du statut'
    });
  }
});

/**
 * Récupération des commandes JUVA
 * GET /api/commandes/juva
 */
router.get('/juva', async (req, res) => {
  try {
    const userId = req.user.id;
    const { limit = 50, offset = 0 } = req.query;

    const commandes = await query(`
      SELECT 
        jc.*,
        cl.enseigne as client_nom
      FROM juva_commande jc
      LEFT JOIN ref_client cl ON jc.client = cl.id_as400
      WHERE jc.deleted = 0
        AND (cl.id_commercial_1 = ? OR cl.id_commercial_2 = ?)
      ORDER BY jc.date_creation DESC
      LIMIT ? OFFSET ?
    `, [userId, userId, parseInt(limit), parseInt(offset)]);

    res.json({
      success: true,
      data: commandes,
      count: commandes.length
    });

  } catch (error) {
    console.error('Erreur récupération commandes JUVA:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des commandes JUVA'
    });
  }
});

module.exports = router;
