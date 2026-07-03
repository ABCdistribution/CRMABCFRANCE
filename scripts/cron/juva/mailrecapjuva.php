<?php
// === CONFIGURATION ===
$backupBase = '/home/backupJuva/export/';
// $to = 'presta.dev@abcosmetique.com';
$to = 'gregory.sylvestre@abcosmetique.com, laurence.carmagnolle@abcosmetique.com, presta.dev@abcosmetique.com';
$from = 'support@abcosmetique.com';

include('/var/www/gescom/scripts/init_scripts.php');
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
                'promoteur' => $promoteur 
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

foreach ($externalCommandes as $ec) {
    $line = "- {$ec['code']} | {$ec['enseigne']} | Promoteur : {$ec['promoteur']} | Date : {$ec['daterealisation']} | Ref Juva : {$ec['client']}";
    $body .= $line . "\n";
}

    $body .= "\nTotal commandes (externe = 1) : " . count($externalCommandes) . " commande(s).\n";
}

// === ENVOI DU MAIL ===

log_msg("DEBUG: Contenu des commandes externe = 1");
var_dump($externalCommandes);
 
$headers = "From: $from\r\n";
if (mail($to, $subject, $body, $headers)) {
    log_msg("✅ Email envoyé à $to");
} else {
    log_msg("❌ Échec de l’envoi de l’email à $to");
}
?>
