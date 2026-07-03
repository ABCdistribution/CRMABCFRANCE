<?php
set_time_limit(0);
ignore_user_abort(true);

$ds = DIRECTORY_SEPARATOR;

$file = "./reprise/HIS20240301094943998889.TMP"; 
$dir = "./reprise/temp/";

$start = microtime(true);

$handle = fopen($file, "r");
if (!$handle) die("Lecture impossible");
$maxCapTest = 1;
$fileCounter = 1;
$fileCounterLines = 0;

$fp = fopen( "/home/nicolas/reprise/temp/part$fileCounter.txt", "w+");

$cp = 1;

while (!feof($handle)) {
    $cp++;

    $data = utf8_decode(fgets($handle, 4096));

    fputs($fp, $data );
    $fileCounterLines++;

    if( $fileCounterLines >= 5000 ) {
        fclose($fp);
        $fileCounter++;
        $fileCounterLines = 0;
        $fp = fopen( "/home/nicolas/reprise/temp/part$fileCounter.txt", "w+");
    }

    //if( $cp > 100000 ) break;
}
@fclose($fp); 
fclose($handle);


$end = microtime(true);
die("Script terminé en ".($end-$start)." secondes, $fileCounter fichiers générés.");