<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

# SalesManagement

// CA du mois en cours
stats::recalcCaMensuel();
//stats::recalcCaMensuel(2023,12);

die('Terminé'); 