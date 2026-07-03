<div class="filters">
  <form class="form-inline" action="<?php echo URL."Stats/".$id_stat;?>" method="POST" id="formFilters">
    <label class="mr-5"><strong><i class="fas fa-filter mr-2"></i> Filtres : </strong></label>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
      </div>
      <input
        type="text" class="form-control datepicker" name="from" placeholder="Du..."
        autocomplete="off" value="<?php echo $_POST['from'] ?? $from;?>">
    </div>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
      </div>
      <input
        type="text" class="form-control datepicker" name="to" placeholder="Au..."
        autocomplete="off" value="<?php echo $_POST['to'] ?? $to;?>">
    </div>
    <button type="submit" class="btn btn-primary ml-2">Appliquer les filtres</button>
  </form>
</div>

<div id="statsRezWrapper" data-get="getVisiteParPromoteurs"></div>

<template id="statstemplate">
  <h1 class="statsTitle"></h1>
  <div class="row">
    <div class="col-8">
      <div class="table-responsive">
        <table class="table table-condensed table-striped table-hover" id="rezTable">
          <thead><tr></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    <div class="col">
      <h2 class="chartsTitle"><i class="fas fa-chart-line"></i> Statistiques</h2>
    </div>
  </div>
</template>
