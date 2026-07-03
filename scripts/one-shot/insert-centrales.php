<?php
die('Désactivé');
include('../init_scripts.php');
global $db;

$file = core::readFile(SCRIPTS_RESSOURCES.'centrales.csv', false);


$headers = explode(";",trim(array_shift($file)));


$f = [];
foreach( $file as $line ) {
  $tmp = explode(";",$line);
  $elem = [];
  foreach( $headers as $k=>$e ) {
    $elem[$e] = trim($tmp[$k]);
  }
  $f[] = $elem;
}

$centrales = [];
$client_sous_centrale = [];

foreach( $f as $k=>$e ) {
  if( !isset($centrales[$e['id_centrale']])) {
    $centrales[$e['id_centrale']] = [
      "name" => $db->escape($e['centrale']),
      "id" => $db->escape($e['id_centrale']),
      "id_parent" => 0,
      "niveau" => 0
    ];
    $query = "
      INSERT INTO ref_centrale (code,libelle,id_parent,niveau) VALUES (
        '".$db->escape($e['id_centrale'])."',
        '".$db->escape($e['centrale'])."',
        0,0
      );";
    $db->execute($query);
  }
  if( !isset($centrales[$e['id_sous_centrale']])) {
    $centrales[$e['id_sous_centrale']] = [
      "name" => $e['sous_centrale'],
      "id" => $e['id_sous_centrale'],
      "id_parent" => $e['id_centrale'] != "" ? $e['id_centrale'] : 999999999,
      "niveau" => 1
    ];
    $query = "
      INSERT INTO ref_centrale (code,libelle,id_parent,niveau) VALUES (
        '".$db->escape($e['id_sous_centrale'])."',
        '".$db->escape($e['sous_centrale'])."',
        '".( $e['id_centrale'] != "" ? $db->escape($e['id_centrale']) : 999999999)."'
        ,1
      );";
    $db->execute($query);
  }
  if( !isset($centrales[$e['id_sous_sous_centrale']])) {
    $centrales[$e['id_sous_sous_centrale']] = [
      "name" => $e['sous_sous_centrale'],
      "id" => $e['id_sous_sous_centrale'],
      "id_parent" => $e['id_sous_centrale'],
      "niveau" => 2
    ];
    $query = "
      INSERT INTO ref_centrale (code,libelle,id_parent,niveau) VALUES (
        '".$db->escape($e['id_sous_sous_centrale'])."',
        '".$db->escape($e['sous_sous_centrale'])."',
        '".( $e['id_sous_centrale'] != "" ? $db->escape($e['id_sous_centrale']) : 999999999)."'
        ,2
      );";
    $db->execute($query);
  }

  $client_sous_centrale[$db->escape($e['id_client'])] = $db->escape($e['id_sous_sous_centrale']);
}

foreach( $client_sous_centrale as $id_client => $code_centrale ) {
  if( $code_centrale > 0 )
    $db->execute("INSERT INTO ref_client_centrale (code_client,code_centrale) VALUES ('$id_client','$code_centrale')");
}

/*
echo '<pre>';
print_r($centrales);
*/




die('Fin');
