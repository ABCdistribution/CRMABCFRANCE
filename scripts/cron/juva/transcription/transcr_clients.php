<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$csvFile = 'transcr_clients.csv';

echo "🔄 Début de traitement du fichier : $csvFile\n";

if (!file_exists($csvFile)) {
    echo "❌ Fichier CSV introuvable : $csvFile\n";
    exit;
}

if (($handle = fopen($csvFile, "r")) !== false) {
    $row = 0;
    $totalLines = 0;
    $updatedLines = 0;
    $notFoundLines = 0;

    while (($data = fgetcsv($handle, 1000, ";")) !== false) {
        $row++;
        if ($row == 1) {
            echo "🔎 En-tête ignorée\n";
            continue;
        }

        // Debug : affiche la ligne lue
        var_dump($data);

        $totalLines++;
        $codeClientSAP = trim($data[0] ?? '');
        $codeClientGenerix = trim($data[1] ?? '');

        if (!$codeClientSAP || !$codeClientGenerix) {
            echo "⚠️ Ligne $row : Données incomplètes\n";
            continue;
        }

        $codeClientSAPEsc = $db->escape($codeClientSAP);
        $codeClientGenerixEsc = $db->escape($codeClientGenerix);

        // Vérification si codeClientGenerix existe dans num_juva (colonne à chercher)
        $db->execute("SELECT 1 FROM ref_client_infos WHERE num_juva = '$codeClientGenerixEsc'");
        if ($db->num()) {
            $sql = "UPDATE ref_client_infos SET num_juva = '$codeClientSAPEsc' WHERE num_juva = '$codeClientGenerixEsc'";
            $db->execute($sql);
            echo "✅ Ligne $row : '$codeClientGenerix' mis à jour avec '$codeClientSAP'\n";
            $updatedLines++;
        } else {
            echo "⚠️ Ligne $row : Code client Generix '$codeClientGenerix' introuvable en base\n";
            $notFoundLines++;
        }
    }
    fclose($handle);

    echo "\n🟢 Mise à jour terminée.\n";
    echo "Total lignes traitées (hors header) : $totalLines\n";
    echo "Clients mis à jour : $updatedLines\n";
    echo "Clients non trouvés : $notFoundLines\n";

} else {
    echo "❌ Impossible d'ouvrir le fichier CSV : $csvFile\n";
}
?>
