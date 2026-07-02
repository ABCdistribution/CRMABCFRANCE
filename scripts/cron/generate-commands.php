<?php

// Script de génération des fichiers de commandes
include('/var/www/gescom/scripts/init_scripts.php');
commande::generateCommandFiles();
die('-- FIN --');
