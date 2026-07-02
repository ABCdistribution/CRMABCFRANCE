<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('/var/www/gescom/scripts/init_scripts.php');
log_msg("🚀 Script import_enseigne.php démarré");

global $db;
$db->execute("SET NAMES 'utf8mb4'");
$db->execute("SET character_set_client = 'utf8mb4'");
$db->execute("SET character_set_results = 'utf8mb4'");
$db->execute("SET character_set_connection = 'utf8mb4'");

$remoteDir = '/Oacces/ProscomToInterface/';
$filename = 'Enseigne.csv';

$host = 'proscom.kleegroup.com';
$port = 22;
$username = 'Oacces';
$password = 'HdQ$|M@@A';

$localDir = '/tmp/';
$backupBaseDir = '/home/backupimport/';

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

$remotePath = "ssh2.sftp://" . intval($sftp) . "/" . ltrim($remoteDir, '/') . $filename;
$localPath = rtrim($localDir, '/') . '/' . $filename;

if (!copy($remotePath, $localPath)) {
    log_msg("❌ Échec du téléchargement du fichier $filename");
    return;
}
log_msg("📥 Fichier téléchargé localement : $localPath");

// Conversion en UTF-8
$content = file_get_contents($localPath);
if ($content === false) {
    log_msg("❌ Erreur de lecture du fichier $localPath");
    return;
}

$content_utf8 = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
$tempPath = $localDir . 'Enseigne_utf8.csv';
file_put_contents($tempPath, $content_utf8);
$localPath = $tempPath;

// Ouverture du fichier
if (($handle = fopen($localPath, 'r')) === false) {
    log_msg("❌ Impossible d'ouvrir le fichier local $localPath");
    return;
}

$header = fgetcsv($handle, 0, ';');
if ($header === false) {
    log_msg("❌ Fichier CSV vide ou incorrect");
    fclose($handle);
    return;
}

$columns = array_flip($header);
log_msg("🛠️ Début de l'importation dans la base de données");

$countInserted = 0;
$countUpdated = 0;
$countErrors = 0;

while (($data = fgetcsv($handle, 0, ';')) !== false) {
    $acronyme = trim($data[$columns['Acronyme']] ?? '');
    $libelle  = $db->escape(trim($data[$columns['Libelle']] ?? ''));
    $visible  = trim($data[$columns['Visible']] ?? '');
    $ordre    = trim($data[$columns['Ordre']] ?? '');

    if ($acronyme === '') {
        log_msg("⚠️ Acronyme vide, ligne ignorée");
        $countErrors++;
        continue;
    }

    $db->execute("SELECT COUNT(*) as cnt FROM juva_enseigne WHERE Acronyme = '$acronyme'");
    $row = $db->get()[0] ?? ['cnt' => 0];
    $exists = ($row['cnt'] > 0);

    $sqlVal = function($val) {
        return ($val !== '') ? "'$val'" : "NULL";
    };

    if ($exists) {
        $sql = "
            UPDATE juva_enseigne SET
                Libelle = '$libelle',
                Visible = {$sqlVal($visible)},
                Ordre = {$sqlVal($ordre)}
            WHERE Acronyme = '$acronyme'
        ";
        if ($db->execute($sql)) {
            $countUpdated++;
        } else {
            log_msg("⚠️ Erreur UPDATE enseigne ($acronyme)");
            $countErrors++;
        }
    } else {
        $sql = "
            INSERT INTO juva_enseigne (Acronyme, Libelle, Visible, Ordre)
            VALUES ('$acronyme', '$libelle', {$sqlVal($visible)}, {$sqlVal($ordre)})
        ";
        if ($db->execute($sql)) {
            $countInserted++;
        } else {
            log_msg("⚠️ Erreur INSERT enseigne ($acronyme)");
            $countErrors++;
        }
    }
}

fclose($handle);

log_msg("✅ Import terminé : $countInserted insérés, $countUpdated mis à jour, $countErrors erreurs.");

// Sauvegarde
$timestamp = date('Y-m-d_Hi');
$backupDir = rtrim($backupBaseDir, '/') . '/' . $timestamp;
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0777, true)) {
        log_msg("❌ Échec de création du dossier de backup : $backupDir");
    } else {
        log_msg("📁 Dossier de backup créé : $backupDir");
    }
}
$backupPath = $backupDir . '/' . $filename;
if (copy($localPath, $backupPath)) {
    log_msg("✅ Backup effectué : $backupPath");
} else {
    log_msg("⚠️ Échec du backup");
}

unlink($localPath);
log_msg("🗑️ Fichier local temporaire supprimé : $localPath");
?>
