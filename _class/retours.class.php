<?php
/**
 * Retours produits APK - export et données.
 */
class retours {

  /**
   * Récupère un rappel produit par id.
   * @param int $id
   * @return array|null
   */
  public static function get($id) {
    global $db;
    $id = (int) $id;
    if ( $id < 1 ) return null;
    $db->execute("
      SELECT r.id, r.user_id, r.id_bordereau, r.photo_path, r.code_magasin, r.num_lot, r.scan_produit, r.code_produit, r.quantite, r.action_produit, r.date_retour,
             u.displayname AS promoteur
      FROM retours_produits_apk r
      LEFT JOIN user u ON u.id = r.user_id
      WHERE r.id = $id
      LIMIT 1
    ");
    return $db->num() ? $db->assoc() : null;
  }

  /**
   * Toutes les lignes d'un même bordereau (même user).
   *
   * @param string $id_bordereau
   * @param int    $user_id
   * @return array<int, array>
   */
  public static function getLignesBordereau($id_bordereau, $user_id) {
    global $db;
    $id_bordereau = trim((string) $id_bordereau);
    $user_id = (int) $user_id;
    if ( $id_bordereau === '' || $user_id < 1 ) {
      return [];
    }
    $db->execute('
      SELECT r.id, r.id_bordereau, r.photo_path, r.code_magasin, r.num_lot, r.scan_produit, r.code_produit, r.quantite, r.action_produit, r.date_retour
      FROM retours_produits_apk r
      WHERE r.id_bordereau = "' . $db->escape($id_bordereau) . '" AND r.user_id = ' . $user_id . '
      ORDER BY r.id ASC
    ');
    $out = [];
    while ( $r = $db->assoc() ) {
      $out[] = $r;
    }
    return $out;
  }

  /**
   * Export CSV des retours (filtres optionnels : magasin, promoteur).
   * Appelé en POST/GET via async avec magasin, promoteur.
   */
  public function exportRetours() {
    if ( !securite::can(28) ) {
      core::ajaxError('Accès interdit');
    }
    global $db;

    $db->execute("
      SELECT id_as400, enseigne
      FROM ref_client
      WHERE deleted = 0 AND actif = 1
    ");
    $magasinLabels = [];
    while ( $r = $db->assoc() ) {
      $code = trim((string) $r['id_as400']);
      $label = trim((string) ($r['enseigne'] ?? ''));
      if ( $code === '' ) continue;
      $magasinLabels[$code] = $label;
      $normalized = ltrim($code, '0');
      if ( $normalized !== '' ) {
        $magasinLabels[$normalized] = $label;
      }
    }

    $code_magasin = trim($_POST['magasin'] ?? $_GET['magasin'] ?? '');
    $id_promoteur = (int) ($_POST['promoteur'] ?? $_GET['promoteur'] ?? 0);

    $where = ['1=1'];
    $params = [];
    if ( $code_magasin !== '' ) {
      $where[] = 'r.code_magasin = "' . $db->escape($code_magasin) . '"';
    }
    if ( $id_promoteur > 0 ) {
      $where[] = 'r.user_id = ' . $id_promoteur;
    }

    $q = "
      SELECT r.id, r.id_bordereau, r.date_retour, u.displayname AS promoteur, r.code_magasin, r.num_lot,
             r.scan_produit, r.code_produit, r.quantite, r.action_produit,
             (SELECT a.id FROM ref_article a
              WHERE (a.id_as400 = r.code_produit OR a.gencode = r.code_produit OR a.id_as400 = r.scan_produit OR a.gencode = r.scan_produit)
                AND a.deleted = 0 AND a.actif = 1
              LIMIT 1) AS code_minos,
             (SELECT a.id_as400 FROM ref_article a
              WHERE (a.id_as400 = r.code_produit OR a.gencode = r.code_produit OR a.id_as400 = r.scan_produit OR a.gencode = r.scan_produit)
                AND a.deleted = 0 AND a.actif = 1
              LIMIT 1) AS ref_article
      FROM retours_produits_apk r
      LEFT JOIN user u ON u.id = r.user_id
      WHERE " . implode(' AND ', $where) . "
      ORDER BY r.id DESC
    ";
    $db->execute($q);
    $rows = [];
    while ( $r = $db->assoc() ) {
      $rows[] = $r;
    }

    $fileName = 'retours_produits_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, [
      'N° bordereau', 'Id ligne', 'Date retour', 'Promoteur', 'Code magasin', 'Libellé magasin', 'N° lot',
      'Code EAN', 'Code produit Minos', 'Ref article', 'Quantité', 'Action'
    ], ';');

    foreach ( $rows as $c ) {
      $code = trim((string) ($c['code_magasin'] ?? ''));
      $label = $magasinLabels[$code] ?? $magasinLabels[ltrim($code, '0')] ?? '';
      fputcsv($out, [
        trim((string) ($c['id_bordereau'] ?? '')) ?: '—',
        $c['id'],
        $c['date_retour'] ?? '',
        $c['promoteur'] ?? '',
        $code,
        $label,
        $c['num_lot'] ?? '',
        $c['scan_produit'] ?? '',
        $c['code_minos'] ?? '',
        $c['ref_article'] ?? '',
        (int) $c['quantite'],
        $c['action_produit'] ?? ''
      ], ';');
    }

    fclose($out);
    exit;
  }

  /**
   * Export Excel (CSV) d'un rappel (bordereau ou ligne unique).
   * POST: id_rappel = id de la ligne retours_produits_apk.
   */
  public function exportRappelExcel() {
    if ( !securite::can(28) ) {
      core::ajaxError('Accès interdit');
    }
    $id_rappel = (int) ($_POST['id_rappel'] ?? $_GET['id_rappel'] ?? 0);
    if ( $id_rappel < 1 ) {
      core::ajaxError('Rappel invalide');
    }
    global $db;
    $rappel = self::get($id_rappel);
    if ( !$rappel ) {
      core::ajaxError('Rappel introuvable');
    }
    $id_bordereau = trim((string) ($rappel['id_bordereau'] ?? ''));
    $user_id = (int) ($rappel['user_id'] ?? 0);
    $lignes = $id_bordereau !== '' && $user_id > 0
      ? self::getLignesBordereau($id_bordereau, $user_id)
      : [$rappel];

    $db->execute("SELECT id_as400, enseigne FROM ref_client WHERE deleted = 0 AND actif = 1");
    $magasinLabels = [];
    while ( $r = $db->assoc() ) {
      $code = trim((string) $r['id_as400']);
      if ( $code === '' ) continue;
      $magasinLabels[$code] = trim((string) ($r['enseigne'] ?? ''));
      $magasinLabels[ltrim($code, '0')] = $magasinLabels[$code];
    }

    $rows = [];
    foreach ( $lignes as $L ) {
      $code = trim((string) ($L['code_magasin'] ?? ''));
      $label = $magasinLabels[$code] ?? $magasinLabels[ltrim($code, '0')] ?? '';
      $code_minos = null;
      $ref_article = null;
      $cp = trim((string) ($L['code_produit'] ?? ''));
      $sp = trim((string) ($L['scan_produit'] ?? ''));
      if ( $cp !== '' || $sp !== '' ) {
        $db->execute("SELECT id, id_as400 FROM ref_article WHERE deleted = 0 AND actif = 1 AND (id_as400 IN ('" . $db->escape($cp) . "','" . $db->escape($sp) . "') OR gencode IN ('" . $db->escape($cp) . "','" . $db->escape($sp) . "')) LIMIT 1");
        if ( $db->num() ) {
          $art = $db->assoc();
          $code_minos = $art['id'];
          $ref_article = $art['id_as400'] ?? '';
        }
      }
      $rows[] = [
        'id_bordereau' => $id_bordereau ?: '—',
        'id' => $L['id'],
        'date_retour' => $L['date_retour'] ?? '',
        'promoteur' => $rappel['promoteur'] ?? '',
        'code_magasin' => $code,
        'libelle_magasin' => $label,
        'num_lot' => $L['num_lot'] ?? '',
        'scan_produit' => $L['scan_produit'] ?? '',
        'code_minos' => $code_minos,
        'ref_article' => $ref_article,
        'quantite' => (int) ($L['quantite'] ?? 0),
        'action_produit' => $L['action_produit'] ?? ''
      ];
    }

    $fileName = 'rappel_' . ( $id_bordereau !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $id_bordereau) : $id_rappel ) . '_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, [
      'N° bordereau', 'Id ligne', 'Date retour', 'Promoteur', 'Code magasin', 'Libellé magasin', 'N° lot',
      'Code EAN', 'Code produit Minos', 'Ref article', 'Quantité', 'Action'
    ], ';');
    foreach ( $rows as $c ) {
      fputcsv($out, [
        $c['id_bordereau'], $c['id'], $c['date_retour'], $c['promoteur'], $c['code_magasin'], $c['libelle_magasin'],
        $c['num_lot'], $c['scan_produit'], $c['code_minos'] ?? '', $c['ref_article'] ?? '', $c['quantite'], $c['action_produit']
      ], ';');
    }
    fclose($out);
    exit;
  }

  /**
   * Export PDF d'un rappel (bordereau ou ligne unique).
   * POST: id_rappel = id de la ligne retours_produits_apk.
   */
  public function exportRappelPdf() {
    if ( !securite::can(28) ) {
      core::ajaxError('Accès interdit');
    }
    $id_rappel = (int) ($_POST['id_rappel'] ?? $_GET['id_rappel'] ?? 0);
    if ( $id_rappel < 1 ) {
      core::ajaxError('Rappel invalide');
    }
    $rappel = self::get($id_rappel);
    if ( !$rappel ) {
      core::ajaxError('Rappel introuvable');
    }
    $id_bordereau = trim((string) ($rappel['id_bordereau'] ?? ''));
    $user_id = (int) ($rappel['user_id'] ?? 0);
    $lignes = $id_bordereau !== '' && $user_id > 0
      ? self::getLignesBordereau($id_bordereau, $user_id)
      : [$rappel];

    global $db;
    $libelleMap = [];
    $codeMinosMap = [];
    $refArticleMap = [];
    $refsSet = [];
    foreach ( $lignes as $L ) {
      foreach ( [trim((string) ($L['code_produit'] ?? '')), trim((string) ($L['scan_produit'] ?? ''))] as $v ) {
        if ( $v !== '' ) { $refsSet[$v] = true; $refsSet[ltrim($v, '0')] = true; }
      }
    }
    if ( !empty($refsSet) ) {
      $in = [];
      foreach ( array_keys($refsSet) as $r ) {
        $in[] = "'" . $db->escape($r) . "'";
      }
      $db->execute('SELECT id, id_as400, gencode, libelle FROM ref_article WHERE deleted = 0 AND actif = 1 AND (id_as400 IN (' . implode(',', $in) . ') OR gencode IN (' . implode(',', $in) . '))');
      while ( $r = $db->assoc() ) {
        $id = trim((string) ($r['id_as400'] ?? ''));
        $lib = trim((string) ($r['libelle'] ?? ''));
        $cm = isset($r['id']) ? (int) $r['id'] : '';
        if ( $id !== '' ) {
          $libelleMap[$id] = $lib; $codeMinosMap[$id] = $cm; $refArticleMap[$id] = $id;
          $n = ltrim($id, '0');
          if ( $n !== '' ) { $libelleMap[$n] = $lib; $codeMinosMap[$n] = $cm; $refArticleMap[$n] = $id; }
        }
        $ge = trim((string) ($r['gencode'] ?? ''));
        if ( $ge !== '' ) { $libelleMap[$ge] = $lib; $codeMinosMap[$ge] = $cm; $refArticleMap[$ge] = $id; }
      }
    }
    $getLibelle = function ( $L ) use ( $libelleMap ) {
      foreach ( [trim((string)($L['code_produit']??'')), ltrim(trim((string)($L['code_produit']??'')),'0'), trim((string)($L['scan_produit']??'')), ltrim(trim((string)($L['scan_produit']??'')),'0')] as $k ) {
        if ( $k !== '' && isset($libelleMap[$k]) ) return $libelleMap[$k];
      }
      return '—';
    };
    $getRef = function ( $L ) use ( $refArticleMap ) {
      foreach ( [trim((string)($L['code_produit']??'')), ltrim(trim((string)($L['code_produit']??'')),'0'), trim((string)($L['scan_produit']??'')), ltrim(trim((string)($L['scan_produit']??'')),'0')] as $k ) {
        if ( $k !== '' && isset($refArticleMap[$k]) ) return $refArticleMap[$k];
      }
      return '—';
    };
    $getCodeMinos = function ( $L ) use ( $codeMinosMap ) {
      foreach ( [trim((string)($L['code_produit']??'')), ltrim(trim((string)($L['code_produit']??'')),'0'), trim((string)($L['scan_produit']??'')), ltrim(trim((string)($L['scan_produit']??'')),'0')] as $k ) {
        if ( $k !== '' && isset($codeMinosMap[$k]) ) return $codeMinosMap[$k];
      }
      return '—';
    };
    $code_mag = trim((string) ($rappel['code_magasin'] ?? ''));
    $libelle_magasin = '';
    if ( $code_mag !== '' ) {
      $db->execute("SELECT enseigne FROM ref_client WHERE (id_as400 = '" . $db->escape($code_mag) . "' OR id_as400 = '" . $db->escape(ltrim($code_mag, '0')) . "') AND deleted = 0 LIMIT 1");
      if ( $db->num() ) {
        $libelle_magasin = trim((string) $db->assoc()['enseigne']);
      }
    }

    ob_start();
    include(PARTIAL . 'pdf/retour-rappel.php');
    $html = ob_get_clean();

    require APP_ROOT . '/vendor/autoload.php';
    $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
    $html2pdf->writeHTML($html);
    $fileName = 'rappel_' . ( $id_bordereau !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $id_bordereau) : $id_rappel ) . '_' . date('Y-m-d_His') . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $html2pdf->output($fileName);
    exit;
  }

  /**
   * Génère un fichier RAPxxxx.txt dans le dossier des commandes
   * à partir d'un bordereau Tracky.
   * Seules les lignes de rappel avec quantite > 0 sont exportées.
   *
   * @param string $id_bordereau
   * @param int    $user_id
   * @param int    $id_export_seq
   * @param PDO|null $pdo Connexion PDO (ex. Tracky) : évite db::execute qui fait die() hors contexte AJAX
   * @return string|false Nom de fichier généré ou false si rien à générer
   */
  public static function generateRappelTxtFromBordereau($id_bordereau, $user_id, $id_export_seq = 0, $pdo = null) {
    global $db;
    $id_bordereau = trim((string) $id_bordereau);
    $user_id = (int) $user_id;
    $id_export_seq = (int) $id_export_seq;

    if ( $id_bordereau === '' || $user_id < 1 ) {
      return false;
    }

    $primaryDirOk = defined('DIR_CMD') && is_dir(DIR_CMD) && is_writable(DIR_CMD);
    if ( ! $primaryDirOk ) {
      error_log('[RAPPEL] DIR_CMD indisponible ou non inscriptible : ' . (defined('DIR_CMD') ? DIR_CMD : '(non défini)') . ' — tentative secours BACKUP_CMD');
    }

    if ( $pdo instanceof PDO ) {
      $lignes = self::fetchLignesBordereauPdo($pdo, $id_bordereau, $user_id);
    } else {
      $lignes = self::getLignesBordereau($id_bordereau, $user_id);
    }
    if ( empty($lignes) ) {
      return false;
    }

    $codeMagasin = trim((string) ($lignes[0]['code_magasin'] ?? ''));
    $eanClient = '';
    $idAs400Client = '';
    if ( $codeMagasin !== '' ) {
      if ( $pdo instanceof PDO ) {
        $c = self::fetchRefClientPdo($pdo, $codeMagasin);
        if ( is_array($c) ) {
          $eanClient = trim((string) ($c['ean_client'] ?? ''));
          $idAs400Client = trim((string) ($c['id_as400'] ?? ''));
        }
      } else {
        $db->execute("SELECT ean_client, id_as400 FROM ref_client WHERE id_as400 IN ('" . $db->escape($codeMagasin) . "','" . $db->escape(ltrim($codeMagasin, '0')) . "') LIMIT 1");
        if ( $db->num() ) {
          $c = $db->assoc();
          $eanClient = trim((string) ($c['ean_client'] ?? ''));
          $idAs400Client = trim((string) ($c['id_as400'] ?? ''));
        }
      }
    }

    $referenceRap = $id_export_seq > 0
      ? $id_export_seq
      : (int) preg_replace('/\D+/', '', (string) $id_bordereau);
    if ( $referenceRap < 1 ) {
      $referenceRap = time();
    }

    $queueDate = '';
    $dateLiv = '';
    foreach ( $lignes as $ligne ) {
      if ( (int) ($ligne['quantite'] ?? 0) > 0 ) {
        $d = (string) ($ligne['date_retour'] ?? '');
        $queueDate = $d;
        $dateLiv = $d;
        break;
      }
    }
    if ( $queueDate === '' ) {
      $queueDate = (string) ($lignes[0]['date_retour'] ?? date('Y-m-d H:i:s'));
      $dateLiv = $queueDate;
    }

    $noClient = preg_replace('/[^A-Za-z0-9 ]/', '', $id_bordereau);
    $lenFn = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
    $subFn = function_exists('mb_substr') ? 'mb_substr' : 'substr';
    if ( $lenFn($noClient) > 30 ) {
      $noClient = $subFn($noClient, 0, 30);
    }

    $struc = [
      1 => ["length" => 4,  "value" => "ABC", "PAD" => "right"],
      2 => ["length" => 3,  "value" => "ABC", "PAD" => "right"],
      3 => ["length" => 3,  "value" => "LOG", "PAD" => "left"],
      4 => ["length" => 13, "value" => str_replace(["\r", "\n"], "", $eanClient), "PAD" => "left"],
      5 => ["length" => 13, "value" => str_replace(["\r", "\n"], "", $idAs400Client), "PAD" => "right"],
      6 => ["length" => 13, "value" => "", "PAD" => "left"],
      7 => ["length" => 8,  "value" => substr(self::toEdiDate($queueDate), 0, 8), "PAD" => "left"],
      8 => ["length" => 15, "value" => $referenceRap, "fill" => "0", "PAD" => "left"],
      9 => ["length" => 30, "value" => $noClient, "PAD" => "right"],
      10 => ["length" => 8, "value" => str_replace("-", "", substr((string) $dateLiv, 0, 10)), "PAD" => "left"],
    ];

    $fileLines = [];
    foreach ( $lignes as $ligne ) {
      $quantite = (int) ($ligne['quantite'] ?? 0);
      if ( $quantite <= 0 ) {
        continue;
      }

      $ean = trim((string) ($ligne['scan_produit'] ?? ''));
      if ( $ean === '' ) {
        $ean = trim((string) ($ligne['code_produit'] ?? ''));
      }

      $line = $struc;
      $line[11] = ["length" => 13, "value" => $ean, "PAD" => "left"];
      $line[12] = ["length" => 15, "value" => $quantite, "PAD" => "left", "decimal" => 1];
      $fileLines[] = self::generateRapLine($line);
    }

    if ( empty($fileLines) ) {
      error_log('[RAPPEL] Aucun produit avec quantité > 0, aucun fichier RAP pour ' . $id_bordereau);
      return false;
    }

    $filename = 'RAP' . str_pad((string) $referenceRap, 10, '0', STR_PAD_LEFT) . '.txt';
    // Ajoute un saut de ligne final pour éviter que le prompt shell se colle au contenu.
    $content = implode("\r\n", $fileLines) . "\r\n";
    $writtenPath = null;

    if ( $primaryDirOk ) {
      $fullPath = rtrim(DIR_CMD, '/') . '/' . $filename;
      $n = @file_put_contents($fullPath, $content);
      if ( $n !== false ) {
        $writtenPath = $fullPath;
        error_log('[RAPPEL] Fichier écrit : ' . $fullPath . ' (' . $n . ' octets)');
      } else {
        error_log('[RAPPEL] Échec écriture primaire : ' . $fullPath);
      }
    }

    $backupDirOk = defined('BACKUP_CMD') && is_dir(BACKUP_CMD) && is_writable(BACKUP_CMD);
    $backupPath = $backupDirOk ? (rtrim(BACKUP_CMD, '/') . '/' . $filename) : null;

    if ( $writtenPath === null && $backupPath !== null ) {
      $n = @file_put_contents($backupPath, $content);
      if ( $n !== false ) {
        $writtenPath = $backupPath;
        error_log('[RAPPEL] Fichier écrit (secours BACKUP_CMD) : ' . $backupPath . ' (' . $n . ' octets)');
      } else {
        error_log('[RAPPEL] Échec écriture secours : ' . $backupPath);
      }
    } elseif ( $writtenPath !== null && $backupPath !== null ) {
      @copy($writtenPath, $backupPath);
    }

    if ( $writtenPath === null ) {
      return false;
    }

    return $filename;
  }

  private static function toEdiDate($dateStr) {
    $ts = strtotime((string) $dateStr);
    if ( $ts === false ) {
      return date('Ymd');
    }
    return date('Ymd', $ts);
  }

  private static function generateRapLine($t) {
    $tmp = [];
    foreach ( $t as $e ) {
      $fill = $e['fill'] ?? ' ';
      $pad = $e['PAD'] ?? 'right';
      if ( !isset($e['decimal']) ) {
        $el = str_pad($e['value'], $e['length'], $fill, $pad === 'left' ? STR_PAD_LEFT : STR_PAD_RIGHT);
      } else {
        if ( strpos((string) $e['value'], '.') > -1 ) {
          list($ent, $dec) = explode('.', (string) $e['value']);
        } else if ( strpos((string) $e['value'], ',') > -1 ) {
          list($ent, $dec) = explode(',', (string) $e['value']);
        } else {
          $ent = $e['value'];
          $dec = 0;
        }
        if ( !$dec || !isset($dec) ) {
          $dec = 0;
        }
        $el = str_pad($ent, $e['length'] - $e['decimal'], '0', $pad === 'left' ? STR_PAD_LEFT : STR_PAD_RIGHT)
          . str_pad($dec, $e['decimal'], '0', STR_PAD_RIGHT);
      }
      $tmp[] = $el;
    }
    return implode($tmp);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private static function fetchLignesBordereauPdo(PDO $pdo, $id_bordereau, $user_id) {
    try {
      $stmt = $pdo->prepare(
        'SELECT r.id, r.id_bordereau, r.photo_path, r.code_magasin, r.num_lot, r.scan_produit, r.code_produit, r.quantite, r.action_produit, r.date_retour
         FROM retours_produits_apk r
         WHERE r.id_bordereau = :bordereau AND r.user_id = :uid
         ORDER BY r.id ASC'
      );
      $stmt->execute([':bordereau' => $id_bordereau, ':uid' => $user_id]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return is_array($rows) ? $rows : [];
    } catch (PDOException $e) {
      error_log('[RAPPEL] fetchLignesBordereauPdo: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * @return array<string, mixed>|null
   */
  private static function fetchRefClientPdo(PDO $pdo, $codeMagasin) {
    try {
      $trim = trim((string) $codeMagasin);
      $ltrim = ltrim($trim, '0');
      $stmt = $pdo->prepare('SELECT ean_client, id_as400 FROM ref_client WHERE id_as400 IN (:a, :b) LIMIT 1');
      $stmt->execute([':a' => $trim, ':b' => $ltrim]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row ?: null;
    } catch (PDOException $e) {
      error_log('[RAPPEL] fetchRefClientPdo: ' . $e->getMessage());
      return null;
    }
  }
}
