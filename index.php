<?php
require_once(ROOT.'gescom/conf.php');

// ERRORS
if( ENV == "DEV" ) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}


global $db;
$db = new db();
new rooter();


?>
