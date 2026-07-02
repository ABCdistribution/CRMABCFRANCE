<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-ca-titre');?></h5>
  </div>
  <div class="card-body m-250 noverflow rel" id="calcCa">

    <div class="input-group mb-3" style="width:300px;margin: 10px auto">
      <input type="text" class="form-control" placeholder="<?php echo l('admin-ca-id-rep');?>" id="id_repr">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" onClick="calcCA()" type="button" id="button-addon2"><?php echo l('admin-ca-calculer');?></button>
      </div>
    </div>


    <div class="infoCa hidden">
      <h5><span> <?php echo l('admin-ca-rez');?></span></h5>
      <h3><b></b> <?php echo l('admin-ca-pour-le-mois');?> <span></span></h3>
      <h4></h4>
      <p><em></em> <?php echo l('admin-ca-factures');?></p>
    </div>

  </div>
  <p class="text-secondary text-center">
    <?php echo l('admin-ca-warn');?>
    
  </p>
</div>
