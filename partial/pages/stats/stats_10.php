<div class="alert alert-info">
  Veuillez choisir au moins un type de DN et une marque afin d'afficher les résultats
</div>
<div class="filters">
  <form class="form-inline" action="<?php echo URL."Stats/".$id_stat;?>" method="POST" id="formFilters">
    <input type="hidden" name="post_marque" value="<?php if(isset($_POST['marque'])) echo $_POST['marque'];?>"/>
    <input type="hidden" name="post_gamme" value="<?php if(isset($_POST['gamme'])) echo $_POST['gamme'];?>"/>
    <label class="mr-5"><strong><i class="fas fa-filter mr-2"></i> Filtres : </strong></label>
    <div class="input-group mr-2">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="type">
        <option value="">-- Choisir--</option>
        <option value="ABC" <?php if(isset($_POST['type']) && $_POST['type'] == "ABC" ) echo 'selected';?> >ABC</option>
        <option value="CONCU" <?php if(isset($_POST['type']) && $_POST['type'] == "CONCU" ) echo 'selected';?> >Concurence</option>
      </select>
    </div>

    <div class="input-group mr-2 hidden" id="sel_marque">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="marque">
        <option value="">-- Choisir--</option>
      </select>
    </div>

    <div class="input-group mr-2 hidden" id="sel_gamme">
      <div class="input-group-prepend">
        <div class="input-group-text"><i class="fas fa-filter mr-2"></i></div>
      </div>
      <select class="form-control" name="gamme">
        <option value="">-- Choisir--</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary ml-2">Appliquer les filtres</button>
  </form>
</div>

<div id="statsRezWrapper" data-get="getDNPresente"></div>

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
