<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;


$date = date("Y-m-d",strtotime("-1 day"));
$until = "2024-02-28";
$delta = 1;
while( $date != $until ) {
    stats::saveCaPromoteur( $date, true );
    $delta++;
    $date = date("Y-m-d",strtotime("-$delta days"));
}

stats::promoteurCaCumulCalc();



die('Terminé');