<?php
include('../init_scripts.php');
global $db;


$first = "3700206712345";

$db->execute("SELECT id FROM ref_article");
$ids = [];
while( $r = $db->assoc() ) $ids[] = $r['id'];

foreach( $ids as $id ) {
  $db->execute("UPDATE ref_article SET gencode = '".($first++)."' WHERE id = $id");
}

die('End :)');
