<?php
$c = client::getByCode($visite['id_client']);
$u = user::getNameFromId($visite['id_user']);
$q = visite::getQuestionnaire($visite['id']);
$buts = visite::getButVisite();
$pem = visite::getPem($visite);
$deballage = visite::getDeballage($visite);
?>




<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-walking"></i> Visite #<?php echo $visite['id'];?>
      réalisée par <?php echo $u;?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Visites">
        <i class="fas fa-long-arrow-alt-left"></i> Retour
      </a>
    </h5>
  </div>
  <div class="card-body">

    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a class="active" href="#details_visite" aria-controls="details_visite" role="tab" data-toggle="tab">Détails de la visite DPH</a></li>
      <?php if( securite::can(14) ) { ?>
        <li role="presentation"><a href="#photos_visiste" aria-controls="photos_visiste" role="tab" data-toggle="tab">Photos DPH <span class="badge badge-light"><?php echo count($visite['photos']);?></span></a></li>
      <?php } ?>
      <li role="presentation"><a href="#pemdiv" aria-controls="pemdiv" role="tab" data-toggle="tab">Visite PEM</a></li>
    </ul>

    <div class="tab-content">


      <div role="tabpanel" class="tab-pane active" id="details_visite">

        <div class="row">
          <div class="col">
            <div class="row">
              <div class="col">
                <h5><span>Le client</span></h5>
                <table class="table table-striped table-condensed">
                  <tr>
                    <th style="width:180px;">Enseigne :</th>
                    <td><?php echo $c['enseigne'];?></td>
                  </tr>
                  <tr>
                    <th>Fiche client :</th>
                    <td><a target="_blank" href="<?php echo URL_APP_ROOT;?>Magasins/Fiche-<?php echo $c['id'];?>">Cliquez ici</a></td>
                  </tr>
                  <tr>
                    <th>Adresse :</th>
                    <td><?php echo $c['code_postal'].' '.$c['code_postal_2'].' '.$c['ville'].' ('.$c['pays'].')';?></td>
                  </tr>
                </table>

                <h5><span>La visite</span></h5>
                <table class="table table-striped table-condensed">
                  <tr>
                    <th  style="width:180px;">Réception visite :</th>
                    <td><?php echo core::dateOutput($visite['queue_date'], true);?></td>
                  </tr>
                  <tr>
                    <th>Réalisée par :</th>
                    <td><?php echo $u;?></td>
                  </tr>
                </table>

                <?php if( securite::can(15) ) { ?>
                <h5><span>Commande liée à la visite</span></h5>
                <table class="table table-striped table-condensed">
                  <tr>
                    <th>Commande :</th>
                    <td><?php
                      if( $visite['cmd'] ) {
                        echo '<a href="'.URL.'Commandes/'.$visite['cmd']['id'].'" target="_blank">';
                        echo 'Commande #'.$visite['cmd']['id'].' de '.core::n($visite['cmd']['total'],2).' € pour le '.core::apkDate2($visite['cmd']['date_liv_estimee']);
                        echo '</a>';
                      }
                      else echo  $visite['no_cmd'] ? '<em>Visite sans commande</em>' : '<em>Commande en attente</em>';
                    ;?></td>
                  </tr>
                  <?php if( $visite['no_cmd_reason'] != "" && $visite['no_cmd'] == 1 ) { ?>
                  <tr>
                    <th>Pas de commande car :</th>
                    <td><?php echo core::getReason($visite['no_cmd_reason']);?></td>
                  </tr>
                  <?php } ?>
                </table>
                <?php } ?>

                <h5><span>Mise en rayon de commande</span></h5>
                <table class="table table-striped table-condensed">
                  <tr>
                    <th  style="width:180px;">Commande à mettre en rayon ?</th>
                    <td><?php echo $deballage['state'] == 1 ? 'Oui' : 'Non';?></td>
                  </tr>
                  <?php if($deballage['state'] == 1 && securite::can(13) ) { ?>
                  <tr>
                    <th>Début de mise en rayon   :</th>
                    <td><?php echo date("G\h i\m s\s",strtotime($deballage['debut']));?></td>
                  </tr>
                  <tr>
                    <th>Fin de mise en rayon   :</th>
                    <td><?php echo date("G\h i\m s\s",strtotime($deballage['fin']));?></td>
                  </tr>
                  <tr>
                    <th>Durée de la mise en rayon   :</th>
                    <td><?php echo core::secondsToTime( strtotime($deballage['fin']) - strtotime($deballage['debut']));?></td>
                  </tr>
                  <?php } ?>
                </table>




              </div>
              <div class="col">

              <?php if( $q ) { ?>
                <h5><span>Questionnaire de visite</span></h5>
                <table class="table table-striped table-condensed">
                  <tr>
                    <th>Avez-vous eu rendez-vous en magasin ?</th>
                    <td><?php echo $q['q1'] == 'O' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';?></td>
                  </tr>
                  <tr>
                    <th>Avez-vous rencontré le représentant magasin ?</th>
                    <td><?php echo $q['q2'] == 'O' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';?></td>
                  </tr>
                  <?php if( $q['q2'] == 'O' ) { ?>
                  <tr>
                    <th>Quel est le nom du représentant magasin ?</th>
                    <td><?php echo $q['chef'];?></td>
                  </tr>
                  <?php } ?>
                  <tr>
                    <th>Quel était le but de la visite ?</th>
                    <td>
                      <?php foreach(explode(",",$q['but']) as $id_but ) echo $buts[$id_but].'<br/>';?>
                    </td>
                  </tr>                  
                  <?php if( trim(strip_tags($q['obs'])) != "" ) { ?>
                  <tr>
                    <th>Observations :</th>
                    <td><?php echo preg_replace("/[(<br>|<br\s\/>)]{2,}/", "<br/>", $q['obs']);;?></td>
                  </tr>
                  <?php } ?>                  
                </table>
                <?php } ?>


                <?php if( securite::can(16) ) { ?>
                <h5><span>DN</span></h5>
                <table class="table table-condensed">
                  <thead>
                    <th>Type</th>
                    <th>Marque</th>
                    <th>Gamme</th>
                    <th class="tc">Metrage</th>
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

                <h5><span>OP / Promos présentes</span></h5>
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
            </div>
          </div>

          <div class="col-3">
            <?php if( securite::can(13) ) { ?>
            <h5><span>Timing de visite</span></h5>
            <?php
            $st = [];
            $stTitle = [
              0=> "Nouvelle visite",
              1=> "Photo(s) d'arrivée",
              2=> "Photo planogramme",
              3=> "Photo(s) du rayon",
              4=> "Mise en rayon des commandes",
              6=> "Verification des PMC",
              7=> "Vérification DN",
              8=> "Vérification promos",
              9=> "Photos vue de face",
              10 => "Photos de fin de visite DPH",
              //11 => "Questionnaire de fin"
            ]; 
            foreach( $visite['steps'] as $step ) {
              if( !isset($stTitle[$step['step_nb']]) ) continue;
              if( $step['step_nb'] == 4 && $deballage['state'] == 0 ) continue;
              $st[] = '
                <div class="card clat">
                  <div class="card-body">
                    <strong>'.$stTitle[$step['step_nb']].'</strong><br/>
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
      </div>

      <?php if( securite::can(14) ) { ?>
      <div role="tabpanel" class="tab-pane" id="photos_visiste">
        <div class="row">
          <?php
          $t = [
            "photos-" => "Photos d'arrivée",
            "photoPlano" => "Photos vue de face",
            "photoRayon" => "Photos de(s) rayon(s)",
            "photoFin" => " Photos de fin de visite DPH"
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
                $tmpContent[] = '<img src="'.$link.'" data-num="'.$num.'" style="max-height: 150px;" class="img-thumbnail viewer"/>';
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
      <?php } ?>


      <div role="tabpanel" class="tab-pane" id="pemdiv">
        <?php include(PAGES."visite/pem.php");?>
      </div>


    </div>
  </div>
</div>
