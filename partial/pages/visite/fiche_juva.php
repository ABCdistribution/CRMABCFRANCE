<?php
$c = client::getByCode($visite['id_client']);
$u = user::getNameFromId($visite['id_user']);
$q = visite::getQuestionnaireJuva($visite['id']);
$buts = visite::getButVisiteJuva();
$alertes = visite::getButAlerteRaison();
$pem = visite::getPemJuva($visite);
$deballage = visite::getDeballageJuva($visite);

$stTitle = [
  0=> l("page-visite-step-debut-dph")."",
  1=> l("page-visite-step-arrivee-site")."",
  2=> l("page-visite-step-photo-plano")."",
  3=> l("page-visite-step-photo-rayon")."",
  4=> l("page-visite-step-mise-en-rayon")."",
  6=> l("page-visite-step-verif-pmc")."",
  7=> l("page-visite-step-verif-dn")."",
  8=> l("page-visite-step-verif-promos")."",
  9=> l("page-visite-step-photo-face")."",
  10 => l("page-visite-step-photo-fin")."",
  //11 => "Questionnaire de fin"
]; 
?>

<div class="card card-primary card-outline">
<div class="d-flex justify-content-end">
  <img src="<?php echo URL; ?>img/juvamine.png" style="width: 250px; height: auto;" alt="Logo Juvamine" />
</div>
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-walking"></i> <?php echo l('page-visite-titre');?> #<?php echo $visite['id'];?>
      <?php echo l('page-visite-realise-par');?> <?php echo $u;?>
      <a class="btn btn-default btn-sm" href="<?Php echo URL;?>Visites">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour');?>
      </a>
    </h5>
  </div> 
  <div class="card-body">
    

    <!-- Onglets -->
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation">
        <a class="active" href="#fiche_cr" aria-controls="fiche_cr" role="tab" data-toggle="tab">
        <?php echo l('page-visite-cr-titre');?> juva
        </a>
      </li>
      <li role="presentation">
        <a href="#fiche_dph" aria-controls="fiche_cr" role="tab" data-toggle="tab">
        <?php echo l('page-visite-juva-titre');?>
        </a>
      </li>
      <!-- <li role="presentation">
        <a href="#fiche_pem" aria-controls="fiche_cr" role="tab" data-toggle="tab">
          
          <?php echo l('page-visite-pem-titre');?>

    
          

        </a>
      </li> -->
    </ul>

    <!-- Sections -->
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="fiche_cr">
        <?php include(PAGES."visite/fiche_cr.php");?>
      </div>
      <div role="tabpanel" class="tab-pane" id="fiche_dph">
        <?php include(PAGES."visite/fiche_dphjuva.php");?>
      </div>
      <div role="tabpanel" class="tab-pane" id="fiche_pem">
        <?php include(PAGES."visite/fiche_pem.php");?>
      </div>
    </div>
  </div>
</div>
