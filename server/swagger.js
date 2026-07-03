/**
 * Configuration Swagger pour l'API CRM
 */

const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'API CRM - Gescom',
      version: '1.0.0',
      description: 'API REST pour la gestion des clients, commandes et visites',
      contact: {
        name: 'Support API',
        email: 'support@gescom.com'
      }
    },
    servers: [
      {
        url: 'http://localhost:3001',
        description: 'Serveur de développement'
      }
    ],
    components: {
      securitySchemes: {
        bearerAuth: {
          type: 'http',
          scheme: 'bearer',
          bearerFormat: 'JWT'
        }
      },
      schemas: {
        User: {
          type: 'object',
          properties: {
            id: { type: 'integer' },
            login: { type: 'string' },
            nom: { type: 'string' },
            prenom: { type: 'string' },
            mail: { type: 'string' },
            id_profile: { type: 'integer' },
            id_repr: { type: 'integer' },
            security: {
              type: 'object',
              properties: {
                profiles: { type: 'array', items: { type: 'string' } },
                user: { type: 'array', items: { type: 'integer' } }
              }
            }
          }
        },
        Client: {
          type: 'object',
          properties: {
            id: { type: 'string' },
            nom: { type: 'string' },
            adresse: { type: 'string' },
            ville: { type: 'string' },
            code_postal: { type: 'string' },
            telephone: { type: 'string' },
            mail: { type: 'string' },
            contacts: { type: 'array', items: { $ref: '#/components/schemas/Contact' } }
          }
        },
        Contact: {
          type: 'object',
          properties: {
            id: { type: 'integer' },
            nom: { type: 'string' },
            prenom: { type: 'string' },
            fonction: { type: 'string' },
            telephone: { type: 'string' },
            mail: { type: 'string' }
          }
        },
        Commande: {
          type: 'object',
          properties: {
            id: { type: 'string' },
            id_client: { type: 'string' },
            date_creation: { type: 'string', format: 'date-time' },
            date_livraison: { type: 'string', format: 'date' },
            statut: { type: 'string', enum: ['EN_ATTENTE', 'CONFIRMEE', 'EN_PREPARATION', 'LIVREE', 'ANNULEE'] },
            commentaire: { type: 'string' },
            lignes: { type: 'array', items: { $ref: '#/components/schemas/LigneCommande' } }
          }
        },
        LigneCommande: {
          type: 'object',
          properties: {
            id_produit: { type: 'integer' },
            quantite: { type: 'integer' },
            prix_unitaire: { type: 'number' }
          }
        },
        Visite: {
          type: 'object',
          properties: {
            id: { type: 'integer' },
            id_client: { type: 'string' },
            id_commande: { type: 'string' },
            date_visite: { type: 'string', format: 'date-time' },
            pmc_state: { type: 'string' },
            pmc_coms: { type: 'string' },
            pem: { type: 'boolean' },
            alerte_raison: { type: 'array', items: { type: 'string' } },
            alerte_obs: { type: 'string' }
          }
        },
        Error: {
          type: 'object',
          properties: {
            error: { type: 'boolean' },
            errorMsg: { type: 'string' }
          }
        },
        Success: {
          type: 'object',
          properties: {
            success: { type: 'boolean' },
            data: { type: 'object' },
            message: { type: 'string' }
          }
        }
      }
    },
    security: [
      {
        bearerAuth: []
      }
    ]
  },
  apis: ['./routes/*.js'] // Chemin vers les fichiers de routes
};

const specs = swaggerJsdoc(options);

module.exports = {
  swaggerUi,
  specs
};





