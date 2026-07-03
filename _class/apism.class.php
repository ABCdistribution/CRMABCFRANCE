<?php

class apism {
  private $params;
  private $user;

  public function __construct( $user, $params ) {
    global $db,$params;
    $this->params = $params;
    $this->user = $user;
    //if( $this->user['id_profile'] == 1 ) api::aError("Vous n'êtes pas autorisé à consulter le SalesManagement");
    $this->router( $params['methode'] ?? "" );    
  }
  public static function getDateFrom() {
    return date("Y-m-d", strtotime("-1 year"));
  }
  public function router( $route ) {
    switch( $route ) {
      case 'getPortefeuilleClients' : client::getPortefeuilleClients( $this->user );
      case 'getClient' : client::getClient( $this->params['id'] ?? "" );
      case 'getCommandes' : commande::getCommandes( $this->params['id'] ?? "" );      
      case 'getPhotosVisite' : visite::getPhotosVisiteSM( $this->params['id'] ?? "", $this->params['produits'] ?? false );  
      case 'getTopVentesClient' : client::getTopVentesClient( $this->params['id'] ?? "" );
      case 'getCommandesSM' : commande::getCommandesSM( $this->params );
      case 'getMyPromoteurs' : commande::getMyPromoteurs( false, $this->params);
      case 'getPlanningSM' : planning::getPlanningSM( $this->params );
      case 'getClientCA' : stats::getClientCASM( $this->params['id'] ?? "", $this->params['isCumulMonth'] );
      case 'getPromoteurCa' : stats::getPromoteurCa();
      case 'getIndicatorsPromoteur' : stats::getIndicatorsPromoteur();
      case 'getTauxNational' : stats::getTauxNational();
      case 'getTauxAvancementRegion' : stats::getTauxAvancementRegion();
      case 'getClassementPromoteurs' : stats::getClassementPromoteurs( $this->params['national'] == 1 ? true : false );
      case 'getDetailsCommandePDF' : commande::getDetailsCommandePDF( $this->params['numero'] );
      default : api::aError("Méthode [APISM] inconnue");
    }
  }
}