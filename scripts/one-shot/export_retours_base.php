<?php
ini_set('memory_limit', '-1');
require_once(__DIR__ . '/../init_scripts.php');

global $db;

// Retours à exporter : date_retour <= ce jour (inclus), format AAAA-MM-JJ.
$DATE_RETOUR_MAX_INCLUSIVE = '2026-03-23';

// Bordereaux à ne pas exporter (ex. liste jointe opérationnelle) — laisser vide si aucune exclusion.
$EXCLURE_ID_BORDEREAUX = [];

$targetDir = APP_ROOT . 'RAPPEL/';
if ( !is_dir($targetDir) ) {
  @mkdir($targetDir, 0777, true);
}
@chmod($targetDir, 0777);

// Même traitement que retours.class.php : génération RAP*.txt par bordereau/user.
// Uniquement les bordereaux dont toutes les lignes sont au plus tard à DATE_MAX (aucune ligne après).
$dateSql = $db->escape($DATE_RETOUR_MAX_INCLUSIVE);
$db->execute("
  SELECT TRIM(r.id_bordereau) AS id_bordereau, r.user_id
  FROM retours_produits_apk r
  WHERE TRIM(COALESCE(r.id_bordereau, '')) <> '' AND r.user_id > 0
  GROUP BY TRIM(r.id_bordereau), r.user_id
  HAVING MAX(DATE(r.date_retour)) <= '" . $dateSql . "'
  ORDER BY MAX(r.id) DESC
");

$exclSet = [];
foreach ( $EXCLURE_ID_BORDEREAUX as $ex ) {
  $ex = trim((string) $ex);
  if ( $ex !== '' ) {
    $exclSet[$ex] = true;
  }
}

$pairs = [];
while ( $r = $db->assoc() ) {
  $idBordereau = trim((string) ($r['id_bordereau'] ?? ''));
  $userId = (int) ($r['user_id'] ?? 0);
  if ( $idBordereau === '' || $userId < 1 ) {
    continue;
  }
  if ( isset($exclSet[$idBordereau]) ) {
    continue;
  }
  $pairs[] = [
    'id_bordereau' => $idBordereau,
    'user_id' => $userId,
  ];
}

$total = count($pairs);
$ok = 0;
$skipped = 0;
$failed = 0;
$generated = [];

foreach ( $pairs as $pair ) {
  $file = retours::generateRappelTxtFromBordereau($pair['id_bordereau'], $pair['user_id']);
  if ( is_string($file) && $file !== '' ) {
    $sourcePath = null;
    if ( defined('DIR_CMD') ) {
      $candidate = rtrim((string) DIR_CMD, '/') . '/' . $file;
      if ( is_file($candidate) ) {
        $sourcePath = $candidate;
      }
    }
    if ( $sourcePath === null && defined('BACKUP_CMD') ) {
      $candidate = rtrim((string) BACKUP_CMD, '/') . '/' . $file;
      if ( is_file($candidate) ) {
        $sourcePath = $candidate;
      }
    }

    $copied = false;
    if ( $sourcePath !== null ) {
      $destPath = $targetDir . $file;
      $copied = @copy($sourcePath, $destPath);
      if ( $copied ) {
        @chmod($destPath, 0666);
      }
    }

    $ok++;
    $generated[] = $file;
    if ( $copied ) {
      echo '[OK] ' . $pair['id_bordereau'] . ' / user ' . $pair['user_id'] . ' => ' . $file . ' (copiee dans RAPPEL)' . PHP_EOL;
    } else {
      echo '[OK] ' . $pair['id_bordereau'] . ' / user ' . $pair['user_id'] . ' => ' . $file . ' (copie RAPPEL impossible)' . PHP_EOL;
    }
  } else {
    // false = rien à générer (ex: quantités à 0) ou échec d'écriture.
    $db->execute("
      SELECT COUNT(*) AS nb_lignes,
             SUM(CASE WHEN quantite > 0 THEN 1 ELSE 0 END) AS nb_qte_pos
      FROM retours_produits_apk
      WHERE id_bordereau = '" . $db->escape($pair['id_bordereau']) . "'
        AND user_id = " . (int) $pair['user_id'] . "
        AND DATE(date_retour) <= '" . $dateSql . "'
    ");
    $check = $db->assoc();
    $nbQtePos = (int) ($check['nb_qte_pos'] ?? 0);
    if ( $nbQtePos > 0 ) {
      $failed++;
      echo '[KO] ' . $pair['id_bordereau'] . ' / user ' . $pair['user_id'] . ' => echec generation fichier' . PHP_EOL;
    } else {
      $skipped++;
      echo '[SKIP] ' . $pair['id_bordereau'] . ' / user ' . $pair['user_id'] . ' => aucune quantite > 0' . PHP_EOL;
    }
  }
}

echo PHP_EOL;
echo 'Traitement termine.' . PHP_EOL;
echo 'Filtre date_retour <= ' . $DATE_RETOUR_MAX_INCLUSIVE . ' (inclus).' . PHP_EOL;
echo 'Total bordereaux: ' . $total . PHP_EOL;
echo 'Generes: ' . $ok . PHP_EOL;
echo 'Sans export (qte <= 0): ' . $skipped . PHP_EOL;
echo 'Echecs: ' . $failed . PHP_EOL;
echo 'Dossier de depot RAPPEL: ' . $targetDir . PHP_EOL;
