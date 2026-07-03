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
$filename = 'Client.csv';

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
    die("❌ Échec du téléchargement du fichier $filename\n");
}
log_msg("📥 Fichier téléchargé localement : $localPath");

// Conversion du fichier ANSI (Windows-1252) en UTF-8
$content = file_get_contents($localPath);
$content_utf8 = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
$tempPath = $localDir . 'Client_utf8.csv';
file_put_contents($tempPath, $content_utf8);

// On utilise maintenant le fichier UTF-8
$localPath = $tempPath;

if (($handle = fopen($localPath, 'r')) === false) {
    die("❌ Impossible d'ouvrir le fichier local $localPath\n");
}

$header = fgetcsv($handle, 0, ';');
if ($header === false) {
    die("❌ Fichier CSV vide ou incorrect\n");
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
    $raisonSociale  = $db->escape(trim($data[$columns['RaisonSociale']] ?? ''));
    $visible        = trim($data[$columns['Visible']] ?? '');
    $surface        = trim($data[$columns['Surface']] ?? '');
    $adresse1       = $db->escape(trim($data[$columns['Adresse1']] ?? ''));
    $adresse2       = $db->escape(trim($data[$columns['Adresse2']] ?? ''));
    $adresse3       = $db->escape(trim($data[$columns['Adresse3']] ?? ''));
    $codePostal     = trim($data[$columns['CodePostal']] ?? '');
    $ville          = $db->escape(trim($data[$columns['Ville']] ?? ''));
    $enseigne       = $db->escape(trim($data[$columns['Enseigne']] ?? ''));
    $champExt2      = $db->escape(trim($data[$columns['ChampExt2']] ?? ''));
    $champExt7      = $db->escape(trim($data[$columns['ChampExt7']] ?? ''));
    $champExt8      = $db->escape(trim($data[$columns['ChampExt8']] ?? ''));
    $champExt9      = $db->escape(trim($data[$columns['ChampExt9']] ?? ''));
    $champExt10     = $db->escape(trim($data[$columns['ChampExt10']] ?? ''));

    $db->execute("SELECT COUNT(*) as cnt FROM juva_client WHERE Gencod = '$gencod' OR IdOriginal = '$idOriginal'");
    $row = $db->get()[0] ?? ['cnt' => 0];
    $exists = ($row['cnt'] > 0);

    $sqlVal = function($val) {
        return ($val !== '') ? "'$val'" : "NULL";
    };

    if ($exists) {
        $sql = "
            UPDATE juva_client SET
                IdOriginal = '$idOriginal',
                Libelle = '$libelle',
                RaisonSociale = '$raisonSociale',
                Visible = {$sqlVal($visible)},
                Surface = {$sqlVal($surface)},
                Adresse1 = '$adresse1',
                Adresse2 = '$adresse2',
                Adresse3 = '$adresse3',
                CodePostal = {$sqlVal($codePostal)},
                Ville = '$ville',
                Enseigne = '$enseigne',
                ChampExt2 = '$champExt2',
                ChampExt7 = '$champExt7',
                ChampExt8 = '$champExt8',
                ChampExt9 = '$champExt9',
                ChampExt10 = '$champExt10'
            WHERE Gencod = '$gencod' OR IdOriginal = '$idOriginal'
        ";
        if ($db->execute($sql)) {
            $countUpdated++;
        } else {
            log_msg("⚠️ Erreur UPDATE client ($gencod)");
            $countErrors++;
        }
    } else {
        $sql = "
            INSERT INTO juva_client
                (IdOriginal, Gencod, Libelle, RaisonSociale, Visible, Surface, Adresse1, Adresse2, Adresse3,
                 CodePostal, Ville, Enseigne, ChampExt2, ChampExt7, ChampExt8, ChampExt9, ChampExt10)
            VALUES
                ('$idOriginal', '$gencod', '$libelle', '$raisonSociale',
                 {$sqlVal($visible)}, {$sqlVal($surface)}, '$adresse1', '$adresse2', '$adresse3',
                 {$sqlVal($codePostal)}, '$ville', '$enseigne',
                 '$champExt2', '$champExt7', '$champExt8', '$champExt9', '$champExt10')
        ";
        if ($db->execute($sql)) {
            $countInserted++;
        } else {
            log_msg("⚠️ Erreur INSERT client ($gencod)");
            $countErrors++;
        }
    }
    // 🔁 Mise à jour dans ref_client_infos
$client = null;

// Match par GENCOD si non vide et différent de gencod null
if ($gencod !== '' && $gencod !== '0000000000000') {
    $db->execute("
        SELECT id_as400 FROM ref_client 
        WHERE ean_client = '$gencod' 
        LIMIT 1
    ");
    $client = $db->get()[0] ?? null;
    if ($client) {
        log_msg("🔍 Match GENCOD trouvé pour $gencod → id_as400 = {$client['id_as400']}");
    }
}

// Sinon, match par adresse
if (!$client && $adresse1 !== '' && $codePostal !== '' && $ville !== '') {
    $db->execute("
        SELECT id_as400 FROM ref_client 
        WHERE adresse1 = '$adresse1' 
          AND (code_postal = '$codePostal' OR code_postal_2 = '$codePostal') 
          AND ville = '$ville' 
        LIMIT 1
    ");
    $client = $db->get()[0] ?? null;
    if ($client) {
        log_msg("🧭 Match ADRESSE trouvé : $adresse1, $codePostal, $ville → id_as400 = {$client['id_as400']}");
    }
}

// Si un client a été trouvé, on met à jour ref_client_infos
if ($client) {
    $id_as400 = $client['id_as400'];
    $db->execute("
        UPDATE ref_client_infos 
        SET num_juva = '$idOriginal' 
        WHERE id_ref_client = '$id_as400'
    ");
    log_msg("🔁 ref_client_infos mis à jour : id_ref_client = $id_as400, num_juva = $idOriginal");
} else {
    log_msg("⚠️ Aucun match pour client ($gencod / $adresse1 - $codePostal - $ville)");
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
