<div class="row">
    <div class="col">

        <h5><span><?php echo l('page-visite-cr-client');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th style="width:180px;"><?php echo l('page-visite-cr-enseigne');?> :</th>
            <td><?php echo $c['enseigne'];?></td>
            </tr>
            <tr>
            <th><?php echo l('page-visite-cr-fiche');?> :</th>
            <td><a target="_blank" href="<?php echo URL_APP_ROOT;?>Magasins/Fiche-<?php echo $c['id'];?>"><?php echo l('cliquez-ici');?></a></td>
            </tr>
            <tr>
            <th><?php echo l('page-visite-cr-adresse');?> :</th>
            <td><?php echo $c['code_postal'].' '.$c['code_postal_2'].' '.$c['ville'].' ('.$c['pays'].')';?></td>
            </tr>
        </table>

        <h5><span><?php echo l('page-visite-cr-visite');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th  style="width:180px;"><?php echo l('page-visite-cr-reception');?> :</th>
            <td><?php echo core::dateOutput($visite['queue_date'], true);?></td>
            </tr>
            <tr>
            <th><?php echo l('page-visite-realise-par');?> :</th>
            <td><?php echo $u;?></td>
            </tr>
        </table>

    </div>
    <div class="col">

    <?php if( securite::can(15) ) { ?>

        <h5><span><?php echo l('page-visite-cr-cmd-liee-visite');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th><?php echo l('page-visite-cr-cmd');?> :</th>
            <td><?php
                if( $visite['cmd'] ) {
                echo '<a href="'.URL.'Commandes/'.$visite['cmd']['id'].'" target="_blank">';
                echo l('page-visite-cr-cmd').' #'.$visite['cmd']['id'].' : '.core::n($visite['cmd']['total'],2).' € - '.core::apkDate2($visite['cmd']['date_liv_estimee']);
                echo '</a>';
                }
                else echo  '<em>'.l( $visite['no_cmd'] ? 'page-visite-cr-visite-ss-cmd' : 'page-visite-cr-cmd-attente').'</em>';
            ;?></td>
            </tr>
            <?php if( $visite['no_cmd_reason'] != "" && $visite['no_cmd'] == 1 ) { ?>
            <tr>
            <th><?php echo l('page-visite-cr-nocmd-car');?> :</th>
            <td><?php echo core::getReason($visite['no_cmd_reason']);?></td>
            </tr>
            <?php } ?>
        </table>

        <?php
        if( isset($pem['nopem']) ) {
            echo '<div class="alert alert-info">'.l('page-visite-cr-nopem-raison').' : '.$pem['nopem'].'</div>';
        }
        else if( isset($pem['bug']) ) {
            echo '<div class="alert alert-info">'.$pem['bug'].'</div>';
        }
        else {
        ?>
        <h5><span><?php echo l('page-visite-cr-cmd-pem');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th><?php echo l('page-visite-cr-cmd-name');?> :</th>
            <td><?php
                $pvisite = $pem['visite'];
                if( $pvisite['cmd'] ) {
                echo '<a href="'.URL.'Commandes/'.$pvisite['cmd']['id'].'" target="_blank">';
                echo l('page-visite-cr-cmd-name').' #'.$pvisite['cmd']['id'].' : '.core::n($pvisite['cmd']['total'],2).' € - '.core::dateOutput($pvisite['cmd']['date_liv_estimee']);
                echo '</a>';
                }
                else echo  '<em>'.l( $pvisite['no_cmd'] ? 'page-visite-cr-visite-ss-cmd' : 'page-visite-cr-cmd-attente').'</em>';
            ?></td>
            </tr>
            <?php if( $pvisite['no_cmd_reason'] != "" && $pvisite['no_cmd'] == 1 ) { ?>
            <tr>
            <th><?php echo l('page-visite-cr-nocmd-car');?> :</th>
            <td><?php echo core::getReason($pvisite['no_cmd_reason']);?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } ?>

        <?php if( isset($visite['alerte_raison']) && $visite['alerte_raison'] != "") { ?>
        <h5><span>Alerte commerciale</span></h5>
        <table class="table table-striped table-condensed">
            <tr>
                <th>Raison(s) :</th>
                <td>
                    <?php foreach(explode(",",$visite['alerte_raison']) as $id_but ) echo $alertes[$id_but].'<br/>'; ?>
                </td>
            </tr>
            <?php if( $visite['alerte_obs'] != "") { ?>
            <tr>
                <th>Observations :</th>
                <td>
                    <?php echo $visite['alerte_obs']; ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php } ?>
    <?php } ?>


    </div>
    <div class="col">

    <?php if( securite::can(13) ) { ?>
            <h5><span><?php echo l('page-visite-step-titre');?></span></h5>
            <?php
            $st = [];

            foreach( $visite['steps'] as $step ) {
              if( !isset($stTitle[$step['step_nb']]) ) continue;
              if( $step['step_nb'] == 4 ) {
                if($deballage['state'] == 0 ) continue;
                $st[] = '
                    <div class="card clat">
                    <div class="card-body">
                        <strong>'.l('page-visite-step-rayon-debut').' :</strong><br/>
                        <i class="far fa-clock"></i> '.date("d/m/Y  G\hi",strtotime($deballage['debut'])).'
                    </div>
                    </div>    
                    <div class="card clat">
                    <div class="card-body">
                        <strong>'.l('page-visite-step-rayon-fin').' :</strong><br/>
                        <i class="far fa-clock"></i> '.date("d/m/Y G\h i",strtotime($deballage['fin'])).'
                    </div>
                    </div>                
                ';
              }
              else {
                $st[] = '
                    <div class="card clat">
                    <div class="card-body">
                        <strong>'.$stTitle[$step['step_nb']].'</strong><br/>
                        <i class="far fa-clock"></i> '.core::apkDate2($step['date_step'],true).'
                    </div>
                    </div>
                ';
              }
            }
            echo implode($st);   
            
            if( isset($pvisite['steps']) ) {
                $st = [];
                $pstTitle = [
                    0=> l('page-visite-step-pem-debut')."",
                    1=> l('page-visite-step-pem-photo-rayon')."",
                    2=> l('page-visite-step-pem-verif')."",
                    10 => l('page-visite-step-pem-photo-fin')."",
                    //11 => "Questionnaire de fin"
                ]; 
                foreach( $pvisite['steps'] as $step ) {
                    if( !isset($pstTitle[$step['step_nb']]) ) continue;
                    $st[] = '
                    <div class="card clat">
                        <div class="card-body">
                        <strong>'.$pstTitle[$step['step_nb']].'</strong><br/>
                        <i class="far fa-clock"></i> '.core::apkDate2($step['date_step'],true).'
                        </div>
                    </div>
                    ';
                }
                echo implode($st);
            }
            ?>


            <?php } ?>


            


    </div>
</div>