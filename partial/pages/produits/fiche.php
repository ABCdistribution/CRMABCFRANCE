<?php
$produit = produit::get($id);
if( !$produit ) return core::error404();
?>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-brush"></i> <?php echo $produit['libelle'];?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Produits">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">
      <div class="row">

        <div class="col-4">
          <?php include(PARTIAL."pages/produits/fiche_ig.php");?>
        </div>

        <div class="col-5">


          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation">
              <a href="#stock" aria-controls="stock" class="active" role="tab" data-toggle="tab">
                <?php echo l('page-produit-onglets-stock');?>
              </a>
            </li>
            <li role="presentation">
              <a href="#comp" aria-controls="comp" role="tab" data-toggle="tab">
                <?php echo l('page-produit-onglets-comp');?>                
              </a>
            </li>
          </ul>

          <div class="card card-primary">
            <div class="card-body">
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="stock">
                  <?php include(PARTIAL."pages/produits/fiche_stock.php");?>
                </div>
                <div role="tabpanel" class="tab-pane" id="comp">
                  <?php include(PARTIAL."pages/produits/fiche_comp.php");?>
                </div>
              </div>
            </div>
          </div>



          <h5><span><?php echo l('page-produit-tarif-ttc');?></span></h5>
          <h1 class="tc"><?php echo  $produit['tarif'];?> €</h1>
          <h5><span><?php echo l('page-produit-gencode');?></span></h5>
          <div class="tc ean13"><?php echo $produit['gencode'];?></div>
        </div>

        <div class="col-3">
          <?php include(PARTIAL."pages/produits/fiche_ic.php");?>          
        </div>

      </div>
  </div>
</div>
