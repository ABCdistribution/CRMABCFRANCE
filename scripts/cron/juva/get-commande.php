<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$remoteDir = '/Oacces/ProscomToInterface/';
$localDir = '/home/JUVAACRM/';
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

// Vérifier la présence de TOPEXPORT
$topEXPORTPath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/') . "TOPEXPORT";

if (!file_exists($topEXPORTPath)) {
    log_msg("⚠️ Aucun fichier TOPEXPORT trouvé");
    exit;
}

log_msg("📄 Fichier TOPEXPORT détecté → début de l'EXPORT...");

// Télécharger les fichiers
$downloadSuccess = true;

foreach ($files as $file) {
    $remotePath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/') . $file;
    $localPath = rtrim($localDir, '/') . '/' . $file;

    if (!file_exists($remotePath)) {
        log_msg("❌ Fichier distant manquant : $file");
        $downloadSuccess = false;
        continue;
    }

    if (copy($remotePath, $localPath)) {
        log_msg("📥 Fichier téléchargé : $localPath");
    } else {
        log_msg("❌ Échec du téléchargement : $file");
        $downloadSuccess = false;
    }
}

// Si tous les fichiers sont bien reçus, supprimer TOPEXPORT
if ($downloadSuccess) {
    if (unlink($topEXPORTPath)) {
        log_msg("🗑️ Fichier TOPEXPORT supprimé sur le SFTP");
    } else {
        log_msg("⚠️ Impossible de supprimer TOPEXPORT sur le SFTP");
    }

    // Sauvegarde locale
    $backupBaseDir = '/home/backupJuva/import/';
    $timestamp = date('Y-m-d_Hi');
    $backupDir = rtrim($backupBaseDir, '/') . '/' . $timestamp;

    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0777, true)) {
            log_msg("❌ Échec de création du dossier de backup : $backupDir");
        } else {
            log_msg("📁 Dossier de backup créé : $backupDir");
        }
    }

    foreach ($files as $file) {
        $src = rtrim($localDir, '/') . '/' . $file;
        $dest = $backupDir . '/' . $file;

        if (file_exists($src) && copy($src, $dest)) {
            log_msg("✅ Backup de $file dans $backupDir");
        } else {
            log_msg("⚠️ Échec du backup pour $file");
        }
    }
} else {
    log_msg("⛔ EXPORT incomplet, TOPEXPORT conservé pour nouvel essai.");
}
?>
