<?php if( !securite::can(26) ) return core::restricted();?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('page-pem-titre');?></h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col" style="max-width:70%;" id="listOpWrapper">
        <h5><span><?php echo l('page-pem-sous-titre-liste');?></span></h5>

        <div class="alert alert-info" role="alert">
          <?php echo l('page-pem-liste-alert');?>
        </div>

        <ul class="list-group" id="listOp">
          <?php
          $pem = produit::getListePem();
          $tmp = [];
          foreach( $pem as $k=>$e ) {
            $statut = '<span class="st badge badge-success float-right">'.l('statut-actif').'</span>';
            if( $e['actif'] == 0 ) $statut = '<span class="st badge badge-danger float-right">'.l('statut-inactif').'</span>';
            $tmp[] = '<li class="list-group-item" data-id="'.$e['id'].'">';
            $tmp[] = ' <span class="badge badge-secondary">'.$e['id_as400'].'</span>'.$e['libelle'];
            $tmp[] = '<button class="btn btn-warning float-right btn-xs edit"><i class="fas fa-edit"></i></button>';
            $tmp[] = $statut;
            $tmp[] = '</li>';
          }
          echo implode($tmp);
          ?>
        </ul>

      </div>
      <div class="col-3" style="min-width:250px;">
        <h5><span><?php echo l('page-pem-ajout-produit');?></span></h5>
        <form method="post" action="#" id="newPromo" onsubmit="return false">
          <div class="form-group">
            <label><?php echo l('page-pem-ajout-code-article');?> :</label>
            <input type="text" class="form-control" name="id_as400" maxlenth="15">
          </div>
          <div class="text-center">
             <button class="btn btn-primary" onClick="searchCode()"><?php echo l('bouton-rechercher');?></button>
          </div>
        </form>
        <div id="addProductPem"></div>
      </div>
    </div>
  </div>
</div>