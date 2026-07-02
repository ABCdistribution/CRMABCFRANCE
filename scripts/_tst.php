<?php
include('init_scripts.php');
global $db;



// CA du mois en cours
commande::getDetailsCommandePDF("123456");

die('Fini !');
?>
