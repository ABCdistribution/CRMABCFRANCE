<?php


// Fonction de log simple
function log_msg($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
}

log_msg("🟢 Début de l'import global JUVA");

$basePath = __DIR__; // Répertoire courant du script

// Liste des scripts à exécuter
$scripts = [
    'import_produits.php' => 'produits',
    'import_clients.php'   => 'clients',
    'import_enseigne.php' => 'enseignes',
];

foreach ($scripts as $file => $label) {
    $path = $basePath . '/' . $file;
    if (file_exists($path)) {
        log_msg("➡️ Import $label : $file");
        include($path);
    } else {
        log_msg("❌ Fichier manquant : $file");
    }
}

log_msg("✅ Import global JUVA terminé");
