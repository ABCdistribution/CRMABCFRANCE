const fs = require('fs');
const path = require('path');

// Liste des pages à mettre à jour
const pages = [
  'Produits',
  'Clients', 
  'Utilisateurs',
  'ProduitsPEM',
  'StratsPEM',
  'Commandes',
  'CommandesJuva',
  'Visites',
  'VisitesJuva',
  'Planning',
  'ValidationPlanning',
  'Prospects',
  'Prospections',
  'PlanningProspection',
  'PlanningProspectionClients',
  'ProspectionGestionCA',
  'VisitesCS',
  'News',
  'JoursOFF',
  'Traductions',
  'Profile'
];

// Fonction pour mettre à jour une page
function updatePage(pageName) {
  const filePath = path.join(__dirname, 'src', 'pages', `${pageName}.tsx`);
  
  if (!fs.existsSync(filePath)) {
    console.log(`Fichier ${filePath} non trouvé`);
    return;
  }
  
  let content = fs.readFileSync(filePath, 'utf8');
  
  // Remplacer le container principal
  content = content.replace(
    /style=\{\{\s*padding:\s*['"]2rem\s+0['"],\s*maxWidth:\s*['"]1200px['"],\s*margin:\s*['"]0\s+auto['"],\s*paddingLeft:\s*['"]2rem['"],\s*paddingRight:\s*['"]2rem['"]\s*\}\}/g,
    'className="page-container"'
  );
  
  // Remplacer le header de page
  content = content.replace(
    /style=\{\{\s*display:\s*['"]flex['"],\s*justifyContent:\s*['"]space-between['"],\s*alignItems:\s*['"]center['"],\s*marginBottom:\s*['"]3rem['"]\s*\}\}/g,
    'className="page-header"'
  );
  
  // Remplacer les boutons du header
  content = content.replace(
    /style=\{\{\s*display:\s*['"]flex['"],\s*gap:\s*['"]1rem['"]\s*\}\}/g,
    'className="page-header-buttons" style={{ display: \'flex\', gap: \'1rem\' }}'
  );
  
  // Remplacer la grille des stats
  content = content.replace(
    /style=\{\{\s*display:\s*['"]grid['"],\s*gridTemplateColumns:\s*['"]repeat\(4,\s*1fr\)['"],\s*gap:\s*['"]2rem['"],\s*marginBottom:\s*['"]3rem['"]\s*\}\}/g,
    'className="stats-grid" style={{ marginBottom: \'3rem\' }}'
  );
  
  // Remplacer la barre de recherche/filtre
  content = content.replace(
    /style=\{\{\s*display:\s*['"]flex['"],\s*gap:\s*['"]1rem['"],\s*alignItems:\s*['"]center['"],\s*marginBottom:\s*['"]2rem['"]\s*\}\}/g,
    'className="search-filter-bar" style={{ marginBottom: \'2rem\' }}'
  );
  
  // Remplacer le conteneur de table
  content = content.replace(
    /style=\{\{\s*backgroundColor:\s*['"]white['"],\s*borderRadius:\s*['"]0\.5rem['"],\s*border:\s*['"]1px\s+solid\s+#e5e7eb['"],\s*overflow:\s*['"]hidden['"]\s*\}\}/g,
    'className="table-responsive" style={{ backgroundColor: \'white\', borderRadius: \'0.5rem\', border: \'1px solid #e5e7eb\', overflow: \'hidden\' }}'
  );
  
  // Pour Profile, remplacer la grille de layout
  if (pageName === 'Profile') {
    content = content.replace(
      /style=\{\{\s*display:\s*['"]grid['"],\s*gridTemplateColumns:\s*['"]repeat\(2,\s*1fr\)['"],\s*gap:\s*['"]2rem['"]\s*\}\}/g,
      'className="profile-layout"'
    );
  }
  
  fs.writeFileSync(filePath, content);
  console.log(`✅ ${pageName}.tsx mis à jour`);
}

// Mettre à jour toutes les pages
console.log('🚀 Mise à jour responsive des pages...');
pages.forEach(updatePage);
console.log('✨ Toutes les pages ont été mises à jour !');
