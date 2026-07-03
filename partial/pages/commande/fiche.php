<?php
global $db;
$c = client::getByCode($commande['id_magasin']);
$u = user::getUserFromLogin($commande['user']);
$db->execute("SELECT * FROM commande_apk_produits WHERE id_commande_apk = '".$db->escape($commande['id'])."'");
$produits = $db->getArray();

?>




<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-walking"></i>  <?php echo l('page-cmd-label-cmd').' # '.$commande['id'];?>
      <?php echo l('page-cmd-label-realise-par').' '.$u['displayname'].' '.l('page-cmd-label-realise-chez').' '.$c['enseigne'];?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Commandes">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col">
        <h5><span><?php echo l('page-cmd-ss-titre-details');?></span></h5>
        <table class="table table-striped">
          <tbody>
            <tr><th><?php echo l('page-cmd-id-cmd');?> :</th><td>#<?php echo $commande['id'];?></td></tr>
            <tr><th><?php echo l('page-cmd-id-apk');?> :</th><td><?php echo $commande['id_apk'];?></td></tr>
            <tr><th><?php echo l('page-cmd-createur');?> :</th><td><?php echo $u['displayname'];?></td></tr>
            <tr><th><?php echo l('page-cmd-date-cmd');?> :</th><td><?php echo core::dateOutput($commande['date_creation'],true);?></td></tr>
            <tr><th><?php echo l('page-cmd-date-livraison');?> :</th><td><?php echo core::apkDate($commande['date_liv_estimee']);?></td></tr>
            <tr><th><?php echo l('page-cmd-date-prochaine');?> :</th><td><?php echo core::apkDate($commande['date_next_cmd']);?></td></tr>
            <?php if( $commande['no_cmd_client'] != "" ) { ?>
            <tr><th><?php echo l('page-cmd-no-cmd-client');?> :</th><td><?php echo $commande['no_cmd_client'];?></td></tr>
            <?php } ?>
            <?php if( $commande['fp_raison'] != "" ) { ?>
            <tr><th><?php echo l('page-cmd-franco');?> :</th><td><?php echo $commande['fp_raison'];?></td></tr>
            <?php } ?>
            <?php
            $db->execute("SELECT id FROM visite WHERE id_commande = '".$commande['id_apk']."' ");
            if( $db->num() ) {
              $id = $db->assoc()['id'];
              echo '<tr><th>'.l('page-cmd-visite-liee').' :</th><td><a href="'.URL.'Visites/'.$id.'" target="_blank">#'.$id.'</a></td></tr>';
            }
            ?>
            <?php if( $commande['externe'] == 1 ) { ?>
              <tr><th><?php echo l('page-cmd-cmd-ext-envoyee-a');?> :</th><td><?php echo $commande['externeMail'];?></td></tr>
              <tr><th><?php echo l('page-cmd-pdf-cmd');?> :</th><td><a target="_blank" href="<?php echo URL_APP_ROOT.'CmdPDF/'.$commande['id'];?>"><?php echo l('bouton-ouvrir');?></a></td></tr>
              <tr>
                <th><?php echo l('page-cmd-trnasform-titre');?> :</th>
                <td>
                  <button class="btn btn-sm btn-primary" onClick="transformCmd(<?php echo $commande['id'];?>)"><?php echo l('page-cmd-bouton-transofrm');?></button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <h5><span><?php echo l('page-cmd-total-cmd');?></span></h5>

        <p class="text-center" style="font-size:60px;color:#264d3a">
          <?php echo core::n($commande['total']);?>€
        </p>

      </div>
      <div class="col">
        <h5><span><?php echo l('page-cmd-list-produits');?> (<?php echo count($produits);?>)</span></h5>
        <div class="list-group" style="font-size:14px;">
          <?php


          foreach( $produits as $k=>$e ) {
            $p = produit::getByCode($e['id_produit']);
            echo '
            <a href="'.URL.'/Produits/Fiche-'.$p['id'].'" class="list-group-item list-group-item-action">
              <span class="mb-1">'.$e['quantite'].' x '.$p['libelle'].' <em>(#'.$p['id_as400'].')</em></span>
              <small class="float-right">'.$e['quantite'].' x '.core::n($e['prix_unitaire']).'€ = '.core::n($e['prix_total']).'€</small>
            </a>';
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
