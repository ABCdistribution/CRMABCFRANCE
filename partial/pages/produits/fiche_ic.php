<h5><span><?php echo l('page-produit-info-sup');?></span></h5>
<form method="post" action="#" id="formInfoSup" onsubmit="return false;">
<input type="hidden" name="id_as400" value="<?php echo $produit['id_as400'];?>"/>
<?php $infos = ref::getRefArticleInfos($produit['id_as400']);?>
<div class="form-group">
    <label><?php echo l('page-produit-details-produit');?></label>
    <textarea class="form-control" name="details" rows="5"><?php echo $infos['details'];?></textarea>
</div>
<div class="form-group">
    <label><?php echo l('page-produit-avantages-produit');?></label>
    <textarea class="form-control" name="avantages" rows="5"><?php echo $infos['avantages'];?></textarea>
</div>
<div class="tc">
    <a class="btn btn-primary btn-sm" onclick="saveInfosSupp()"><?php echo l('bouton-enregistrer');?></a>
</div>
</form>