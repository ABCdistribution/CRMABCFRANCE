<?php if( !securite::can(28) ) return core::restricted();?>
<?php
global $db, $params;

if ( isset($params[1]) ) {
  $id = (int) $params[1];
  $rappel = retours::get($id);
  if ( $id > 0 && $id == $params[1] && $rappel ) {
    $idBord = trim((string) ($rappel['id_bordereau'] ?? ''));
    if ( $idBord !== '' ) {
      $lignes_bordereau = retours::getLignesBordereau($idBord, (int) ($rappel['user_id'] ?? 0));
    } else {
      $lignes_bordereau = [$rappel];
    }
    $code = trim((string) ($rappel['code_magasin'] ?? ''));
    $db->execute("SELECT enseigne FROM ref_client WHERE id_as400 = '" . $db->escape($code) . "' AND deleted = 0 LIMIT 1");
    $rappel['libelle_magasin'] = $db->num() ? trim((string) $db->assoc()['enseigne']) : '';
    if ( $rappel['libelle_magasin'] === '' && $code !== '' ) {
      $db->execute("SELECT enseigne FROM ref_client WHERE id_as400 = '" . $db->escape(ltrim($code, '0')) . "' AND deleted = 0 LIMIT 1");
      $rappel['libelle_magasin'] = $db->num() ? trim((string) $db->assoc()['enseigne']) : '';
    }
    include(PAGES . 'retours/fiche.php');
    return;
  }
}

$filter_magasin = trim($_GET['magasin'] ?? '');
$filter_promoteur = (int) ($_GET['promoteur'] ?? 0);

// Map des magasins actifs pour retrouver le libellé depuis le code
$db->execute("
  SELECT id_as400, enseigne
  FROM ref_client
  WHERE deleted = 0 AND actif = 1
  ORDER BY enseigne
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

// Liste des magasins distincts utilisés dans les retours pour le filtre
$db->execute("
  SELECT DISTINCT code_magasin
  FROM retours_produits_apk
  WHERE code_magasin != ''
  ORDER BY code_magasin
");
$magasins = [];
while ( $r = $db->assoc() ) {
  $code = trim((string) $r['code_magasin']);
  $nom = $magasinLabels[$code] ?? $magasinLabels[ltrim($code, '0')] ?? '';
  $magasins[] = ['code' => $code, 'nom' => $nom];
}

// Liste des promoteurs (users ayant fait au moins un retour) pour le filtre
$db->execute("
  SELECT DISTINCT r.user_id, u.displayname
  FROM retours_produits_apk r
  LEFT JOIN user u ON u.id = r.user_id
  WHERE r.user_id > 0
  ORDER BY u.displayname
");
$promoteurs = [];
while ( $r = $db->assoc() ) {
  $promoteurs[] = ['id' => (int) $r['user_id'], 'displayname' => $r['displayname'] ?? '#' . $r['user_id']];
}

// Requête filtrée
$where = ['1=1'];
if ( $filter_magasin !== '' ) {
  $where[] = 'r.code_magasin = "' . $db->escape($filter_magasin) . '"';
}
if ( $filter_promoteur > 0 ) {
  $where[] = 'r.user_id = ' . $filter_promoteur;
}

$db->execute("
  SELECT
    MIN(r.id) AS id,
    MAX(r.date_retour) AS date_retour,
    r.user_id,
    MAX(u.displayname) AS promoteur,
    MAX(r.code_magasin) AS code_magasin,
    COUNT(*) AS nb_lignes,
    NULLIF(TRIM(MAX(r.id_bordereau)), '') AS id_bordereau,
    SUM(IFNULL(r.quantite, 0)) AS qte_retour_totale
  FROM retours_produits_apk r
  LEFT JOIN user u ON u.id = r.user_id
  WHERE " . implode(' AND ', $where) . "
  GROUP BY r.user_id, IFNULL(NULLIF(TRIM(r.id_bordereau), ''), CONCAT('_legacy_', r.id))
  ORDER BY MAX(r.id) DESC
");
$retours = [];
while ( $r = $db->assoc() ) {
  $retours[] = $r;
}
?>
<div class="row" id="page-rappels-produits">
  <div class="col">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Rappels produits</h5>
      </div>
      <div class="card-body">
        <form method="get" action="<?php echo URL; ?>Retours" class="mb-4" id="formFiltresRetours">
          <div class="row align-items-end">
            <div class="col-md-3">
              <label class="mb-1">Magasin</label>
              <select class="form-control" name="magasin" id="filterMagasin">
                <option value="">Tous les magasins</option>
                <?php foreach ( $magasins as $m ) : ?>
                <option value="<?php echo htmlspecialchars($m['code']); ?>"<?php echo ($filter_magasin === $m['code']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($m['nom']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="mb-1">Promoteur</label>
              <select class="form-control" name="promoteur" id="filterPromoteur">
                <option value="">Tous les promoteurs</option>
                <?php foreach ( $promoteurs as $p ) : ?>
                <option value="<?php echo $p['id']; ?>"<?php echo ($filter_promoteur === $p['id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($p['displayname']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrer
              </button>
              <a href="<?php echo URL; ?>Retours" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
            <div class="col-md-2 text-right">
              <a download="rappels_produits.xls" class="btn btn-success" href="#" id="btnExportRetours">
                <i class="fas fa-file-excel"></i> Export Excel
              </a>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-hover" id="tableRetours">
            <thead>
              <tr>
                <th>N° bordereau</th>
                <th>Lignes</th>
                <th>Date retour</th>
                <th>Promoteur</th>
                <th>Code magasin</th>
                <th>Libellé magasin</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $retours as $row ) : ?>
              <?php $rowLabel = $magasinLabels[trim((string) ($row['code_magasin'] ?? ''))] ?? $magasinLabels[ltrim(trim((string) ($row['code_magasin'] ?? '')), '0')] ?? ''; ?>
              <?php $bid = trim((string) ($row['id_bordereau'] ?? '')); ?>
              <?php $hasQteRetour = (int) ($row['qte_retour_totale'] ?? 0) > 0; ?>
              <tr class="row-rappel-clickable<?php echo $hasQteRetour ? ' row-rappel-qte-retour' : ''; ?>" role="button" tabindex="0" data-href="<?php echo URL; ?>Retours/<?php echo (int) $row['id']; ?>">
                <td><?php echo $bid !== '' ? htmlspecialchars($bid) : '—'; ?><?php if ( $bid === '' ) : ?> <small class="text-muted">(#<?php echo (int) $row['id']; ?>)</small><?php endif; ?></td>
                <td><?php echo (int) ($row['nb_lignes'] ?? 1); ?></td>
                <td><?php echo htmlspecialchars($row['date_retour'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['promoteur'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['code_magasin'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($rowLabel); ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if ( empty($retours) ) : ?>
              <tr>
                <td colspan="6" class="text-center text-muted">Aucun rappel enregistré.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
#tableRetours tbody tr.row-rappel-clickable { cursor: pointer; }
#tableRetours tbody tr.row-rappel-clickable:hover td { background-color: rgba(0,0,0,.05); }
#tableRetours tbody tr.row-rappel-qte-retour td { background-color: rgba(40, 167, 69, 0.14); }
#tableRetours tbody tr.row-rappel-qte-retour:hover td { background-color: rgba(40, 167, 69, 0.22); }
</style>

<script>
$(document).ready(function() {
  if ( $.fn.DataTable && $('#tableRetours tbody tr').length > 0 && $('#tableRetours tbody tr td').first().text().indexOf('Aucun') === -1 ) {
    $('#tableRetours').DataTable({
      order: [[2, 'desc']],
      pageLength: 25,
      language: {
        search: "Filtrer :",
        lengthMenu: "Afficher _MENU_ lignes",
        info: "_TOTAL_ rappel(s)",
        infoEmpty: "0 rappel",
        infoFiltered: "(filtrés sur _MAX_)",
        paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" }
      }
    });
  }

  $('#btnExportRetours').on('click', function(e) {
    e.preventDefault();
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = _global.app_url + 'async/';
    form.target = '_blank';
    form.style.display = 'none';
    function addInput(name, value) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    }
    addInput('methode', 'retours::exportRetours');
    addInput('magasin', $('#filterMagasin').val() || '');
    addInput('promoteur', $('#filterPromoteur').val() || '');
    document.body.appendChild(form);
    form.submit();
    setTimeout(function() { form.remove(); }, 5000);
    return false;
  });

  // Clic sur une ligne : aller vers la page détail du rappel
  $(document).on('click', '#tableRetours tbody tr.row-rappel-clickable', function() {
    var href = $(this).data('href');
    if (href) window.location = href;
  });
  $(document).on('keydown', '#tableRetours tbody tr.row-rappel-clickable', function(e) {
    if (e.which === 13 || e.which === 32) {
      var href = $(this).data('href');
      if (href) window.location = href;
      e.preventDefault();
    }
  });
});
</script>
