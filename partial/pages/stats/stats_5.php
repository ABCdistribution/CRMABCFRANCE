<div class="filters">
  <form class="form-inline" action="<?php echo URL."Stats/".$id_stat;?>" method="POST" id="formFilters">
  <label class="mr-5"><strong><i class="fas fa-filter mr-2"></i> <?php echo l('stat-filtre-nom');?> : </strong></label>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
      </div>
      <input
        type="date" class="form-control" name="from" placeholder="<?php echo l('date-du');?>..."
        autocomplete="off" value="<?php echo $_POST['from'] ?? $from;?>">
    </div>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
      </div>
      <input
        type="date" class="form-control " name="to" placeholder="<?php echo l('date-au');?>..."
        autocomplete="off" value="<?php echo $_POST['to'] ?? $to;?>">
    </div>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="no_visit_reason">
        <?php
        $reasons = [0=> l("stats-raisons")];
        $reasons = $reasons + core::getListReason('CMD_SS_VIS');
        $reasons["autre"] = l("stats-autres-raisons");
        foreach( $reasons as $k=>$e) {
          $s = ( isset($_POST['no_visit_reason']) && $_POST['no_visit_reason'] == $k ? 'selected' : '' );
          echo '<option value="'.$k.'" '.$s.'>'.$e.'</option>';
        }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary ml-2"><?php echo l('stat-filtre-appliquer');?></button>
  </form>
</div>

<div id="statsRezWrapper" data-get="getCommandesSansVisites"></div>

<template id="statstemplate">
  <h1 class="statsTitle"></h1>
  <div class="row">
    <div class="col">
      <div class="table-responsive">
        <table class="table table-condensed table-franco table-striped table-hover t-mag" id="rezTable">
          <thead><tr></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</template>
