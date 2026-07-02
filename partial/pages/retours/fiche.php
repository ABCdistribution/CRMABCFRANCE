<?php
if ( !isset($rappel) || !$rappel ) {
  echo '<div class="alert alert-warning">Rappel introuvable.</div>';
  echo '<a href="'.URL.'Retours" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour à la liste</a>';
  return;
}
$libelle_magasin = $rappel['libelle_magasin'] ?? '';
$lignes_bordereau = $lignes_bordereau ?? [$rappel];
$id_bordereau = trim((string) ($rappel['id_bordereau'] ?? ''));
$photo_path = '';
foreach ( $lignes_bordereau as $L ) {
  if ( !empty(trim((string) ($L['photo_path'] ?? ''))) ) {
    $photo_path = trim((string) $L['photo_path']);
    break;
  }
}
$photo_url = $photo_path !== '' ? rtrim(URL, '/') . '/datas/uploads/retours/' . rawurlencode($photo_path) : '';

// Libellés et infos produits (ref_article) — requête groupée
global $db;
$libelleMap = [];
$codeMinosMap = [];
$refArticleMap = [];
if ( !empty($lignes_bordereau) && isset($db) && $db ) {
  $refsSet = [];
  foreach ( $lignes_bordereau as $L ) {
    foreach ( [trim((string) ($L['code_produit'] ?? '')), trim((string) ($L['scan_produit'] ?? ''))] as $v ) {
      if ( $v === '' ) {
        continue;
      }
      $refsSet[$v] = true;
      $n = ltrim($v, '0');
      if ( $n !== '' ) {
        $refsSet[$n] = true;
      }
    }
  }
  $refsList = array_keys($refsSet);
  if ( !empty($refsList) ) {
    $in = [];
    foreach ( $refsList as $r ) {
      $in[] = "'" . $db->escape($r) . "'";
    }
    $db->execute('SELECT id, id_as400, gencode, libelle FROM ref_article WHERE deleted = 0 AND actif = 1 AND (id_as400 IN (' . implode(',', $in) . ') OR gencode IN (' . implode(',', $in) . '))');
    while ( $r = $db->assoc() ) {
      $lib = trim((string) ($r['libelle'] ?? ''));
      $id = trim((string) ($r['id_as400'] ?? ''));
      $codeMinos = isset($r['id']) ? (int) $r['id'] : '';
      $refArt = $id;
      if ( $id !== '' ) {
        $libelleMap[$id] = $lib;
        $codeMinosMap[$id] = $codeMinos;
        $refArticleMap[$id] = $refArt;
        $idNorm = ltrim($id, '0');
        if ( $idNorm !== '' ) {
          $libelleMap[$idNorm] = $lib;
          $codeMinosMap[$idNorm] = $codeMinos;
          $refArticleMap[$idNorm] = $refArt;
        }
      }
      $ge = trim((string) ($r['gencode'] ?? ''));
      if ( $ge !== '' ) {
        $libelleMap[$ge] = $lib;
        $codeMinosMap[$ge] = $codeMinos;
        $refArticleMap[$ge] = $refArt;
      }
    }
  }
}
$retours_libelle_ligne = static function ( array $L ) use ( $libelleMap ) {
  $cp = trim((string) ($L['code_produit'] ?? ''));
  $sc = trim((string) ($L['scan_produit'] ?? ''));
  if ( $cp !== '' && isset($libelleMap[$cp]) ) {
    return $libelleMap[$cp];
  }
  if ( $cp !== '' ) {
    $n = ltrim($cp, '0');
    if ( $n !== '' && isset($libelleMap[$n]) ) {
      return $libelleMap[$n];
    }
  }
  if ( $sc !== '' && isset($libelleMap[$sc]) ) {
    return $libelleMap[$sc];
  }
  if ( $sc !== '' ) {
    $n = ltrim($sc, '0');
    if ( $n !== '' && isset($libelleMap[$n]) ) {
      return $libelleMap[$n];
    }
  }
  return '';
};
$retours_code_minos_ligne = static function ( array $L ) use ( $codeMinosMap ) {
  $cp = trim((string) ($L['code_produit'] ?? ''));
  $sc = trim((string) ($L['scan_produit'] ?? ''));
  foreach ( [$cp, ltrim($cp, '0'), $sc, ltrim($sc, '0')] as $k ) {
    if ( $k !== '' && isset($codeMinosMap[$k]) ) {
      return $codeMinosMap[$k];
    }
  }
  return '—';
};
$retours_ref_article_ligne = static function ( array $L ) use ( $refArticleMap ) {
  $cp = trim((string) ($L['code_produit'] ?? ''));
  $sc = trim((string) ($L['scan_produit'] ?? ''));
  foreach ( [$cp, ltrim($cp, '0'), $sc, ltrim($sc, '0')] as $k ) {
    if ( $k !== '' && isset($refArticleMap[$k]) ) {
      return $refArticleMap[$k];
    }
  }
  return '—';
};
?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-box-open"></i>
      <?php if ( $id_bordereau !== '' ) : ?>
        Bordereau <code class="text-primary"><?php echo htmlspecialchars($id_bordereau); ?></code>
        <span class="badge badge-secondary ml-1"><?php echo count($lignes_bordereau); ?> ligne(s)</span>
      <?php else : ?>
        Détail rappel produit <span class="text-muted">#<?php echo (int) $rappel['id']; ?></span>
      <?php endif; ?>
      <div class="float-right">
        <form method="post" action="<?php echo URL_APP_ROOT; ?>async/" target="_blank" style="display:inline-block; margin-left: 6px;">
          <input type="hidden" name="methode" value="retours::exportRappelExcel" />
          <input type="hidden" name="id_rappel" value="<?php echo (int) $rappel['id']; ?>" />
          <button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel
          </button>
        </form>
        <form method="post" action="<?php echo URL_APP_ROOT; ?>async/" target="_blank" style="display:inline-block; margin-left: 6px;">
          <input type="hidden" name="methode" value="retours::exportRappelPdf" />
          <input type="hidden" name="id_rappel" value="<?php echo (int) $rappel['id']; ?>" />
          <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf"></i> Convertir en PDF
          </button>
        </form>
        <a class="btn btn-default btn-sm" href="<?php echo URL; ?>Retours" style="margin-left: 6px;">
          <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
      </div>
    </h5>
  </div>
  <div class="card-body">
    <?php if ( $id_bordereau !== '' ) : ?>
    <dl class="row mb-3">
      <dt class="col-sm-3">N° bordereau</dt>
      <dd class="col-sm-9"><strong><?php echo htmlspecialchars($id_bordereau); ?></strong></dd>
      <dt class="col-sm-3">Date retour</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['date_retour'] ?? '—'); ?></dd>
      <dt class="col-sm-3">Promoteur</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['promoteur'] ?? '—'); ?></dd>
      <dt class="col-sm-3">Magasin</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['code_magasin'] ?? '—'); ?> — <?php echo htmlspecialchars($libelle_magasin); ?></dd>
    </dl>
    <?php else : ?>
    <dl class="row mb-3">
      <dt class="col-sm-3">Id ligne</dt>
      <dd class="col-sm-9"><?php echo (int) $rappel['id']; ?></dd>
      <dt class="col-sm-3">Date retour</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['date_retour'] ?? '—'); ?></dd>
      <dt class="col-sm-3">Promoteur</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['promoteur'] ?? '—'); ?></dd>
      <dt class="col-sm-3">Code magasin</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($rappel['code_magasin'] ?? '—'); ?></dd>
      <dt class="col-sm-3">Libellé magasin</dt>
      <dd class="col-sm-9"><?php echo htmlspecialchars($libelle_magasin); ?></dd>
    </dl>
    <?php endif; ?>

    <?php if ( $photo_url !== '' ) : ?>
    <div class="mb-4">
      <h6 class="text-muted mb-2">Photo du bordereau</h6>
      <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Photo bordereau" class="img-thumbnail viewer" data-name="Photo bordereau" style="max-height: 320px; cursor: pointer;">
    </div>
    <?php endif; ?>

    <h6 class="text-muted mb-2">Produits</h6>
    <style>
      .table-rappel-produits tbody tr.rappel-ligne-recupere td { background-color: rgba(23, 162, 184, 0.18); }
    </style>
    <div class="table-responsive">
      <table class="table table-sm table-striped table-bordered table-rappel-produits">
        <thead>
          <tr>
            <th>Libellé produit</th>
            <th>N° lot</th>
            <th>Code EAN</th>
            <th>Code produit Minos</th>
            <th>Ref article</th>
            <th>Qté</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $lignes_bordereau as $L ) : ?>
          <?php $libProd = $retours_libelle_ligne($L); ?>
          <?php $isRecupere = (($L['action_produit'] ?? '') === 'recupere'); ?>
          <tr<?php echo $isRecupere ? ' class="rappel-ligne-recupere"' : ''; ?>>
            <td><?php echo $libProd !== '' ? htmlspecialchars($libProd) : '<span class="text-muted">—</span>'; ?></td>
            <td><?php echo htmlspecialchars($L['num_lot'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($L['scan_produit'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars((string) $retours_code_minos_ligne($L)); ?></td>
            <td><?php echo htmlspecialchars((string) $retours_ref_article_ligne($L)); ?></td>
            <td><?php echo (int) ($L['quantite'] ?? 0); ?></td>
            <td>
              <?php
              $action = $L['action_produit'] ?? '';
              if ( $action === 'recupere' ) {
                echo '<span class="badge badge-info">' . htmlspecialchars($action) . '</span>';
              } elseif ( $action === 'detruit' ) {
                echo '<span class="badge badge-secondary">' . htmlspecialchars($action) . '</span>';
              } elseif ( $action === 'absent' ) {
                echo '<span class="badge badge-warning">' . htmlspecialchars($action) . '</span>';
              } else {
                echo htmlspecialchars($action ?: '—');
              }
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
