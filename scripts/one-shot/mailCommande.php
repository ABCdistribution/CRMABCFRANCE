<?php
// Script de génération des fichiers de commandes
include('/var/www/gescom/scripts/init_scripts.php');

if( !isset($_GET['id_c'])) die('ko');

new commandePDF( intval($_GET['id_c']) );
die('Fin');
?>