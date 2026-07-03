<?php
$c = client::getByCode($visite['id_client']);
$u = user::getNameFromId($visite['id_user']);
$steps = prospection::getVisiteCsSteps($visite['id']);
?>




<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-walking"></i> 
      <?php echo l('page-visite-cs-titre');?> #<?php echo $visite['id'];?>
      <?php echo l('page-visite-cs-realisee-par');?> <?php echo $u;?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>VisitesCS">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">
    <div class="row">

    <div class="col-md-6">
            
            <h4 style="color:#486a59"><?php echo l('page-visite-cs-details');?></h4>
            <table class="table">
                <tbody>
                    <tr>
                        <th><?php echo l('page-visite-cs-details-id');?> :</th>
                        <td><?php echo $visite['id_client'];?></td>
                    </tr>
                    <tr>
                        <th><?php echo l('page-visite-cs-details-client');?> :</th>
                        <td><?php echo client::getByCode($visite['id_client'])['enseigne'];?></td>
                    </tr>
                    <tr>
                        <th><?php echo l('page-visite-cs-details-cs');?> :</th>
                        <td><?php echo user::getNameFromId($visite['id_user']);?></td>
                    </tr>
                    <tr>
                        <th><?php echo l('page-visite-cs-details-date-visite');?>:</th>
                        <td><?php echo core::dateOutput($visite['creation'],true);?></td>
                    </tr>                                                                
                    <tr>
                        <th><?php echo l('page-visite-cs-details-prochaine-visite');?> :</th>
                        <td><?php echo core::dateOutput($visite['date_prochain_rdv'],true);?></td>
                    </tr>                                                                                
                </tbody>
            </table>

            <ul class="list-group">
            <?php
                echo '<li class="list-group-item">';
                if( $visite['rdv'] == 0 ) echo '<i class="fas fa-calendar-times"></i>';
                else echo '<i class="fas fa-calendar-check"></i>';
                if( $visite['rdv'] == 1 )
                    echo l('page-visite-cs-visite-avec-rdv').'';
                else 
                    echo l('page-visite-cs-visite-sans-rdv').'';
                echo '</li>';

                
                if( $visite['rdv'] == 1 ) {
                    $tmp = explode(",",$visite['objets'] );
                    foreach($tmp as $o) {
                        echo '<li class="list-group-item"><i class="fas fa-star"></i>';
                        echo l('page-visite-cs-visite-objet').' : <strong>';
                        if( $o == 1 ) echo l('page-visite-cs-visite-bilan');
                        if( $o == 2 ) echo l('page-visite-cs-visite-partenariat');
                        if( $o == 3 ) echo l('page-visite-cs-visite-promo');
                        echo '</strong></li>';
                    }
                }
                else {
                    echo '<li class="list-group-item">';
                    if( $visite['responsable'] == 1 ) 
                        echo '<span class="text-success"><i class="fas fa-user-check"></i> '.l('page-visite-cs-rencontre-resp').' </span>';
                    else 
                        echo '<span class="text-danger"><i class="fas fa-user-slash"></i> '.l('page-visite-cs-norencontre-resp').'</span>';
                    echo '</li>';                    
                }

                echo '<li class="list-group-item"><i class="fas fa-feather-alt"></i> '.l('page-visite-cs-cr').' : <em>';
                echo ($visite['compte_rendu'] == ""? l('page-visite-cs-cr-no'):$visite['compte_rendu']).'</em></li>';
            ?>
            </ul>
        </div>

        <div class="col-md-6">
            <div class="list-group">
                <?php
                foreach( $steps as $k=>$e ) {

                    $v = $e['value'];
                    if( $v == "1" ) $v = '<i class="fas fa-check text-success"></i>';
                    else if( $v == "0" ) $v = '<i class="fas fa-times text-danger"></i>';
                    else $v = '<span class="text-info">'.$v.'</span>';
                    if( $e['name'] == "end" ) $v = "";

                    echo '
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                        <h4 class="mb-1">'.$e['libelle'].' : '.$v.'</h4>
                        <small>'.$e['heure'].'</small>
                        </div>
                        <small>
                        '.($e['remarque'] == "" ? '<span class="text-secondary">'.l('page-visite-cs-no-remarque').'</span>' : $e['remarque']).'
                        </small>
                    </a>         
                    ';        
                }
                ?>
            </div>
        </div>
        
    </div>

  </div>
</div>
