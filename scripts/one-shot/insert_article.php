<?php
die('Désactivé');
include('../init_scripts.php');
global $db;

$file = core::readFile(SCRIPTS_RESSOURCES.'art.txt', false);


$db->execute("SELECT * FROM tmp_import_article");
$struc = [];
$fields = [];
while( $r = $db->assoc() ) {
  if( $r['libelle'] != "" ) {
    $fields[] = $r['libelle'];
    $struc[] = $r;
  }
}
$fields = implode(",",$fields);

/*
echo '<pre>';
print_r($fields);
exit;
*/

$dump = [];

$articles = [];
$q = [];
$cp = 0;
foreach( $file as $k=>$line ) {
  $tmp = [];
  $dump_tmp = [];
  foreach( $struc as $e ) {
    $val = trim(substr($line,$e['len_from']-1,$e['length']));
    $val = iconv("UTF-8","UTF-8//IGNORE",$val);
    $tmp[] = "'".$db->escape($val)."'";
    $dump_tmp[$e['libelle']] = $db->escape($val);
  }
  $dump[] = $dump_tmp;
  $db->execute("INSERT INTO ref_article ($fields) VALUES (".implode(",",$tmp).");");
  $cp++;
  //if( $k > 50 ) break;
}


//echo '<pre>';
//print_r($dump);
echo $cp." Articles insérés<br/>";
die('FIN');
?>
