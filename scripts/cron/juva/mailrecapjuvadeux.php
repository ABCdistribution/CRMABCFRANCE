<?php
// === CONFIGURATION ===
$backupBase = '/home/backupJuva/export/';
// $to = 'presta.dev@abcosmetique.com';
$to = 'gregory.sylvestre@abcosmetique.com, laurence.carmagnolle@abcosmetique.com, presta.dev@abcosmetique.com';
$from = 'support@abcosmetique.com';

require_once('/var/www/gescom/vendor/autoload.php');
include('/var/www/gescom/scripts/init_scripts.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $db;
$db->execute("SET NAMES 'utf8mb4'");

// === Fonction de log ===
function log_msg($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
}

// === DATE D’HIER ===
$yesterday = strtotime('-1 day');
$targetDate = date('Y-m-d', $yesterday);
$displayDate = date('d/m/Y', $yesterday);
$pattern = $backupBase . $targetDate . '_*';

// === Commandes du CSV ===
$commandes = [];

$dirs = glob($pattern, GLOB_ONLYDIR);

if (empty($dirs)) {
    log_msg("⚠️ Aucun dossier de backup trouvé pour la date : $targetDate – On continue avec les données en base.");
} else {
    $latestDir = $dirs[0];
    $commandeCsv = $latestDir . '/Commande.csv';

    if (file_exists($commandeCsv)) {
        log_msg("📁 Dossier backup trouvé : $latestDir");
        log_msg("📄 Lecture de : $commandeCsv");

        $fp = fopen($commandeCsv, 'r');
        $headers = fgetcsv($fp, 0, ';');

        $idxNum = array_search('Code', $headers);
        $idxClient = array_search('Client', $headers);
        $idxDate = array_search('Date', $headers);
        $idxUtilisateur = array_search('Utilisateur', $headers);

        while (($row = fgetcsv($fp, 0, ';')) !== false) {
            $codeClient = $row[$idxClient];
            $login = $row[$idxUtilisateur];

            // Requête pour obtenir le nom de l’enseigne
            $enseigne = '(inconnu)';
            $sql = "
                SELECT r.enseigne
                FROM ref_client_infos i
                JOIN ref_client r ON r.id_as400 = i.id_ref_client
                WHERE i.num_juva = '$codeClient'
                LIMIT 1
            ";
            $db->execute($sql);
            $res = $db->get();
            if (!empty($res)) {
                $enseigne = $res[0]['enseigne'];
            }

            // Requête pour obtenir le nom du promoteur
            $promoteur = '(non trouvé)';
            $sqlPromoteur = "SELECT displayname FROM user WHERE login = '$login' LIMIT 1";
            log_msg("🔍 Requête promoteur : $sqlPromoteur");
            $db->execute($sqlPromoteur);
            $resPromoteur = $db->get();
            if (!empty($resPromoteur)) {
                $promoteur = $resPromoteur[0]['displayname'];
            }

            $commandes[] = [
                'numero' => $row[$idxNum],
                'client' => $codeClient,
                'enseigne' => $enseigne,
                'date' => $row[$idxDate],
                'promoteur' => $promoteur,
                'source' => 'CSV'
            ];
        }

        fclose($fp);
        log_msg("📦 " . count($commandes) . " commande(s) trouvée(s) dans le fichier CSV");
    } else {
        log_msg("⚠️ Fichier Commande.csv introuvable dans : $latestDir – On continue avec les données en base.");
    }
}

// === Commandes en base avec externe = 1 (non issues du fichier CSV) ===
$sqlMissing = "
    SELECT c.code, c.client, c.daterealisation, c.daterealisation, u.displayname AS promoteur
    FROM juva_commande c
    LEFT JOIN user u ON u.login = c.utilisateur
    WHERE DATE(c.daterealisation) = '" . date('Y-m-d', $yesterday) . "'
      AND c.externe = 1
";
log_msg("🔍 Requête commandes externe = 1 : $sqlMissing");

$db->execute($sqlMissing);
$missingCommandes = $db->get();

log_msg("📛 " . count($missingCommandes) . " commande(s) en base non issues du fichier CSV (externe = 1) :");

foreach ($missingCommandes as $mc) {
    log_msg("- {$mc['code']} | Client : {$mc['client']} | Promoteur : {$mc['promoteur']} | Date : {$mc['daterealisation']}");
}

// === Commandes en base avec externe = 1 ===
$sqlExternes = "
    SELECT c.code, c.client, c.daterealisation, c.daterealisation, u.displayname AS promoteur
    FROM juva_commande c
    LEFT JOIN user u ON u.login = c.utilisateur
    WHERE DATE(c.daterealisation) = '" . date('Y-m-d', $yesterday) . "'
      AND c.externe = 1
";

$db->execute($sqlExternes);
$externalCommandes = $db->get();

log_msg("📦 " . count($externalCommandes) . " commande(s) avec externe = 1 trouvée(s) en base :");

foreach ($externalCommandes as $ec) {
    log_msg("- {$ec['code']} | Client : {$ec['client']} | Promoteur : {$ec['promoteur']} | Date : {$ec['daterealisation']}");
}

// === Enrichissement des enseignes pour les commandes externes ===
foreach ($externalCommandes as $idx => $ec) {
    $enseigne = '(inconnu)';
    $client = $ec['client'];

    $sql = "
        SELECT r.enseigne
        FROM ref_client_infos i
        JOIN ref_client r ON r.id_as400 = i.id_ref_client
        WHERE i.num_juva = '$client'
        LIMIT 1
    ";
    $db->execute($sql);
    $res = $db->get();
    if (!empty($res)) {
        $enseigne = $res[0]['enseigne'];
    }
    $externalCommandes[$idx]['enseigne'] = $enseigne;
}

// === CONSTRUCTION DU MAIL ===
$subject = "✅ Commandes du jour envoyées à Juva – $displayDate";
$body = "Commandes du jour envoyées à Juva – $displayDate\n\n";

// Commandes du fichier CSV
if (!empty($commandes)) {
    $body .= "Commandes issues du fichier CSV :\n";
    foreach ($commandes as $cde) {
        $line = "- {$cde['numero']} | {$cde['enseigne']} | Promoteur : {$cde['promoteur']} | Date : {$cde['date']} | Ref Juva : {$cde['client']}";
        $body .= $line . "\n";
    }
    $body .= "\nTotal : " . count($commandes) . " commande(s) du fichier CSV.\n\n";
}

// Commandes externe = 1 en base
if (!empty($externalCommandes)) {
    $body .= "Commandes envoyés par mail:\n";
    foreach ($externalCommandes as $ec) {
        $line = "- {$ec['code']} | {$ec['enseigne']} | Promoteur : {$ec['promoteur']} | Date : {$ec['daterealisation']} | Ref Juva : {$ec['client']}";
        $body .= $line . "\n";
    }

    $body .= "\nTotal commandes (externe = 1) : " . count($externalCommandes) . " commande(s).\n";
}

// === GÉNÉRATION DU CSV RÉCAPITULATIF À JOINDRE ===
$csvHeaders = ['code', 'enseigne', 'promoteur', 'date', 'client', 'source'];
$csvRows = [];

foreach ($commandes as $c) {
    $csvRows[] = [
        'code' => $c['numero'],
        'enseigne' => $c['enseigne'],
        'promoteur' => $c['promoteur'],
        'date' => $c['date'],
        'client' => $c['client'],
        'source' => 'CSV'
    ];
}

foreach ($externalCommandes as $e) {
    $csvRows[] = [
        'code' => $e['code'],
        'enseigne' => isset($e['enseigne']) ? $e['enseigne'] : '',
        'promoteur' => $e['promoteur'],
        'date' => $e['daterealisation'],
        'client' => $e['client'],
        'source' => 'BASE(externe=1)'
    ];
}

$csvStream = fopen('php://temp', 'r+');
fputcsv($csvStream, $csvHeaders, ';');
foreach ($csvRows as $row) {
    fputcsv($csvStream, $row, ';');
}
rewind($csvStream);
$csvContent = stream_get_contents($csvStream);
fclose($csvStream);

// === ENVOI DU MAIL AVEC PHPMailer ===
$mail = new PHPMailer(true);

try {
    // Transport par défaut via mail() système
    $mail->isMail();
    $mail->CharSet = 'UTF-8';

    // Expéditeur
    $mail->setFrom($from, 'Support ABCosmetique');

    // Destinataires (séparés par virgule)
    foreach (explode(',', $to) as $addr) {
        $addr = trim($addr);
        if ($addr !== '') {
            $mail->addAddress($addr);
        }
    }

    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = $body;

    // Pièce jointe CSV
    $filename = 'recap-juva-' . date('Y-m-d', $yesterday) . '.csv';
    $mail->addStringAttachment($csvContent, $filename, 'base64', 'text/csv');

    $mail->send();
    log_msg("✅ Email envoyé à $to (avec pièce jointe $filename)");
} catch (Exception $e) {
    log_msg("❌ Échec de l’envoi de l’email: " . $mail->ErrorInfo);
}

?>


