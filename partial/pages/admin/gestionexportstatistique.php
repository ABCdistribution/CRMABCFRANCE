<?php if( !securite::can(8) ) return core::restricted();

global $db;

function csv_clean($value) {

  $value = str_replace(
    array("\r", "\n", ";"),
    array(" ", " ", ","),
    $value
  );

  return trim($value);
}

function db_all_assoc($query) {

  global $db;

  $rows = array();

  $db->execute($query);

  while( $r = $db->assoc() ) {
    $rows[] = $r;
  }

  return $rows;
}

/**
 * EXPORT CSV
 */
if( isset($_POST['export_stat']) ) {

  session_write_close();

  $codes_articles = isset($_POST['codes_articles'])
    ? $_POST['codes_articles']
    : array();

  $date_debut = trim($_POST['date_debut']);
  $date_fin   = trim($_POST['date_fin']);

  if( empty($codes_articles) || empty($date_debut) ) {

    die('Veuillez sélectionner au moins un article et une date.');
  }

  if( empty($date_fin) ) {
    $date_fin = $date_debut;
  }

  $codes_sql = array();

  foreach( $codes_articles as $code ) {

    $code = trim($code);

    if( $code != '' ) {

      $codes_sql[] = "'".$db->escape($code)."'";
    }
  }

  if( empty($codes_sql) ) {

    die('Aucun article valide.');
  }

  $sql = "
    SELECT 
      cap.id_produit AS code_article,
      cap.quantite AS quantite,
      ca.user AS promoteur,
      ca.id_magasin AS magasin
    FROM commande_apk_produits cap
    INNER JOIN commande_apk ca 
      ON ca.id = cap.id_commande_apk
    WHERE cap.id_produit IN (".implode(',', $codes_sql).")
      AND DATE(ca.date_creation_apk)
      BETWEEN '".$db->escape($date_debut)."'
      AND '".$db->escape($date_fin)."'
    ORDER BY cap.id_produit ASC,
             ca.date_creation_apk ASC
  ";

  $rows = db_all_assoc($sql);

  $filename = 'export_statistique_'.$date_debut.'_'.$date_fin.'.csv';

  if( ob_get_length() ) {
    ob_clean();
  }

  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');

  echo "\xEF\xBB\xBF";

  echo "Code article;Quantite;Promoteur;Magasin\n";

  foreach( $rows as $r ) {

    echo csv_clean($r['code_article']).';';
    echo csv_clean($r['quantite']).';';
    echo csv_clean($r['promoteur']).';';
    echo csv_clean($r['magasin'])."\n";
  }

  exit;
}

/**
 * LISTE ARTICLES
 */
$articles = db_all_assoc("
  SELECT 
    id_as400,
    libelle
  FROM ref_article
  WHERE id_as400 IS NOT NULL
    AND id_as400 != ''
    AND actif = 1
  ORDER BY id_as400 ASC
");

?>

<style>

.export-wrapper{
  max-width:1100px;
  margin:0 auto;
}

.export-card{
  background:#fff;
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.export-header{
  background:linear-gradient(135deg,#343a40,#495057);
  color:#fff;
  padding:25px;
}

.export-header h3{
  margin:0;
  font-weight:600;
}

.export-header p{
  margin-top:8px;
  opacity:0.85;
}

.export-body{
  padding:25px;
}

.export-help{
  background:#f8f9fa;
  border-left:4px solid #343a40;
  padding:14px;
  border-radius:8px;
  margin-bottom:25px;
}

.article-select{
  min-height:300px;
}

.btn-export{
  padding:10px 25px;
  border-radius:8px;
  font-weight:600;
}

.selected-count{
  margin-top:8px;
  color:#666;
  font-size:13px;
}

</style>

<div class="export-wrapper">

  <div class="export-card">

    <div class="export-header">

      <h3>
        <i class="fas fa-file-export"></i>
        Gestion Export statistique
      </h3>

      <p>
        Export des commandes par article sur une période donnée.
      </p>

    </div>

    <div class="export-body">

      <div class="export-help">
        Sélectionnez un ou plusieurs produits puis une période afin de générer un fichier CSV compatible Excel.
      </div>

      <iframe
        name="exportFrame"
        style="display:none;"
      ></iframe>

      <form
        method="post"
        target="exportFrame"
        onsubmit="setTimeout(function(){ location.reload(); }, 1500);"
      >

        <div class="form-group">

          <label>
            <strong>Recherche produit</strong>
          </label>

          <input
            type="text"
            id="articleSearch"
            class="form-control"
            placeholder="Rechercher un code ou un libellé..."
          >

        </div>

        <div class="form-group">

          <label>
            <strong>Produits</strong>
          </label>

          <select
            name="codes_articles[]"
            id="articlesSelect"
            class="form-control article-select"
            multiple
            required
          >

            <?php foreach( $articles as $a ) { ?>

              <option value="<?php echo htmlspecialchars($a['id_as400']); ?>">

                <?php echo htmlspecialchars(
                  $a['id_as400'].' - '.$a['libelle']
                ); ?>

              </option>

            <?php } ?>

          </select>

          <div class="selected-count">

            <span id="selectedCount">0</span>
            produit(s) sélectionné(s)

          </div>

          <small class="text-muted">
            Maintenez CTRL pour sélectionner plusieurs produits.
          </small>

        </div>

        <div class="row">

          <div class="col-md-6">

            <div class="form-group">

              <label>
                <strong>Date début</strong>
              </label>

              <input
                type="date"
                name="date_debut"
                class="form-control"
                required
              >

            </div>

          </div>

          <div class="col-md-6">

            <div class="form-group">

              <label>
                <strong>Date fin</strong>
              </label>

              <input
                type="date"
                name="date_fin"
                class="form-control"
              >

              <small class="text-muted">
                Laisser vide pour une seule journée.
              </small>

            </div>

          </div>

        </div>

        <button
          type="submit"
          name="export_stat"
          class="btn btn-dark btn-export"
        >

          <i class="fas fa-download"></i>
          Exporter CSV

        </button>

      </form>

    </div>

  </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function() {

  var searchInput   = document.getElementById('articleSearch');
  var select        = document.getElementById('articlesSelect');
  var selectedCount = document.getElementById('selectedCount');

  function updateCount() {

    var count = 0;

    for( var i = 0; i < select.options.length; i++ ) {

      if( select.options[i].selected ) {
        count++;
      }
    }

    selectedCount.innerHTML = count;
  }

  searchInput.addEventListener('keyup', function() {

    var search = this.value.toLowerCase();

    for( var i = 0; i < select.options.length; i++ ) {

      var option = select.options[i];

      var txt = option.text.toLowerCase();

      if(
        txt.indexOf(search) !== -1
        || option.selected
      ) {

        option.style.display = '';

      } else {

        option.style.display = 'none';
      }
    }
  });

  select.addEventListener('change', updateCount);

});

</script>