<?php



class deportedFiles {

 public static function database() {
    error_log("[database] Début\n", 3, "/tmp/deported_debug.log");

    $datas = [];
    $datas['date'] = date('Y-m-d');
    
    error_log("[database] buildClients\n", 3, "/tmp/deported_debug.log");
    $datas['clients'] = self::buildClients();
    
    error_log("[database] buildProduits\n", 3, "/tmp/deported_debug.log");
    $datas['produits'] = self::buildProduits();

    error_log("[database] buildProduitsJuva\n", 3, "/tmp/deported_debug.log");
    $datas['produitsJuva'] = self::buildProduitsJuva();

    error_log("[database] buildNews\n", 3, "/tmp/deported_debug.log");
    $datas['news'] = self::buildNews();

    error_log("[database] buildPromos\n", 3, "/tmp/deported_debug.log");
    $datas['promos'] = self::buildPromos();

    error_log("[database] getMarques\n", 3, "/tmp/deported_debug.log");
    $datas['marques'] = self::getMarques();

    error_log("[database] getGammes\n", 3, "/tmp/deported_debug.log");
    $datas['gammes'] = self::getGammes( $datas['marques'] );

    error_log("[database] getConcurence\n", 3, "/tmp/deported_debug.log");
    $datas['concurence'] = self::getConcurence();

    error_log("[database] getOptions\n", 3, "/tmp/deported_debug.log");
    $datas['options'] = self::getOptions();

    error_log("[database] getDeportedProspects\n", 3, "/tmp/deported_debug.log");
    $datas['prospects'] = prospect::getDeportedProspects();

    error_log("[database] getDeportedProspections\n", 3, "/tmp/deported_debug.log");
    $datas['prospections'] = prospection::getDeportedProspections();

    error_log("[database] getStratsPem\n", 3, "/tmp/deported_debug.log");
    $datas['stratsPEM'] = produit::getStratsPem();

    error_log("[database] getLangues\n", 3, "/tmp/deported_debug.log");
    $datas['langues'] = lang::getDeported();

    error_log("[database] getMarquesJuva\n", 3, "/tmp/deported_debug.log");
    $datas['marquesJuva'] = self::getMarquesJuva();

    // $datas['gammesJuva'] = self::getGammesJuva();

    error_log("[database] getConcurenceJuva\n", 3, "/tmp/deported_debug.log");   
    $datas['concurenceJuva'] = self::getConcurenceJuva();

    error_log("[database] Fin\n", 3, "/tmp/deported_debug.log");
    return $datas;
}
  public static function generate() {
    set_time_limit(0);
    ignore_user_abort(true);
    include(SCRIPTS.'cron/deported-database.php');
    die('{}');
  }
public static function buildClients() {
  global $db;
  error_log("[buildClients] Début");
  $clients = [];

  $query = "
    SELECT
    a.id, a.id_as400, a.enseigne,
    a.adresse1,a.adresse2,a.adresse3, a.code_postal, a.code_postal_2, a.ville, a.pays,
    a.langue, a.devise,
    a.contact_1, a.contact_2, a.contact_3, a.tel1, a.tel2, a.actif,
    a.id_commercial_1, rep1.libelle as commercial_1,
    a.id_commercial_2, rep2.libelle as commercial_2,
    '".FRANCO_DE_PORT."' as 'franco'
    FROM
      ref_client a
      LEFT JOIN ref_client_centrale cc ON cc.code_client = a.id_as400
      LEFT JOIN referentiels rep1 ON rep1.valeur = a.id_commercial_1 AND rep1.nature = 'REPR' AND a.id_commercial_1 > 0
      LEFT JOIN referentiels rep2 ON rep2.valeur = a.id_commercial_2 AND rep2.nature = 'REPR' AND a.id_commercial_2 > 0 AND a.id_commercial_2 != a.id_commercial_1
    WHERE
      a.deleted = 0
      AND a.pays IN ('FR','BE','LU','AD')
      AND LENGTH(a.enseigne)  > 3
      AND statut_commande_par = 'O'
  ";
  $db->execute($query);
  $clients = $db->getArray();
  error_log("[buildClients] Clients récupérés : " . count($clients));
 $total = count($clients);
$counter = 0;
  foreach( $clients as $k=>$e ) {
   $counter++;
   error_log("[buildClients] ($counter / $total) Traitement client : " . $e['id_as400']);

    $clients[$k]['contacts'] = [];
    $db->execute("SELECT * FROM ref_client_contact WHERE deleted = 0 AND id_ref_client = '".$e['id_as400']."' ");
    while( $r = $db->assoc() )
      $clients[$k]['contacts'][] = $r;
    error_log("[buildClients][" . $e['id_as400'] . "] Contacts OK");

    $clients[$k]['infos'] = client::getInfos($e['id_as400']);
    $f = ['cli_avant_ouverture','flash',"chaussures_secu",'attestation','cni'];
    foreach( $f as $field ) {
      if( $clients[$k]['infos'][$field] == 1 ) $clients[$k]['infos'][$field] = "oui";
      else if( $clients[$k]['infos'][$field] == 2 ) $clients[$k]['infos'][$field] = "non";
      else $clients[$k]['infos'][$field] = "";
    }
    foreach( $clients[$k]['infos'] as $a=>$b )
      $clients[$k]['infos'][$a] = strtoupper($b);
    error_log("[buildClients][" . $e['id_as400'] . "] Infos supp OK");

    $clients[$k]['dn'] = self::getLastDn($e['id_as400']);
    $clients[$k]['dnPem'] = self::getLastDnPem($e['id_as400']);
    error_log("[buildClients][" . $e['id_as400'] . "] DN OK");

    $clients[$k]['factures'] = [];
    if( ENV == "PROD" ) {
      $db->execute("
        SELECT no_commande,jour_facture,mois_facture,annee_facture FROM ref_facture
        WHERE
          id_client_cmd LIKE '".$e['id_as400']."'
        ORDER BY annee_facture,mois_facture,jour_facture DESC
      ");
      $no_cmd = [];
      while( $r = $db->assoc() ) {
        if( in_array($r['no_commande'],$no_cmd) ) continue;
        $clients[$k]['factures'][] = [
          "no_cmd" => $r['no_commande'],
          "date" => $r['jour_facture']."/".$r['mois_facture']."/".$r['annee_facture']
        ];
        $no_cmd[] = $r['no_commande'];
      }
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Factures OK");

    $clients[$k]['visites'] = [];
    $db->execute("
      SELECT id,queue_date FROM visite
      WHERE
        id_client = '".$e['id_as400']."'
      ORDER BY queue_date DESC
      LIMIT 30
    ");
    while( $r = $db->assoc() ) {
      $clients[$k]['visites'][] = [
        "id" => $r['id'],
        "date" => core::dateOutput($r['queue_date'])
      ];
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Visites OK");

    $year = date('Y');
    $clients[$k]['ca'] = [
      "y" => $year,
      "t" => 0,
      "c" => 0,
      "v" => 0
    ];
    $no_fac = $no_cmd = [];
    if( ENV == "PROD" ) {
      $db->execute("SELECT no_facture,no_commande,montant_facture,facture_avoir FROM ref_facture WHERE id_client_cmd = '".$e['id_as400']."' AND facture_avoir = 'F' AND annee_facture = '$year' ");
      while( $r = $db->assoc() ) {
        if( in_array($r['no_facture'],$no_fac) ) continue;
        $clients[$k]['ca']["t"] += ( $r['facture_avoir'] == 'F' ? $r['montant_facture'] : - $r['montant_facture'] );
        $no_fac[] = $r['no_facture'];
        $no_cmd[$r['no_commande']] = "";
      }
      $clients[$k]['ca']['c'] = count(array_keys($no_cmd));
      $clients[$k]['ca']["t"] = core::n($clients[$k]['ca']["t"],2);
      $db->execute("SELECT count(*) as nb FROM visite WHERE id_client = '".$e['id_as400']."' AND queue_date LIKE '$year%' ");
      $clients[$k]['ca']["v"] = $db->assoc()['nb'];
    }
    error_log("[buildClients][" . $e['id_as400'] . "] CA OK");

    $clients[$k]['promo'] = [];
    $db->execute("SELECT id FROM visite WHERE id_client = '".$e['id_as400']."' ORDER BY id DESC LIMIT 1");
    if( $db->num() ) {
      $id_visite = $db->assoc()['id'];
      $db->execute("SELECT id_as400 FROM visite_promo WHERE id_visite = '$id_visite'");
      while( $r = $db->assoc() )
        $clients[$k]['promo'][] = $r['id_as400'];
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Promos OK");

    $clients[$k]['remarques'] = [];
    $db->execute("SELECT * FROM ref_client_remarque WHERE id_as400 = '".$e['id_as400']."' AND deleted = 0 ORDER BY date_creation DESC");
    if( $db->num() ) {
      $clients[$k]['remarques'] = $db->getArray();
      foreach( $clients[$k]['remarques'] as $id_rem => $rem ) {
        $name = "??";
        if( $rem['id_repr'] > 0 ) {
          $user = user::getFromIdRepr($rem['id_repr']);
          if( $user ) $name = $user['displayname'];
        }
        $clients[$k]['remarques'][$id_rem]['name'] = $name;
        $clients[$k]['remarques'][$id_rem]['date_creation'] = core::dateOutput($rem['date_creation']);
      }
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Remarques OK");

    $clients[$k]['periodicite'] = 1;
    $db->execute("SELECT id_periodicite FROM ref_client_periodicite WHERE id_client_as400 = '".$e['id_as400']."' AND deleted = 0");
    if( $db->num() ) {
      $id_periodicite = $db->assoc()['id_periodicite'];
      switch( $id_periodicite ) {
        case '0':
        case '1':
        case '2':
          $clients[$k]['periodicite'] = 1; break;
        case '3':
          $clients[$k]['periodicite'] = 2; break;
        case '4':
          $clients[$k]['periodicite'] = 3; break;
        case '5':
          $clients[$k]['periodicite'] = 4; break;
        case '6':
          $clients[$k]['periodicite'] = 5; break;
        default:
          $clients[$k]['periodicite'] = 1;
      }
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Periodicité OK");

    $clients[$k]['colis'] = [];
    $db->execute("
      SELECT 
        id,no_logis,colis,produits 
      FROM 
        commande_colis
      WHERE
        id_client = '".$e['id_as400']."'
        AND id_visite = 0
    ");
    if( $db->num() ) {
      $datas = $db->get();
      foreach( $datas as $e ) {
        $e['codes'] = [];
        $db->execute("SELECT id,code,code2 FROM commande_colis_codes WHERE id_commande_colis = ".$e['id']);
        while( $r = $db->assoc() ) {
          $e['codes'][$r['id']] = [ $r['code'], $r['code2'] ];
        }
        $clients[$k]['colis'][] = $e;
      }
    }
    error_log("[buildClients][" . $e['id_as400'] . "] Colis OK");
  }

  error_log("[buildClients] Fin");
  return $clients;
}


  public static function getLastDn( $id_as400 ) {
    global $db;
    $dn = [ "abc" => [], "concu" => [] ];
    $db->execute("SELECT id FROM visite WHERE id_client = '".$id_as400."' AND pem = 0 ORDER BY id DESC");
    $ids = [];
    while( $r = $db->assoc() ) $ids[] = $r['id'];

    $id_visite = 0;
    foreach( $ids as $id ) {
      $db->execute("SELECT * FROM visite_dn WHERE id_visite = $id");
      if( $db->num() ) {
          while( $r = $db->assoc() ) {
            $key = ( $r['type'] == "ABC" ? "abc" : "concu" );
            $dn[$key][] = ["ma" => $r['marque'], "ga"=> $r['gamme'], "me" => $r['metrage'] ];
          }
          break;
      }
    }

    return $dn;
  }

  public static function getLastDnPem( $id_as400 ) {
    global $db;
    $dn = [ "abc" => [], "concu" => [] ];
    $db->execute("SELECT id_visite FROM visite WHERE id_client = '".$id_as400."' AND pem = 0 ORDER BY id DESC");
    $ids = [];
    while( $r = $db->assoc() ) $ids[] = $r['id_visite'];

    $id_visite = 0;
    foreach( $ids as $id ) {
      $db->execute("SELECT id FROM visite WHERE id_visite_linked = '$id' AND pem = 1");
      if( $db->num() ) {
        $id = $db->assoc()['id'];
        $db->execute("SELECT * FROM visite_dn WHERE id_visite = $id");
        while( $r = $db->assoc() ) {
          $key = ( $r['type'] == "ABC" ? "abc" : "concu" );
          $dn[$key][] = ["ma" => $r['marque'], "ga"=> $r['gamme'], "me" => $r['metrage'] ];
        }
        break;
      }
    }
    return $dn;
  }

  public static function buildProduits() {
    global $db;
    $query = "
      SELECT
      a.id,
      a.id_ita,
      a.id_as400, a.libelle, a.gencode, a.actif, a.statut,
      CASE sous_famille_acd
        WHEN 'U001' THEN 1
        WHEN 'U002' THEN 1
        WHEN 'U003' THEN 1
        ELSE 0
      END as  is_plv,
      rt.tarif as tarif,
      r1.libelle as famille,
      r3.libelle as type_article,
      r4.libelle as marque,
      r5.libelle as code_tva,
      r6.libelle as gamme,
      IF(a.zparm_pcb,a.zparm_pcb,1) as pcb,
      rai.details as details,
      rai.avantages as avantages,
      ras.stock,
      rasw.id_switch,
      rasw.seuil
      FROM
        ref_article a
        LEFT JOIN ref_tarif rt ON rt.code_article = a.id_as400
        LEFT JOIN ref_article_stock ras ON ras.id_as400 = a.id_as400
        LEFT JOIN ref_article_switch rasw ON rasw.id_as400 = a.id_as400
        LEFT JOIN referentiels r1 ON r1.valeur = a.code_famille AND r1.nature = 'FAMA'
        LEFT JOIN referentiels r2 ON r2.valeur = a.sous_famille AND r2.nature = 'SFAMA'
        LEFT JOIN referentiels r3 ON r3.valeur = a.type_article AND r3.nature = 'TYPA'
        LEFT JOIN referentiels r4 ON r4.valeur = a.code_marque AND r4.nature = 'CMAR'
        LEFT JOIN referentiels r5 ON r5.valeur = a.code_tva AND r5.nature = 'TVA'
        LEFT JOIN referentiels r6 ON r6.valeur = a.gamme AND r6.nature = 'FAMS'
        LEFT JOIN ref_article_infos rai ON a.id_as400 = rai.id_as400
      WHERE
        a.deleted = 0
        AND gencode > 0
        AND COALESCE(a.retour_autorise, 0) = 0
        AND LENGTH(a.libelle) > 3
    "; //AND rt.tarif > 0
    $db->execute($query);
    $produits = $db->getArray();

    foreach( $produits as $k=>$e ) {
      $produits[$k]['pcb'] = intval($e['pcb']);
      if( floatval($e['tarif']) == 0 ) $produits[$k]['tarif'] = 0;
    }

    /*
    echo '<pre>';
    print_r($produits);
    exit;
    */

    return $produits;
  }
  public static function buildProduitsJuva() {
    global $db;
    $produitsJuva = [];
    $query = "
        SELECT
        idoriginal AS id,
        gencod AS gencode,
        libelle,
        ordre,
        datedebutdisponibilite,
        datefindisponibilite,
        pcb,
        souspcb,
        longueur,
        largeur,
        hauteur,
        profondeur,
        poids,
        contenance,
        pvc,
        prix as tarif,
        nouveaute,
        incontournable
        FROM juva_produit
        ORDER BY ordre ASC
    ";

    $db->execute($query);
    while ($row = $db->assoc()) {
      $produitsJuva[] = $row;  // Ajoute chaque ligne au tableau
    }
    return $produitsJuva;
}


 
  public static function buildNews() {
    $news = [];
    $all = news::getAll();
    foreach( $all as $k=>$e ) {
      if( $e['published'] == 0 ) continue;
      $news[] = [
        "id" => $e['id'],
        "titre" => $e['titre'],
        "contenu" => $e['contenu'],
        "desc" => news::getDesc($e['contenu'],80),
        "date" => core::dateOutput($e['date_publication']),
        "auteur" => user::getNameFromId($e['createur']),
        "photo" => core::getPublicFileLink($e['id_photo'])
      ];
    }
    return $news;
  }

  public static function buildPromos() {
    $promos = [];
    $all = promo::getAll();
    foreach( $all as $k=>$e ) {
      if( $e['actif'] == 0 ) continue;
      $promos[] = [
        "id_as400" => $e['id_as400'],
        "libelle" => $e['libelle']
      ];
    }
    return $promos;
  }

  public static function getMarques() {
    $marques = [];
    global $db;
    $db->execute('SELECT DISTINCT * FROM referentiels WHERE nature = "CMAR" AND libelle != "annulé" ORDER BY libelle');
    while( $r = $db->assoc() ) {
      $marques[$r['valeur']] = [
        "id" => $r['valeur'],
        "libelle" => $r['libelle']
      ];
    }
    return $marques;
  }

  public static function getGammes( $marques ) {
    $gammes = [];
    global $db;

    $g = [];
    $db->execute("SELECT valeur,libelle FROM referentiels WHERE nature LIKE 'FAMS' ORDER BY libelle");
    while( $r = $db->assoc() ) {
      $g[ $r['valeur'] ] = strtoupper($r['libelle']);
    }

    foreach( $marques as $id => $m ) {
      $gammes[$id] = [];
      $db->execute("SELECT DISTINCT gamme FROM ref_article WHERE code_marque = '$id' ");
      while( $r = $db->assoc() ) {
        $gammes[$id][$r['gamme']] = $g[$r['gamme']];
      }
    }
    return $gammes;
  }

  public static function getConcurence() {
    global $db;
    $concu = ["m" => [],"g" => []];
    $db->execute("SELECT id,libelle FROM ref_concurence_marque WHERE visible = 1 AND deleted = 0");
    while( $r = $db->assoc() ) $concu['m'][$r['id']] = $r['libelle'];
    $db->execute("SELECT id,libelle FROM ref_concurence_gamme WHERE visible = 1 AND deleted = 0");
    while( $r = $db->assoc() ) $concu['g'][$r['id']] = $r['libelle'];
    return $concu;
  }

  public static function getOptions() {
    global $db;
    $db->execute("SELECT * FROM apk_select_options");
    $options = [];
    while( $r = $db->assoc() ) {
      if( !isset($options[$r['type']])) $options[$r['type']] = [];
      $options[$r['type']][$r['id']] = $r['libelle'];
    }
    /* PEM Articles */
    $options['ARTICLE_PEM'] = [];
    $db->execute("SELECT * FROM pem_article WHERE actif = 1 AND deleted = 0 ORDER BY libelle");
    while( $r = $db->assoc() ) {
      $options['ARTICLE_PEM'][$r['id']] = $r['id_as400'].' - '.$r['libelle'];
    }

    return $options;
  }
  

  //partie juvamine
  public static function getMarquesJuva() {
    global $db;
    $db->execute("SELECT id, libelle FROM ref_marques_juva WHERE actif = 1 ORDER BY libelle");
    $marquesJuva = [];
    while ($row = $db->assoc()) {
      $marquesJuva[] = $row;
    }
    return $marquesJuva;
  }
  
  // public static function getGammesJuva() {
  //   global $db;
  
  //   // 1. Récupérer tous les libellés de gammes actives (id => libelle en MAJ)
  //   $g = [];
  //   $db->execute("SELECT id, libelle FROM ref_gammes_juva WHERE actif = 1 ORDER BY libelle");
  //   while ($r = $db->assoc()) {
  //     $g[$r['id']] = strtoupper($r['libelle']);
  //   }
  
  //   // 2. Construire l’arborescence des gammes par marque
  //   $gammesJuva = [];
  //   $db->execute("SELECT id AS id_gamme, id_marque FROM ref_gammes_juva WHERE actif = 1");
  //   while ($r = $db->assoc()) {
  //     $id_marque = $r['id_marque'];
  //     $id_gamme = $r['id_gamme'];
  
  //     if (!isset($gammesJuva[$id_marque])) {
  //       $gammesJuva[$id_marque] = [];
  //     }
  
  //     if (isset($g[$id_gamme])) {
  //       $gammesJuva[$id_marque][$id_gamme] = $g[$id_gamme];
  //     }
  //   }
  
  //   return $gammesJuva;
  // }
  
  
  //juvamine duplication dn de abc
  // Partie juvamine - suite

public static function getConcurenceJuva() {
  global $db;
  $concuJuva = ["m" => [], "g" => []];
  $db->execute("SELECT id, libelle FROM ref_concurence_marque_juva WHERE visible = 1 AND deleted = 0");
  while ($r = $db->assoc()) {
      $concuJuva['m'][$r['id']] = $r['libelle'];
  }
  $db->execute("SELECT id, libelle FROM ref_concurence_gamme_juva WHERE visible = 1 AND deleted = 0");
  while ($r = $db->assoc()) {
      $concuJuva['g'][$r['id']] = $r['libelle'];
  }
  return $concuJuva;
}

public static function getLastDnJuva($id_as400) {
  global $db;
  $dnJuva = ["abc" => [], "concu" => []];
  $db->execute("SELECT id FROM visite WHERE id_client = '".$id_as400."' AND pem = 0 ORDER BY id DESC");
  $ids = [];
  while ($r = $db->assoc()) {
      $ids[] = $r['id'];
  }

  foreach ($ids as $id) {
      $db->execute("SELECT * FROM visite_dn_juva WHERE id_visite = $id");
      if ($db->num()) {
          while ($r = $db->assoc()) {
              $key = ($r['type'] == "ABC" ? "abc" : "concu");
              $dnJuva[$key][] = [
                  "ma" => $r['marque'],
                  "ga" => $r['gamme'],
                  "me" => $r['metrage']
              ];
          }
          break; // une fois qu'on a trouvé, on arrête
      }
  }

  return $dnJuva;
}



}



 ?>
