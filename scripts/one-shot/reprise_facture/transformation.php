<?php

include('../../init_scripts.php');
global $db;

function dd($datas) {
    echo '<pre>';
    var_dump($datas);
    exit;
    
}

$mapping = [];
$db->execute("SELECT mapping,len_from,`length`,decimal_value FROM struc_ref_facture WHERE mapping IS NOT NULL");
while( $r = $db->assoc() ) $mapping[$r['mapping']] = [$r['len_from'],$r['length'],$r['decimal_value']];

$headers = implode(',',array_keys($mapping));

$dir = "./reprise/temp/";
$dirQ = "./reprise/queries/";
$files = array_diff(scandir($dir),[".",".."]);

$count = 1;
foreach($files as $file) {
    
    $lines = file($dir.$file);
    $queries = [];
    foreach( $lines as $line ) {
        $tmp = [];
        foreach($mapping as $k=>$e) {
            $v = trim(mb_substr($line,$e[0]-1, $e[1]));
            if( $e[2] > 0 ) {
                $v = floatval($v / pow(10,$e[2]));
            }
            $tmp[$k] = "'".$db->escape($v)."'";
        }
        $queries[] = "(".implode(",",$tmp).")";
    }

    $query = "INSERT INTO ref_facture ($headers) VALUES ".implode(",",$queries).";";
    file_put_contents($dirQ."query_$count.sql", $query) ;
    $count++;
}

die('Génération des requetes terminée.');
