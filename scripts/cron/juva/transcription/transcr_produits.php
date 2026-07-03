<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$csvFile = 'transcr_produits.csv';

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
        $codeGNX = trim($data[0] ?? '');
        $codeRacine = trim($data[1] ?? '');

        if (!$codeGNX || !$codeRacine) {
            echo "⚠️ Ligne $row : Données incomplètes\n";
            continue;
        }
        $codeGNXEsc = $db->escape($codeGNX);
        $codeRacineEsc = $db->escape($codeRacine);

        // Vérification si codeGNX existe en base (dans idoriginal)
        $db->execute("SELECT 1 FROM juva_produit WHERE idoriginal = '$codeGNXEsc'");
        if ($db->num()) {
            $sql = "UPDATE juva_produit SET idoriginal = '$codeRacineEsc' WHERE idoriginal = '$codeGNXEsc'";
            $db->execute($sql);
            echo "✅ Ligne $row : '$codeGNX' mis à jour avec '$codeRacine'\n";
            $updatedLines++;
        } else {
            echo "⚠️ Ligne $row : Code GNX '$codeGNX' introuvable en base\n";
            $notFoundLines++;
        }
    }
    fclose($handle);

    echo "\n🟢 Mise à jour terminée.\n";
    echo "Total lignes traitées (hors header) : $totalLines\n";
    echo "Produits mis à jour : $updatedLines\n";
    echo "Produits non trouvés : $notFoundLines\n";

} else {
    echo "❌ Impossible d'ouvrir le fichier CSV : $csvFile\n";
}
?>
