<?php
die('Désactivé');
include('../init_scripts.php');
global $db;
$breaker = 0;


echo '<pre>';

$file = core::readFile(SCRIPTS_RESSOURCES.'factures.csv');
$headers = explode(";",array_shift($file));
$n = getFacturesNum();

foreach($headers as $k=>$e ) {
  $e = trim($e);
  if( $e == "direction_region_actuel" ) $headers[$k] = "dir_region";
  else if( $e == "chef_de_secteur_actuel" ) $headers[$k] = "chef_secteur";
  else if( $e == "Code_et_nom_representant2" ) $headers[$k] = "rep";
  else if( $e == "code_et_designation_article" ) $headers[$k] = "article";
  else if( $e == "quantite gratuite" ) $headers[$k] = "qte_gratuite";
  else if( $e == "quantite livree" ) $headers[$k] = "qte_livree";
  else if( $e == "Qte facture" ) $headers[$k] = "qte_facture";
  else if( $e == "quantite cde" ) $headers[$k] = "qte_cmd";
  else if( $e == "CA Net" ) $headers[$k] = "ca_net";
  else if( $k == 0 ) $headers[$k] = "numero";

  #echo "/$e/ => /".$headers[$k]."/<br/>";
}

$cp_insert = 0;
$ids = [];
foreach( $file as $l ) {
  $tmp = explode(';',$l);
  foreach($tmp as $k=>$e) {
    $tmp[$headers[$k]] = $e;
    unset($tmp[$k]);
  }

  $tmp['id_client_livre'] = getLeft($tmp['Code_et_nom_client_livre']);
  $tmp['client_livre'] = getRight($tmp['Code_et_nom_client_livre']);
  $tmp['id_client_facture'] = getLeft($tmp['code_et_nom_client_facture']);
  $tmp['id_rep'] = getLeft($tmp['rep']);
  $tmp['nom_rep'] = getRight($tmp['rep']);
  $tmp['date_facture'] = core::dateInput($tmp['date_facture']);
  $tmp['id_article'] = getLeft($tmp['article']);
  $tmp['article'] = getRight($tmp['article']);
  $tmp['ca_net'] = core::toFloat($tmp['ca_net']);


  # check doublons
  $str = $tmp['numero'].'_'.core::dateOutput($tmp['date_facture']).'_'.$tmp['id_article'];
  if( in_array($str,$n) ) {
    #echo "Non inséré car doublon : $str ($l)<br/>";
    continue;
  }



  $ids[] = $db->insertArray("facture",$tmp);

  $cp_insert++;
  if( $breaker && $cp_insert >= $breaker ) break;
}

echo count($ids)." factures injectées :<br/>".implode(", ",$ids);
die('<br/>FIN');





function getLeft($str) {
  $t = explode(' : ',trim($str));
  return array_shift($t);
}
function getRight($str) {
  $t = explode(' : ',trim($str));
  return array_pop($t);
}
function getFacturesNum() {
  global $db;
  $db->execute("SELECT distinct numero,date_facture,id_article from facture");
  $n = [];
  while( $r = $db->assoc() ) $n[] = $r['numero']."_".core::dateOutput($r['date_facture']).'_'.$r['id_article'];
  return $n;
}
