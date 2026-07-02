<?php
include(__DIR__ . '/../init_scripts.php');
global $db;

$db->execute("SHOW COLUMNS FROM ref_client_infos LIKE 'id_user_dr'");
if ($db->num()) {
    echo "Colonne id_user_dr déjà présente.\n";
    exit(0);
}

$db->execute("
    ALTER TABLE ref_client_infos
    ADD COLUMN id_user_dr INT UNSIGNED NULL DEFAULT NULL AFTER cni
");
echo "Colonne id_user_dr ajoutée sur ref_client_infos.\n";
