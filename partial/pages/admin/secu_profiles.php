<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-profiles-titre');?></h5>
  </div>
  <div class="card-body m-250">
    <div class="row">
      <div class="col">
        <h5><span><?php echo l('admin-profiles');?></span></h5>
        <div class="list-group" id="selectProfile">
          <?php
          foreach( securite::getProfils() as $k=>$e ) {
            $d = ( $e['defaut'] == 1 ? '<i class="fas fa-star" rel="tooltip" title="Profil par défaut"></i>' : '' );
            echo '<a href="#" class="list-group-item list-group-item-action" data-homepage="'.rawurlencode($e['homepage']).'" data-id="'.$k.'" data-name="'.rawurlencode($e['libelle']).'" data-defaut="'.$e['defaut'].'">';
            echo $d.' '.$e['libelle'].' </a>';
          }
          ?>
        </div>
      </div>
      <div class="col-1"></div>
      <div class="col">
        <h5><span><?php echo l('admin-profiles-new');?></span></h5>
        <div class="form-group">
          <label><?php echo l('admin-profiles-new-name');?></label>
          <input type="text" class="form-control" name="libelle_new_profil"/>
          <small class="form-text text-muted"><?php echo l('admin-profiles-new-name-info');?></small>
        </div>
        <div class="tc">
          <button class="btn btn-primary btn-sm" onClick="createProfil()"><?php echo l('admin-profiles-new-create');?></button>
        </div>
      </div>
    </div>
  </div>
</div>
