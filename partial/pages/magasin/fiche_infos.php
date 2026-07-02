<div class="row">
    <div class="col-8">
        <form method="post" action="#" class="form-save" data-table="ref_client" data-id="<?php echo $id;?>">
        <div class="bloc">
        <?php
        echo '<h5><span>'.l('page-client-infos-title-as400').'</span></h5>';
        echo core::colSplit([
        core::printInput( l('page-client-infos-libelle').'','enseigne',$magasin['enseigne']),
        core::printInput( l('page-client-infos-id_as400').'','id_as400',$magasin['id_as400'], ($magasin['id_as400']!=""?true:false)),
        core::printInput( l('page-client-infos-adresse1').'','adresse1',$magasin['adresse1']),
        core::printInput( l('page-client-infos-adresse2').'','adresse2',$magasin['adresse2']),
        core::printInput( l('page-client-infos-adresse3').'','adresse3',$magasin['adresse3']),
        core::printInput( l('page-client-infos-code-postal1').'','code_postal',$magasin['code_postal']),
        core::printInput( l('page-client-infos-code-postal2').'','code_postal_2',$magasin['code_postal_2']),
        core::printInput( l('page-client-infos-ville').'','ville',$magasin['ville']),
        core::printInput( l('page-client-infos-code-pays').'','pays',$magasin['pays']),
        core::printInput( l('page-client-infos-devise').'','devise',$magasin['devise']),
        core::printInput( l('page-client-infos-siret').'','siret',$magasin['siret']),
        core::printInput( l('page-client-infos-forme').'','forme_entreprise',$magasin['forme_entreprise']),
        ],2);
        echo '<h5><span>'.l('page-client-infos-title-contacts').'</span></h5>';
        echo core::colSplit([
        core::printInput( l('page-client-infos-contact1').'','contact_1',$magasin['contact_1']),
        core::printInput( l('page-client-infos-contact2').'','tel1',$magasin['tel1']),
        core::printInput( l('page-client-infos-contact3').'','contact_2',$magasin['contact_2']),
        core::printInput( l('page-client-infos-contact4').'','tel2',$magasin['tel2']),
        core::printInput( l('page-client-infos-contact5').'','contact_3',$magasin['contact_3']),
        ],2);
        ?>
        <div class="tc">
            <br/>
            <button class="btn abc btn-sm btnSubmitForm"><?php echo l('bouton-enregistrer');?></button>
        </div>
        </div>        
    </form>


    
    </div>
    <div class="col" >

    <h5><span><?php echo l('page-client-infos-periodicite');?></span></h5>
        <select class="form-control" name="periodicite" data-id="<?php echo $magasin['id_as400'];?>">
        <option value="0"><em><?php echo l('page-client-infos-periodicite-aucune');?></em></option>
        <?php
        $p = client::getPeriodicite( $magasin['id_as400'] );
        $all = client::getAllPeriodicite();
        foreach( $all as $k=>$e ) {
        $s = ( isset($p['id_periodicite']) && $p['id_periodicite'] == $k ? 'selected' : '' );
        echo '<option value="'.$k.'" '.$s.' >'.$e['libelle'].'</option>';
        }
        ?>
        </select>
        <?php
        if( !empty($p) ) {
        echo '
        <p class="text-right text-secondary">
        <small>
            '.l('page-client-infos-periodicite-user').' <strong>'.user::getNameFromId($p['id_user']).'</strong>
            '.l('date-le').' '.core::dateOutput($p['date_creation'],true).'
        </small>
        </p>
        ';
        }
        ?>

    <h5><span><?php echo l('page-client-infos-plannification');?></span></h5>
    <?php
        echo client::printPlannification( $magasin['id_as400'] );
    ?>

    <h5><span><?php echo l('role-dr');?></span></h5>
    <select class="form-control" name="id_user_dr" data-id="<?php echo $magasin['id_as400'];?>">
        <option value="0"><em><?php echo l('page-client-infos-role-nul');?></em></option>
        <?php
        $id_user_dr = intval($magasin['infos']['id_user_dr'] ?? 0);
        foreach (user::getAllDR() as $dr) {
            $label = '#'.$dr['id_repr'].' : '.$dr['displayname'];
            if (!empty($dr['secteur'])) {
                $label .= ' ('.$dr['secteur'].')';
            }
            $s = ($id_user_dr === intval($dr['id']) ? 'selected' : '');
            echo '<option value="'.$dr['id'].'" '.$s.'>'.$label.'</option>';
        }
        ?>
    </select>
    <h5><span><?php echo l('role-cs');?></span></h5>
    <p class="tc text-secondary"><?php echo l('page-client-infos-role-nul');?></p>
    <h5><span><?php echo l('role-promoteurs');?></span></h5>

    <p class="tc text-secondary">
        <?php if( $magasin['commercial_1'] ) echo $magasin['commercial_1']['libelle'];?>
        <?php if( $magasin['commercial_2'] && $magasin['commercial_2']['libelle'] != l('page-client-infos-role-nul') ) echo '<br/>'.$magasin['commercial_2']['libelle'];?>
    </p>
    <h5><span><?php echo l('page-client-infos-remarque');?></span></h5>
    <?php
    global $db;
    $db->execute("SELECT * FROM ref_client_remarque WHERE id_as400 = '".$magasin['id_as400']."' ORDER BY id DESC LIMIT 10");
    if( !$db->num() )
    echo '<p class="tc text-secondary">'.l('page-client-infos-role-nul').'</p>';
    else {
    $rem = $db->getArray();
    foreach( $rem as $k=>$e ) {
        $name = user::getFromIdRepr($e['id_repr'])['displayname'];
        echo '<p><strong>'.$name.'</strong>, '.l('date-le').' '.core::dateOutput($e['date_creation']).' :<br/><em>'.$e['remarque'].'</em></p>';
    }
    }
    ?>        


    <h5><span><?php echo l('page-client-infos-crm');?></span></h5>
    <form method="POST" action="#" id="formInfosSup" onsubmit="return false;" style="background:#e4e4e4;padding: 15px;">
        <input type="hidden" name="id_as400" value="<?php echo $magasin['id_as400'];?>"/>
        <?php
        echo core::colSplit([
            core::printSelect( l('page-client-infos-crm-type-cmd').'','type_cmd',$magasin['infos']['type_cmd'],false,[""=>"","edi"=>"EDI","crm"=>"CRM"]),
            core::printSelect( l('page-client-infos-crm-avant-ouverture').'','cli_avant_ouverture',$magasin['infos']['cli_avant_ouverture'],false,[""=>"","1"=>"OUI","2"=>"NON"]),
            core::printSelect( l('page-client-infos-crm-flasher').'','flash',$magasin['infos']['flash'],false,[""=>"","1"=>"OUI","2"=>"NON"]),
            core::printSelect( l('page-client-infos-crm-labell').'','cmd_labell',$magasin['infos']['cmd_labell'],false,[""=>"","direct"=>"DIRECT","base"=>"BASE"]),
            core::printSelect( l('page-client-infos-crm-chaussure').'','chaussures_secu',$magasin['infos']['chaussures_secu'],false,[""=>"","1"=>"OUI","2"=>"NON"]),
            core::printSelect( l('page-client-infos-crm-attestation').'','attestation',$magasin['infos']['attestation'],false,[""=>"","1"=>"OUI","2"=>"NON"]),
            core::printSelect( l('page-client-infos-crm-cni').'','cni',$magasin['infos']['cni'],false,[""=>"","1"=>"OUI","2"=>"NON"]),
            core::printInput(
                'Numéro client juva',
                'num_juva',
                $magasin['infos']['num_juva'],
                false
            ),
            ],1);
        ?>
        <div class="tc">
            <br/>
            <button class="btn abc btn-sm btnSaveInfosSupp"><?php echo l('bouton-enregistrer');?></button>
        </div>
    </form>


    </div>
</div>




