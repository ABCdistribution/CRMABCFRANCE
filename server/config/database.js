const mysql = require('mysql2/promise');

// Configuration de la base de données (même que le CRM PHP)
const dbConfig = {
  host: 'localhost',
  user: 'abcdistribution',
  password: 'i_89UYT_Op0#qsd',
  database: 'gescom',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

// Créer le pool de connexions
const pool = mysql.createPool(dbConfig);

// Fonction pour tester la connexion
async function testConnection() {
  try {
    const connection = await pool.getConnection();
    console.log('✅ Connexion à la base de données réussie');
    connection.release();
    return true;
  } catch (error) {
    console.error('❌ Erreur de connexion à la base de données:', error.message);
    return false;
  }
}

// Fonction pour exécuter une requête
async function query(sql, params = []) {
  try {
    const [rows] = await pool.execute(sql, params);
    return rows;
  } catch (error) {
    console.error('Erreur SQL:', error.message);
    throw error;
  }
}

module.exports = {
  pool,
  testConnection,
  query
};
