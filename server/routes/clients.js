const express = require('express');
const router = express.Router();
const { query } = require('../config/database');

/**
 * Récupération de la liste des clients d'un utilisateur
 * GET /api/clients
 */
router.get('/', async (req, res) => {
  try {
    const userId = req.user.id;
    
    // Récupération des clients assignés à l'utilisateur
    const clients = await query(`
      SELECT 
        c.id,
        c.id_as400,
        c.enseigne,
        c.raison_sociale,
        c.adresse1 as adresse,
        c.code_postal,
        c.ville,
        c.pays,
        c.id_commercial_1,
        c.id_commercial_2,
        c.actif,
        c.date_creation
      FROM ref_client c
      WHERE c.deleted = 0 
        AND c.actif = 1
        AND (c.id_commercial_1 = ? OR c.id_commercial_2 = ? OR ? = 2)
      ORDER BY c.enseigne ASC
    `, [userId, userId, req.user.id_profile]);

    res.json({
      success: true,
      data: clients,
      count: clients.length
    });

  } catch (error) {
    console.error('Erreur récupération clients:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des clients'
    });
  }
});

/**
 * Récupération des détails d'un client
 * GET /api/clients/:id
 */
router.get('/:id', async (req, res) => {
  try {
    const clientId = req.params.id;
    const userId = req.user.id;

    console.log(`[DEBUG CLIENT DETAILS] ==========================================`);
    console.log(`[DEBUG CLIENT DETAILS] Requête détails client ID: ${clientId}`);
    console.log(`[DEBUG CLIENT DETAILS] Utilisateur ID: ${userId}`);
    console.log(`[DEBUG CLIENT DETAILS] Profile utilisateur: ${req.user.id_profile}`);

    // Récupération du client
    const queryClient = `
      SELECT 
        c.*,
        ci.*
      FROM ref_client c
      LEFT JOIN ref_client_infos ci ON c.id_as400 = ci.id_ref_client
      WHERE c.id = ? AND c.deleted = 0 AND c.actif = 1
        AND (c.id_commercial_1 = ? OR c.id_commercial_2 = ?)
    `;
    const paramsClient = [clientId, userId, userId];
    
    console.log(`[DEBUG CLIENT DETAILS] Requête SQL client:`, queryClient);
    console.log(`[DEBUG CLIENT DETAILS] Paramètres:`, paramsClient);

    const clients = await query(queryClient, paramsClient);

    console.log(`[DEBUG CLIENT DETAILS] Résultat requête client: ${clients.length} ligne(s)`);
    if (clients.length > 0) {
      console.log(`[DEBUG CLIENT DETAILS] Client trouvé:`, {
        id: clients[0].id,
        id_as400: clients[0].id_as400,
        enseigne: clients[0].enseigne,
        id_commercial_1: clients[0].id_commercial_1,
        id_commercial_2: clients[0].id_commercial_2
      });
    }

    if (clients.length === 0) {
      console.log(`[DEBUG CLIENT DETAILS] ❌ Client non trouvé ou accès refusé`);
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    const client = clients[0];
    console.log(`[DEBUG CLIENT DETAILS] ✅ Client principal récupéré, ID AS400: ${client.id_as400}`);

    // Récupération des contacts
    console.log(`[DEBUG CLIENT DETAILS] Récupération des contacts...`);
    try {
      const contacts = await query(`
        SELECT * FROM ref_client_contact 
        WHERE id_ref_client = ? AND deleted = 0
        ORDER BY nom ASC
      `, [client.id_as400]);
      console.log(`[DEBUG CLIENT DETAILS] ✅ ${contacts.length} contact(s) trouvé(s)`);
    } catch (error) {
      console.log(`[DEBUG CLIENT DETAILS] ❌ Erreur contacts:`, error.message);
      const contacts = [];
    }

    // Récupération des centrales
    console.log(`[DEBUG CLIENT DETAILS] Récupération des centrales...`);
    try {
      const centrales = await query(`
        SELECT * FROM ref_centrale 
        WHERE code_client_cmd = ?
      `, [client.id_as400]);
      console.log(`[DEBUG CLIENT DETAILS] ✅ ${centrales.length} centrale(s) trouvée(s)`);
    } catch (error) {
      console.log(`[DEBUG CLIENT DETAILS] ❌ Erreur centrales:`, error.message);
      const centrales = [];
    }

    // Récupération des visites récentes
    console.log(`[DEBUG CLIENT DETAILS] Récupération des visites...`);
    try {
      const visites = await query(`
        SELECT 
          v.id,
          v.id_visite,
          v.date_creation,
          v.pem,
          v.alerte_raison,
          v.alerte_obs
        FROM visite v
        WHERE v.id_client = ? AND v.deleted = 0
        ORDER BY v.date_creation DESC
        LIMIT 10
      `, [client.id_as400]);
      console.log(`[DEBUG CLIENT DETAILS] ✅ ${visites.length} visite(s) trouvée(s)`);
    } catch (error) {
      console.log(`[DEBUG CLIENT DETAILS] ❌ Erreur visites:`, error.message);
      const visites = [];
    }

    client.contacts = contacts;
    client.centrales = centrales;
    client.visites_recentes = visites;

    res.json({
      success: true,
      data: client
    });

  } catch (error) {
    console.error('Erreur récupération client:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération du client'
    });
  }
});

/**
 * Récupération des statistiques d'un client
 * GET /api/clients/:id/stats
 */
router.get('/:id/stats', async (req, res) => {
  try {
    const clientId = req.params.id;
    const userId = req.user.id;

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id FROM ref_client 
      WHERE id = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [clientId, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    const client = clientCheck[0];

    // Statistiques des visites
    const statsVisites = await query(`
      SELECT 
        COUNT(*) as total_visites,
        COUNT(CASE WHEN pem = 1 THEN 1 END) as visites_pem,
        COUNT(CASE WHEN alerte_raison != '' THEN 1 END) as visites_alertes
      FROM visite 
      WHERE id_client = ? AND deleted = 0
    `, [client.id_as400]);

    // Statistiques des commandes
    const statsCommandes = await query(`
      SELECT 
        COUNT(*) as total_commandes,
        SUM(montant_total) as montant_total
      FROM commande 
      WHERE id_client = ? AND deleted = 0
    `, [client.id_as400]);

    // Dernière visite
    const derniereVisite = await query(`
      SELECT date_creation, pem, alerte_raison
      FROM visite 
      WHERE id_client = ? AND deleted = 0
      ORDER BY date_creation DESC
      LIMIT 1
    `, [client.id_as400]);

    res.json({
      success: true,
      data: {
        visites: statsVisites[0],
        commandes: statsCommandes[0],
        derniere_visite: derniereVisite[0] || null
      }
    });

  } catch (error) {
    console.error('Erreur récupération stats client:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la récupération des statistiques'
    });
  }
});

/**
 * Ajout d'un contact client
 * POST /api/clients/:id/contacts
 */
router.post('/:id/contacts', async (req, res) => {
  try {
    const clientId = req.params.id;
    const userId = req.user.id;
    const { nom, prenom, fonction, telephone, mail } = req.body;

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id_as400 FROM ref_client 
      WHERE id = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [clientId, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    const clientAs400 = clientCheck[0].id_as400;

    // Insertion du contact
    const result = await query(`
      INSERT INTO ref_client_contact 
      (id_ref_client, nom, prenom, fonction, telephone, mail, id_createur, date_creation)
      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    `, [clientAs400, nom, prenom, fonction, telephone, mail, userId]);

    res.json({
      success: true,
      data: { id: result.insertId },
      message: 'Contact ajouté avec succès'
    });

  } catch (error) {
    console.error('Erreur ajout contact:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de l\'ajout du contact'
    });
  }
});

/**
 * Mise à jour d'un contact client
 * PUT /api/clients/:id/contacts/:contactId
 */
router.put('/:id/contacts/:contactId', async (req, res) => {
  try {
    const clientId = req.params.id;
    const contactId = req.params.contactId;
    const userId = req.user.id;
    const { nom, prenom, fonction, telephone, mail } = req.body;

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id_as400 FROM ref_client 
      WHERE id = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [clientId, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    // Mise à jour du contact
    await query(`
      UPDATE ref_client_contact 
      SET nom = ?, prenom = ?, fonction = ?, telephone = ?, mail = ?
      WHERE id = ? AND id_ref_client = ? AND deleted = 0
    `, [nom, prenom, fonction, telephone, mail, contactId, clientCheck[0].id_as400]);

    res.json({
      success: true,
      message: 'Contact mis à jour avec succès'
    });

  } catch (error) {
    console.error('Erreur mise à jour contact:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la mise à jour du contact'
    });
  }
});

/**
 * Suppression d'un contact client
 * DELETE /api/clients/:id/contacts/:contactId
 */
router.delete('/:id/contacts/:contactId', async (req, res) => {
  try {
    const clientId = req.params.id;
    const contactId = req.params.contactId;
    const userId = req.user.id;

    // Vérification que le client appartient à l'utilisateur
    const clientCheck = await query(`
      SELECT id_as400 FROM ref_client 
      WHERE id = ? AND deleted = 0 AND actif = 1
        AND (id_commercial_1 = ? OR id_commercial_2 = ?)
    `, [clientId, userId, userId]);

    if (clientCheck.length === 0) {
      return res.status(404).json({
        error: true,
        errorMsg: 'Client non trouvé'
      });
    }

    // Suppression logique du contact
    await query(`
      UPDATE ref_client_contact 
      SET deleted = 1, date_modification = NOW()
      WHERE id = ? AND id_ref_client = ? AND deleted = 0
    `, [contactId, clientCheck[0].id_as400]);

    res.json({
      success: true,
      message: 'Contact supprimé avec succès'
    });

  } catch (error) {
    console.error('Erreur suppression contact:', error);
    res.status(500).json({
      error: true,
      errorMsg: 'Erreur lors de la suppression du contact'
    });
  }
});

module.exports = router;
