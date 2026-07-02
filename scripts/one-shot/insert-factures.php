<?php
die('Désactivé');
include('../init_scripts.php');
global $db;

$file = core::readFile(SCRIPTS_RESSOURCES.'factures.txt', false);


$db->execute("SELECT * FROM tmp_import_facture");
$struc = [];
$fields = [];
while( $r = $db->assoc() ) {
  if( $r['libelle'] != "" ) {
    $fields[] = $r['libelle'];
    $struc[] = $r;
  }
}
$fields = implode(",",$fields);




$factures = [];
$q = [];
foreach( $file as $k=>$line ) {
  $tmp = $tmp2 = [];
  foreach( $struc as $e ) {
    $strip = substr($line,$e['len_from']-1,$e['length']);
    $val = trim($strip);


    if( $e['decimal_value'] > 0 ) {
      //$tmp2[$e['libelle']."_original"] = $val;
      $len = strlen($val)-1;
      $dec = substr($val,$len-$e['decimal_value'],$e['decimal_value']);
      $ent = substr($val,0,$len-$e['decimal_value']);
      $val = floatval(intval($ent).".".intval($dec));
    }

    $tmp2[$e['libelle']] = $val . " [".$strip."]";
    //$tmp[] = "'".$db->escape($val)."'";
  }
  $factures[] = $tmp2;
  //$db->execute("INSERT INTO ref_article ($fields) VALUES (".implode(",",$tmp).");");
  if( $k > 50 ) break;
}

echo '<pre>';
print_r($factures);

die('FIN');
?>
