<?php
include('/var/www/gescom/scripts/init_scripts.php');
global $db;

$localDir = DIR_JUVAACRM;
$fichiersAttendus = ['Commande.csv', 'CommandeLigne.csv', 'CommandeStatutERP.csv'];

foreach ($fichiersAttendus as $fichier) {
    $cheminComplet = $localDir . $fichier;
    
    if (file_exists($cheminComplet)) {
        error_log("Test import de : $cheminComplet");
        error_log("Fichier détecté : $fichier");

        // Traitement du fichier CSV
        if (($handle = fopen($cheminComplet, "r")) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $row++;

                if ($row == 1) continue; // ignorer l'en-tête
                if ($fichier === 'CommandeStatutERP.csv') {
                    // Lecture des colonnes : acronyme, libelle, visible, ordre
                    $acronyme = trim($data[0]);
                    $libelle  = trim($data[1]);
                    $visible  = isset($data[2]) ? (strtolower(trim($data[2])) == 'true' || trim($data[2]) == '1' ? 1 : 0) : 0;
                    $ordre    = isset($data[3]) ? intval($data[3]) : null;

                 
                // Vérifie si l'acronyme existe déjà
                $db->execute("SELECT acronyme FROM juva_statuterp WHERE acronyme = '$acronyme'");
                if ($db->num()) {
                    // Update si déjà présent
                    $sql = "UPDATE juva_statuterp 
                            SET libelle = '$libelle', visible = '$visible', ordre = '$ordre' 
                            WHERE acronyme = '$acronyme'";
                    error_log("⟳ Update : $acronyme");
                } else {
                    // Insert si nouveau
                    $sql = "INSERT INTO juva_statuterp (acronyme, libelle, visible, ordre)
                            VALUES ('$acronyme', '$libelle', '$visible', '$ordre')";
                    error_log("➕ Insert : $acronyme");
                }

                 $db->execute($sql);
                }
                // traidement autre fichiers

                if($fichier === 'Commande.csv') {
                    // Lecture des colonnes :
                   // Lecture des colonnes du CSV
                    $code        = trim($data[0]);
                    $client      = trim($data[1]);
                    $statutERP   = trim($data[2]);
                    $origine     = trim($data[3]);
                    $commentaire = trim($data[4]);
                    $champExt1   = trim($data[5]);
                    $champExt2   = trim($data[6]);
                    $champExt3   = trim($data[7]);


                    // Échappement pour sécurité SQL
                    $code        = $db->escape($code);
                    $client      = $db->escape($client);
                    $statutERP   = $db->escape($statutERP);
                    $origine     = $db->escape($origine);
                    $commentaire = $db->escape($commentaire);
                    $champExt1   = $db->escape($champExt1);
                    $champExt2   = $db->escape($champExt2);
                    $champExt3   = $db->escape($champExt3);

                    // Vérifie si la commande existe déjà
                    $db->execute("SELECT code FROM juva_commande WHERE code = '$code'");
                    if ($db->num()) {
                        // Update
                        $sql = "
                            UPDATE juva_commande SET
                                client = '$client',
                                StatutERP = '$statutERP',
                                origine = '$origine',
                                commentaire = '$commentaire',
                                ChampExt1 = '$champExt1',
                                ChampExt2 = '$champExt2',
                                ChampExt3 = '$champExt3'
                            WHERE code = '$code'
                        ";
                        error_log("⟳ Update : $code");
                    } else {
                        // Insert
                        $sql = "
                        INSERT INTO juva_commande
                            (code, client, statuterp, origine, commentaire, ChampExt1, ChampExt2, ChampExt3)
                        VALUES
                            ('$code','$client', '$statutERP', '$origine', '$commentaire', '$champExt1', '$champExt2', '$champExt3')
                    ";
                    error_log("➕ Insert : $sql");
                    }

                    $db->execute($sql);
                }
                if ($fichier === 'commandeLigne.csv') {
                    // Lecture des colonnes
                    $commande     = trim($data[0]);
                    $numeroLigne  = trim($data[1]);
                    $produit      = trim($data[2]);
                    $quantite     = trim($data[3]);
                
                    // Échappement
                    $commande     = $db->escape($commande);
                    $numeroLigne  = $db->escape($numeroLigne);
                    $produit      = $db->escape($produit);
                    $quantite     = intval($quantite);
                
                    // Vérifie si la ligne existe déjà (on suppose clé unique = commande + numero_ligne)
                    $db->execute("SELECT id FROM juva_commandeligne WHERE commande = '$commande' AND numero_ligne = '$numeroLigne'");
                    if ($db->num()) {
                        // Update
                        $sql = "
                            UPDATE juva_commandeligne SET
                                produit = '$produit',
                                quantite = $quantite
                            WHERE commande = '$commande' AND numero_ligne = '$numeroLigne'
                        ";
                       // error_log("⟳ Update ligne : $commande / $numeroLigne");
                    } else {
                        // Insert
                        $sql = "
                            INSERT INTO juva_commandeligne
                                (commande, numero_ligne, produit, quantite)
                            VALUES
                                ('$commande', '$numeroLigne', '$produit', $quantite)
                        ";
                        error_log("➕ Insert ligne : $commande / $numeroLigne");
                    }
                
                    $db->execute($sql);
                }
                
                // debug :
                 error_log("Ligne $row : " . implode(" | ", $data));
               
               
                error_log("Ligne $row : " );
            }
            fclose($handle);
            error_log("Import terminé pour $fichier");

            // Archiver ou supprimer après traitement
            unlink($cheminComplet);
           
        } else {
            error_log("Impossible d'ouvrir $fichier pour lecture.");
        }
    }
    else {
        error_log("Fichier introuvable : $fichier");
    }
}
