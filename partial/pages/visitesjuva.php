<?php if( !securite::can(5) ) return core::restricted();?>
<?php
global $params;
// echo '<div style="
//     background-color: #fff3cd;
//     color: #856404;
//     border: 1px solid #ffeeba;
//     padding: 15px;
//     border-radius: 8px;
//     font-family: Arial, sans-serif;
//     font-size: 16px;
//     max-width: 600px;
//     margin: 20px auto;
//     text-align: center;
// ">
//     🚧 Cette section est en cours de développement. Merci de votre compréhension.
// </div>';
if( isset($params[1]) ) {
  $id = intval($params[1]);
  $visite = visite::getJuva($id);
  if( $id == $params[1] && $visite && $visite['pem'] == 0 ) {
    include(PAGES."visite/fiche_juva.php");
    return;
  }
}


?>
<div class="row" id="page-visites">
  <div class="col">
    <div class="card card-primary card-outline">
    <div class="d-flex justify-content-end">
  <img src="<?php echo URL; ?>img/juvamine.png" style="width: 250px; height: auto;" alt="Logo Juvamine" />
</div>
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-visites-titre');?>- Juva</h5>
      </div>
      <div class="card-body">
          <div id="vTable-wrapper" class="rel" style="padding:5px;">

            <!-- Filtres -->
            <div class='row' id="filtersDiv">
              <div class="col">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
                  </div>
                  <input type="text" class="form-control" placeholder="<?php echo l('bouton-rechercher');?>" id="searchField">
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="button-addon2" onclick="loadTableVisite()"><?php echo l('bouton-rechercher');?></button>
                  </div>
                </div>
              </div>
              <div class="col-3">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="far fa-calendar-alt"></i></span>
                  </div>
                  <?php
                    $today = date("Y-m-d");
                    ?>
                  <input type="date" class="form-control" autocomplete="off" placeholder="<?php echo l('date-du');?>..." name="from" value="<?php echo $today; ?>">
                  <input type="date" class="form-control" autocomplete="off" placeholder="<?php echo l('date-au');?>..." name="to" value="<?php echo $today; ?>">                  
                </div>
              </div>              
              <div class="col-3">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <label class="input-group-text" for="inputGroupSelect01"><?php echo l('page-cmds-resultats-par-pages');?></label>
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
              <span class="d01"></span> / <span class="d02"></span> <?php echo l('page-cmds-resultats');?>
            </p>

            <div class="table-responsive">
            <table class="table table-striped" id="vTable">
              <thead>
                <th><?php echo l('page-visites-table-date');?></th>
                <th><?php echo l('page-visites-table-code-client');?></th>
                <th><?php echo l('page-visites-table-client');?></th>
                <th><?php echo l('page-visites-table-periode');?></th>
                <th><?php echo l('page-visites-table-commercial');?></th>
                <th><?php echo l('page-visites-table-cmd-liee');?></th>
                <th><?php echo l('page-visites-table-raison');?></th>
                <th class="tc"><?php echo l('page-visites-table-photos');?></th>
                <!-- <th class="tc"><?php echo l('page-visites-table-promos');?></th> -->
                <th class="tc"><?php echo l('page-visites-table-dn');?></th>
                <!-- <th class="tc"><?php echo l('page-visites-table-pem');?></th> -->
                <th class="tc">JUVA</th>

              </thead>
              <tbody></tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
