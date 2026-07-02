<?php
include('/var/www/gescom/scripts/init_scripts.php');

global $db;

# On liste tous les promoteurs
$db->execute("SELECT id_repr FROM `user` WHERE id_profile = 1 AND actif = 1 AND deleted = 0 AND id_repr > 0");
$promoteurs = [];
while( $r = $db->assoc() ) $promoteurs[] = $r['id_repr'];



$date = date("Y-m-d",strtotime("-1 day"));
$until = "2023-01-01";
$delta = 1;
while( $date != $until ) {
    foreach( $promoteurs as $id_promoteur ) {
        stats::saveTempsPromoteur( $id_promoteur, $date, true );
    }
    $date = date("Y-m-d",strtotime("-$delta days"));
    $delta++;
}


die('Terminé');