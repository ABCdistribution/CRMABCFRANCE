<h5><span><?php echo l('page-produit-titre-comp');?></span></h5>

<div class="alert alert-dismissible">
    <i class="icon fa fa-info-circle"></i> : 
    <?php echo l('page-produit-titre-warn');?>
    <strong><?php echo $produit['libelle'];?></strong>
</div>


<div class="list-group" id="list-comp"></div>

<div class="text-right">
    <button class="btn btn-primary" onclick="addProduitComp()"><?php echo l('page-produit-comp-btn');?></button>
</div>