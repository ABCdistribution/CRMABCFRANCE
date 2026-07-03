<?php
$c = prospect::get($prospection['id_prospect']);
$u = user::getNameFromId($prospection['id_user']);
?>




<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-walking"></i> <?php echo l('page-prospection-titre');?> #<?php echo $prospection['id'];?>
      <?php echo l('page-prospection-realise-par');?> <?php echo $u;?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Prospections">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">

    <div class="row">
        <div class="col">

            <h5><span><?php echo l('page-prospection-infos');?></span></h5>

            <table class="table">
                <tr>
                    <th><?php echo l('page-prospection-infos-prospect');?></th>
                    <td><?php echo $c['nom'];?></td>
                </tr>
                <tr>
                    <th><?php echo l('page-prospection-infos-type');?></th>
                    <td><?php echo $c['ptype'];?></td>
                </tr> 
                <tr>
                    <th><?php echo l('page-prospection-infos-adresse');?></th>
                    <td><?php echo implode(', ',[ $c['adresse'],$c['cp'],$c['ville'] ]);?></td>
                </tr>                               
                <tr>
                    <th><?php echo l('page-prospection-infos-commercial');?></th>
                    <td><?php echo $u;?></td>
                </tr>                                
                <tr>
                    <th><?php echo l('page-prospection-infos-creation');?></th>
                    <td><?php echo core::dateOutput($prospection['date_creation'],true);?></td>
                </tr>
            </table>


            <h5><span><?php echo l('page-visite-dph-dn-titre');?></span></h5>
            <table class="table table-condensed">
                <thead>
                <th><?php echo l('dn-nom');?></th>
                <th><?php echo l('dn-gamme');?></th>
                <th><?php echo l('dn-metrage');?></th>
                <th><?php echo l('dn-broches');?></th>
                <th><?php echo l('dn-plv');?></th>
                <th><?php echo l('dn-pem');?></th>
                </thead>
                <tbody>
                <?php
                $dn = $prospection['dn'];
                $tmp = [];
                foreach( $dn as $k=>$e ) {
                    $tmp[] = '
                    <tr>
                    <td>'.$e['nom'].'</td>
                    <td>'.$e['gamme'].'</td>
                    <td>'.$e['metrage'].'</td>
                    <td>'.$e['broches'].'</td>
                    <td>'.$e['plv'].'</td>
                    <td>'.$e['pem'].'</td>
                    </tr>
                    ';
                }
                echo implode("",$tmp);
                ?>
                </tbody>
            </table>    


        </div>
        <div class="col">

            <h5><span><?php echo l('page-prospection-infos-progression');?></span></h5>
            <?php
            $st = [];
            foreach( $prospection['steps'] as $step ) {
              $st[] = '
                <div class="card clat">
                  <div class="card-body">
                    <div class="numstep">'.$step['step'].'°</div>
                    <strong>'.prospection::getStep($step['name']).'</strong><br/>
                    <i class="far fa-clock"></i> '.core::dateOutput($step['value'],true).'
                  </div>
                </div>
              ';
            }
            echo implode($st);
            ?>    

            <h5><span><?php echo l('page-prospection-infos-photos');?></span></h5>
            <div class="tc" id="photos_visiste">
                <?php
                $num = 1;
                foreach( $prospection['photos'] as $i ) {
                    $link = URL_APP_ROOT.'datas/prospection/'.$i['path'];
                    echo '<img src="'.$link.'" data-num="'.$num.'" style="max-height: 150px;" class="img-thumbnail viewer"/>';
                    $num++;
                }
                ?>
            </div>
        </div>        
    </div>


  </div>
</div>
