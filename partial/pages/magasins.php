<?php if( !securite::can(2) ) return core::restricted();?>
<?php
global $params;
$p = array_pop($params);
if( strpos($p,"Fiche") > -1 ) {
  $tmp = explode("-",$p);
  $id = intval(array_pop($tmp));
  return include(PAGES.'magasin/fiche.php');
}
if( $p != "magasins" ) {
  global $db;
  $db->execute("SELECT id FROM ref_client WHERE LOWER(enseigne) = '".$db->escape(rawurldecode($p))."' ");
  if( $db->num() ) {
    $datas = $db->assoc();
    $id = $datas['id'];
    return include(PAGES.'magasin/fiche.php');
  }
}



?>
<div class="row">
  <div class="col-md-6">

    <?php echo core::alert( core::n(ref::countTotalClient()).' '.l('page-client-references'),"",false);?>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">
            <?php echo l('page-client-rechercher');?>
            <button class="float-right btn btn-primary btn-sm" onclick="newClient()">+ <?php echo l('page-client-nouveau');?></button>
        </h5>
      </div>
      <div class="card-body">
        <div class="input-group">
          <input type="text" class="form-control" id="fieldSearchClient" placeholder="">
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="searchClient()"><?php echo l('bouton-rechercher');?></button>
          </span>
        </div>
        <br/>
        <table class="table table-condensed table-striped">
          <tbody id="resultSearchClient"></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">



    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-client-added');?></h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $clients = ref::getLastClients(5);
              if( count($clients) ) {
                foreach( $clients as $c ) {
                  echo '<tr><td>'.$c['enseigne'].'</td><td align="right">';
                  echo '<a href="'.URL.'Magasins/Fiche-'.$c['id'].'" class="btn btn-default btn-sm">'.l('fiche-client').' <i class="fas fa-long-arrow-alt-right ml"></i></a>';
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
        <h5 class="m-0"><?php echo l('page-client-updated');?> </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $clients = ref::getLastClients(5,'date_modification');
              if( count($clients) ) {
                foreach( $clients as $c ) {
                  echo '<tr><td>'.$c['enseigne'].'</td><td align="right">';
                  echo '<a href="'.URL.'Magasins/Fiche-'.$c['id'].'" class="btn btn-default btn-sm">'.l('fiche-client').' <i class="fas fa-long-arrow-alt-right ml"></i></a>';
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
