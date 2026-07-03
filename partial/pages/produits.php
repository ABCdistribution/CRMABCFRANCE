<?php if( !securite::can(1) ) return core::restricted();?>
<?php
global $params;
$p = array_pop($params);
if( strpos($p,"Fiche") > -1 ) {
  $tmp = explode("-",$p);
  $id = intval(array_pop($tmp));
  return include(PAGES.'produits/fiche.php');
}
?>
<div class="row">
  <div class="col-md-6">

    <div class="small-box bg-success">
      <div class="inner">
        <h3><?php echo ref::countTotalProduit();?></h3>
        <p><?php echo l('page-produits-references');?></p>
      </div>
      <div class="icon">
        <i class="fas fa-brush"></i>
      </div>
    </div>

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-produits-rechercher');?></h5>
      </div>
      <div class="card-body">
        <div class="input-group">
          <input type="text" class="form-control" id="fieldSearchProduit" placeholder="<?php echo l('page-produits-rechercher-placeholder');?>">
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="searchProduit()"><?php echo l('page-produits-rechercher-button');?></button>
          </span>
        </div>
        <br/>
        <table class="table table-condensed table-striped">
          <tbody id="resultSearchProduit"></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">



    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-produits-derniers');?></h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $clients = ref::getLastProduits(5);
              if( count($clients) ) {
                foreach( $clients as $c ) {
                  echo '<tr><td>'.$c['libelle'].'</td><td align="right">';
                  echo '<a href="'.URL.'Produits/Fiche-'.$c['id'].'" class="btn btn-default btn-xs">'.l('fiche-produit').' <i class="fas fa-long-arrow-alt-right ml"></i></a>';
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
        <h5 class="m-0"><?php echo l('page-produits-updated');?> </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-condensed table-striped">
            <tbody>
              <?php
              $produits = ref::getLastProduits(5,'date_modification');
              if( count($produits) ) {
                foreach( $produits as $c ) {
                  echo '<tr><td>'.$c['libelle'].'</td><td align="right">';
                  echo '<a href="'.URL.'Produits/Fiche-'.$c['id'].'" class="btn btn-default btn-sm">'.l('fiche-produit').'<i class="fas fa-long-arrow-alt-right ml"></i></a>';
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
