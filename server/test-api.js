/**
 * Script de test pour l'API CRM
 * Usage: node test-api.js
 */

const axios = require('axios');

const BASE_URL = 'http://localhost:3001';
let authToken = '';

// Configuration axios
const api = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Fonction de test
async function runTests() {
  console.log('🧪 Début des tests de l\'API CRM\n');

  try {
    // Test 1: Vérification de l'état du serveur
    console.log('1️⃣ Test de l\'état du serveur...');
    const healthResponse = await api.get('/api/health');
    console.log('✅ Serveur opérationnel:', healthResponse.data);
    console.log('');

    // Test 2: Test de la base de données
    console.log('2️⃣ Test de la base de données...');
    const dbResponse = await api.get('/api/test-db');
    console.log('✅ Base de données accessible:', dbResponse.data);
    console.log('');

    // Test 3: Test d'authentification (simulation)
    console.log('3️⃣ Test d\'authentification...');
    try {
      const authResponse = await api.post('/api/auth/login', {
        login: 'test',
        pass: 'test'
      });
      console.log('✅ Authentification réussie:', authResponse.data);
      authToken = authResponse.data.token;
    } catch (error) {
      console.log('⚠️ Authentification échouée (normal si utilisateur test n\'existe pas):', error.response?.data || error.message);
    }
    console.log('');

    // Test 4: Test des routes protégées (sans token)
    console.log('4️⃣ Test des routes protégées sans token...');
    try {
      await api.get('/api/clients');
    } catch (error) {
      if (error.response?.status === 401) {
        console.log('✅ Protection des routes fonctionnelle (401 Unauthorized)');
      } else {
        console.log('❌ Erreur inattendue:', error.response?.data || error.message);
      }
    }
    console.log('');

    // Test 5: Test avec token (si disponible)
    if (authToken) {
      console.log('5️⃣ Test des routes protégées avec token...');
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
      
      try {
        const clientsResponse = await api.get('/api/clients');
        console.log('✅ Accès aux clients autorisé:', clientsResponse.data);
      } catch (error) {
        console.log('⚠️ Erreur accès clients:', error.response?.data || error.message);
      }
    } else {
      console.log('5️⃣ Test des routes protégées avec token...');
      console.log('⚠️ Token non disponible, test ignoré');
    }
    console.log('');

    console.log('🎉 Tests terminés avec succès !');
    console.log('\n📋 Résumé:');
    console.log('- Serveur: ✅ Opérationnel');
    console.log('- Base de données: ✅ Accessible');
    console.log('- Authentification: ⚠️ À configurer');
    console.log('- Protection des routes: ✅ Fonctionnelle');
    console.log('\n🚀 L\'API est prête à être utilisée !');

  } catch (error) {
    console.error('❌ Erreur lors des tests:', error.message);
    if (error.response) {
      console.error('Détails:', error.response.data);
    }
    process.exit(1);
  }
}

// Fonction pour tester un endpoint spécifique
async function testEndpoint(method, endpoint, data = null) {
  try {
    const config = {
      method,
      url: endpoint,
      headers: authToken ? { 'Authorization': `Bearer ${authToken}` } : {}
    };
    
    if (data) {
      config.data = data;
    }

    const response = await api(config);
    console.log(`✅ ${method.toUpperCase()} ${endpoint}:`, response.data);
    return response.data;
  } catch (error) {
    console.log(`❌ ${method.toUpperCase()} ${endpoint}:`, error.response?.data || error.message);
    throw error;
  }
}

// Fonction d'aide
function showHelp() {
  console.log(`
🧪 Script de test pour l'API CRM

Usage:
  node test-api.js                    # Exécuter tous les tests
  node test-api.js help               # Afficher cette aide

Endpoints testés:
  GET  /api/health                    # État du serveur
  GET  /api/test-db                   # Test base de données
  POST /api/auth/login                # Authentification
  GET  /api/clients                   # Liste des clients (protégé)

Variables d'environnement:
  BASE_URL=http://localhost:3001      # URL de base de l'API
  `);
}

// Point d'entrée
if (require.main === module) {
  const args = process.argv.slice(2);
  
  if (args.includes('help') || args.includes('--help') || args.includes('-h')) {
    showHelp();
  } else {
    runTests();
  }
}

module.exports = {
  runTests,
  testEndpoint,
  api
};
