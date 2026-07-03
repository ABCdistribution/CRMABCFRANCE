
<?php if( !securite::can(20) ) return core::restricted();?>
<?php
global $params;
$p = array_pop($params);
if( strpos($p,"Fiche") > -1 ) {
  $tmp = explode("-",$p);
  $id = intval(array_pop($tmp));
  return include(PAGES.'prospect/fiche.php');
}
if( $p != "Prospects" ) {
  global $db;
  $db->execute("SELECT id FROM prospect WHERE LOWER(nom) = '".$db->escape(rawurldecode($p))."' ");
  if( $db->num() ) {
    $datas = $db->assoc();
    $id = $datas['id'];
    return include(PAGES.'prospect/fiche.php');
  }
}
?>

<div class="row">
  <div class="col-md-6">

    <?php echo core::alert( core::n(ref::countTotalProspects())." ".l("page-prospects-disclaimer"),"",false);?>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">
            <?php echo l("page-prospects-rechercher");?>
            <button class="float-right btn btn-primary btn-sm" onclick="newProspect()">+ <?php echo l("page-prospects-nouveau");?></button>
        </h5>
      </div>
      <div class="card-body">
        <div class="input-group">
          <input type="text" class="form-control" id="fieldSearchProspect" placeholder="...">
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="searchProspect()"><?php echo l("bouton-rechercher");?></button>
          </span>
        </div>
        <br/>
        <table class="table table-condensed table-striped">
          <tbody id="resultSearchProspect"></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">



    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l("page-prospects-last-created");?></h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $prospects = ref::getLastProspects(5);
              if( count($prospects) ) {
                foreach( $prospects as $c ) {
                  echo '<tr><td>'.$c['enseigne'].'</td><td align="right">';
                  echo '<a href="'.URL.'Prospects/Fiche-'.$c['id'].'" class="btn btn-default btn-sm">'.l("fiche-prospect").' <i class="fas fa-long-arrow-alt-right ml"></i></a>';
                  echo '</td></tr>';
                }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l("page-prospects-last-updated");?></h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $prospects = ref::getLastProspects(5,'date_modification');
              if( count($prospects) ) {
                foreach( $prospects as $c ) {
                  echo '<tr><td>'.$c['nom'].'</td><td align="right">';
                  echo '<a href="'.URL.'Prospects/Fiche-'.$c['id'].'" class="btn btn-default btn-sm">'.l("fiche-prospect").' <i class="fas fa-long-arrow-alt-right ml"></i></a>';
                  echo '</td></tr>';
                }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>




  </div>
</div>
