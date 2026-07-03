<?php

class reports {

  public static function getReps() {
    global $db;
    $db->execute("SELECT DISTINCT id_rep,nom_rep FROM facture ORDER BY nom_rep");
    $rep = [];
    while( $r = $db->assoc() ) $rep[$r['id_rep']] = $r['nom_rep'];
    return $rep;
  }

  public static function getTotalFactures() {
    global $db;
    $db->execute("SELECT numero from facture group by numero");
    return $db->num();
  }

  public static function getTotalClient() {
    global $db;
    $db->execute("SELECT count(*) as nb FROM facture group by id_client_livre");
    return $db->assoc()['nb'];
  }

  public static function getTotalProduit() {
    global $db;
    $db->execute("SELECT count(*) as nb FROM facture group by id_article");
    return $db->assoc()['nb'];
  }

  public static function getTotalCA() {
    global $db;
    $t = 0;
    $db->execute("SELECT ca_net FROM facture");
    while( $r = $db->assoc() ) $t += $r['ca_net'];
    return $t;
  }

  public static function getTotalLivres() {
    global $db;
    $t = 0;
    $db->execute("SELECT SUM(qte_livree) as nb FROM facture");
    return $db->assoc()['nb'];
  }

  public static function getTotalCmd() {
    global $db;
    $t = 0;
    $db->execute("SELECT SUM(qte_cmd) as nb FROM facture");
    return $db->assoc()['nb'];
  }

  public static function getTopArticles( $top = 5 ) {
    global $db;
    $db->execute("SELECT count(*) as total, id_article,article FROM facture group by id_article order by total desc limit $top");
    $rez = [];
    while( $r = $db->assoc() ) $rez[] = $r;
    return $rez;
  }

  public static function getTopClients() {
    global $db;
      $db->execute("SELECT sum(a.ca_net) as total, a.id_client_livre,a.client_livre as client,a.dir_region,SUM(a.qte_livree) as qte
        from facture a
        group by a.id_client_livre order by total desc limit 4
    ");
    $rez = [];
    while( $r = $db->assoc() ) $rez[] = $r;
    return $rez;
  }

  public static function getRegion( $str ) {
    $tmp = explode(" ",$str);
    foreach( $tmp as $k=>$e ) {
      if( $e == "Région" ) return $tmp[$k+1];
      if( $e == "Commercial" ) return $tmp[$k+1];
    }
    $tmp = explode(" : ",$str);
    $tmp = explode(" ",$tmp[1]);
    return array_shift($tmp);
  }

  public static function getCaByDate() {
    global $db;
    $db->execute("SELECT sum(ca_net) as total,date_facture FROM `facture` group by date_facture order by date_facture");
    $rez = [];
    while( $r = $db->assoc() ) $rez[] = $r;
    return $rez;
  }

  public static function getTotalLivresByDate() {
    global $db;
    $db->execute("SELECT sum(qte_livree) as total,date_facture FROM `facture` group by date_facture order by date_facture");
    $rez = [];
    while( $r = $db->assoc() ) $rez[] = $r;
    return $rez;
  }


  public static function getRegionFacturesInfo( $half ) {
    global $db;
    $db->execute("SELECT DISTINCT dir_region FROM facture");
    $reg = [];
    while( $r = $db->assoc() ) $reg[self::getRegion($r['dir_region'])] = [
      "region" => $r['dir_region']
    ];
    $rez = [];
    foreach( $reg as $name=>$e ) {
      $db->execute("SELECT distinct numero FROM facture WHERE dir_region = '".$db->escape($e['region'])."' AND date_facture ".($half?'>':'<')." '2021-01-16'");
      if( $db->num() > 10 ) {
        $rez[$name] = $db->num();
      }
    }

    return $rez;
  }


  public static function getRegionCAInfo( $half ) {
    global $db;
    $db->execute("SELECT DISTINCT dir_region FROM facture");
    $reg = [];
    while( $r = $db->assoc() ) $reg[self::getRegion($r['dir_region'])] = [
      "region" => $r['dir_region']
    ];
    $rez = [];
    foreach( $reg as $name=>$e ) {
      $db->execute("SELECT SUM(ca_net) as total FROM facture WHERE dir_region = '".$db->escape($e['region'])."' AND date_facture ".($half?'>':'<')." '2021-01-16'");
      $total = $db->assoc()['total'];
      if( $total > 2000 )
        $rez[$name] = number_format($total,2,".","");
    }

    return $rez;
  }

}





 ?>
