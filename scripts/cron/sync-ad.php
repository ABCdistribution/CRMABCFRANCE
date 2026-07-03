<?php
// Script de synchronisation des comptes AD <=> CRM
include('/var/www/gescom/scripts/init_scripts.php');
$datas = ldap::ldapSync();
global $updateQueries;
echo 'Synchronisation AD <=> CRM terminée : '.$updateQueries.' query update';
exit;