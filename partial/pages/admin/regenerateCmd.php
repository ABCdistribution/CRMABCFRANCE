<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-regen-titre');?></h5>
  </div>
  <div class="card-body m-250 noverflow rel" id="calcCa">

    <div class="input-group mb-3" style="width:300px;margin: 10px auto">
      <input type="text" class="form-control" placeholder="<?php echo l('admin-regen-id');?>" id="id_cmd">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" onClick="regenerateCMD()" type="button" id="button-addon2"><?php echo l('admin-regen-calc');?></button>
      </div>
    </div>

  </div>
</div>
