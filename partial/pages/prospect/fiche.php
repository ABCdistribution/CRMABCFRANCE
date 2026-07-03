<?php
$prospect = prospect::getFull($id);
if( !$prospect ) return core::error404();
?>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-store"></i> <?php echo $prospect['nom'];?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Prospects">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">

  <?php
  $tabs = [
    "infos" => l('page-prospect-onglet-info')."",
    "contacts" => l('page-prospect-onglet-contact')."",
    "dn" => l('page-prospect-onglet-dn')."",
    "taches" => l('page-prospect-onglet-taches')."",
  ];
  
  echo '<ul class="nav nav-tabs" id="myTab" role="tablist">';
  foreach($tabs as $k=>$tab ) {
    echo '<li class="nav-item">';
    echo '<a class="nav-link '.($k=="infos"?'active':'').'" id="'.$k.'-tab" data-toggle="tab" href="#'.$k.'" 
    role="tab" aria-controls="'.$k.'" aria-selected="true">'.$tab.'</a>';
    echo '</li>';
  }
  echo '</ul>';


  echo '<div class="tab-content" id="myTabContent">';
  foreach( $tabs as $k=>$tab ) {
    echo '<div class="tab-pane fade '.($k=="infos"?'show active':'').'" id="'.$k.'" role="tabpanel" aria-labelledby="'.$k.'-tab">';
    include(PAGES.'prospect/fiche_'.$k.'.php');
    echo '</div>';
  }
  echo '</div>';


  ?>


        
  </div>
</div>
