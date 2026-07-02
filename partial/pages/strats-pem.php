<?php if( !securite::can(26) ) return core::restricted();?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('page-strat-pem-titre');?></h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col" style="max-width:70%;" id="listOpWrapper">
        <h5><span><?php echo l('page-strat-pem-liste');?></span></h5>
        <div id="stratsWrapper"></div>
      </div>
      <div class="col-4">
        <h5><span><?php echo l('page-strat-pem-creer-strat');?></span></h5>
        <form id="addStratPem" onsubmit="return false">
          <div class="form-group">
            <label><?php echo l('page-strat-pem-creer-libelle');?> :</label>
            <input type="text" class="form-control" name="libelle" maxlenth="15">
          </div>
          <div class="text-center">
             <button class="btn btn-primary" onClick="addStrat()"><?php echo l('bouton-ajouter');?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>  

    