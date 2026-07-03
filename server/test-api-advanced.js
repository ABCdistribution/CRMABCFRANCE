/**
 * Script de test avancé pour l'API CRM
 * Usage: node test-api-advanced.js [--endpoint=ENDPOINT] [--verbose]
 */

const axios = require('axios');
const colors = require('colors');

const BASE_URL = process.env.API_URL || 'http://localhost:3001';
let authToken = '';
let testResults = {
  passed: 0,
  failed: 0,
  total: 0
};

// Configuration axios
const api = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Fonction pour afficher les résultats
function logResult(testName, success, details = '') {
  testResults.total++;
  if (success) {
    testResults.passed++;
    console.log(`✅ ${testName}`.green);
    if (details) console.log(`   ${details}`.gray);
  } else {
    testResults.failed++;
    console.log(`❌ ${testName}`.red);
    if (details) console.log(`   ${details}`.red);
  }
}

// Fonction pour tester un endpoint
async function testEndpoint(method, endpoint, data = null, expectedStatus = 200, description = '') {
  const testName = `${method.toUpperCase()} ${endpoint}${description ? ` - ${description}` : ''}`;
  
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
    const success = response.status === expectedStatus;
    
    logResult(testName, success, 
      success ? `Status: ${response.status}` : `Expected: ${expectedStatus}, Got: ${response.status}`
    );
    
    return { success, data: response.data, status: response.status };
  } catch (error) {
    const status = error.response?.status || 0;
    const success = status === expectedStatus;
    
    logResult(testName, success, 
      success ? `Status: ${status}` : `Expected: ${expectedStatus}, Got: ${status} - ${error.message}`
    );
    
    return { success, error: error.message, status };
  }
}

// Tests d'authentification
async function testAuthentication() {
  console.log('\n🔐 Tests d\'authentification'.cyan.bold);
  
  // Test 1: Login avec des identifiants valides (simulation)
  const loginResult = await testEndpoint('POST', '/api/auth/login', {
    login: 'test',
    pass: 'test',
    device: 'Test Script'
  }, 200, 'Login utilisateur');
  
  if (loginResult.success && loginResult.data?.token) {
    authToken = loginResult.data.token;
    console.log(`   Token récupéré: ${authToken.substring(0, 20)}...`.gray);
  }
  
  // Test 2: Vérification du token
  if (authToken) {
    await testEndpoint('POST', '/api/auth/check-token', {
      token: authToken
    }, 200, 'Vérification token');
  }
  
  // Test 3: Login avec des identifiants invalides
  await testEndpoint('POST', '/api/auth/login', {
    login: 'invalid',
    pass: 'invalid'
  }, 401, 'Login avec identifiants invalides');
  
  // Test 4: Login sans données
  await testEndpoint('POST', '/api/auth/login', {}, 400, 'Login sans données');
}

// Tests de santé du système
async function testHealth() {
  console.log('\n🏥 Tests de santé du système'.cyan.bold);
  
  await testEndpoint('GET', '/api/health', null, 200, 'Health check');
  await testEndpoint('GET', '/api/test-db', null, 200, 'Test base de données');
}

// Tests des clients
async function testClients() {
  console.log('\n👥 Tests des clients'.cyan.bold);
  
  if (!authToken) {
    console.log('⚠️ Token d\'authentification manquant, tests clients ignorés'.yellow);
    return;
  }
  
  // Test 1: Liste des clients
  await testEndpoint('GET', '/api/clients?limit=5&offset=0', null, 200, 'Liste des clients');
  
  // Test 2: Détails d'un client (simulation)
  await testEndpoint('GET', '/api/clients/CLIENT123', null, 200, 'Détails client');
  
  // Test 3: Statistiques client
  await testEndpoint('GET', '/api/clients/CLIENT123/stats', null, 200, 'Statistiques client');
  
  // Test 4: Ajout d'un contact
  await testEndpoint('POST', '/api/clients/CLIENT123/contacts', {
    nom: 'Test',
    prenom: 'Contact',
    fonction: 'Testeur',
    telephone: '0123456789',
    mail: 'test@example.com'
  }, 200, 'Ajout contact');
}

// Tests des commandes
async function testCommandes() {
  console.log('\n📦 Tests des commandes'.cyan.bold);
  
  if (!authToken) {
    console.log('⚠️ Token d\'authentification manquant, tests commandes ignorés'.yellow);
    return;
  }
  
  // Test 1: Liste des commandes
  await testEndpoint('GET', '/api/commandes?limit=5&offset=0', null, 200, 'Liste des commandes');
  
  // Test 2: Détails d'une commande
  await testEndpoint('GET', '/api/commandes/CMD123', null, 200, 'Détails commande');
  
  // Test 3: Création d'une commande
  await testEndpoint('POST', '/api/commandes', {
    id_client: 'CLIENT123',
    date_livraison: '2025-09-10',
    commentaire: 'Commande test',
    lignes: [
      { id_produit: 1, quantite: 10 }
    ]
  }, 200, 'Création commande');
  
  // Test 4: Mise à jour statut
  await testEndpoint('PUT', '/api/commandes/CMD123/status', {
    statut: 'CONFIRMEE'
  }, 200, 'Mise à jour statut');
}

// Tests des visites
async function testVisites() {
  console.log('\n🏢 Tests des visites'.cyan.bold);
  
  if (!authToken) {
    console.log('⚠️ Token d\'authentification manquant, tests visites ignorés'.yellow);
    return;
  }
  
  // Test 1: Visites d'un client
  await testEndpoint('GET', '/api/visites/client/CLIENT123?limit=5&offset=0', null, 200, 'Visites client');
  
  // Test 2: Futures visites
  await testEndpoint('GET', '/api/visites/futures', null, 200, 'Futures visites');
  
  // Test 3: Création d'une visite
  await testEndpoint('POST', '/api/visites', {
    id_client: 'CLIENT123',
    id_commande: 'CMD123',
    no_cmd: 'CMD-2025-001',
    pmc_state: 'Bon état',
    pmc_coms: 'Commentaires',
    pem: true,
    alerte_raison: ['Maintenance'],
    alerte_obs: 'Observations'
  }, 200, 'Création visite');
}

// Tests de sécurité
async function testSecurity() {
  console.log('\n🛡️ Tests de sécurité'.cyan.bold);
  
  // Test 1: Accès sans token
  await testEndpoint('GET', '/api/clients', null, 401, 'Accès sans token');
  
  // Test 2: Token invalide
  const originalToken = authToken;
  authToken = 'invalid-token';
  await testEndpoint('GET', '/api/clients', null, 401, 'Token invalide');
  authToken = originalToken;
  
  // Test 3: Route inexistante
  await testEndpoint('GET', '/api/nonexistent', null, 404, 'Route inexistante');
}

// Test de performance
async function testPerformance() {
  console.log('\n⚡ Tests de performance'.cyan.bold);
  
  const startTime = Date.now();
  const promises = [];
  
  // Test de charge simple
  for (let i = 0; i < 10; i++) {
    promises.push(api.get('/api/health'));
  }
  
  try {
    await Promise.all(promises);
    const duration = Date.now() - startTime;
    logResult('Test de charge (10 requêtes)', true, `Durée: ${duration}ms`);
  } catch (error) {
    logResult('Test de charge (10 requêtes)', false, error.message);
  }
}

// Fonction principale
async function runTests() {
  console.log('🧪 Tests avancés de l\'API CRM'.rainbow.bold);
  console.log(`🌐 URL de base: ${BASE_URL}`.gray);
  console.log('='.repeat(50));
  
  try {
    await testHealth();
    await testAuthentication();
    await testSecurity();
    await testClients();
    await testCommandes();
    await testVisites();
    await testPerformance();
    
    // Résumé final
    console.log('\n' + '='.repeat(50));
    console.log('📊 Résumé des tests'.cyan.bold);
    console.log(`✅ Tests réussis: ${testResults.passed}`.green);
    console.log(`❌ Tests échoués: ${testResults.failed}`.red);
    console.log(`📈 Total: ${testResults.total}`);
    
    const successRate = ((testResults.passed / testResults.total) * 100).toFixed(1);
    console.log(`🎯 Taux de réussite: ${successRate}%`.cyan);
    
    if (testResults.failed === 0) {
      console.log('\n🎉 Tous les tests sont passés !'.green.bold);
    } else {
      console.log(`\n⚠️ ${testResults.failed} test(s) ont échoué`.yellow.bold);
    }
    
  } catch (error) {
    console.error('\n💥 Erreur critique lors des tests:'.red.bold);
    console.error(error.message.red);
    process.exit(1);
  }
}

// Fonction d'aide
function showHelp() {
  console.log(`
🧪 Script de test avancé pour l'API CRM

Usage:
  node test-api-advanced.js                    # Exécuter tous les tests
  node test-api-advanced.js --verbose          # Mode verbeux
  node test-api-advanced.js --endpoint=health  # Tester un endpoint spécifique
  node test-api-advanced.js help               # Afficher cette aide

Options:
  --verbose, -v     Mode verbeux (plus de détails)
  --endpoint=NAME   Tester un endpoint spécifique
  --help, -h        Afficher cette aide

Variables d'environnement:
  API_URL=http://localhost:3001  # URL de base de l'API

Exemples:
  API_URL=http://localhost:3001 node test-api-advanced.js
  node test-api-advanced.js --endpoint=health --verbose
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
  testResults
};





