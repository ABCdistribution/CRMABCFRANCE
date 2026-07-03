<form method="post" action="#" class="form-save" data-table="ref_article" data-id="<?php echo $id;?>">
    <?php

    $ids = $produit['id_as400'];
    if( $produit['id_ita'] != "" ) $ids .= " // " . $produit['id_ita'];
    

    echo '<h5><span>'.l('page-produit-infos-generales').'</span></h5>';
    echo core::colSplit([core::printInput(l('page-produit-libelle'),'libelle',$produit['libelle'],true)],1);
    echo core::colSplit([
    core::printInput(l('page-produit-id_as400'),'id_as400',$ids,true),
    core::printInput(l('page-produit-famille'),'famille',$produit['famille']['libelle'],true),
    core::printInput(l('page-produit-gencode').'','gencode',$produit['gencode'],true),
    core::printInput(l('page-produit-type-article').'','type_article',$produit['type_article']['libelle'],true),
    core::printInput(l('page-produit-tva').'','tva',$produit['tva']['libelle'],true),
    core::printInput(l('page-produit-famille-plan').'','famille_plan',$produit['famille_plan']['libelle'],true),
    core::printInput(l('page-produit-gamme').'','gamme',$produit['gamme']['libelle'],true),
    core::printInput(l('page-produit-marque').'','marque',$produit['marque']['libelle'],true),
    core::printInput(l('page-produit-famille-acd').'','famille_acd',$produit['famille_acd']['libelle'],true),
    core::printInput(l('page-produit-ss-famille-acd').'','ss_famille_acd',$produit['ss_famille_acd']['libelle'],true),
    core::printInput(l('page-produit-statut').'','statut',$produit['statut'],true),
    core::printInput(l('page-produit-ss-statut').'','sous_statut',$produit['sous_statut'],true),

    ],2);
    echo core::alert(
        l('page-produit-alert-title'),
        l('page-produit-alert-message'),
        false
    );
    ?>
</form>