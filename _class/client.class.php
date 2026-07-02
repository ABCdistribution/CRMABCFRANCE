<?php

class client {

  public static function get( $id ) {
    if( !$id ) return false;
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM ref_client WHERE id = $id AND deleted = 0 AND actif = 1");
    $client = $db->getLine();
    if( !$client  ) return false;
    $client['commercial_1'] = $client['id_commercial_1'] ? ref::getReferentielValue( 'REPR', $client['id_commercial_1'] ) : "";
    $client['commercial_2'] = $client['id_commercial_2'] ? ref::getReferentielValue( 'REPR', $client['id_commercial_2'] ) : "";
    $client['centrales'] = self::getClientCentrales( $client['id_as400'] );

    $client['infos'] = self::getInfos( $client['id_as400'] );

    $client['contacts'] = [];
    $db->execute("SELECT * FROM ref_client_contact WHERE id_ref_client = '".$client['id_as400']."' AND deleted = 0 ");
    while( $r = $db->assoc() )
      $client['contacts'][] = $r;

    return $client;
  }

  public static function getByCode( $code, $forceInt = false ) {
    global $db;
    $f = 'id_as400';
    if( $forceInt ) $f = ' CAST(id_as400 as UNSIGNED)';
    $db->execute("SELECT id FROM ref_client WHERE $f LIKE '%".$db->escape($code)."%' ");
    return $db->num() ? self::get($db->assoc()['id']) : false;
  }

  public static function getInfos( $id_as400 ) {
    global $db;
    $q = "SELECT * FROM ref_client_infos WHERE id_ref_client = '".$db->escape($id_as400)."' ";
    $db->execute($q);
    if( !$db->num() ) {
      $db->execute("INSERT INTO ref_client_infos (id_ref_client) VALUES ('".$db->escape($id_as400)."') ");
      $db->execute($q);
    }
    return $db->assoc();
  }


  public static function getClientCentrales( $code ) {
    $c = [
      "centrale" => [ "libelle" => "" , "code" => "" ],
      "scentrale" => [ "libelle" => "" , "code" => "" ],
      "sscentrale" => [ "libelle" => "" , "code" => "" ]
    ];
    global $db;
    $db->execute("SELECT * FROM ref_centrale WHERE code_client_cmd = '".$db->escape($code)."' ");
    if( !$db->num() ) {
      return $c;
    }
    $datas = $db->assoc();
    $c["sscentrale"] = [ "libelle" => $datas['sscentrale'] , "code" => $datas['code_sscentrale'] ];
    $c["scentrale"] = [ "libelle" => $datas['scentrale'] , "code" => $datas['code_scentrale'] ];
    $c["centrale"] = [ "libelle" => $datas['centrale'] , "code" => $datas['code_centrale'] ];
    return $c;
  }
  public static function getCentrale( $code ) {
    global $db;
    $db->execute("SELECT * FROM ref_centrale WHERE code = '".$db->escape($code)."' ");
    return $db->num() ? $db->assoc() : false;
  }

  public static function create() {
    global $db;
    if( !isset($_POST['name']) || $_POST['name'] == "" )
      core::aError("Aucun nom de client reçu");
    $name = $db->escape(strtoupper(trim($_POST['name'])));
    if( mb_strlen($name) > 100 || mb_strlen($name) < 4 )
      core::aError("Le nom du client doit contenir entre 5 et 100 caractères");

    $db->execute("INSERT INTO ref_client (enseigne,raison_sociale,id_createur) VALUES ('$name','$name',".ID.")");

    die('{ "id" : "'.$db->lastId().'" }');
  }

  public static function getAllPeriodicite() {
    global $db, $memPeriod;
    if( isset($memPeriod) ) return $memPeriod;
    $db->execute("SELECT * FROM periodicite");
    $datas = $db->getArray();
    foreach( $datas as $k=>$e ) {
      $datas[$k]['libelle'] = l("periodicite-$k");
    }
    $memPeriod = $datas;
    return $datas;
  }

  public static function getPeriodicite( $id_as400, $id_periodicite = 0 ) {
    global $db, $memoryPeriodicite;
    $ap = self::getAllPeriodicite();
    if( !isset($memoryPeriodicite[$id_periodicite])) {
      $db->execute("SELECT * FROM ref_client_periodicite WHERE id_client_as400 = '".$db->escape($id_as400)."' AND deleted = 0 ORDER BY id DESC LIMIT 1");
      if( !$db->num() ) return [];
      $datas = $db->assoc();
      $id_periodicite = $datas['id_periodicite'];
      $datas['libelle'] = $ap[$datas['id_periodicite']]['libelle'];
      $memoryPeriodicite[$id_periodicite] = $datas;
    }    
    return $memoryPeriodicite[$id_periodicite];
  }
  public static function addPeriodicite() {
    $id = $_POST['id'];
    $v = intval($_POST['v']);
    global $db;
    $db->execute("
      INSERT INTO ref_client_periodicite
      (id_client_as400,id_periodicite,id_user)
      VALUES
      ('".$db->escape($id)."',$v,".ID.")
    ");
    die('{}');
  }

  public static function saveDirecteurRegional() {
    global $db;
    $id_as400 = $_POST['id'] ?? '';
    $id_user = intval($_POST['v'] ?? 0);
    if (!$id_as400) core::aError();

    self::getInfos($id_as400);

    if ($id_user > 0) {
      $user = user::exist($id_user);
      if (!$user || !$user['actif'] || $user['deleted'] || !in_array(intval($user['id_profile']), stats::$PROFILE_REGION)) {
        core::aError();
      }
    }

    $val = $id_user > 0 ? $id_user : 'NULL';
    $db->execute("
      UPDATE ref_client_infos
      SET id_user_dr = $val
      WHERE id_ref_client = '".$db->escape($id_as400)."'
    ");
    die('{}');
  }

  public static function addNoteFromApk( $params ) {
    global $db;

    if( !isset($params['id_apk']) ) return;
    $db->execute("SELECT id FROM ref_client_remarque WHERE id_apk = '".$db->escape($params['id_apk'])."' AND deleted = 0");
    if( $db->num() ) return;

    $db->execute("
      INSERT INTO ref_client_remarque 
      (id_repr,id_as400,remarque,id_apk)
      VALUES
      (
        '".$db->escape( $params['id_repr'] ?? 0 )."',
        '".$db->escape( $params['id_client'] ?? 0 )."',
        '".$db->escape( $params['remarque'] ?? "" )."',
        '".$db->escape( $params['id_apk'] ?? "" )."'
      )
    ");
    return;
  }

  public static function addContact( $params ) {
    global $db;

    if( !isset($params['id_client']) )
      api::aError("Impossible d'ajouter le client sans id de client");
    
    $client = self::getByCode( $params['id_client'] );
    if( !$client )
      api::aError("Client introuvable");

    if( count($client['contacts']) > 0 ) {
      foreach( $client['contacts'] as $k=>$e ) {
        if( $e['nom'] == $params['nom'] && $e['prenom'] == $params['prenom'] ) {
          return true;
        }
      }
    }

    $id_user = defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : ID;

    $db->execute("
      INSERT INTO ref_client_contact
      (id_ref_client,id_user,prenom,nom,poste,fixe,portable,mail)
      VALUES
      (
        '".$db->escape($params['id_client'])."',
        $id_user,
        '".$db->escape($params['prenom'])."',
        '".$db->escape($params['nom'])."',
        '".$db->escape($params['poste'])."',
        '".$db->escape($params['fixe'])."',
        '".$db->escape($params['portable'])."',
        '".$db->escape($params['mail'])."'
      )
    ");


    return true;
  }

  public static function editContact( $params ) {
    global $db;
    $id = intval($params['id']??0);
    $db->execute("SELECT * FROM ref_client_contact WHERE id = $id ");
    if( !$db->num() ) die('{}');
    $db->execute("
      UPDATE ref_client_contact
      SET
        prenom = '".e($params['prenom'])."',
        nom = '".e($params['nom'])."',
        poste = '".e($params['poste'])."',
        fixe = '".e($params['fixe'])."',
        portable = '".e($params['portable'])."',
        mail = '".e($params['mail'])."'
      WHERE
        id = $id
    ");
    return true;
  }

  public static function deleteContact( $params ) {
    if( !isset($params['id_client']) ) api::aError("Client introuvable");
    if( !isset($params['key']) ) api::aError("Clé de supression introuvable");

    global $db;
    $db->execute("
      SELECT * FROM ref_client_contact
      WHERE
        id_ref_client = '".$db->escape($params['id_client'])."'
        AND CONCAT(nom,portable,fixe,poste) = '".$db->escape($params['key'])."'
    ");

    if( !$db->num() ) api::aError("Contact introuvable");
    $datas = $db->assoc();
    $db->execute("UPDATE ref_client_contact SET deleted = 1 WHERE id = ".$datas['id']);

    api::ajaxRep([]);
  }

  public static function saveInfosSupp() {
    global $db;

    $q = "
      UPDATE ref_client_infos
      SET
        type_cmd = '".$db->escape($_POST['type_cmd']??'')."',
        cli_avant_ouverture = '".$db->escape($_POST['cli_avant_ouverture']??'')."',
        flash = '".$db->escape($_POST['flash']??'')."',
        cmd_labell = '".$db->escape($_POST['cmd_labell']??'')."',
        chaussures_secu = '".$db->escape($_POST['chaussures_secu']??'')."',
        attestation = '".$db->escape($_POST['attestation']??'')."',
        cni = '".$db->escape($_POST['cni']??'')."',
        num_juva = '".$db->escape($_POST['num_juva']??'')."'
      WHERE
        id_ref_client = '".$db->escape($_POST['id_as400'])."'
    ";

    $db->execute($q);
    die('{}');
  }

  public static function updateInfoSup( $params ) {
    $id = $params['id'];
    $field = $params['field'];
    $val = $params['val'];

    if( $val == "OUI" ) $val = 1;
    else if( $val == "NON" ) $val = 2;
    else $val = strtolower($val);

    global $db;
    $db->execute("UPDATE ref_client_infos SET $field = '".$db->escape($val)."' WHERE id_ref_client = '".$db->escape($id)."' ");
    api::ajaxRep([]);
  }

  public static function getStatsClient( $params ) {
    global $db;
    $datas = ["t" => 1,"c"=>2,"v"=>3];

    $id_client = $params['id'];
    $from = core::dateInput($params['from']);
    $to = core::dateInput($params['to']);

    $no_fac = $no_cmd = [];
    $db->execute("
      SELECT no_facture,no_commande,montant_facture,facture_avoir  
      FROM ref_facture 
      WHERE 
        id_client_cmd = '".$id_client."' 
        AND facture_avoir = 'F' 
        AND CONCAT(annee_facture,'-',mois_facture,'-',jour_facture) BETWEEN '$from' AND '$to'
    ");
    while( $r = $db->assoc() ) {
      if( in_array($r['no_facture'],$no_fac) ) continue;
      $sum = ( $r['facture_avoir'] == 'F' ? floatval($r['montant_facture']) : floatval(- $r['montant_facture']) );
      $datas["t"] += floatval($sum);
      $no_fac[] = $r['no_facture'];
      $no_cmd[$r['no_commande']] = "";
    }
    $datas["t"] = strval($datas["t"]);
    $datas['c'] = count(array_keys($no_cmd));
    $db->execute("
      SELECT count(*) as nb 
      FROM visite 
      WHERE 
        id_client = '".$id_client."' 
        AND ( queue_date BETWEEN '$from' AND '$to' )
      ");
    $datas["v"] = $db->assoc()['nb'];




    api::ajaxRep($datas);
  }

  public static function printPlannification( $id_as400 ) {
    global $db;
    $db->execute("SELECT * FROM plannification WHERE id_client = '".$id_as400."' AND rec > 0 AND deleted = 0");
    if( !$db->num() ) return '<p class="text-center text-secondary">'.l('page-client-infos-no-plannification').'</p>';
    $pl = $db->assoc();
    $u = $pl['id_repr'] ? user::getFromIdRepr($pl['id_repr']) : ["displayname" => ""];

    $days = [
      1 => l("date-lundi"),
      2 => l("date-mardi"),
      3 => l("date-mercredi"),
      4 => l("date-jeudi"),
      5 => l("date-vendredi"),
    ];
    $d = [];
    $tmp = explode(",",$pl['days']);
    foreach( $tmp as $e ) $d[] = $days[$e];
    $content = [];
    $content[] = '<p class="text-primary">';
    $content[] = l('page-client-infos-recursivite')." <strong>".$pl['rec']." ".l("date-semaine")."(s)</strong><br/>";
    $content[] = "<strong>".implode(",",$d)."</strong><br/>";
    $content[] = "<em>".l("cree-par")." : ".$u['displayname'].'</em>';
    $content[] = '</p>';
    return implode($content);
  }

  public static function getPortefeuilleClients( $user ) {
    global $db; 
    $id_repr = stats::getMyPromoteurs();
    $id_reprs = implode(",",$id_repr);

    $allCa = stats::getAllCaClients();


    $db->execute("SELECT id_as400 FROM stat_delta_ca_client");
    $clientsList = [];
    while( $r = $db->assoc() ) $clientsList[] = $r['id_as400'];


    $w = 'AND ( CAST(a.id_commercial_1 as UNSIGNED) IN ('.$id_reprs.') OR CAST(a.id_commercial_2 as UNSIGNED) IN ('.$id_reprs.') )';
    if( $id_repr < 1 ) $w = "";

    $q1 = "
      SELECT 
        id_as400, enseigne 
      FROM 
        ref_client a
      WHERE
        a.deleted = 0
        $w
        AND a.pays IN ('FR','BE','LU','AD')
        AND LENGTH(a.enseigne)  > 3
        AND ( statut_commande_par = 'O' OR statut_livre = 'O' )
      ORDER BY
        a.enseigne
    ";

    $db->execute($q1);

    /*

        AND id_commercial_2 NOT IN ('003','910') 
        AND id_commercial_1 NOT IN ('003','910')

    */

    $datas = $db->get();
    $clients = [];
    foreach( $datas as $k=>$e ) {
      

      if( !in_array(intval($e['id_as400']),$clientsList) ) {
        unset($datas[$k]);
        continue;
      }

      $ca_n1 = 0;
      $ca_n = 0;
      $evol = 0;
      foreach( $allCa as $a ) {
        if( $a['id_as400'] == $e['id_as400'] ) {
          $ca_n1 = $a['ca_an_passe'];
          $ca_n = $a['ca'];
          $evol = $a['p'];
          break;
        }
      }

      if( $ca_n1 == 0 && $ca_n == 0 ) {
        unset($datas[$k]);
        continue;
      }

      $clients[] = [
        "client" => $e['enseigne'],
        "id_as400" => $e['id_as400'],
        "ca_n1" => round($ca_n1),
        "ca_n" => round($ca_n),
        "evol" => round($evol)
      ];
    }


    


    api::ajaxRep(["clients" => $clients]);
  }

  public static function getClient( $id_as400 ) {
    if( !$id_as400 || $id_as400 == "" ) api::aError("Aucun identifiant client recu");
    global $db;
    $db->execute("
      SELECT 
         id_as400,
         enseigne,
         ean_client,
         adresse1,
         adresse2
         adresse3,
         code_postal,
         ville,
         tel1,tel2,
         id_commercial_1,
         id_commercial_2
      FROM 
        ref_client 
      WHERE 
        id_as400 = '".e($id_as400)."' 
    ");
    if( !$db->num() ) api::aError("Client introuvable");
    $client = $db->getLine();
    $db->execute("SELECT * FROM ref_client_contact WHERE id_ref_client = '".e($id_as400)."' AND deleted = 0");
    $client['contacts'] = $db->num() ? $db->get() : [];
    $client['photo'] = 'https://picsum.photos/400/300?random=1';

    // Commerciaux
    $client['user1'] = null;
    if( $client['id_commercial_1'] > 0  ) 
      $client['user1'] = user::getFromIdRepr($client['id_commercial_1'])['displayname'];

    $client['user2'] = null;
    if( $client['id_commercial_2'] > 0 && $client['id_commercial_2'] != $client['id_commercial_1'] ) 
      $client['user2'] = user::getFromIdRepr($client['id_commercial_2'])['displayname'];

    $client['infos'] = [];

    # Infos sup
    $db->execute("SELECT id_periodicite FROM ref_client_periodicite WHERE id_client_as400 = '".e($id_as400)."' ");
    $client['infos']['id_periodicite'] = $db->num() ? $db->getLine()['id_periodicite'] : null;
    $client['infos']['periodicite'] = $client['infos']['id_periodicite'] ? ref::get('periodicite',$client['infos']['id_periodicite']) : null;

    $db->execute("SELECT * FROM tournee WHERE id_as400 = '".e($id_as400)."' AND deleted = 0");
    $client['infos']['tournee'] = $db->getLine() ?? false;

    
    $db->execute("SELECT * FROM ref_client_infos WHERE id_ref_client = '".e($id_as400)."'");
    $client['infos']['details'] = $db->getLine() ?? false;

    foreach( $client['infos']['details'] as $k=>$e ) {
      if( $e == "0" || trim($e) == "" || $e == null ) $client['infos']['details'][$k] = "?";
      if( $e == "1" ) $client['infos']['details'][$k] = "Oui";
      if( $e == "2" ) $client['infos']['details'][$k] = "Non";
    }

    $client['dn'] = deportedFiles::getLastDn($id_as400);

    // Classement CA
    $id_repr = stats::getMyPromoteurs();
    $a = stats::formatIdRepr($id_repr);
    $a = str_replace("id_repr","id_commercial_1",$a);
    $db->execute("SELECT * FROM stat_delta_ca_client WHERE id_as400 IN (
      SELECT id_as400 FROM ref_client WHERE (".$a.") 
    ) ORDER BY ca DESC");
    $raw = $db->getArray();
    $cp = 0;
    $client['classement'] = 0;
    foreach( $raw as $row ) {
      $cp++;
      if( $row['id_as400'] == intval($client['id_as400']) ) {
        $client['classement'] = $cp;
        break;
      }
    }

    api::ajaxRep(["client" => $client]);
  }

  public static function getTopVentesClient( $id_as400 ) {
    global $db;
    $d = date('Y-m-d',strtotime("-3 months"));
    $db->execute("
      SELECT 
        a.code_article,
        SUM(CAST(a.quantite AS UNSIGNED)) as qte,
        SUM(CAST(a.montant AS UNSIGNED)) as total,
        p.libelle
      FROM 
        `commandes_as400` a 
        LEFT JOIN ref_article p ON a.code_article = p.id_as400
      WHERE 
        a.code_client_cmd = '".e($id_as400)."' 
        AND a.checky = '0000'
        AND STR_TO_DATE(CONCAT(annee_annul,'-',LPAD(mois_annul,2,'00'),'-',LPAD(jour_annul,2,'00')), '%Y-%m-%d') >= '".$d."'
      GROUP BY 
        a.code_article 
      ORDER BY 
        total DESC 
      LIMIT 10
    ");
    api::ajaxRep(["top" => $db->get(),"client" => $id_as400 ]);
  }

  public static function editNoteFromApk( $params ) {
    global $db;
    $db->execute("UPDATE ref_client_remarque SET remarque = '".e($params['remarque'])."' WHERE id_apk = '".e($params['id_apk'])."' ");
    return true;
  }
  public static function deleteNoteFromApk( $params ) {
    global $db;
    $db->execute("DELETE FROM ref_client_remarque WHERE id_apk = '".e($params['id_apk'])."' ");
    return true;
  }


}
