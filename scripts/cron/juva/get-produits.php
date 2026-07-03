<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$localDir = DIR_JUVAPRODUIT;
$fichier = 'Produit.csv';
$cheminComplet = $localDir . $fichier;

if (file_exists($cheminComplet)) {
    error_log("🟢 Fichier détecté : $fichier");

    if (($handle = fopen($cheminComplet, "r")) !== false) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            $row++;
            if ($row == 1) continue; // Ignore l'en-tête

            // Lecture des colonnes
            [
                $idOriginal, $gencod, $libelle, $ordre, $dateDebut, $dateFin, $pcb, $sousPcb,
                $longueur, $largeur, $hauteur, $profondeur, $poids, $contenance,
                $pvc, $prix, $nouveaute, $incontournable, $secteur, $sousRayon
            ] = array_map('trim', $data + array_fill(0, 20, null)); // Assure 20 colonnes minimum

            // Nettoyage
            $idOriginal     = $db->escape($idOriginal);
            $gencod         = $db->escape($gencod);
            $libelle        = $db->escape($libelle);
            $secteur        = $db->escape($secteur);
            $sousRayon      = $db->escape($sousRayon);

            $nouveaute      = strtolower($nouveaute) === 'true' || $nouveaute == '1' ? 1 : 0;
            $incontournable = strtolower($incontournable) === 'true' || $incontournable == '1' ? 1 : 0;

            // Vérifie si le produit existe déjà
            $db->execute("SELECT IdOriginal FROM juva_produit WHERE IdOriginal = '$idOriginal'");
            if ($db->num()) {
                $sql = "
                    UPDATE juva_produit SET
                        Gencod = '$gencod',
                        Libelle = '$libelle',
                        Ordre = '$ordre',
                        DateDebutDisponibilite = '$dateDebut',
                        DateFinDisponibilite = '$dateFin',
                        PCB = '$pcb',
                        SousPCB = '$sousPcb',
                        Longueur = '$longueur',
                        Largeur = '$largeur',
                        Hauteur = '$hauteur',
                        Profondeur = '$profondeur',
                        Poids = '$poids',
                        Contenance = '$contenance',
                        PVC = '$pvc',
                        Prix = '$prix',
                        Nouveaute = '$nouveaute',
                        Incontournable = '$incontournable',
                        ChampExt19 = '$secteur',
                        ChampExt20 = '$sousRayon'
                    WHERE IdOriginal = '$idOriginal'
                ";
                error_log("⟳ Update : $idOriginal");
            } else {
                $sql = "
                    INSERT INTO juva_produit (
                        IdOriginal, Gencod, Libelle, Ordre,
                        DateDebutDisponibilite, DateFinDisponibilite, PCB, SousPCB,
                        Longueur, Largeur, Hauteur, Profondeur,
                        Poids, Contenance, PVC, Prix,
                        Nouveaute, Incontournable, ChampExt19, ChampExt20
                    ) VALUES (
                        '$idOriginal', '$gencod', '$libelle', '$ordre',
                        '$dateDebut', '$dateFin', '$pcb', '$sousPcb',
                        '$longueur', '$largeur', '$hauteur', '$profondeur',
                        '$poids', '$contenance', '$pvc', '$prix',
                        '$nouveaute', '$incontournable', '$secteur', '$sousRayon'
                    )
                ";
                error_log("➕ Insert : $idOriginal");
            }

            $db->execute($sql);
        }
        fclose($handle);
        error_log("✅ Import terminé pour $fichier");
        unlink($cheminComplet);
    } else {
        error_log("❌ Impossible d’ouvrir le fichier $fichier.");
    }
} else {
    error_log("⚠️ Fichier introuvable : $fichier");
}
?>
