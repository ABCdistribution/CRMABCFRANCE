<?php 
if( !securite::can(20) ) return core::restricted();

global $params;
if( isset($params[1]) ) {
  $id = intval($params[1]);
  $prospection = prospection::get($id);
  if( $id == $params[1] && $prospection ) {
    include(PAGES."prospection/fiche.php");
    return;
  }
}
?>
<div class="row" id="page-prospections">
  <div class="col">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-prospections-titre');?></h5>
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
                  <input type="date" class="form-control" autocomplete="off" placeholder="<?php echo l('date-du');?>..." name="from">
                  <input type="date" class="form-control" autocomplete="off" placeholder="<?php echo l('date-au');?>..." name="to">                  
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
                <th><?php echo l('page-prospections-table-date');?></th>
                <th><?php echo l('page-prospections-table-prospect');?></th>
                <th><?php echo l('page-prospections-table-type');?></th>
                <th><?php echo l('page-prospections-table-commercial');?></th>
                <th><?php echo l('page-prospections-table-etape');?></th>
                <th class="tc"><?php echo l('page-prospections-table-photos');?></th>
                <th class="tc"><?php echo l('page-prospections-table-dn');?></th>
              </thead>
              <tbody></tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
