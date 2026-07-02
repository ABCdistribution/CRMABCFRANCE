<?php
$magasin = client::get($id);
if( !$magasin ) return core::error404();
?>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-store"></i> <?php echo $magasin['enseigne'];?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Magasins">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div>
  <div class="card-body">

  <?php
  $tabs = [
    "infos" => l('page-client-onglet-informations'),
    "contacts" => l('page-client-onglet-contacts'),
    "dn" => l('page-client-onglet-dn'),
    "other" => l('page-client-onglet-activite'),
    "taches" => l('page-client-onglet-taches')
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
    include(PAGES.'magasin/fiche_'.$k.'.php');
    echo '</div>';
  }
  echo '</div>';


  ?>


        
  </div>
</div>
