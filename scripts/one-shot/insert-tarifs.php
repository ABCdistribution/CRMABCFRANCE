<?php
die('desactivé');
include('../init_scripts.php');
global $db;

$file = core::readFile(SCRIPTS_RESSOURCES.'tarifs.txt', false);


$articles = [];
foreach( $file as $l ) {
  $first_blank = strpos($l," ");
  $id = substr($l,0,$first_blank);
  $decimal = substr($l,44);
  $entiere = substr($l,36,8);
  $articles[$id] = floatval(intval($entiere).".".$decimal);
  $db->execute("INSERT INTO ref_tarif (code_article,tarif) VALUES ('".$db->escape($id)."', ".$articles[$id]." )");
}




die('ok');
