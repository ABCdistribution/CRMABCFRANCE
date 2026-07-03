<div class="row">
    <div class="col-md-4">

        <?php if( securite::can(15) ) { ?>
        <h5><span><?php echo l('page-visite-dph-cmd-liee');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th>Commande :</th>
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
        <?php } ?>

        <h5><span><?php echo l('page-visite-dph-mise-rayon');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th  style="width:180px;"><?php echo l('page-visite-dph-cmd-mise-rayon');?></th>
            <td><?php echo $deballage['state'] == 1 ? l('oui') : l('non');?></td>
            </tr>
            <?php if($deballage['state'] == 1 && securite::can(13) ) { ?>
                <tr>
                    <th><?php echo l('page-visite-dph-mise-rayon-debut');?> :</th>
                    <td><?php echo date("G\h i\m s\s",strtotime($deballage['debut']));?></td>
                </tr>
                <tr>
                    <th><?php echo l('page-visite-dph-mise-rayon-fin');?>   :</th>
                    <td><?php echo date("G\h i\m s\s",strtotime($deballage['fin']));?></td>
                </tr>
                <tr>
                    <th><?php echo l('page-visite-dph-mise-rayon-duree');?>   :</th>
                    <td><?php echo core::secondsToTime( strtotime($deballage['fin']) - strtotime($deballage['debut']));?></td>
                </tr>


                <?php
                $colis = visite::getDetailScanColis($visite['id']);
                if( count( $colis ) > 0 ) {
                    echo '<tr><td colspan="2" class="colisTd">';
                    echo '<details ><summary style="padding-left:15px;font-weight:bold;">'.l('page-visite-dph-mise-rayon-details').'</summary>';
                    echo '<ul class="colis">';
                    foreach( $colis as $k=>$e ) {
                        echo '
                            <li class="colisCmd">
                                <span class="title">N° Logis : '.$e['no_logis'].'</span>
                                <p>'.$e['colis'].' '.l('page-visite-dph-mise-rayon-colis-contenant').' '.$e['produits'].' '.l('page-visite-dph-mise-rayon-produits').'</p>
                                <table class="table">
                                    <thead>
                                        <th>'.l('page-visite-dph-mise-rayon-table-colis').'</th>
                                        <th>'.l('page-visite-dph-mise-rayon-table-sscc').'</th>
                                        <th>'.l('page-visite-dph-mise-rayon-table-no-contenant').'</th>
                                        <th>'.l('page-visite-dph-mise-rayon-table-date').'</th>
                                        <th>'.l('page-visite-dph-mise-rayon-table-type').'</th>
                                    </thead>
                                    <tbody>
                        ';
                        foreach($e['liste_colis'] as $c ) {
                            echo '<tr>';
                            echo '<td>#'.$c['id'].'</td>';
                            echo '<td>'.$c['code'].'</td>';
                            echo '<td>'.$c['code2'].'</td>';
                            echo '<td>'.core::dateOutput($c['date_scan'],true).'</td>';
                            echo '<td>'.( $c['manually'] ? '<i class="far fa-hand-paper"></i>':'<i class="fas fa-barcode"></i>' ).'</td>';
                            echo '</tr>';
                        }
                        echo '
                                    </tbody>
                                </table>
                            </li>
                        
                        ';
                    }
                    echo '</ul>
                    
                    <div class="legende">
                        <strong>'.l('page-visite-dph-mise-legende').' :</strong><br/>
                        <i class="fas fa-barcode"></i> : '.l('page-visite-dph-mise-colis-scanne').'<br/>
                        <i class="far fa-hand-paper"></i> : '.l('page-visite-dph-mise-colis-manuel').'
                    </div>
                    
                    
                    ';
                    echo '</details>';
                    echo '</td></tr>';
                }
                ?>
            <?php } ?>
        </table>        

        <?php if( securite::can(16) ) { ?>
        <h5><span><?php echo l('page-visite-dph-dn-titre');?></span></h5>
        <table class="table table-condensed">
            <thead>
            <th><?php echo l('dn-type');?></th>
            <th><?php echo l('dn-marque');?></th>
            <th><?php echo l('dn-gamme');?></th>
            <th class="tc"><?php echo l('dn-metrage');?></th>
            </thead>
            <tbody>
            <?php
            $dn = visite::getVisiteDn($visite['id']);
            $tmp = [];
            foreach( $dn as $k=>$e ) {
                $c = ( $e['type'] == "ABC" ? 'bg-abc' : 'bg-concu' );
                $tmp[] = '
                <tr class="'.$c.'">
                <td>'.($e['type'] == "CONCU" ? "Concurence" : "ABC").'</td>
                <td>'.$e['marque'].'</td>
                <td>'.$e['gamme'].'</td>
                <td class="tc">'.$e['metrage'].' m</td>
                </tr>
                ';
            }
            echo implode($tmp);
            ?>
            </tbody>
        </table>
        <?php } ?>

        <h5><span><?php echo l('page-visite-dph-mise-op-promo-titre');?></span></h5>
        <?php
        $st = [];
        foreach( $visite['promos'] as $promo ) {
            $p = promo::get($promo['id_as400']);
            if( !$p ) continue;
            $st[] = '
            <div class="card clat">
                <div class="card-body">
                <i class="fas fa-check green"></i>'.$p['libelle'].' <em class="float-right text-secondary">'.$p['id_as400'].'</em><br/>
                </div>
            </div>
            ';
        }
        echo implode($st);
        ?>



    </div>
    <div class="col-md-5">

        <?php if( $q ) { ?>
        <h5><span><?php echo l('visite-questionnaire-titre');?></span></h5>
        <table class="table table-striped table-condensed">
            <tr>
            <th><?php echo l('visite-questionnaire-rdv');?></th>
            <td><?php echo $q['q1'] == 'O' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';?></td>
            </tr>
            <tr>
            <th><?php echo l('visite-questionnaire-representant');?></th>
            <td><?php echo $q['q2'] == 'O' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';?></td>
            </tr>
            <?php if( $q['q2'] == 'O' ) { ?>
            <tr>
            <th><?php echo l('visite-questionnaire-represantant-nom');?></th>
            <td><?php echo $q['chef'];?></td>
            </tr>
            <?php } ?>
            <tr>
            <th><?php echo l('visite-questionnaire-but');?></th>
            <td>
                <?php foreach(explode(",",$q['but']) as $id_but ) echo $buts[$id_but].'<br/>';?>
            </td>
            </tr>                  
            <?php if( trim(strip_tags($q['obs'])) != "" ) { ?>
            <tr>
            <th><?php echo l('visite-questionnaire-obs');?> :</th>
            <td><?php echo preg_replace("/[(<br>|<br\s\/>)]{2,}/", "<br/>", $q['obs']);;?></td>
            </tr>
            <?php } ?>                  
        </table>
        <?php } ?>



        <div class="row photo-wrapper">
          <?php
          $t = [
            "photos-" => l('page-visite-step-arrivee-site'),
            "photoPlano" => l('page-visite-step-photo-face'),
            "photoRayon" => l('page-visite-step-photo-rayon'),
            "photoFin" => l('page-visite-step-photo-fin')
          ];
          $num = 1;
          $content = [];
          foreach( $t as $k=>$e ) {
            $tmpContent = [];
            $tmpContent[] = '<div class="col-12"><h5><span>'.$e.'</span></h5>';
            $count = 0;
            foreach( $visite['photos'] as $i ) {
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


    </div>

    <div class="col-3">
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
                      <strong>'.l('page-visite-dph-mise-rayon-debut').' :</strong><br/>
                      <i class="far fa-clock"></i> '.date("d/m/Y à G\hi",strtotime($deballage['debut'])).'
                  </div>
                  </div>    
                  <div class="card clat">
                  <div class="card-body">
                      <strong>'.l('page-visite-dph-mise-rayon-fin').' :</strong><br/>
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
        ?>
        <?php } ?>
    </div>
</div>