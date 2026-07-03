<?php if( !securite::can(5) ) return core::restricted();?>
<?php
global $params;
if( isset($params[1]) ) {
  $id = intval($params[1]);
  $visite = visite::get($id);
  if( $id == $params[1] && $visite ) {
    include(PAGES."visite/fiche.php");
    return;
  }
}
?>
<div class="row" id="page-visites">


  <div class="col-3">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Les 15 dernières visites</h5>
      </div>
      <div class="card-body" id="today">
    <?php
    $v = visite::getDailyVisits();
    if( empty($v) ) {
      echo '<p class="text-center text-secondary">Aucune visite pour le moment</p>';
    }
    else {
      echo '<div class="list-group">';
      foreach( $v as $k=>$e ) {
        $vi = visite::get($e);
        echo '<a href="'.URL.'Visites/'.$e.'" target="_blank" class="list-group-item list-group-item-action">
          <div class="d-flex w-100 justify-content-between header">
            <h4 class="mb-1" style="font-size:17px;">
              <i class="fas fa-store" style="margin-right:5px;"></i>
              '.client::get($vi['id_client'])['enseigne'].'
            </h4>
          </div>
          <small>par <b>'.user::getNameFromId($vi['id_user']).'</b></small>
          <div class="row">
            <div class="col text-left text-secondary">
              <small>il y a '.core::dateFrom($vi['date_creation']).' à '.date("H\hi",strtotime($vi['date_creation'])).'</small>
            </div>
            <div class="col-3 text-right">
              <small>'.count($vi['photos']).' <i class="fas fa-camera"></i></small>
            </div>
          </div>
        </a>';
      }
      echo '</div>';
    }
    ?>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Toutes les Visites</h5>
      </div>
      <div class="card-body">
          <div id="vTable-wrapper" class="rel" style="padding:5px;">

            <!-- Filtres -->
            <div class='row'>
              <div class="col">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
                  </div>
                  <input type="text" class="form-control" placeholder="Rechercher" id="searchField">
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="button-addon2" onclick="loadTableVisite()">Rechercher</button>
                  </div>
                </div>
              </div>
              <div class="col-3">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <label class="input-group-text" for="inputGroupSelect01">Résultats par pages</label>
                  </div>
                  <select class="custom-select" id="nbResults">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
              </div>
            </div>

            <p id="disclaimer" class="text-center">
              Affichage de <span class="d01"></span> sur <span class="d02"></span> résultats
            </p>


            <table class="table table-striped" id="vTable">
              <thead>
                <th>Date</th>
                <th>Client</th>
                <th>Commercial</th>
                <th>Commande liée</th>
                <th>Photos</th>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <script>
            $(document).ready(function() {
              loadTableVisite();
            })
          </script>
        </div>
      </div>
    </div>
  </div>
