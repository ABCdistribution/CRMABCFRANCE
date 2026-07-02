<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$from = date('Y-m-d',strtotime("-1 week") );

$q = "SELECT id FROM commande_apk WHERE no_facture IS NULL AND date_creation > '$from'";
$db->execute($q);
$ids = [];
while( $r = $db->assoc() ) $ids[] = $r['id'];

$queries = [];
foreach( $ids as $id_cmd ) {
    $q = "SELECT   no_commande , no_facture, montant_facture,numero_bl FROM ref_facture WHERE no_crm = $id_cmd LIMIT 1";
    $db->execute($q);
    if( !$db->num() ) continue;
    $datas = $db->assoc();
    $queries[] = "
        UPDATE commande_apk 
        SET 
            no_facture = '".e($datas['no_facture'])."',
            no_commande = '".e($datas['no_commande'])."',
            total_reel = '".e($datas['montant_facture'])."',
            bl = '".e($datas['numero_bl'])."'
        WHERE
            id = $id_cmd
    ";
}

echo implode('<br/>', $queries);