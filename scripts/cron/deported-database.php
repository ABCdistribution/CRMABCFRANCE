<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/deported_debug.log');
error_reporting(E_ALL);

error_log("===== Script lancé à " . date('Y-m-d H:i:s') . " =====");

include('/var/www/gescom/scripts/init_scripts.php');
global $db;

error_log("Appel deportedFiles::database()");
$database = deportedFiles::database();

error_log("Encodage JSON");
$database = json_encode($database);

error_log("Conversion charset");
$database = iconv('UTF-8', 'ASCII//IGNORE',  $database);

$filename = "database_" . date("Y-m-d") . "_" . time() . ".json";
$json = DISTANT . $filename;

error_log("Création du fichier JSON : $json");
@touch($json);
if (!file_exists($json)) {
    error_log("❌ Impossible de créer le fichier JSON", 3, "/tmp/deported_debug.log");
    die("Impossible de créer le json");
}

error_log("Écriture dans le fichier JSON");
file_put_contents($json, $database);

error_log("Chmod sur le fichier");
chmod($json, 0777);

error_log("Insertion dans la table dd_history");
$db->execute("
  INSERT INTO dd_history
  (name, size)
  VALUES
  ('" . $db->escape($filename) . "', " . filesize($json) . ")
");
error_log("✅ Insertion OK");

$old = date("Y-m-d", strtotime("-5 hours"));
error_log("Suppression des fichiers plus vieux que $old");
$db->execute("SELECT * FROM dd_history WHERE date_creation < '$old' ");

if ($db->num()) {
    $files = $db->getArray();
    foreach ($files as $file) {
        $p = DISTANT . $file['name'];
        error_log("Suppression de $p");
        @unlink($p);
        if (!file_exists($p)) {
            error_log("Fichier supprimé, suppression en base");
            $db->execute("DELETE FROM dd_history WHERE id = " . $file['id']);
        }
    }
}

error_log("✅ La base de données portable a été mise à jour : $filename");
$msg = 'La base de données portable a été mise à jour : ' . $filename;

core::end();
