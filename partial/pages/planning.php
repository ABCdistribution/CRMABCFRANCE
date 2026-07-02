<?php if( !securite::can(17) ) return core::restricted();?>

  <h1>
    <?php echo l('page-planning-titre');?>
    <a class="float-right btn-xs abc bg-danger" onclick="truncatePlanning()"><?php echo l('page-planning-bouton-supprimer');?></a>
    <a class="float-right btn-xs abc" id="btnUploadCSV" onclick="uploadPlanning()"><?php echo l('page-planning-bouton-charger');?></a>
  </h1>

  <form method="post" id="formUploadPlanning" class="hidden">
    <input type="file" name="filePlanning" accept=".csv"/>
  </form>

  <div class="clearfix"></div>

  <div class="alert alert-info" role="alert" id="infoCSV">
    <div class="row">
      <div class="col">
        <?php echo l('page-planning-import-csv');?>
        </div>
      <div class="col">
        <?php echo l('page-planning-import-structure');?>
      </div>
      <div class="col">
        <?php echo l('page-planning-import-date');?>
      </div>
  </div>
</div>


    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-planning-rechercher-promoteur');?></h5>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label><?php echo l('page-planning-representant');?></label>
          <select class="form-control getPlanning" id="planning_sel_id_repr">
            <?php
            foreach( planning::getPlanningRepr() as $id => $o ) {
              if( empty($o) )
                echo '<option value="0" data-id="0"></option>';
              else
                echo '<option value="'.$id.'" data-id="'.$o['id_repr'].'">#'.$o['id_repr'].' : '.$o['name'].'</option>';
            }
            ?>
          </select>
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><?php echo l('page-planning-dates');?> :</span>
          </div>
          <input type="date" class="form-control getPlanning" name="from" autocomplete="off" value="<?php echo date("Y-m-d");?>" placeholder="<?php echo l('date-du');?>...">
          <input type="date" class="form-control getPlanning" name="to" autocomplete="off" placeholder="<?php echo l('date-au');?>">
        </div>
      </div>
    </div>
  <div id="pl-wrapper"></div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
