<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('/var/www/gescom/scripts/init_scripts.php');
global $db;
$db->execute("SET NAMES 'utf8mb4'");
$db->execute("SET character_set_client = 'utf8mb4'");
$db->execute("SET character_set_results = 'utf8mb4'");
$db->execute("SET character_set_connection = 'utf8mb4'");

$remoteDir = '/Oacces/ProscomToInterface/';
$filename = 'Produit.csv';

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

// Conversion du fichier ANSI (Windows-1252) en UTF-8
$content = file_get_contents($localPath);
if ($content === false) {
    log_msg("❌ Erreur de lecture du fichier $localPath");
    return;
}

$content_utf8 = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
$tempPath = $localDir . 'Produit_utf8.csv';
file_put_contents($tempPath, $content_utf8);

// On utilise maintenant le fichier UTF-8
$localPath = $tempPath;

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
    $idOriginal     = trim($data[$columns['IdOriginal']] ?? '');
    $gencod         = trim($data[$columns['Gencod']] ?? '');
    $libelle        = $db->escape(trim($data[$columns['Libelle']] ?? ''));
    $ordre          = trim($data[$columns['Ordre']] ?? '');
    $dateDebut      = trim($data[$columns['DateDebutDisponibilite']] ?? '');
    $dateFin        = trim($data[$columns['DateFinDisponibilite']] ?? '');
    $pcb            = trim($data[$columns['PCB']] ?? '');
    $sousPCB        = trim($data[$columns['SousPCB']] ?? '');
    $longueur       = trim($data[$columns['Longueur']] ?? '');
    $largeur        = trim($data[$columns['Largeur']] ?? '');
    $hauteur        = trim($data[$columns['Hauteur']] ?? '');
    $poids          = trim($data[$columns['Poids']] ?? '');
    $contenance     = trim($data[$columns['Contenance']] ?? '');
    $pvc            = trim($data[$columns['PVC']] ?? '');
    $prix           = str_replace(',', '.', trim($data[$columns['Prix']] ?? ''));
    $nouveaute      = trim($data[$columns['Nouveaute']] ?? '');
    $incontournable = trim($data[$columns['Incontournable']] ?? '');
    $champExt19     = $db->escape(trim($data[$columns['ChampExt19']] ?? ''));
    $champExt20     = $db->escape(trim($data[$columns['ChampExt20']] ?? ''));

    $db->execute("SELECT COUNT(*) as cnt FROM juva_produit WHERE Gencod = '$gencod' OR IdOriginal = '$idOriginal'");
    $row = $db->get()[0] ?? ['cnt' => 0];
    $exists = ($row['cnt'] > 0);

    // Préparer les valeurs nullables
    $sqlVal = function($val) {
        return ($val !== '') ? "'$val'" : "NULL";
    };

    if ($exists) {
        $sql = "
            UPDATE juva_produit SET
                IdOriginal = '$idOriginal',
                Libelle = '$libelle',
                Ordre = {$sqlVal($ordre)},
                DateDebutDisponibilite = {$sqlVal($dateDebut)},
                DateFinDisponibilite = {$sqlVal($dateFin)},
                PCB = {$sqlVal($pcb)},
                SousPCB = {$sqlVal($sousPCB)},
                Longueur = {$sqlVal($longueur)},
                Largeur = {$sqlVal($largeur)},
                Hauteur = {$sqlVal($hauteur)},
                Poids = {$sqlVal($poids)},
                Contenance = {$sqlVal($contenance)},
                PVC = {$sqlVal($pvc)},
                Prix = {$sqlVal($prix)},
                Nouveaute = {$sqlVal($nouveaute)},
                Incontournable = {$sqlVal($incontournable)},
                ChampExt19 = '$champExt19',
                ChampExt20 = '$champExt20'
            WHERE Gencod = '$gencod' OR IdOriginal = '$idOriginal'
        ";
        if ($db->execute($sql)) {
            $countUpdated++;
        } else {
            log_msg("⚠️ Erreur UPDATE produit ($gencod)");
            $countErrors++;
        }
    } else {
        $sql = "
            INSERT INTO juva_produit 
                (IdOriginal, Gencod, Libelle, Ordre, DateDebutDisponibilite, DateFinDisponibilite,
                 PCB, SousPCB, Longueur, Largeur, Hauteur, Poids, Contenance, PVC, Prix,
                 Nouveaute, Incontournable, ChampExt19, ChampExt20)
            VALUES 
                ('$idOriginal', '$gencod', '$libelle',
                 {$sqlVal($ordre)}, {$sqlVal($dateDebut)}, {$sqlVal($dateFin)},
                 {$sqlVal($pcb)}, {$sqlVal($sousPCB)}, {$sqlVal($longueur)},
                 {$sqlVal($largeur)}, {$sqlVal($hauteur)}, {$sqlVal($poids)},
                 {$sqlVal($contenance)}, {$sqlVal($pvc)}, {$sqlVal($prix)},
                 {$sqlVal($nouveaute)}, {$sqlVal($incontournable)},
                 '$champExt19', '$champExt20')
        ";
        if ($db->execute($sql)) {
            $countInserted++;
        } else {
            log_msg("⚠️ Erreur INSERT produit ($gencod)");
            $countErrors++;
        }
    }
}

fclose($handle);

log_msg("✅ Import terminé : $countInserted insérés, $countUpdated mis à jour, $countErrors erreurs.");

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
