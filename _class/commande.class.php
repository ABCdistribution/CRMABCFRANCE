<?php


require APP_ROOT.'/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
set_time_limit(0);

class commande {


  public static function newJuva($cmd) {
    error_log("generation de la commande JUVA");
    error_log("[JUVA] Commande ".$cmd['id']." enregistrée avec ".count($cmd['commandeLigne'])." lignes.");


    global $db;

    // Vérifie si la commande existe déjà
    $db->execute("SELECT id_abc FROM juva_commande WHERE id_abc = '".$db->escape($cmd['id'])."'");
    if ($db->num()) {
        core::logApk("[JUVA] Commande déjà existante : ".$cmd['id']);
        return;
    }
    $dateRealisation = '';
    if (!empty($cmd['dateRealisation'])) {
        $dateRealisation = date('Y-m-d H:i:s', strtotime($cmd['dateRealisation']));
    }
    error_log("[JUVA] DateLivraison : " . core::dateInput($cmd['dateLivraison']));
    error_log("[JUVA] DateRealisation : " . $dateRealisation );


     // Insertion commande_juva
    //  '".$db->escape($cmd['client'])."',
     $query = "
     INSERT INTO juva_commande 
      (id_as400, client, id_abc, utilisateur, datelivraison, daterealisation, reference, statut, origine, commentaire, externe, externeMail)
      VALUES 
      (
          '".$db->escape($cmd['magasin'])."',
          '".($db->escape($cmd['client']))."',
          '".$db->escape($cmd['id'])."',
          '".$db->escape($cmd['utilisateur'])."',
          '".core::dateInput($cmd['dateLivraison'])."',
          '".$dateRealisation."',
          '".$db->escape($cmd['Reference'])."',
          ".intval($cmd['Statut']).",
          '".$db->escape($cmd['Origine'])."',
          '".$db->escape($cmd['Commentaire'])."',
          ".intval($cmd['externe']).",
          '".$db->escape($cmd['externeMail'])."'
      )
 ";
          // Récupère l'ID auto-incrémenté
          error_log("REQUETE SQL : " . $query);
         
          // Exécution
          $db->execute($query);
          $id_commande = $db->lastId();
           // Génère le code JUVA : ABC + id sur 11 caractères (padded left)
          //14 caractère avec abc
          $code_juva = 'ABC' . str_pad($id_commande, 11, '0', STR_PAD_LEFT);

          // Met à jour la colonne code_juva
          $db->execute("UPDATE juva_commande SET code = '".$db->escape($code_juva)."' WHERE id = ".intval($id_commande));

          

          if (isset($cmd['commandeLigne']) && is_array($cmd['commandeLigne'])) {
            $i = 1; // compteur de ligne
            foreach ($cmd['commandeLigne'] as $ligne) {
                $query = "
                    INSERT INTO juva_commandeligne
                    (numero_ligne, commande, produit, quantite)
                    VALUES (
                        $i,
                        '".$db->escape($code_juva)."',
                        '".$db->escape($ligne['Produit'])."',
                        ".intval($ligne['Quantite'])."
                    )
                ";
                
                // Log de la requête
                error_log("[JUVA][Ligne $i] SQL : " . trim(preg_replace('/\s+/', ' ', $query)));
        
                // Exécution
                $db->execute($query);
                $i++;
            }

$total = 0.0;
foreach ($cmd['commandeLigne'] as $ligne) {
    $idOriginal = $db->escape($ligne['Produit']);
    $quantite = intval($ligne['Quantite']);

    $db->execute("SELECT prix, pcb FROM juva_produit WHERE idoriginal = '$idOriginal' LIMIT 1");


    if ($db->num()) {
      $row = $db->assoc();
      $prix = floatval($row['prix']);
      $pcb = intval($row['pcb']) ?: 1;
      $ligneTotal = $quantite * $pcb * $prix;
      $total += $ligneTotal;
      error_log("[DEBUG] Produit: $idOriginal | Qte: $quantite | PCB: $pcb | PU: $prix | Total ligne: $ligneTotal");
    } else {
        error_log("[JUVA][WARN] Produit $idOriginal non trouvé dans juva_produit");
    }
}

$total = number_format($total, 2, '.', ''); // format SQL

$db->execute("UPDATE juva_commande SET total = '$total' WHERE id = $id_commande");
error_log("[JUVA] ✅ Total enregistré : $total €");

        }
        
     
      error_log("[JUVA] Commande ".$cmd['id']." enregistrée avec ".count($cmd['commandeLigne'])." lignes.");

      if ($cmd['externe'] == 0) {
        self::generateCommandeJuvaCSV($id_commande);
    }
    
    if ($cmd['externe'] == 1) {
      error_log("Commande externe : " . ($cmd['externe'] == 1 ? "Oui" : "Non"), 0);
      $GLOBALS['isJuva'] = true;
      error_log("après avoir défini isJuva : " . $GLOBALS['isJuva']);
      //ici je pense c l'id abc pas id de cjuva_commande?
      new commandePDF($id_commande, false, true);
  }
  
   
}
public static function generateCommandeJuvaCSV($id_commande) {
  global $db;

  // 1. Récupération de la commande en base
  $db->execute("SELECT * FROM juva_commande WHERE id = " . intval($id_commande));
  $cmd = $db->assoc();

  if (!$cmd) {
      error_log("[JUVA] ❌ Commande introuvable avec ID $id_commande");
      return;
  }

  // === COMMANDE.CSV ===
  $filenameCommande = DIR_CMDJUVA . 'Commande.csv';
  $isNewCmdFile = !file_exists($filenameCommande);

  $f1 = fopen($filenameCommande, 'a'); // 🔁 append mode
  if ($isNewCmdFile) {
      fputcsv($f1, [
          'Code', 'Client', 'Utilisateur', 'DateLivraison', 'Date',
          'Reference', 'Statut', 'Origine', 'Commentaire' 
          // 'ChampLibre1'
      ], ';');
  }

  fputcsv($f1, [
      $cmd['code'],
      $cmd['client'],
      $cmd['utilisateur'],
     date('Y-m-d', strtotime($cmd['datelivraison'])),
      date('Y-m-d', strtotime($cmd['daterealisation'])),
      $cmd['reference'],
      $cmd['statut'],
      $cmd['origine'],
      $cmd['commentaire'],
      // $cmd['champlibre1']
  ], ';');
  fclose($f1);
  error_log("[JUVA] ✅ Commande.csv mis à jour");
   // === COMMANDELIGNE.CSV ===
   $filenameLigne = DIR_CMDJUVA . 'CommandeLigne.csv';
   $isNewLigneFile = !file_exists($filenameLigne);

   $f2 = fopen($filenameLigne, 'a');
   if ($isNewLigneFile) {
       fputcsv($f2, ['Commande', 'Code', 'Produit', 'Quantite'], ';');
   }

   $db->execute("SELECT * FROM juva_commandeligne WHERE commande = '".$db->escape($cmd['code'])."'");

   $lignes = $db->get();
   foreach ($lignes as $ligne) {
       fputcsv($f2, [
          $cmd['code'], 
           $ligne['numero_ligne'],
           $ligne['produit'],
           $ligne['quantite']
       ], ';');
   }
   fclose($f2);
   error_log("[JUVA] ✅ CommandeLigne.csv mis à jour");

  // Log du contenu pour vérif
 // Log du contenu ajouté (dernières lignes seulement)
foreach ([$filenameCommande, $filenameLigne] as $filepath) {
  if (file_exists($filepath)) {
      $lines = file($filepath);
      $total = count($lines);
      $linesToShow = array_slice($lines, -5); // 👈 On log juste les 5 dernières lignes

      error_log("[JUVA-CSV] 📄 Aperçu des dernières lignes de " . basename($filepath) . " ($total lignes totales)");
      foreach ($linesToShow as $index => $line) {
          error_log("[JUVA-CSV][" . basename($filepath) . "][L" . ($total - count($linesToShow) + $index + 1) . "] " . trim($line));
      }
  } else {
      error_log("[JUVA-CSV] ❌ Fichier introuvable : $filepath");
  }
}

}
  public static function new( $cmd ) {
    global $db;

    if( defined('API_ID_USER') && API_ID_USER > 0 ) {
      $user = user::exist(API_ID_USER);
    }

    $db->execute("SELECT id FROM commande_apk WHERE id_apk = '".$db->escape($cmd['id'])."' AND id_magasin = '".$db->escape($cmd['magasin'])."' AND deleted = 0");
    if( $db->num() ) {
      core::logApk( l("cmd-err-exist") ." (".$cmd['id'].")");
      return;
    }

    if( !isset($cmd['fp_raison']) ) $cmd['fp_raison'] = "";
    if( !isset($cmd['user']) || $cmd['user'] == "" ) $cmd['user'] = $user['login'];

    if( !isset($cmd['no_visit']) ) $cmd['no_visit'] = 0;
    if( !isset($cmd['no_visit_reason']) ) $cmd['no_visit_reason'] = "";
    if( !isset($cmd['externe']) ) $cmd['externe'] = 0;
    if( !isset($cmd['externeMail']) ) $cmd['externeMail'] = 0;

    $q = "INSERT INTO
      commande_apk
      (
        id_apk, id_magasin, user, date_creation_apk, date_liv_estimee, date_next_cmd,
        no_cmd_client, queue_date,fp_raison,no_visit,no_visit_reason,externe,externeMail

      )
    VALUES
      (
          '".$db->escape($cmd['id'])."',
          '".$db->escape($cmd['magasin'])."',
          '".$db->escape($cmd['user'])."',
          '".$db->escape($cmd['date_creation'])."',
          '".$db->escape($cmd['date_liv_estimee'] ?? '')."',
          '".$db->escape($cmd['date_next_commande'] ?? '')."',
          '".$db->escape($cmd['no_cmd_client'] ?? '')."',
          '".$db->escape($cmd['queueDate'])."',
          '".$db->escape($cmd['fp_raison'])."',
          '".$db->escape($cmd['no_visit'])."',
          '".$db->escape($cmd['no_visit_reason'])."',
          '".$db->escape($cmd['externe'])."',
          '".$db->escape($cmd['externeMail'])."'
      )
    ";
    #error_log("Requete de génération de la commande : ");
    #error_log(str_replace("\r","",$q));
    $db->execute($q);
    $id_commande = $db->lastId();

    $total = 0;
    foreach( $cmd['produits'] as $id_as400 => $qte ) {
      $produit = produit::getByCode( $id_as400 );
      if( !$produit ) continue;
      if( !$produit['tarif'] || $produit['tarif'] == null ) $produit['tarif'] = 0;
      $tp = ( $produit['tarif'] > 0 ? floatval( intval($qte) * floatval(str_replace(",",".",$produit['tarif']) ) ) : 0);
      $total += $tp;
      $db->execute("
        INSERT INTO commande_apk_produits
        (id_commande_apk, id_produit,quantite,pcb,prix_unitaire,prix_total)
        VALUES
        ( $id_commande,
          '".$db->escape($id_as400)."',
          ".intval($qte).",
          '".( intval($produit['zparm_pcb']) ? intval($produit['zparm_pcb']) : 1 )."',
          '".$db->escape($produit['tarif'])."',
          '$tp'
        )
      ");

      
      // Produits complémentaires
      $pComp = produit::getListComp( $id_as400 );
      foreach( $pComp as $comp ) {
        if( $comp['actif'] == 0 ) continue;
        $c = produit::getByCode( $comp['id_as400_comp'] );
        if( !$c ) continue;
        if( !$c['tarif'] || $c['tarif'] == null ) $c['tarif'] = 0;
        $tp = ( $c['tarif'] > 0 ? floatval( intval($qte) * floatval(str_replace(",",".",$c['tarif']) ) ) : 0);
        $total += $tp;
        $db->execute("
          INSERT INTO commande_apk_produits
          (id_commande_apk, id_produit,quantite,pcb,prix_unitaire,prix_total)
          VALUES
          ( $id_commande,
            '".$db->escape($c['id_as400'])."',
            ".intval($comp['qte']).",
            '".( intval($c['zparm_pcb']) ? intval($c['zparm_pcb']) : 1 )."',
            '".$db->escape($c['tarif'])."',
            '$tp'
          )
        ");
      }


    }

    // Log des produis de remplacement
    if( isset($cmd['replacements']) && !empty($cmd['replacements']) ) {
      foreach( $cmd['replacements'] as $k=>$e ) {
        $db->execute("
          INSERT INTO log_replacement
          (id_as400,seuil,stock,id_switch,id_cmd)
          VALUES
          ('".e($e['old'])."','".e($e['old_seuil'])."','".e($e['old_stock'])."','".e($e['new'])."',$id_commande)        
        ");
      }
    }



    $db->execute("UPDATE commande_apk SET total = '$total' WHERE id = $id_commande");

    // Si gescomtest ludivin, on met statut a 1
    if( API_ID_USER == 14 ) {
      $db->execute("UPDATE commande_apk SET statut = 1 WHERE id = $id_commande");
      return;
    }

    // Génération du fichier => Ne pas générer la commandes directement car si montage HS => Erreur sur le tel
    if( core::isOkMountPoints() )
      self::generateCommandFiles();

    if( $cmd['externe'] == 1 ) new commandePDF( $id_commande );

    return;
  }

  public static function regenerateCmdFic() {
    global $db;
    $id = intval($_POST['id']);
    $db->execute("SELECT * FROM commande_apk WHERE id = $id AND deleted = 0");
    if( !$db->num() ) core::ajaxError("Commande introuvable");
    $cmd = $db->assoc();
    if( $cmd['externe'] == 1 ) core::ajaxError( l("cmd-err-ext"));
    //if( $cmd['statut'] == 0 ) core::ajaxError("Le fichier de commande n'a pas encore été généré");
    $db->execute("UPDATE commande_apk SET statut = 0 WHERE id = $id");
    if( core::isOkMountPoints() )
      self::generateCommandFiles();
    die('{}');
  }

  public static function getApkCmd( $limit = 15, $id = null, $full = true ) {
    global $db;
    $w = "";
    if( $id > 0 ) $w = " WHERE id = ".intval($id);
    $db->execute("SELECT * FROM commande_apk  $w ORDER BY id DESC LIMIT $limit");
    $cmds = $db->getArray();
    foreach( $cmds as $k=>$e ) {

      if( !$full ) {
        if( strpos($e['date_liv_estimee'],"T") > 0 )
          $cmds[$k]['date_liv_estimee'] = core::apkDate2($e['date_liv_estimee']);
        if( strpos($e['date_next_cmd'],"T") > 0 )
          $cmds[$k]['date_next_cmd'] = core::apkDate2($e['date_next_cmd']);

        $cmds[$k]['client'] = client::getByCode($e['id_magasin'])['enseigne'];
        $cmds[$k]['user'] = user::getNameFromLogin($e['user']);
        $cmds[$k]['date_creation_apk'] = core::dateOutput($e['date_creation_apk']);
        $cmds[$k]['queue_date'] = core::dateOutput($e['queue_date']);
        $cmds[$k]['date_creation'] = core::dateOutput($e['date_creation']);
        $cmds[$k]['total'] = core::n($e['total']);
      }

      $cmds[$k]['produits'] = [];
      $db->execute("SELECT * FROM commande_apk_produits WHERE id_commande_apk = $k");
      $produits = $db->getArray();
      foreach( $produits as $r ) {
        $db->execute("SELECT * FROM ref_article WHERE id_as400 = '".$r['id_produit']."' ");
        if( !$db->num() ) continue;
        $a = $db->assoc();

        if( $full )
          $cmds[$k]['produits'][$r['id_produit']] =  $a;
        else {
          $cmds[$k]['produits'][$r['id_produit']]['libelle'] = $a['libelle'];
          $cmds[$k]['produits'][$r['id_produit']]['pu'] = core::n($r['prix_unitaire']);
          $cmds[$k]['produits'][$r['id_produit']]['pt'] = core::n($r['prix_total']);
        }

        $cmds[$k]['produits'][$r['id_produit']]['qte'] = $r['quantite'];
      }
      $cmds[$k]['id_visite'] = 0;
      $db->execute("SELECT id FROM visite WHERE id_commande = '".$e['id_apk']."' AND deleted = 0");
      if( $db->num() ) {
        $datas = $db->assoc();
        $cmds[$k]['id_visite'] = $datas['id'];
      }
    }
    return $cmds;
  }




  public static function transformCmd() {
    $id = intval($_POST['id'] ?? 0);
    global $db;
    $db->execute("SELECT * FROM commande_apk WHERE id = $id AND externe = 1 AND statut = 0");
    if( !$db->num() ) core::ajaxError( l("cmd-err-impossible-action") );
    $db->execute("UPDATE commande_apk SET externe = 0 WHERE id = $id ");
    if( core::isOkMountPoints() ) self::generateCommandFiles();
    die('{}');
  }


  /** Génération des fichiers de commandes vers AS400 **/

  public static function generateCommandFilesAjax() {
    self::generateCommandFiles();
    die('{}');
  }
  public static function generateCommandFiles() {
    global $db;

    // Dossier de génération des fichiers
    if( !is_dir(DIR_CMD) || !file_exists(DIR_CMD) ) {
       core::aError("[ERREUR] Impossible de générer les commandes au format .txt car le montage n'existe pas : ".DIR_CMD.", veuillez vérifier les points de montage");
    }

    $db->execute("SELECT * FROM commande_apk WHERE statut = 0 AND externe = 0");
    if( !$db->num() ) return;

    $cmds = $db->getArray();
    foreach( $cmds as $k=>$e ) {
      if( $e['externe'] != 0 ) continue;
      $db->execute("SELECT ean_client,id_as400 FROM ref_client WHERE id_as400 = '".$e['id_magasin']."'");
      $d = $db->assoc();
      $cmds[$k]['ean_client'] = $d['ean_client'];
      $cmds[$k]['id_as400'] = $d['id_as400'];

      $cmds[$k]['produits'] = [];
      $db->execute("SELECT * FROM commande_apk_produits WHERE id_commande_apk = $k");
      $produits = $db->getArray();
      foreach( $produits as $u=>$r ) {
        $db->execute("SELECT gencode FROM ref_article WHERE id_as400 = '".$r['id_produit']."' ");
        if( !$db->num() ) {
          unset($cmds[$k]['produits'][$u]);
          continue;
        }
        $gencode = $db->assoc()['gencode'];
        $cmds[$k]['produits'][$gencode] = $r['quantite'];
      }
    }

    /*
    echo '<pre>';
    var_dump($cmds);
    exit;
    */



    // Génération du fichier
    $cp = 0;
    foreach( $cmds as $k=>$e ) {

      $noclient = $e['no_cmd_client'];
      $noclient = preg_replace("/[^A-Za-z0-9 ]/", '', $noclient);
      if( mb_strlen($noclient) > 30 )
        $noclient = mb_substr($noclient,0,30);


      $struc = [
        1 => [
          "length" => 4,
          "value" => "ABC",
          "PAD" => "right"
        ],
        2 => [
          "length" => 3,
          "value" => "ABC",
          "PAD" => "right"
        ],
        3 => [
          "length" => 3,
          "value" => "LOG",
          "PAD" => "left"
        ],
        4 => [
          "length" => 13,
          "value" => str_replace(["\r","\n"],"",($e['ean_client'])),
          "PAD" => "left"
        ],
        5 => [
          "length" => 13,
          "value" => str_replace(["\r","\n"],"",($e['id_as400'])),
          "PAD" => "right"
        ],
        6 => [
          "length" => 13,
          "value" => "",
          "PAD" => "left"
        ],
        7 => [
          "length" => 8,
          "value" => substr(self::apkDateToEdi($e['queue_date']),0,8),
          "PAD" => "left"
        ],
        8 => [
          "length" => 15,
          "value" => $k,
          "fill" => "0",
          "PAD" => "left"
        ],
        9 => [
          "length" => 30,
          "value" => $noclient,
          "PAD" => "right"
        ],
        10 => [
          "length" => 8,
          "value" => str_replace("-","",core::dateInput($e['date_liv_estimee'])),
          "PAD" => "left"
        ],
      ];

      $file = [];
      foreach( $e['produits'] as $ean => $qte ) {
        $line = $struc;
        $line[11] = [
          "length" => 13,
          "value" => $ean,
          "PAD" => "left"
        ];
        $line[12] = [
          "length" => 15,
          "value" => $qte,
          "PAD" => "left",
          "decimal" => 1
        ];

        $file[] = self::generateLine($line);
      }

      $filename = 'CDE'.str_pad($k,10,"0",STR_PAD_LEFT).'.txt';
      file_put_contents( DIR_CMD.$filename, implode("\r\n",$file) );

      # Génération d'un backup du txt de la commande sur le CRM
      @copy( DIR_CMD.$filename, BACKUP_CMD.$filename );

      $db->execute("UPDATE commande_apk SET statut = 1, filename = '".$db->escape($filename)."' WHERE id = $k");
      $cp++;
    }
  }







  public static function apkDateToEdi( $apkDate ) {
    $tmp = explode("T", $apkDate);
    return str_replace("-","",$tmp[0]);
  }
  public static function generateLine( $t ) {
    $tmp = [];
    foreach( $t as $k=>$e ) {
      $fill = $e['fill'] ?? " ";
      $pad = ( isset($e['PAD']) ? $e['PAD'] : "right" );
      if( !isset($e['decimal']) )
        $el = str_pad($e['value'],$e['length'],$fill, $pad == "left" ? STR_PAD_LEFT : STR_PAD_RIGHT);
      else {
        if( strpos($e['value'],".") > -1 ) list($ent,$dec) = explode(".",$e['value']);
        else if( ( strpos($e['value'],",") > -1 ) ) list($ent,$dec) = explode(",",$e['value']);
        else {
          $ent = $e['value'];
          $dec = 0;
        }
        if( !$dec || !isset($dec) ) $dec = 0;
        $el = str_pad($ent,$e['length']-$e['decimal'],"0", $pad == "left" ? STR_PAD_LEFT : STR_PAD_RIGHT).str_pad($dec,$e['decimal'],"0",STR_PAD_RIGHT);
      }
      //error_log($el);
      $tmp[] = $el;
    }
    return implode($tmp);
  }



  public static function getHistoriqueCommande() {
    $commandes = [];
    $user = user::exist(API_ID_USER);

    //test
    //$user['login'] = 'elodie.albertoni';

    global $db;
    $db->execute("SELECT * FROM commande_apk WHERE deleted = 0 AND user = '".$db->escape($user['login'])."' ORDER BY id DESC LIMIT 100");
    $c = [];
    if( $db->num() ) {
      $commandes = $db->getArray();
      foreach( $commandes as $k=>$e ) {
        $tmp = [
          "id" => $e['id'],
          "c" => client::getByCode($e['id_magasin'])['enseigne'],
          "d" => core::dateOutput($e['queue_date']),
          "f" => core::dateFrom($e['date_creation']),
          "t" => core::n($e['total']),
          //"e" => core::apkDate2($e['date_liv_estimee'])
        ];
        $c[] = $tmp;
      }
    }
    api::ajaxRep(["commandes"=>$c]);
  }

  public static function getHistoriqueCommandeDetails() {
    global $params;
    $id = intval($params['id']);
    $cmds = self::getApkCmd(1,$id,false);
    if( empty($cmds) ) api::aError("Impossible de trouver les détails de cette commande");
    $cmd = array_pop($cmds);
    $cmd['date_creation_apk'] = core::apkDate($cmd['date_creation_apk']);
    api::ajaxRep(["commande"=>$cmd]);
  }

  public static function getLastCommandesClient( $id, $limit = 10 ) {
    global $db;
    $cli = client::get($id);
    $q = "SELECT id FROM commande_apk WHERE id_magasin = '".$db->escape($cli['id_as400'])."' ORDER BY id DESC LIMIT $limit ";
    $db->execute($q);
    if( !$db->num() ) return false;
    $ids = [];
    while( $r = $db->assoc() ) $ids[] = $r['id'];
    return $ids;
  }

  public static function getDetailsCommande() {
    global $db,$params;
    $cmd = [];
    $no_cmd = $db->escape($params['no_cmd']);
    $db->execute("SELECT * FROM ref_facture WHERE no_commande = '$no_cmd' LIMIT 1");
    if( !$db->num() ) api::aError("Commande introuvable");
    $cmd = $db->assoc();
    $db->execute("SELECT id,id_article,article,qte_facturee FROM ref_facture WHERE no_commande = '$no_cmd' ");
    $cmd['produits'] = $db->getArray();
    $db->execute("SELECT montant_facture FROM ref_facture WHERE no_commande = '$no_cmd' GROUP BY no_facture");
    $total = 0;
    while( $r = $db->assoc() ) $total += $r['montant_facture'];
    $cmd['total'] = core::n($total,2);

    api::ajaxRep(["commande"=>$cmd]);
  }

  public static function getTotalFactures() {
    global $db;
    $db->execute("SELECT no_facture FROM ref_facture group by no_facture");
    die('{"nb" : "'.core::n($db->num()).'" }');
  }

  public static function get( $id ) {
    global $db;
    $id = $db->escape($id);
    $db->execute("SELECT * FROM commande_apk WHERE id = '$id' OR id_apk = '$id' ");
    return $db->num() ? $db->assoc() : false;
  }

  public static function getJuva( $id ) {
    global $db;
    $id = $db->escape($id);
    $db->execute("SELECT * FROM juva_commande WHERE id = '$id' OR id_abc = '$id' ");
    return $db->num() ? $db->assoc() : false;
  }








    public static function searchBoard() {
      $str = trim(strtolower($_POST['str']));
      $limit = intval($_POST['limit']);
      $offset = intval($_POST['offset']);
      $from = $_POST['from'];
      $to = $_POST['to'];

      $w = [];
      $w[] = "c.deleted = 0";

      $checkDate = true;
      $tmp = explode("/",$from);
      if( count($tmp) != 3 ) $checkDate = false;
      $from = core::dateInput($from);
      $tmp = explode("/",$to);
      if( count($tmp) != 3 ) $checkDate = false;
      $to = date('Y-m-d',strtotime('+1 day',strtotime(core::dateInput($to))));
  
      if( $checkDate )
        $w[] = " c.queue_date >= '$from' AND c.queue_date < '$to' ";

      global $db;
      if( $str != "" ) {
        $str = $db->escape($str);

        $date = $str;
        if( strpos($date,"/") > 0 ) {
          $tmp = explode("/",$str);
          $date = implode("-",array_reverse($tmp));
        }

        $w[] = "
        (
          LOWER(u.displayname) LIKE '%".$str."%'
          OR
          LOWER(cli.enseigne) LIKE '%".$str."%'
          OR
          LOWER(cli.id_as400) LIKE '%".$str."%'
          OR
          c.date_creation LIKE '%".$date."%'
        )
        ";
      }

      /* Total de résultats */
      $countQuery = "
      SELECT count(*) as nb 
      FROM
        commande_apk c
        LEFT JOIN user u ON c.user = u.login
        LEFT JOIN ref_client cli ON c.id_magasin = cli.id_as400
      WHERE
        ".implode(" AND ", $w);
      $db->execute($countQuery);
      $count = $db->assoc()['nb'];

      /* Résultats à afficher */
      $q = "
        SELECT 
          c.id,
          c.id_magasin,
          cli.enseigne as client,
          c.no_visit,
          c.no_visit_reason,
          c.total,
          c.fp_raison,
          c.queue_date,
          u.displayname as user,
          c.date_liv_estimee,
          c.no_cmd_client,
          c.externe,
          c.statut,
          ( SELECT id FROM visite WHERE id_commande = c.id_apk ) as id_visite
        FROM
          commande_apk c
          LEFT JOIN user u ON c.user = u.login
          LEFT JOIN ref_client cli ON c.id_magasin = cli.id_as400
        WHERE
          ".implode(" AND ", $w)."
        ORDER BY
          c.date_creation DESC
        LIMIT
          ".($offset*$limit).", $limit
      ";
      $db->execute($q);
      if( !$db->num() ) core::ajax(["html"=>"<tr><td class='text-center' colspan='11'>Aucun résultat</td></tr>"]);

      $ids = [];
      while( $r = $db->assoc() ) $ids[$r['id']] = $r;
      $html = [];
      foreach( $ids as $id_commande => $c ) {

        $linkLibelle = "<em>".l("cmd-statut-attente")."</em>";
        if( $c['id_visite'] > 0 ) {
          $id_visite = $c['id_visite'];
          $link = URL.'Visites/'.$id_visite;
          $linkLibelle = '<a href="'.$link.'">#'.$id_visite.'</a>';
        }
        else {
          $linkLibelle = '<i class="fas fa-times text-danger" rel="tooltip" title="'.core::getReason($c['no_visit_reason']).'"></i>';
        }

        $total = number_format(floatval($c['total']),2,","," ").'€';
        if( $c['fp_raison'] != "" )
          $total .= ' <i class="fas fa-info-circle text-secondary ml-2" rel="tooltip" title="'.core::getReason($c['fp_raison']).'"></i>';

        $periodicite = "";
        $clientPeriodicite = client::getPeriodicite($c['id_magasin']);
        if( !empty($clientPeriodicite) ) $periodicite = $clientPeriodicite['libelle'];

        $html[] = '<tr data-id="'.$id_commande.'">';
        $html[] = '<td>'.$id_commande.'</td>';
        $html[] = '<td>'.core::dateOutput($c['queue_date'],true).'</td>';
        $html[] = '<td>'.$c['id_magasin'].'</td>';
        $html[] = '<td>'.$c['client'].'</td>';
        $html[] = '<td><small>'.$periodicite.'</small></td>';

        $html[] = '<td>'.$c['user'].'</td>';
        $html[] = '<td class="tc">'.$linkLibelle.'</td>';
        $html[] = '<td>'.$c['date_liv_estimee'].'</td>';
        $html[] = '<td>'.$c['no_cmd_client'].'</td>';
        $html[] = '<td class="tc">'.$total.'</td>';

        if( $c['externe'] == 1 )
          $html[] = '<td class="tc"><i class="fas fa-external-link-alt" rel="tooltip" title="Commande externe"></i></td>';
        else {
          if( $c['statut'] == 1 )
            $html[] = '<td class="tc"><i class="fas fa-check" style="color:#A5D6A7" rel="tooltip" title="Envoyée à l\'AS400"></i></td>';
          else 
          $html[] = '<td class="tc"><i class="fas fa-times" style="color:#E57373" rel="tooltip" title="Non evnoyée à l\'AS400"></i></td>';
        }

        $html[] = '</tr>';
      }
      die(json_encode(["html"=>implode($html),"count"=> core::n($count)]));
    }

public static function searchBoardJuva() {
    global $db;

    $str = trim(strtolower($_POST['str']));
    $limit = intval($_POST['limit']);
    $offset = intval($_POST['offset']);
    $from = $_POST['from'];
    $to = $_POST['to'];

    $w = [];
    $w[] = "1=1"; // Toujours vrai

    $checkDate = true;
    $tmp = explode("/",$from);
    if (count($tmp) != 3) $checkDate = false;
    $from = core::dateInput($from);
    $tmp = explode("/",$to);
    if (count($tmp) != 3) $checkDate = false;
    $to = date('Y-m-d',strtotime('+1 day',strtotime(core::dateInput($to))));

    if ($checkDate) {
      $w[] = " c.daterealisation >= '$from' AND c.daterealisation < '$to' ";
    }

    if ($str !== "") {
        $search = $db->escape($str);
        $w[] = "(LOWER(c.client) LIKE '%$search%' OR LOWER(c.code) LIKE '%$search%' OR LOWER(c.utilisateur) LIKE '%$search%')";
    }

  $w[] = "c.code LIKE 'ABC%'" ; // Filtre pour les commandes ABC

    // Compte total
    $db->execute("SELECT count(*) as nb FROM juva_commande c WHERE " . implode(" AND ", $w));
    $count = $db->assoc()['nb'];
    // Résultats
    $q = "
    SELECT 
        c.id,
        cli.enseigne AS client_libelle,  -- Libellé client
        c.code,
      u.displayname AS utilisateur,
        c.daterealisation,
        c.datelivraison,
        c.reference,
        c.statut,
        c.origine,
        c.total,
        c.commentaire,
        c.externe,
        c.externeMail
    FROM juva_commande c
   LEFT JOIN ref_client cli ON cli.id_as400 = c.id_as400

    LEFT JOIN user u ON u.login = c.utilisateur
    WHERE " . implode(" AND ", $w) . "
    ORDER BY c.id DESC
    LIMIT " . ($offset * $limit) . ", $limit
";



    $db->execute($q);
    if (!$db->num()) {
        core::ajax(["html" => "<tr><td class='text-center' colspan='10'>Aucun résultat</td></tr>"]);
    }

    $rows = $db->getArray();
    $html = [];

    foreach ($rows as $c) {
      $html[] = "<tr data-id=\"{$c['id']}\">";
      $html[] = "<td>{$c['id']}</td>";
      $html[] = "<td>" . core::dateOutput($c['daterealisation'], true) . "</td>";
      $html[] = "<td>{$c['code']}</td>";
      $html[] = "<td>{$c['client_libelle']}</td>";  
      $html[] = "<td>{$c['utilisateur']}</td>";
      $html[] = "<td>{$c['datelivraison']}</td>";         
      $html[] = "<td>{$c['total']}€</td>";
      // $html[] = "<td>{$c['statut']}</td>";         
      // $html[] = "<td>{$c['origine']}</td>";           
      // $html[] = "<td>{$c['commentaire']}</td>";
      // 🔽 C’EST ICI QUE TU AJOUTES L’ÉTAPE 4 :
      if ($c['externe'] == 1) {
          $html[] = '<td class="tc"><i class="fas fa-external-link-alt" rel="tooltip" title="Commande externe"></i></td>';
      }
      else {
         
            $html[] = '<td class="tc"><i class="fas fa-check" style="color:#A5D6A7" rel="tooltip" title="Envoyée à juva"></i></td>';
          
     }

      $html[] = "</tr>";
  }

    die(json_encode([
        "html" => implode("", $html),
        "count" => core::n($count)
    ]));
}

public static function exportBoardJuva() {
    global $db;

    $fromRaw = $_POST['from'] ?? $_GET['from'] ?? '';
    $toRaw = $_POST['to'] ?? $_GET['to'] ?? '';
    $str = trim(strtolower($_POST['str'] ?? $_GET['str'] ?? ''));

    $w = [];
    $w[] = "1=1";

    $checkDate = true;
    $tmp = explode("/", $fromRaw);
    if (count($tmp) != 3) $checkDate = false;
    $from = core::dateInput($fromRaw);
    $tmp = explode("/", $toRaw);
    if (count($tmp) != 3) $checkDate = false;
    $to = date('Y-m-d', strtotime('+1 day', strtotime(core::dateInput($toRaw))));

    if ($checkDate) {
      $w[] = " c.daterealisation >= '$from' AND c.daterealisation < '$to' ";
    }

    if ($str !== "") {
      $search = $db->escape($str);
      $w[] = "(LOWER(c.client) LIKE '%$search%' OR LOWER(c.code) LIKE '%$search%' OR LOWER(c.utilisateur) LIKE '%$search%')";
    }

    $w[] = "c.code LIKE 'ABC%'";

    $q = "
      SELECT 
        c.id,
        cli.enseigne AS client_libelle,
        c.code,
        u.displayname AS utilisateur,
        c.daterealisation,
        c.datelivraison,
        c.total,
        c.externe
      FROM juva_commande c
      LEFT JOIN ref_client cli ON cli.id_as400 = c.id_as400
      LEFT JOIN user u ON u.login = c.utilisateur
      WHERE " . implode(" AND ", $w) . "
      ORDER BY c.id DESC
    ";

    $db->execute($q);
    $rows = $db->num() ? $db->getArray() : [];

    $fromName = ($checkDate ? $from : '');
    $toName = ($checkDate ? core::dateInput($toRaw) : '');
    $fileName = 'commandes_juva.csv';
    if ($fromName && $toName) {
      $fileName = 'commandes_juva_' . $fromName . '_' . $toName . '.csv';
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // BOM UTF-8 pour compatibilité Excel (accents)
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, [
      l('page-cmds-table-id'),
      l('page-cmds-table-date-cmd'),
      l('page-cmds-table-code'),
      l('page-cmds-table-client'),
      l('page-cmds-table-commercial'),
      l('page-cmds-table-date-souhaitee'),
      'Total',
      l('page-cmds-table-statut')
    ], ';');

    foreach ($rows as $c) {
      $statut = ($c['externe'] == 1) ? 'Externe' : 'Envoyée à juva';
      fputcsv($out, [
        $c['id'],
        core::dateOutput($c['daterealisation'], true),
        $c['code'],
        $c['client_libelle'],
        $c['utilisateur'],
        $c['datelivraison'],
        $c['total'] . '€',
        $statut
      ], ';');
    }

    fclose($out);
    exit;
}


    public static function getLastCmdMinosPrintable() {
      $id_as400 = $_POST['id_as400'];
      $limit = $_POST['limit'] ?? 100;
      $tpl = [];
      global $db;

      $db->execute("
      SELECT DISTINCT no_commande,jour_facture,mois_facture,annee_facture,id_rep FROM ref_facture
      WHERE
        id_client_cmd LIKE '".$id_as400."'
      ORDER BY annee_facture DESC,mois_facture DESC,jour_facture DESC LIMIT $limit
      ");
      if( !$db->num() ) 
        $tpl[] = '<p class="tc text-secondary">'.l('cmd-not-found').'</p>';
      else {
          $rez = [];
          while( $r = $db->assoc() ) $rez[] = $r;
          $tmp = ['<div class="list-group" style="max-height:500px;overflow:auto;">'];
          $tmp[]= '';
          $users = [];
          foreach( $rez as $e ) {
              $rep = "";
              if( !isset($users[$e['id_rep']]) ) {
                $users[$e['id_rep']] = user::getFromIdRepr($e['id_rep']);
              }
              if( isset($users[$e['id_rep']]['displayname'])) $rep = '<span class="badge badge-primary"><i class="fas fa-user-tie"></i> '.$users[$e['id_rep']]['displayname'].'</span>';
              $date = $e['annee_facture']."-".$e['mois_facture']."-".$e['jour_facture'];
              $tmp[] = '<a href="#" class="list-group-item list-group-item-action getDetailsCmd"  data-id="'.$e['no_commande'].'">';
              $tmp[] = $rep.' #'.$e['no_commande'].' ';
              $tmp[] = ' le '.core::dateOutput($date).' <em class="small">(il y a '.core::dateFrom($date).')</em>';
              $tmp[] = '</a>';
          }
          $tmp[] = '</div>';
          $tpl[] = implode($tmp);
      }

      core::ajaxReturnHtml($tpl);
    }

    public static function getDetailsCmdMinos() {
      $numero = $_POST['numero'] ?? null;
      global $db;
      $tpl = [];
      $db->execute("
        SELECT * FROM ref_facture 
        WHERE 
          no_commande = '".$db->escape($numero)."' 
          AND facture_avoir = 'F'
        ORDER BY
          article    
      ");
      if( !$db->num() ) $tpl[] = "Commande introuvable";
      else {
        $qte = $refs = 0;
        $tmp = [];
        $tmp[] = '<ul class="list-group text-left" style="max-height:300px;">';
        while( $r = $db->assoc() ) {
          $refs++;
          $qte += intval($r['qte_facturee']);
          $tmp[] = '<li class="list-group-item">
          <span class="badge badge-primary">'.$r['qte_facturee'].'</span> 
            '.$r['article'].'
          </li>';
        }
        $tmp[] = '</ul>';
        $tpl[] = '<p class="text-left">
          <strong>Total références : </strong>'.number_format($refs,0).' <br/>
          <strong>Cumul des quantités : </strong>'.number_format($qte,0).'
        </p>';
        $tpl[] = implode('',$tmp);
      }
      core::ajaxReturnHtml($tpl);
    }


    public static function getCommandes( $id_as400 ) {
      global $db;
      $visites = $commandes = [];
      $id_as400 = e($id_as400);
      $db->execute("
        SELECT 

          id,
          id_commande,
          id_user,
          no_cmd,
          no_cmd_reason,
          pmc_state,
          pmc_coms,
          queue_date

        FROM visite 
        WHERE
          id_client = '$id_as400'
          AND deleted = 0
        ORDER BY id DESC
        LIMIT 500
      ");
      $visites = $db->get();
      $users = [];
      foreach( $visites as $k=>$e ) {
        $cmd = $e['no_cmd'] == 0;
        if( $cmd ) {
          $db->execute("SELECT total,date_liv_estimee FROM commande_apk WHERE id_apk = '".e($e['id_commande'])."' ");
          $cmd = $db->getLine();
        }
        if( !in_array($e['id_user'],$users) ) 
          $users[$e['id_user']] = user::getNameFromId($e['id_user']);
      
        $tmp = [
          "id" => $e['id'],
          "d1" => core::dateOutput($e['queue_date']),
          "p" => $users[$e['id_user']],
          "t" => $cmd ? number_format($cmd['total'],2,","," ")." €" : '',
          "c" => $cmd ? "✔" : "⨉",
          "m" => $cmd ? "" : core::getReason($e['no_cmd_reason']),
          "pmc" => $e['pmc_state'] ? "✔": "⨉",
          "d2" => $cmd['date_liv_estimee']
        ];
        $commandes[] = $tmp;
      }


      api::ajaxRep(["commandes"=>$commandes]);
    }

    public static function getCommandesSM( $params ) {
      global $db;

      $cmds = [];
      $w = [];

      $id_users = [];
      if( isset($params['promoteur']) ) {
        if( $params['promoteur'] > 0 ) 
          $id_users[] = user::exist($params['promoteur'])['id_repr'];
      }
      if( empty($id_users) ) {
        $id_users = stats::getMyPromoteurs();
        //foreach($tmp as $e ) $id_users[] = $e['id'];
      }
      if( empty($id_users) ) $id_users = [API_ID_USER];
      $w[] = " CAST(ca.id_repr AS UNSIGNED) IN (".implode(",",$id_users).") ";

      // Terms
      if( isset($params['terms']) && trim($params['terms']) != "" ) {
        $tmp = explode(" ",strtolower(trim($params['terms'])));
        $words = [];
        foreach( $tmp as $word ) {
          $words[] = "
            ( 
              LOWER(cli.enseigne) LIKE '%".e($word)."%' 
              OR
              LOWER(cli.id_as400) LIKE '%".e($word)."%' 
              OR
              LOWER(u.displayname) LIKE  '%".e($word)."%' 
            )
          ";
        }
        $w[] = " ( ".implode(" AND ",$words)." ) ";
      }

      // Dates
      $from = date('Y').'-01-01';
      $to = date('Y-m-d');
      if( isset($params['start']) && $params['start'] != "" ) {
        $from = e($params['start']);
        if( isset($params['end']) && $params['end'] != "" ) 
          $to = e($params['end']);
        if( strtotime($to) < strtotime($from) ) 
          api::aError("Veuillez reseigner des dates correctes");
      }
      
      $q = "
        SELECT 
          c.*,
          ca.id_commande_apk
        FROM
          commandes_as400_total c
          LEFT JOIN commandes_as400 ca ON ca.id = ( SELECT id FROM commandes_as400 WHERE numero = c.numero LIMIT 1 ) 
          LEFT JOIN ref_client cli ON ca.code_client_cmd = cli.id_as400
          LEFT JOIN user u ON u.id_repr = ca.id_repr
        WHERE
          c.date_commande BETWEEN '$from' AND '$to'
          AND ".implode(' AND ',$w)."      
        ORDER BY
          c.date_commande DESC
        LIMIT 200
      ";
      //dd($q);
      $db->execute($q);

      $cmds = $db->getArray();
      foreach( $cmds as $k=>$e ) {
        $db->execute("
          SELECT 
            code_client_cmd,
            raison_sociale_cmd
          FROM 
            commandes_as400
          WHERE
            numero = '".$e['numero']."'
          LIMIT 1
        ");
        $d = $db->assoc();
        $cmds[$k]['date_commande'] = core::dateOutput($e['date_commande']);
        $cmds[$k]['id_as400'] = $d['code_client_cmd'];
        $cmds[$k]['client'] = $d['raison_sociale_cmd'];
        $cmds[$k]['total'] = number_format($e['total'],2,","," ")." €";

        $id_cmd_apk = intval($e['id_commande_apk']);
        if( $id_cmd_apk < 1 ) {
          $cmds[$k]['v'] = false;
          $cmds[$k]['dv'] = "-";
        }
        else {
          $db->execute("SELECT id_apk,date_liv_estimee FROM commande_apk WHERE id = '$id_cmd_apk' ");
          $datas = $db->assoc();
          $cmds[$k]['dv'] = core::dateOutput($datas['date_liv_estimee']);
          $id_apk = $datas['id_apk'];
          $db->execute("SELECT id FROM visite WHERE id_commande = '$id_apk' ");
          $datas = $db->assoc();
          $cmds[$k]['v'] = $datas['id'] ?? false;
        }
      }

      api::ajaxRep(["commandes" => array_values($cmds), "q" => $q ]);
    }

    public static function getMyPromoteurs( $return = false, $params = [] ) {
      $ids = [API_ID_USER];
      $users = [ 0 => ["id" => 0, "name" => "Tous"] ];
      
      $profiles = [1];
      if( isset($params['all']) && $params['all'] == 1 ) {
        $profiles = [1,2,3,4,5,6,7,8];
      }

      // TODO : Ajouter les autres promoteurs pour les n+x
      global $db;

      $mapProfiles = [];
      $db->execute("SELECT id,libelle FROM secu_profile");
      while( $r = $db->assoc() ) $mapProfiles[$r['id']] = $r['libelle'];


      $db->execute("
        SELECT id
        FROM user 
        WHERE 
          actif = 1 
          AND id_profile IN (".implode(",",$profiles).") 
          AND displayname != '' 
          ".( count($profiles) == 1 ? " AND id_repr != '' " : "")." 
      ");
      while( $r = $db->assoc() )
        if( !in_array($r['id'],$ids) )
          $ids[] = $r['id'];
      

      global $db;
      $db->execute("SELECT id,displayname,id_profile,id_repr as name FROM user WHERE id IN (".implode(",",$ids).") ORDER BY displayname");
      while( $r = $db->assoc() ) {
        $r['profile'] = $mapProfiles[$r['id_profile']] ?? "#";
        $users[] = $r;
      }
      if( !$return ) api::ajaxRep(["promoteurs" => $users]);
      return $users;
    }

    public static function getDetailsCommandePDF( $numero ) {
        global $db;
        $db->execute("SELECT * FROM commandes_as400 WHERE numero = '".$db->escape($numero)."'");
        if( !$db->num() ) exit;
        $datas = $db->get();
        if( count($datas) == 0 ) exit;

        $cde = $datas[0];

        $cde['client'] = client::getByCode($cde['code_client_cmd']);
        

        $total = $qteTotal = 0;

        $cde['produits'] = [];
        foreach( $datas as $k=>$e ) {

          $famille_a = $db->execute("SELECT libelle FROM referentiels WHERE nature = 'FAMA' AND valeur = '".$db->escape($e['famille'])."'")->assoc()['libelle'] ?? "";
          $famille_b = $db->execute("SELECT libelle_short FROM referentiels WHERE nature = 'FAMB' AND valeur = '".$db->escape($e['famille_acd'])."'")->assoc()['libelle_short'] ?? $e['famille_acd'];
          $p = $db->execute("SELECT * FROM ref_article WHERE id_as400 = '".$db->escape($e['code_article'])."' ")->assoc();

          $total += $e['montant'];
          $qteTotal += $e['quantite'];

          $cde['produits'][] = [
            "libelle" => $e['libelle_article'],
            "famille_a" => mb_strtoupper($famille_a),
            "famille_b" => mb_strtoupper($famille_b),
            "id" => $p['id_as400'],
            "qte" => $e['quantite'],
            "total" => number_format($e['montant'],2,","," "),
            "tu" => number_format(round($e['montant']/$e['quantite'],2),2,","," "),
            "code" => $p['gencode'] ?? "",
            "barcode" => '<barcode type="EAN13" value="'.($p['gencode'] ?? "").'" style="color: #000; width:30mm; height:6mm; font-size:2.5mm"></barcode>'
          ];

        }

        $total = number_format($total,2,","," ");
        $qteTotal = number_format($qteTotal,0,","," ");
        

        ob_start();
        include(PARTIAL."pdf/detail-commande.php");
        $html = ob_get_contents();
        ob_end_clean();


      
        $html2pdf = new Html2Pdf('L', 'A4', 'fr');
        $html2pdf->writeHTML( $html );
        $pdf = $html2pdf->output( "$numero.pdf" );
        exit;
    }



}
