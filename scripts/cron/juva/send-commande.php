<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$localDir = DIR_CMDJUVA;
$remoteDir = '/Oacces/InterfaceToProscom/';

$files = ['Commande.csv', 'CommandeLigne.csv'];

$host = 'proscom.kleegroup.com';
$port = 22;
$username = 'Oacces';
$password = 'HdQ$|M@@A';

function log_msg($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
}

// Connexion SSH
$connection = ssh2_connect($host, $port);
if (!$connection) {
    die("❌ Connexion SSH échouée\n");
}

if (!ssh2_auth_password($connection, $username, $password)) {
    die("❌ Authentification échouée\n");
}

$sftp = ssh2_sftp($connection);
if (!$sftp) {
    die("❌ Erreur SFTP\n");
}

log_msg("✅ Connexion SFTP réussie");

// Transfert des fichiers
foreach ($files as $file) {
    $localPath = rtrim($localDir, '/') . '/' . $file;
    $remotePath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/') . $file;

    if (!file_exists($localPath)) {
        log_msg("⚠️ Fichier introuvable : $localPath");
        continue;
    }

    if (copy($localPath, $remotePath)) {
        log_msg("📤 Fichier transféré : $file");
    } else {
        log_msg("❌ Échec du transfert : $file");
    }
}

// Création du fichier TOPIMPORT.txt
$topimportPath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/') . 'TOPIMPORT';

if (file_put_contents($topimportPath, '') !== false) {
    log_msg("📄 Fichier TOPIMPORT créé");
} else {
    log_msg("❌ Échec de création de TOPIMPORT");
}

$remoteDirPath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/');

log_msg("📂 Contenu du dossier distant :");
$dirHandle = opendir($remoteDirPath);
if ($dirHandle) {
    while (($entry = readdir($dirHandle)) !== false) {
        if ($entry !== '.' && $entry !== '..') {
            echo "  - $entry\n";
        }
    }
    closedir($dirHandle);
} else {
    log_msg("❌ Impossible d’ouvrir le dossier distant pour lecture.");
}


// Création du dossier de backup avec timestamp
$backupBaseDir = '/home/backupJuva/export/';
$timestamp = date('Y-m-d_Hi');
$backupDir = rtrim($backupBaseDir, '/') . '/' . $timestamp;

if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0777, true)) {
        log_msg("❌ Échec de création du dossier de backup : $backupDir");
    } else {
        log_msg("📁 Dossier de backup créé : $backupDir");
    }
}

// Copie des fichiers envoyés dans le dossier de backup
foreach ($files as $file) {
    $src = rtrim($localDir, '/') . '/' . $file;
    $dest = $backupDir . '/' . $file;

    if (file_exists($src) && copy($src, $dest)) {
        log_msg("✅ Backup de $file dans $backupDir");
    } else {
        log_msg("⚠️ Échec du backup pour $file");
    }
}
// Suppression des fichiers locaux
// fonction qui supprime les fichiers locaux apres backup

function deleteLocalFiles($localDir, $files) {
    foreach ($files as $file) {
        $filePath = rtrim($localDir, '/') . '/' . $file;
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                log_msg("✅ Fichier supprimé : $file");
            } else {
                log_msg("❌ Échec de suppression du fichier : $file");
            }
        } else {
            log_msg("⚠️ Fichier introuvable pour suppression : $file");
        }
    }
}
deleteLocalFiles($localDir, $files);

?>
