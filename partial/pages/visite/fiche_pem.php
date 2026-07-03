<?php

if( isset($pem['nopem']) ) {
    echo '<div class="alert alert-info">'.l('page-visite-cr-nopem-raison').' : '.$pem['nopem'].'</div>';
    return;
}
else if( isset($pem['bug']) ) {
    echo '<div class="alert alert-info">'.$pem['bug'].'</div>';
    return;
}

$pvisite = $pem['visite'];
?>

<div class="row">
    <div class="col">
    <?php if( securite::can(15) ) { ?>
        <h5><span><?php echo l('page-visite-cr-cmd-pem');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th><?php echo l('page-visite-cr-cmd-name');?> :</th>
            <td><?php
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

            <?php if( securite::can(16) ) { ?>
            <h5><span><?php echo l('dn-pem');?></span></h5>
            <table class="table table-condensed">
                <thead>
                <th><?php echo l('dn-pem-article');?>Article</th>
                <th><?php echo l('dn-pem-quantite');?>Quantité</th>
                </thead>
                <tbody>
                <?php
                $dn = visite::getVisiteDn($pvisite['id']);
                $tmp = [];
                foreach( $dn as $k=>$e ) {
                    $tmp[] = '
                    <tr class="bg-abc">
                    <td>'.$e['marque'].'</td>
                    <td>'.$e['gamme'].'</td>
                    </tr>
                    ';
                }
                echo implode($tmp);
                ?>
                </tbody>
            </table>
            <?php } ?>



    </div>




    <div class="col photo-wrapper">

        <?php if( securite::can(14) ) { ?>

            <div class="row">
            <?php
            $t = [
                "photos-" => l('page-visite-step-pem-photo-rayon'),
                "photoFin" => l('page-visite-step-pem-photo-fin')
            ];
            $num = 1;
            $content = [];
            foreach( $t as $k=>$e ) {
                $tmpContent = [];
                $tmpContent[] = '<div class="col-12"><h5><span>'.$e.'</span></h5>';
                $count = 0;
                foreach( $pvisite['photos'] as $i ) {
                if( strpos($i['file'],$k) !== FALSE || ($k=="photos-" && strpos($i['file'],"Arrivee") !== FALSE) ) {
                    $link = URL_APP_ROOT.'datas/visites/'.$i['file'];
                    $tmpContent[] = '<img src="'.$link.'" data-num="'.$num.'" data-name="'.core::photoAppnameToTime($i['app_name']).'" style="max-height: 150px;" class="img-thumbnail viewer"/>';
                    $num++;
                    $count++;
                }
                }
                $tmpContent[] = '</div>';
                if( $count > 0 ) $content[] = implode($tmpContent);
            }
            echo implode($content);
            ?>
            </div>
        <?php } ?>

    </div>



    <div class="col ">
        <?php if( securite::can(13) ) { ?>
        <h5><span><?php echo l('page-visite-step-pem-titre');?>Timing de visite PEM</span></h5>
        <?php
        $st = [];
        $pstTitle = [
            0=> l('page-visite-step-pem-debut'),
            1=> l('page-visite-step-pem-photo-rayon'),
            2=> l('page-visite-step-pem-verif'),
            10 => l('page-visite-step-pem-photo-fin'),
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
        ?>
        <?php } ?>
    
    </div>
</div>

