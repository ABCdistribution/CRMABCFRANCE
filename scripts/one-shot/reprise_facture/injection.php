<?php

include('../../init_scripts.php');
global $db;


$dirQ = "./reprise/queries/";
$files = array_diff(scandir($dirQ),[".",".."]);

$start = microtime(true);

foreach( $files as $file ) {
    $db->execute("PURGE BINARY LOGS BEFORE '".date("Y-m-d H:i:s")."';");
    $query = file_get_contents($dirQ.$file);
    $db->execute($query);
    unlink($dirQ.$file);
}

$end = microtime(true);

die("Injection terminée en ".($end-$start)." secondes");