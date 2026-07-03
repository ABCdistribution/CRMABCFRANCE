<?php
include('../init_scripts.php');
global $db;

$file = core::readFile(SCRIPTS_RESSOURCES.'clients_v2.csv', false);


$headers = explode(";",trim(array_shift($file)));


$f = [];
foreach( $file as $nb => $line ) {
  $tmp = explode(";",$line);
  $elem = [];
  foreach( $headers as $k=>$e ) {
    $val = $db->escape(filter(trim($tmp[$k])));
    if( $e == "id_commercial_1" || $e == "id_commercial_2" ) {
      if( intval($val) < 1 ) $val = 0;
    }
    $elem[$e] = "'".$val."'";
  }
  $f[] = $elem;
}

$qH = " INSERT INTO ref_client (".implode(",",$headers).") VALUES ";
$v = [];
foreach( $f as $e ) {
  $v[] = " (".implode(",",$e).") ";
  if( count($v) > 100) {
    $db->execute( $qH . implode(", ",$v) . ";" );
    $v = [];
  }
}
if( count($v) != 0 )
  $db->execute( $qH . implode(", ",$v) . ";" );





die('FIN');

















function filter($var) {
  if( $var == "*" ) return "";
  if( $var == "**" ) return "";
  if( $var == "***" ) return "";
  if( $var == "****" ) return "";
  if( $var == "*****" ) return "";
  if( $var == "******" ) return "";
  if( $var == "." ) return "";
  return $var;
}






/*
$file = core::readFile(SCRIPTS_RESSOURCES.'clients.csv');
$file = core::splitCols($file);


$mapping = [
  "id_centrale",
  "centrale",
  "id_sous_centrale",
  "sous_centrale",
  "id_magasin",
  "magasin",
  "id_magasin_facture",
  "magasin_facture",
  "id_commercial",
  "commercial"
];

$final = [];
foreach( $file as $el ) {
  $tmp = [];
  foreach( $el as $k=>$e ) {
    if( isset($mapping[$k]) )
      $tmp[$mapping[$k]] = $e;
  }
  $final[] = $tmp;
}


#echo core::dumpObj($final);


foreach( $final as $client ) {
  $id_centrale = ref::createCentrale($client['centrale'],$client['id_centrale']);
  $id_sous_centrale = ref::createSousCentrale($client['sous_centrale'],$client['id_sous_centrale'],$id_centrale);

  $id_client_facture = 0;
  if( $client['id_magasin'] != $client['id_magasin_facture'] ) {
    $id_client_facture = intval($client['id_magasin_facture']);
  }
  $id_commercial = ( intval($client['id_commercial']) > 0 ? $client['id_commercial'] : 0 );
  ref::createClient(  $client['magasin'], $client['id_magasin'], $id_sous_centrale, $id_client_facture, $id_commercial );

}
*/
