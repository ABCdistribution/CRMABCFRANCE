<?php if( !securite::can(6) ) return core::restricted();?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('op-promo-titre');?></h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col" style="max-width:70%;" id="listOpWrapper">
        <h5><span><?php echo l('op-promo-listes');?></span></h5>

        <div class="alert alert-primary" role="alert">
          <i class="fas fa-arrows-alt" style="margin-right:15px"></i> 
          <?php echo l('op-promo-listes-disclaimer');?>
          
        </div>

        <ul class="list-group" id="listOp">
          <?php
          $promos = promo::getAll();
          $tmp = [];
          foreach( $promos as $k=>$e ) {
            $statut = '<span class="st badge badge-success float-right">'.l('statut-actif').'</span>';
            if( $e['actif'] == 0 ) $statut = '<span class="st badge badge-danger float-right">'.l('statut-inactif').'</span>';
            $tmp[] = '<li class="list-group-item" data-id="'.$e['id'].'">';
            $tmp[] = '<span class="badge badge-secondary">'.$e['id_as400'].'</span> '.$e['libelle'];
            $tmp[] = '<button class="btn btn-warning float-right btn-xs edit"><i class="fas fa-edit"></i></button>';
            $tmp[] = $statut;
            $tmp[] = '</li>';
          }
          echo implode($tmp);
          ?>
        </ul>

      </div>
      <div class="col-3" style="min-width:250px;">
        <h5><span><?php echo l('op-promo-nouvelle-op');?></span></h5>
        <form method="post" action="#" id="newPromo">
          <div class="form-group">
            <label><?php echo l('op-promo-code-op');?></label>
            <input type="text" class="form-control" name="id_as400" maxlenth="15">
          </div>
          <div class="form-group">
            <label><?php echo l('op-promo-code-designation');?></label>
            <input type="text" class="form-control" name="libelle" maxlength="100">
          </div>
          <div class="form-group">
            <label><?php echo l('op-promo-code-statut');?></label>
            <select class="form-control" name="actif">
              <option value="1" selected><?php echo l('statut-actif');?></option>
              <option value="0"><?php echo l('statut-inactif');?></option>
            </select>
          </div>
        </form>
        <div class="tc"><br/>
          <a class="btn btn-primary btn-sm" href="#" onclick="createOp()"><?php echo l('bouton-creer');?></a>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
