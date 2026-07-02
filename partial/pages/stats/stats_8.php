<div class="alert alert-info">
  <?php echo l('stats-warning-dn');?>
</div>
<div class="filters">
  <form class="form-inline" action="<?php echo URL."Stats/".$id_stat;?>" method="POST" id="formFilters">
    <input type="hidden" name="post_marque" value="<?php if(isset($_POST['marque'])) echo $_POST['marque'];?>"/>
    <input type="hidden" name="post_gamme" value="<?php if(isset($_POST['gamme'])) echo $_POST['gamme'];?>"/>
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
      <select class="form-control" name="type">
        <option value=""><?php echo l('stats-choisir');?></option>
        <option value="ABC" <?php if(isset($_POST['type']) && $_POST['type'] == "ABC" ) echo 'selected';?> >ABC</option>
        <option value="CONCU" <?php if(isset($_POST['type']) && $_POST['type'] == "CONCU" ) echo 'selected';?> ><?php echo l('stat-dn-concurence');?></option>
      </select>
    </div>

    <div class="input-group mr-2 hidden" id="sel_marque">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="marque">
        <option value=""><?php echo l('stats-choisir');?></option>
      </select>
    </div>

    <div class="input-group mr-2 hidden" id="sel_gamme">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="gamme">
        <option value=""><?php echo l('stats-choisir');?></option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary ml-2"><?php echo l('stat-filtre-appliquer');?></button>
  </form>
</div>

<div id="statsRezWrapper" data-get="getDN"></div>

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
