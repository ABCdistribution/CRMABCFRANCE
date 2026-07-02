
<h5><span><?php echo l('page-produit-titre-stock');?></span></h5>
    <?php if( $produit['stock'] !== false ) { ?>
    <div class="tc">
    <h1><?php echo  number_format($produit['stock']['stock'],0,","," ");?> <?php echo l('abreviation-exemplaires');?></h1>
    <table class="table text-left">
        <tr>
        <th><?php echo l('page-produit-stock-last-update');?> :</th>
        <td><?php echo $produit['stock']['last_update'] > 0 ? l('abreviation-ilya').' '.core::dateFrom($produit['stock']['last_update']) : l('jamais');?></td>
        </tr>
        <tr><td style="border:0 none;" colspan="2"><br/></td></tr>
        <tr>
        <th><?php echo l('page-produit-seuil-remplacement');?> :</th>
        <td>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control text-right" id="id_qte_switch" placeholder="<?php echo l('page-produit-quantite-placeholder');?>" value="<?php echo $produit['switch']['seuil'] ?? "";?>">
            <div class="input-group-append"><span class="input-group-text" id="basic-addon2"><?php echo l('abreviation-exemplaires');?></span></div>
        </div>
        </td>
        </tr>
        <tr>
        <th><?php echo l('page-produit-code-remplacement');?>  :</th>
        <td>
            <div class="input-group input-group-sm">
            <input type="text" class="form-control" id="id_switch" placeholder="<?php echo l('page-produit-code-recherche');?>" value="<?php echo $produit['switch']['id_switch'] ?? "";?>">
            <div class="input-group-append">
                <span class="input-group-text" id="stateController" style="min-width:32px" >
                <?php
                    if( isset($produit['switch']['id_switch']) && $produit['switch']['id_switch'] != "" ) {
                    if( produit::getByCode($produit['switch']['id_switch']) ) echo '<i class="fas fa-check"></i>';
                    else '<i class="fas fa-times" rel="tooltip" title="'.l('page-produit-article-inconnu').'"></i>';
                    }
                ?>
                </span>
            </div>
            </div>                  
        </td>
        </tr>  
        <?php 
        if( isset($produit['switch']['id_user']) ) {
        echo '<tr><td colspan="2" class="text-center" style="font-size:12px">';
        echo l('page-produit-remplacement-update').'';
        echo user::getNameFromId($produit['switch']['id_user']).' ';
        echo l('date-le').' '.core::dateOutput($produit['switch']['last_update'],true);
        echo '</td></tr>';
        }
        ?>
        <tr>
        <td colspan="2" class="text-center">
            <a class="btn btn-danger text-white btn-sm" onclick="killArticleSwitch()"><?php echo l('bouton-annuler');?></a>
            <a class="btn btn-primary btn-sm" onclick="saveArticleSwitch()"><?php echo l('bouton-enregistrer');?></a>
            <br/><br/>
        </td>
        </tr>                          
    </table>
    </div>
    <?php } else { ?>
    <p class="text-center text-secondary"><?php echo l('page-produit-article-no-info');?></p>
    <?php } ?>