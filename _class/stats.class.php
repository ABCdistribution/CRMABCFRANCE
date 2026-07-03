<?php

class stats {

  public static $PROFILE_PROMOTEUR = [1];
  public static $PROFILE_REGION = [6];
  public static $PROFILE_CS = [4];
  public static $PROFILE_DIRECTION = [5,2,7,8];

  public static function isPromoteur( $id_profile = null ) {
    if( $id_profile == null && !defined('ID_PROFILE') ) return false;
    if( !$id_profile ) $id_profile = ID_PROFILE;
    return in_array($id_profile, self::$PROFILE_PROMOTEUR);
  }
  public static function isRegion( $id_profile = null ) {
    if( $id_profile == null && !defined('ID_PROFILE') ) return false;
    if( !$id_profile ) $id_profile = ID_PROFILE;
    return in_array($id_profile, self::$PROFILE_REGION);
  }
  public static function isChefSecteur( $id_profile = null ) {
    if( $id_profile == null && !defined('ID_PROFILE') ) return false;
    if( !$id_profile ) $id_profile = ID_PROFILE;
    return in_array($id_profile, self::$PROFILE_CS);
  }
  public static function isDirection( $id_profile = null ) {
    if( $id_profile == null && !defined('ID_PROFILE') ) return false;
    if( !$id_profile ) $id_profile = ID_PROFILE;
    return in_array($id_profile, self::$PROFILE_DIRECTION);
  }

  public static function formatDate( $date ) {
    if( strpos($date,"/") > -1 ) {
      if( strpos($date," ") > -1 ) {
        $tmp = explode(" ",$date);
        $date = $tmp[0];
      }
      $tmp = explode("/",$date);
      $date = implode("-",array_reverse($tmp));
    }
    $tmp = explode(" ",$date);
    foreach( $tmp as $e ) {
      if( strpos($e,"-") > -1 )
        return $e;
    }
    return $date;
  }

  public static function response( $rez ) {
    die( json_encode($rez) );
  }

  public static function getStats() {
    if( !isset($_POST['call']) ) core::aError("call non recu");
    $c = trim($_POST['call']);
    if( !method_exists(new stats(),$c) ) core::aError("Méthode $c non trouvée");
    self::$c($_POST);
  }
  
  public static function getListeRegion() {
    global $db;
    $regions = [];
    $db->execute("SELECT DISTINCT secteur, id FROM user WHERE deleted = 0 AND secteur <> '' AND actif = 1 ORDER BY secteur");
    while( $r = $db->assoc() ) $regions[$r['secteur']] = $r['secteur'];
    return $regions;
  }

  public static function getAlertesCom( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $where_region = ($params['fp_region'] != '') ? " AND u.secteur = '".$params['fp_region']."'" : "";
    $db->execute("
    SELECT
      u.displayname AS '".l('stat-promoteur')."', 
      u.secteur AS '".l('stat-region')."',
      cli.id_as400 AS '".l("stats-code-client")."',
      cli.enseigne AS '".l("stats-magasin")."',
      v.id AS 'Visite',
      v.alerte_raison AS 'Raisons',
      v.alerte_obs AS 'Observations',
      v.queue_date AS '".l("stats-date")."'
    FROM
      visite v
    LEFT JOIN user u on v.id_user = u.id
    LEFT JOIN ref_client cli on cli.id_as400 = v.id_client
    WHERE
       v.deleted = 0
       AND alerte_raison > 0
      AND v.queue_date >= '$from'
      AND v.queue_date < '$to'
      ".$where_region."
      ORDER BY v.queue_date DESC
    ");

    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $rez['datas'][] = $r;
    }

    foreach( $rez['datas'] as $key => $val ) {
      $raisons = '';
      if($rez['datas'][$key]['Raisons'] > 0){
        $db->execute("SELECT libelle FROM apk_select_options WHERE id IN (".$rez['datas'][$key]['Raisons'].")");
        while( $r_libelle = $db->assoc() ) {
          if($raisons != '') $raisons .= ', ';
          $raisons .= $r_libelle['libelle'];
        }    
      }
      $rez['datas'][$key]['Raisons'] = $raisons;
    }

    self::response($rez);
  }

  public static function getVisiteParPromoteurs( $params ) {
    global $db;
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $q = "
    SELECT 
      u.displayname as '".l('stat-promoteur')."', 
      u.secteur as '".l('stat-region')."',
      count(*) as '".l('stat-total-visite')."',
      v.id_user
    FROM visite v
    LEFT JOIN user u ON v.id_user = u.id
    WHERE
      v.deleted = 0
      AND v.pem = 0
      AND v.queue_date >= '$from'
      AND v.queue_date < '$to'
      AND u.actif = 1
    GROUP BY v.id_user
    ORDER BY `".l('stat-total-visite')."` DESC
    ";
    $q = trim(preg_replace('/\s\s+/', ' ', $q));
    $db->execute($q);
    $rez = ["datas" => [], "query" => $db->query];
    $count = 0;
    $prev = 0;
    $total = 0;
    $ids = [0];
    while( $r = $db->assoc() ) {
      if( trim($r[l('stat-region')]) == "" ) $r[l('stat-region')] = "-";
      if( $prev != $r[l('stat-total-visite')] || $prev == 0 ) {
        $prev = $r[l('stat-total-visite')];
        $count++;
      }
      if( $r['id_user'] > 0 ) $ids[] = $r['id_user'];
      $r[l('stat-classement')] = $count;
      $rez["datas"][] = $r;
      $total += $r[l('stat-total-visite')];
    }
    
    $db->execute("
      SELECT 
        u.displayname as '".l('stat-promoteur')."', 
        u.secteur as '".l('stat-region')."',
        0 as '".l('stat-total-visite')."',
        u.id as id_user
      FROM user u 
      WHERE
        id NOT IN (".implode(",",$ids).") 
        AND id_profile = 1
        AND u.displayname IS NOT NULL
        AND u.actif = 1
        AND deleted = 0
    ");
    while( $r = $db->assoc() ) {
      $r[l('stat-classement')] = "-";
      $rez["datas"][] = $r;
    }



    foreach( $rez['datas'] as $k=>$e ) {
      $id_user = $e['id_user'];
      $user = user::exist($id_user);
      $login = $user['login'];
      unset($rez['datas'][$k]['id_user']);


      $db->execute("
        SELECT count(*) as nb FROM planning
        WHERE
          id_repr = '".$user['id_repr']."'
          AND date_passage >= '$from'
          AND date_passage < '$to'          
          AND deleted = 0
      ");
      $rez['datas'][$k][l('stat-visites-prevues')] = $db->assoc()['nb'];

      $db->execute("
        SELECT count(*) as nb FROM visite
        WHERE
          id_user  = '".$id_user."'
          AND pem = 0
          AND queue_date >= '$from'
          AND queue_date < '$to'    
          AND no_cmd_reason = 6      
          AND deleted = 0
      ");
      $rez['datas'][$k][l('stat-rayons-pleins')] = $db->assoc()['nb'];


      $db->execute("
      SELECT 
        count(*) as nb
      FROM commande_apk cmd
          LEFT JOIN user u ON u.login = '$login'
      WHERE
        cmd.deleted = 0
        AND cmd.queue_date >= '$from'
        AND cmd.queue_date < '$to'
        AND cmd.user = '$login'
        AND u.actif = 1
      ");
      $rez['datas'][$k][l('stat-total-commande')] = $db->assoc()['nb'];


      
      

      $count = $e[l('stat-classement')];
      unset($rez['datas'][$k][l('stat-classement')]);
      $rez['datas'][$k][l('stat-classement')] = $count;

      $rez['datas'][$k]['%'] = $total > 0 ? number_format($e[l('stat-total-visite')] * 100 / $total,2,","," ")."%" : "0%";

    }

    self::response($rez);
  }




  
  public static function getCommandesParPromoteurs( $params ) {
    global $db;
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $rez = ['datas' => []];
    $totalCommandes = 0;

    $users = [];
    $secteurs = [];
    $db->execute("
      SELECT 
        id,displayname,id_repr,secteur 
      FROM 
        user 
      WHERE 
        actif = 1 
        AND deleted = 0 
        AND id_profile = 1
      ORDER BY displayname
    ");
    while( $r = $db->assoc() ) {
      $users[$r['id']] = [
        "promoteur" => $r['displayname'],
        "secteur" => $r['secteur'],
        "id_repr" => $r['id_repr'],
        "total_visites" => 0,
        "visites_prevues" => 0,
        "rayon_plein" => 0,
        "cde_crm" => 0,
        "cde_edi" => 0,
        "cde_mail" => 0,
        "total_crm" => 0,
        "total_edi" => 0,
        "total_mail" => 0,
        "total" => 0
      ];
      if( !in_array($r['secteur'],$secteurs) )
        $secteurs[] = $r['secteur'];
    }
      

    /* Total visites par promoteur + rayon plein */
    $db->execute("
      SELECT 
        id_user,
        no_cmd_reason
      FROM 
        visite v
      WHERE
        v.queue_date >= '$from'
        AND v.pem = 0
        AND v.queue_date <= '$to'
        AND v.deleted = 0
    ");
    while( $r = $db->assoc() ) {
      if( !isset($users[$r['id_user']]) ) continue;
      $users[$r['id_user']]['total_visites']++;
      if( $r['no_cmd_reason'] == 6 )
        $users[$r['id_user']]['rayon_plein']++;
    }

    /* Total visites prévues */
    foreach(  $users as $id_user => $u ) {
      $db->execute("
      SELECT count(*) as nb FROM planning
      WHERE
        id_repr = '".$u['id_repr']."'
        AND date_passage >= '$from'
        AND date_passage < '$to'          
        AND deleted = 0      
      ");
      $users[$id_user]['visites_prevues'] = $db->assoc()['nb'];
    }

    /* Récupération des Commandes CRM sur la periode*/
    $db->execute("
      SELECT 
        u.id as id_user,c.total , c.type_cmd
      FROM
        commandes_as400_total c
        LEFT JOIN user u ON c.id_repr = u.id_repr
      WHERE
        c.date_commande >= '$from'
        AND c.date_commande < '$to' 
    ");
    $commandes = [];
    while( $r = $db->assoc() )
      $commandes[] = $r;

    /* Attribution des commandes */
    foreach(  $commandes as $c ) {
      if( !isset($users[$c['id_user']]) ) continue;

      if( $c['type_cmd'] == 'C' ) {
        $users[$c['id_user']]['cde_crm']++;
        $users[$c['id_user']]['total_crm'] += $c['total'];
      }
      else if( in_array($c['type_cmd'],['A','W']) ) {
        $users[$c['id_user']]['cde_edi']++;
        $users[$c['id_user']]['total_edi'] += $c['total'];
      }    
      else if( in_array($c['type_cmd'],[2,'2']) ) {
        $users[$c['id_user']]['cde_mail']++;
        $users[$c['id_user']]['total_mail'] += $c['total'];
      }    

      $users[$c['id_user']]['total'] += $c['total'];
    }

    /* Récupération des régions */
    $tmpFrance = [
      "promoteur" => "<strong class='title t2'>Total FRANCE</strong>",
      "secteur" => "",
      "id_repr" => 0,
      "total_visites" => 0,
      "visites_prevues" => 0,
      "rayon_plein" => 0,
      "cde_crm" => 0,
      "cde_edi" => 0,
      "cde_mail" => 0,
      "total_crm" => 0,
      "total_edi" => 0,
      "total_mail" => 0,
      "total" => 0
    ]; 

    $ss = ["nord ouest","nord est","sud est","sud ouest","idf"];
    $datas = [];
    foreach( $secteurs as $s ) {
      $tmp = [
        "promoteur" => "<strong class='title'>Total ".$s."</strong>",
        "secteur" => $s,
        "id_repr" => 0,
        "total_visites" => 0,
        "visites_prevues" => 0,
        "cde_crm" => 0,
        "cde_edi" => 0,
        "cde_mail" => 0,
        "total_crm" => 0,
        "total_edi" => 0,
        "total_mail" => 0,
        "rayon_plein" => 0,
        "total" => 0
      ];      
      $add = [];
      foreach( $users as $id_user => $u ) {
        if( $u['secteur'] != $s ) continue;
        foreach( $u as $k=>$e ) {
          if( is_numeric($e) ) {
            $tmp[$k] += floatval($e);
            if( in_array(strtolower($u['secteur']),$ss ) )
              $tmpFrance[$k] += floatval($e);
          }
        }
        $add[] = $u;
      }
      if( !empty($add) ) {
        array_unshift($add,$tmp);
        array_push($datas, ...$add);
      }
    }

    array_unshift($datas,$tmpFrance);

    $totalCommandes = $tmpFrance['cde_crm']+$tmpFrance['cde_edi']+$tmpFrance['cde_mail'];

    /* Formatage */
    foreach( $datas as $k=>$e ) {

      foreach( $e as $i=>$j ) {
        if( $j === 0 || $j == "0" ) $e[$i] = "";
      }

      $tcrm = $datas[$k]['total_crm'] && $datas[$k]['total_crm'] > 0 ? number_format($datas[$k]['total_crm'],2,","," ")."€" : '';
      $tedi = $datas[$k]['total_edi'] && $datas[$k]['total_edi'] > 0 ? number_format($datas[$k]['total_edi'],2,","," ")."€" : '';
      $tmail = $datas[$k]['total_mail'] && $datas[$k]['total_mail'] > 0 ? number_format($datas[$k]['total_mail'],2,","," ")."€" : '';

      $datas[$k] = [
        l("stat-cmd-promoteur-promoteur")."" => $e['promoteur'],
        l("stat-cmd-promoteur-total-visites")."" => $e['total_visites'],
        l("stat-cmd-promoteur-visites-prevues")."" => $e['visites_prevues'],
        l("stat-cmd-promoteur-rayon-plein")."" => $e['rayon_plein'],
        l("stat-cmd-promoteur-cmd-crm")."" => $e['cde_crm'] ?? 0,
        l("stat-cmd-promoteur-cmd-edi")."" => $e['cde_edi'] ?? 0,
        l("stat-cmd-promoteur-cmd-mail")."" => $e['cde_mail'] ?? 0 ,
        l("stat-cmd-promoteur-total-crm")."" => $tcrm,
        l("stat-cmd-promoteur-total-edi")."" => $tedi,
        l("stat-cmd-promoteur-total-mail")."" => $tmail,
        l("stat-cmd-promoteur-total")."" => $datas[$k]['total'] > 0 ? number_format($datas[$k]['total'],2,","," ")."€" : ''
      ];
    }

    
    $rez = [
      'datas' => $datas,
      'commandes' => $totalCommandes,
      'users' => count($users)
    ];

    self::response($rez);
  }


  public static function getCommandesSansVisites( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $no_visit_reason = $params['no_visit_reason'];

    $raison = "";
    if( is_numeric($no_visit_reason) && $no_visit_reason != 0 ) $raison = " AND c.no_visit_reason = '".intval($no_visit_reason)."' ";
    elseif( $no_visit_reason == "autre" )  $raison = " AND ( c.no_visit_reason != '' AND c.no_visit_reason != '0' AND concat('',c.no_visit_reason * 1) != c.no_visit_reason ) ";

    $db->execute("
    SELECT
      cli.id_as400 as '".l("stats-code-client")."',
      cli.enseigne as '".l("stats-magasin")."',
      u.displayname as '".l("stats-promoteur")."',
      c.queue_date as '".l("stats-date")."',
      c.total as '".l("js-stats-valeurs")."',
      c.no_visit_reason as '".l("js-stats-choix")."'
    FROM
      commande_apk c
      LEFT JOIN ref_client cli ON c.id_magasin = cli.id_as400
      LEFT JOIN user u ON c.user = u.login
    WHERE
       c.deleted = 0
      AND c.queue_date >= '$from'
      AND c.queue_date < '$to'
      AND c.total < ".FRANCO_DE_PORT."
      AND c.no_visit = 1
      AND u.actif = 1
      $raison
      ORDER BY c.queue_date DESC
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    foreach( $rez['datas'] as $k=>$e ) {
      $rez['datas'][$k][l("js-stats-choix")] =  ( is_numeric($e[l("js-stats-choix")]) ? core::getReason($e[l("js-stats-choix")]) : l("js-stats-autre").' : <em>« '.$e[l("js-stats-choix")].' »</em>' );
    }


    self::response($rez);
  }

  public static function getVisistesSansCommandes( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $no_cmd_reason = $params['no_cmd_reason'];

    $raison = "";
    if( is_numeric($no_cmd_reason) && $no_cmd_reason != 0 ) $raison = " AND c.no_cmd_reason = '".intval($no_cmd_reason)."' ";
    elseif( $no_cmd_reason == "autre" )  $raison = " AND ( c.no_cmd_reason != '' AND c.no_cmd_reason != '0' AND concat('',c.no_cmd_reason * 1) != c.no_cmd_reason ) ";

    $db->execute("
    SELECT
      c.id as 'ID',
      cli.id_as400 as '".l("stats-code-client")."',
      cli.enseigne as '".l("stats-magasin")."',
      u.displayname as '".l("stats-promoteur")."',
      c.queue_date as '".l("stats-date")."',
      c.no_cmd_reason as '".l("js-stats-choix")."'
    FROM
      visite c
      LEFT JOIN ref_client cli ON c.id_client = cli.id_as400
      LEFT JOIN user u ON c.id_user = u.id
    WHERE
       c.deleted = 0
       AND c.pem = 0
      AND c.queue_date >= '$from'
      AND c.queue_date < '$to'
      AND c.no_cmd = 1
      AND u.actif = 1
      AND u.actif = 1
      $raison
      ORDER BY c.queue_date DESC
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    foreach( $rez['datas'] as $k=>$e ) {
      $rez['datas'][$k][l("js-stats-choix")] =  ( is_numeric($e[l("js-stats-choix")]) ? core::getReason($e[l("js-stats-choix")]) : l("js-stats-autre").' : <em>« '.$e[l("js-stats-choix")].' »</em>' );
    }


    self::response($rez);
  }

  public static function getPromosVisites( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $promo = trim($params['promo']);

    $op = "";
    if( $promo != "" ) {
      $op = " AND vp.id_as400 = '".$db->escape($promo)."' ";
    }

    $db->execute("
    SELECT
    cli.id_as400 as '".l("stats-code-client")."',
    cli.enseigne as '".l("stats-magasin")."',
    u.displayname as '".l("stats-promoteur")."',
    c.queue_date as '".l("stats-date")."',
      vp.id_as400 as '".l("stats-op")."'
    FROM
      visite c
      LEFT JOIN visite_promo vp ON c.id = vp.id_visite
      LEFT JOIN ref_client cli ON c.id_client = cli.id_as400
      LEFT JOIN user u ON c.id_user = u.id
    WHERE
       c.deleted = 0
      AND c.pem = 0
      AND c.queue_date >= '$from'
      AND c.queue_date < '$to'
      AND vp.id_as400 != ''
      AND u.actif = 1
      $op
      ORDER BY c.queue_date DESC
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    self::response($rez);
  }


  /* DN */
  public static function getConcurenceMarque() {
    global $db;
    $db->execute("SELECT libelle FROM ref_concurence_marque ORDER BY libelle");
    $datas = [];
    while( $r = $db->assoc() ) $datas[] = $r['libelle'];
    return $datas;
  }
  public static function getConcurenceGammes() {
    global $db;
    $db->execute("SELECT libelle FROM ref_concurence_gamme ORDER BY libelle");
    $datas = [];
    while( $r = $db->assoc() ) $datas[] = $r['libelle'];
    return $datas;
  }
  public static function getDnMarques() {
    $type = $_POST['type'];
    if( $type == "CONCU") {
      self::response([ "datas" => self::getConcurenceMarque() ]);
    }
    global $db;
    $db->execute('SELECT DISTINCT * FROM referentiels WHERE nature = "CMAR" AND libelle != "annulé" ORDER BY libelle');
    $datas = [];
    while( $r = $db->assoc() ) $datas[] = $r['libelle'];
    self::response([ "datas" => $datas ]);
  }

  public static function getDnGamme() {
    $marque = rawurldecode($_POST['marque']);
    if( in_array($marque,self::getConcurenceMarque())) {
      self::response([ "datas" => self::getConcurenceGammes() ]);
    }
    global $db;
    $db->execute("SELECT DISTINCT * FROM referentiels WHERE nature = 'CMAR' AND libelle = '".$db->escape($marque)."' ");
    $value = intval($db->assoc()['valeur']);
    $db->execute("SELECT distinct gamme from ref_article where code_marque = ".$value." AND gamme > 0");
    $gammes = [];
    $datas = [];
    while( $r = $db->assoc() ) $gammes[] = intval($r['gamme']);
    if( count($gammes) > 0 ) {
      $db->execute("SELECT DISTINCT * FROM referentiels WHERE nature = 'FAMI' AND valeur IN (".implode(",",$gammes).") ");
      while( $r = $db->assoc() ) $datas[] = $r['libelle'];
    }
    self::response([ "datas" => $datas ]);
  }

  public static function getDN( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $type = rawurldecode(trim($params['type']));
    $marque = trim(rawurldecode($params['marque']));
    $gamme = trim(rawurldecode($params['gamme']));

    if( $type == "" || $marque == "" ) self::response($rez);

    $where = [];
    $where[] = ' c.deleted = 0 ';
    $where[] = " c.queue_date >= '$from' ";
    $where[] = " c.queue_date < '$to' ";
    if( $type != "" ) $where[] = " type = '".$db->escape($type)."' ";
    if( $marque != "" ) $where[] = " marque = '".$db->escape($marque)."' ";
    if( $gamme != "" ) $where[] = " gamme = '".$db->escape($gamme)."' ";
    $where = implode(" AND ",$where);

    $db->execute("
    SELECT
    cli.id_as400 as '".l("stats-code-client")."',
    cli.enseigne as '".l("stats-magasin")."',
    u.displayname as '".l("stats-promoteur")."',
    c.queue_date as '".l("stats-date")."',
    vp.type as '".l("dn-type")."',
    vp.marque as '".l("dn-marque")."',
    vp.gamme as '".l("dn-gamme")."',
    vp.metrage as '".l("dn-metrage")."'
    FROM
      visite c
      LEFT JOIN visite_dn vp ON c.id = vp.id_visite
      LEFT JOIN ref_client cli ON c.id_client = cli.id_as400
      LEFT JOIN user u ON c.id_user = u.id
    WHERE
      $where
      AND c.pem = 0
      AND u.actif = 1
      ORDER BY c.queue_date DESC
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    self::response($rez);
  }



  public static function getDNPresente( $params ) {
    global $db;
    $rez = ["datas" => []];
    $type = $db->escape(rawurldecode(trim($params['type'])));
    $marque = $db->escape(trim(rawurldecode($params['marque'])));
    $gamme = $db->escape(trim(rawurldecode($params['gamme'])));

    if( $type == "" || $marque == "" ) self::response($rez);

    $id_clients_visites = [];
    $db->execute("SELECT DISTINCT id_client FROM visite WHERE deleted = 0 AND pem = 0");
    while( $r = $db->assoc() ) $id_clients_visites[] = $r['id_client'];

    $id_last_visite = [];
    foreach( $id_clients_visites as $id_client ) {
      $db->execute("SELECT id FROM visite WHERE pem = 0 AND id_client = '".$db->escape($id_client)."' ORDER BY id DESC LIMIT 1");
      if( !$db->num() ) return;
      $id_last_visite[] = $db->assoc()['id'];
    }

    $where = [];
    $where[] = ' vdn.deleted = 0 ';
    $where[] = ' v.pem = 0 ';
    $where[] = " vdn.id_visite IN (".implode(",",$id_last_visite).") ";
    if( $type != "" ) $where[] = " vdn.type = '$type' ";
    if( $marque != "" ) $where[] = " vdn.marque = '$marque' ";
    if( $gamme != "" ) $where[] = " vdn.gamme = '$gamme' ";
    $where = implode(" AND ",$where);    

    $db->execute("
      SELECT 
        cli.id_as400 as 'code client',
        cli.enseigne as magasins,
        u.displayname as promoteurs,
        v.queue_date as date,
        vdn.type,
        vdn.marque,
        vdn.gamme,
        vdn.metrage        
      FROM 
        visite_dn vdn
        LEFT JOIN visite v ON vdn.id_visite = v.id
        LEFT JOIN ref_client cli ON v.id_client = cli.id_as400
        LEFT JOIN user u ON v.id_user = u.id        
      WHERE
        $where
      ORDER BY
        cli.enseigne
    ");

    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    self::response($rez);
  }



  public static function getVisiteFaites( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);

    $db->execute("
      SELECT id,id_repr,displayname 
      FROM user 
      WHERE 
        actif = 1 
        AND id_profile = 1 
        AND deleted = 0 
        AND id_repr > 0
      ORDER BY displayname
    ");
    $datas = [];
    $raw = $db->get();
    foreach( $raw as $r ) {
      $datas[] = [
        l('stat-faites-promoteur') => $r['displayname'],
        l('stat-faites-total-visite') => 0,
        l('stat-faites-total-prevues') => 0,
        l('stat-faites-total-annulees') => 0,
        'id' => $r['id'],
        'id_repr' => $r['id_repr']
      ];
    }

    $classed = [];
    foreach( $datas as $k=>$e ) {
      $db->execute("
        SELECT count(*) as nb 
        FROM visite 
        WHERE
          queue_date >= '$from'
          AND queue_date <= '$to'
          AND id_user = ".$e['id']."
          AND deleted = 0
          AND pem = 0
      ");
      $datas[$k][l('stat-faites-total-visite')] = $db->assoc()['nb'];
      $q = "SELECT id,green
        FROM planning 
        WHERE
          date_passage >= '$from' 
          AND date_passage < '$to'
          AND id_repr = ".$e['id_repr']."
          AND deleted = 0
      ";
      $db->execute($q);
      //dd($q);
      while( $r = $db->assoc() ) {
        $datas[$k][l('stat-faites-total-prevues')]++;
        if( $r['green'] == 2 )
          $datas[$k][l('stat-faites-total-annulees')]++;
      }

      $datas[$k][l('stat-faites-rapport')] = "-";
      if( $datas[$k][l('stat-faites-total-prevues')] > 0 ) {
        $a = $datas[$k][l('stat-faites-total-visite')] * 100 / $datas[$k][l('stat-faites-total-prevues')];
        $datas[$k][l('stat-faites-rapport')] = number_format($a,2,","," ")."%";
      }

      unset($datas[$k]['id']);
      unset($datas[$k]['id_repr']);
    }

    foreach( $datas as $k=>$e ) {
      $c = str_pad($e[l('stat-faites-total-visite')],10,"0",STR_PAD_LEFT);
      $classed[$c." ".$e[l('stat-faites-promoteur')]] = $e;
    }
    krsort($classed);
    $datas = [];
    foreach( $classed as $k=>$e )
      $datas[] = $e;

    $rez['datas'] = $datas;
    $rez['u'] = count($datas);

    self::response($rez);
  }

















  public static function getCommandesVisitesSecteur( $params ) {
    global $db;
    $rez = ["datas" => []];
    $secteurs = [];
    $db->execute("SELECT DISTINCT secteur FROM user WHERE secteur != '' ");
    while( $r = $db->assoc() ) {
      $secteurs[] = $r['secteur'];
      $rez['datas'][$r['secteur']] = [];
    }

    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);

    foreach( $secteurs as $secteur ) {
      $rez['datas'][$secteur]['Secteur']  = $secteur;
      $users = [];
      $db->execute("SELECT id,login FROM user WHERE secteur = '$secteur' ");
      while( $r = $db->assoc() ) $users[$r['id']] = "'".$db->escape($r['login'])."'";

      $db->execute("
        SELECT count(*) as nb FROM visite
        WHERE
          id_user IN (".implode(",",array_keys($users)).")
          AND deleted = 0
          AND pem = 0
          AND queue_date >= '$from'
          AND queue_date < '$to'
      ");
      $rez['datas'][$secteur]['visites'] = $db->assoc()['nb'];

      $db->execute("
        SELECT total FROM commande_apk
        WHERE
          user IN (".implode(",",$users).")
          AND deleted = 0
          AND queue_date >= '$from'
          AND queue_date < '$to'
      ");
      $rez['datas'][$secteur]['commandes'] = $db->num();
      $rez['datas'][$secteur]['total'] = 0;
      while( $r = $db->assoc() ) $rez['datas'][$secteur]['total'] += intval($r['total']);
    }

    $rez['datas']['total'] = [
      "secteur" => "Total",
      "visites" => 0,
      "commandes" => 0,
      "total" => 0,
    ];
    foreach( $rez['datas'] as $secteur => $e ) {
      $rez['datas']['total']['visites'] += $e['visites'];
      $rez['datas']['total']['commandes'] += $e['commandes'];
      $rez['datas']['total']['total'] += $e['total'];
    }
    krsort($rez['datas']);

    self::response($rez);
  }




  public static function getFrancoNonAtteints( $params ) {
    global $db;
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $fp_raison = $params['fp_raison'];

    $raison = "";
    if( is_numeric($fp_raison) && $fp_raison != 0 ) $raison = " AND c.fp_raison = '".intval($fp_raison)."' ";
    elseif( $fp_raison == "autre" )  $raison = " AND ( c.fp_raison != '' AND c.fp_raison != '0' AND concat('',c.fp_raison * 1) != c.fp_raison ) ";

    $db->execute("
    SELECT
      cli.id_as400 as '".l("stats-code-client")."',
      cli.enseigne as '".l("stats-magasin")."',
      u.displayname as '".l("stats-promoteur")."',
      c.queue_date as '".l("stats-date")."',
      c.total as '".l("js-stats-valeurs")."',
      c.fp_raison as '".l("js-stats-choix")."'
    FROM
      commande_apk c
      LEFT JOIN ref_client cli ON c.id_magasin = cli.id_as400
      LEFT JOIN user u ON c.user = u.login
    WHERE
       c.deleted = 0
      AND c.queue_date >= '$from'
      AND c.queue_date < '$to'
      AND c.total < ".FRANCO_DE_PORT."
      $raison
      ORDER BY c.queue_date DESC
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $r['date'] = core::dateOutput($r['date']);
      $rez['datas'][] = $r;
    }

    foreach( $rez['datas'] as $k=>$e ) {
      $rez['datas'][$k][l("js-stats-choix")] =  ( is_numeric($e[l("js-stats-choix")]) ? core::getReason($e[l("js-stats-choix")]) : l("js-stats-autre").' : <em>« '.$e[l("js-stats-choix")].' »</em>' );
    }


    self::response($rez);
  }





























  public static function getDatas() {
    $f = core::dateInput($_POST['f']);
    $t = core::dateInput($_POST['t']);
    $s = [];
    global $db;

    // Commandes internes
    $db->execute("
      SELECT count(*) as cmds, SUM(total) as cmds_total
      FROM commande_apk
      WHERE
        queue_date BETWEEN '$f' AND '$t'
        AND deleted = 0
        AND externe = 0
    ");
    $datas = $db->assoc();
    $s['cmds'] = $datas['cmds'];
    $s['cmds_total'] = $datas['cmds_total'];
    $s['cmd_moy'] = $datas['cmds'] > 0 ? $datas['cmds_total'] / $datas['cmds'] : 0;

    // Commandes externes
    $db->execute("
      SELECT count(*) as ext_cmds, SUM(total) as ext_cmds_total
      FROM commande_apk
      WHERE
        queue_date BETWEEN '$f' AND '$t'
        AND deleted = 0
        AND externe = 1
    ");
    $datas = $db->assoc();
    $s['ext_cmds'] = $datas['ext_cmds'];
    $s['ext_cmds_total'] = $datas['ext_cmds_total'];
    $s['ext_cmd_moy'] = $datas['ext_cmds'] > 0 ? $datas['ext_cmds_total'] / $datas['ext_cmds'] : 0;      

    // Visites
    $db->execute("
      SELECT id FROM visite 
      WHERE
      queue_date BETWEEN '$f' AND '$t'
      AND deleted = 0      
      AND pem = 0
    ");
    $ids = [];
    while( $r = $db->assoc() ) $ids[] = $r['id'];
    $s['vis'] = count($ids);

    // Commandes pays autre que la france


    // Combien d'utilisateurs
    $db->execute("
      SELECT DISTINCT id_user FROM visite
      WHERE
        queue_date BETWEEN '$f' AND '$t'
        AND deleted = 0   
        AND pem = 0
    ");
    $s['users'] = $db->num();
    
    // Pays d'origine
    $db->execute("
      SELECT 
        cli.pays 
      FROM 
        commande_apk c 
        LEFT JOIN ref_client cli ON cli.id_as400 = c.id_magasin
      WHERE
        c.queue_date BETWEEN '$f' AND '$t'    
    ");
    $pays = [];
    while( $r = $db->assoc() ) {
      if( !isset($pays[$r['pays']]) ) $pays[$r['pays']] = 0;
      $pays[$r['pays']]++;
    }



    $o = '
    <table class="table table-dark text-center">
      <thead>
        <tr>
          <th scope="col">Commandes internes</th>
          <th scope="col">Total commandes internes</th>
          <th scope="col">Commandes internes moyenne</th>
          <th scope="col">Commandes externes</th>
          <th scope="col">Total commandes externes</th>
          <th scope="col">Commandes externes moyenne</th>
          <th scope="col">Visites</th>
          <th scope="col">Promoteurs</th>
        </tr>
      </thead>    
      <tbody>
        <tr>
          <td>'.number_format($s['cmds'],0,","," ").'</td>
          <td>'.number_format($s['cmds_total'],2,","," ").'€</td>
          <td>'.number_format($s['cmd_moy'],2,","," ").'€</td>
          <td>'.number_format($s['ext_cmds'],0,","," ").'</td>
          <td>'.number_format($s['ext_cmds_total'],2,","," ").'€</td>
          <td>'.number_format($s['ext_cmd_moy'],2,","," ").'€</td>
          <td>'.number_format($s['vis'],0,","," ").'</td>
          <td>'.number_format($s['users'],0,","," ").'</td>
        </tr>
      </tbody>
    </table>


    <div>
    ';
    foreach( $pays as $p => $nb ) {
      if( $p == "" ) $p = "(??)";
      $o .= "Commandes $p : $nb <br/>";
    }
    $o .= '
    </div>
    ';







    core::ajax(["html" => $o]);

    

  }



















  /* Calcul des temps passés promoteurs */
  public static function saveTempsPromoteur( $id_repr, $date, $rewrite = false ) {
    global $db;
    $date = $db->escape($date);
    $id_repr = intval($id_repr);
    if( $id_repr < 1 ) return;

    /* Récupération des id_user depuis le id_repr */
    $id_users = [];
    $db->execute("SELECT id FROM user WHERE id_repr = $id_repr AND actif = 1 AND deleted = 0");
    while( $r = $db->assoc() ) $id_users[] = $r['id'];
    
    /* Récupération des visites */
    $visites = [];
    $db->execute("SELECT id FROM visite WHERE queue_date LIKE '%$date%' AND pem = 0 AND id_user IN (".implode(",",$id_users).") ORDER BY queue_date");
    while( $r = $db->assoc() ) $visites[] = $r['id'];

    /* On boucle sur chaque visite */
    $last_step = 0;
    $ica = [];
    foreach( $visites as $id_visite ) {
      $steps = self::getVisiteTimingPhotos($id_visite);
      $temps_init = 0; // Temps entre le début de la visite et la première photo
      $tsteps = self::getVisiteSteps($id_visite);
      $from = strtotime($steps['from']);
      $to = strtotime($steps['to']);
      $temps_init = $from - strtotime(array_shift($tsteps)['date_step']);
      $temps_visite = $to - $from;
      $temps_transport = 0;
      if( $last_step > 0 ) {
        $temps_transport = $from - $last_step;
      }
      $last_step = $to;
      if( $rewrite ) {
        $q = "DELETE FROM stat_promoteur_visite WHERE id_repr = $id_repr AND date_stat = '$date' AND id_visite = $id_visite";
        $db->execute($q);
      }
      
      $q = "INSERT INTO stat_promoteur_visite 
        (id_repr,date_stat,id_visite,temps_init,temps_visite,temps_transport) 
        VALUES
        ($id_repr,'$date', $id_visite,$temps_init, $temps_visite, $temps_transport);
      ";
      $db->execute($q);
      $ica[$date][$id_repr] = [];
    }



  }

  public static function getVisiteSteps( $id_visite ) {
    global $db;
    $steps = [];
    $db->execute("SELECT * FROM visite_step WHERE id_visite = $id_visite ORDER BY step_nb");
    while( $r = $db->assoc() ) $steps[] = $r;
    return $steps;
  }
  public static function getVisiteTimingPhotos( $id_visite ) {
    global $db;
    $vsteps = self::getVisiteSteps($id_visite);
    $steps = [
      "from" => 0,
      "to" => 0
    ];
    // Photo début
    $db->execute("SELECT app_name FROM visite_photo WHERE id_visite = $id_visite AND app_name LIKE '%photoArrivee%' LIMIT 1");
    if( $db->num() ) {
      $tmp = explode("_",$db->assoc()['app_name']);
      $tmp2 = explode(".",array_pop($tmp));
      $steps['from'] = $tmp2[0];
    }
    else {
      $steps['from'] = array_shift($vsteps)['date_step'];
    }
    // Photo Fin
    $db->execute("SELECT app_name FROM visite_photo WHERE id_visite = $id_visite AND app_name LIKE '%photoFin%' ORDER BY id DESC LIMIT 1");
    if( $db->num() ) {
      $tmp = explode("_",$db->assoc()['app_name']);
      $tmp2 = explode(".",array_pop($tmp));
      $steps['to'] = $tmp2[0];
    }
    else {
      $steps['to'] = array_pop($vsteps)['date_step'];
    }

    return $steps;
  }








  public static function getTempsPromoteurs( $params) {
    global $db;
    $db->execute("SET sql_mode = '';");
    $rez = ["datas" => []];
    $from = self::formatDate($params['from']);
    $to = self::formatDate($params['to']);
 

    $today = strtotime(date("Y-m-d")." 00:00:00");
    if( strtotime($from) >= $today || strtotime($to) >= $today )
      core::ajaxError( l("stats-avancee-warning") );

    if( $from == "" || $to == "" ) core::ajaxError( l("stats-avancee-warning-date") );


    $upTo = date("Y-m-d", strtotime("+1 day", strtotime($to)));

    $format = [ 
      l("stats-avancee-decallage"),
      l("stats-avancee-visite"),
      l("stats-avancee-transport"),
      l("stats-avancee-total")
    ];
    $db->execute("SELECT 
      u.id AS id_user,
      a.id_repr as 'ID',
      displayname AS '".l("stats-avancee-promoteur")."',
      0 AS '".l("stats-avancee-can1")."',
      0 AS '".l("stats-avancee-ca")."',
      SUM(temps_init) AS '".$format[0]."',
      SUM(temps_visite) AS '".$format[1]."',
      SUM(temps_transport) AS '".$format[2]."',
      SUM(temps_visite) + SUM(temps_transport)  AS '".$format[3]."'
      FROM
        stat_promoteur_visite a
        INNER JOIN user u ON ( a.id_repr = u.id_repr AND u.login != 'gescomtest.ludivin'AND u.actif = 1 )
      WHERE
        a.date_stat BETWEEN '$from' AND '$to'
      GROUP BY
        a.id_repr
    ");
    $rez['query'] = str_replace(["\r","\n"],"",$db->query);
    while( $r = $db->assoc() ) {
      $rez['datas'][] = $r;
    }

    // Ajout du CA
    $from2 = date("Y-m-d", strtotime("-1 year",strtotime($from)));
    $to2 = date("Y-m-d", strtotime("-1 year",strtotime($to)));
    

    foreach( $rez['datas'] as $k=>$e ) {
      $rez['datas'][$k][l("stats-avancee-moyenne-total")] = "0h 00m 00s";
      foreach( $format as $f ) {
        $rez['datas'][$k][$f] = core::secondsToTime($e[$f]);
      }
      $id_repr = intval($e['ID']);
      $total_time = $e[l("stats-avancee-total")]; // Temps total déjà calculé

      // Calcul du nombre de jours ouvrés (exclure samedis et dimanches)
      $startDate = strtotime($from);
      $endDate = strtotime($to);
      $workingDays = 0;

      while ($startDate <= $endDate) {
          $dayOfWeek = date('N', $startDate); // 1 (lundi) à 7 (dimanche)
          if ($dayOfWeek < 6) { // Exclure samedi (6) et dimanche (7)
              $workingDays++;
          }
          $startDate = strtotime("+1 day", $startDate);
      }

      // Calcul de la moyenne
      $average_time = ($workingDays > 0) ? round($total_time / $workingDays) : 0;

      // Ajout de la colonne "Moyenne" avec le calcul
      $rez['datas'][$k][l("stats-avancee-moyenne-total")] = core::secondsToTime($average_time);












      $q = "SELECT SUM(ca_facture) AS total FROM stat_promoteur_ca WHERE id_repr = '$id_repr' ";
      $db->execute($q." AND date_stat >= '$from' AND  date_stat <= '$to'");
      $rez['datas'][$k][l("stats-avancee-ca")] = number_format(floor($db->assoc()['total']),0,","," ").' €';
      $db->execute($q." AND date_stat >= '$from2' AND  date_stat <= '$to2'");
      $rez['datas'][$k][l("stats-avancee-can1")] = number_format(floor($db->assoc()['total']),0,","," ").' €';

      $id_user = $e['id_user'];
      unset($rez['datas'][$k]['id_user']);
      unset($rez['datas'][$k]['ID']);
      $db->execute("
        SELECT count(*) as nb FROM visite WHERE queue_date >= '$from' AND queue_date < '$upTo' ANd id_user = $id_user AND pem = 0
      ");
      $rez['datas'][$k][l("stats-avancee-visites")] = $db->assoc()['nb'];

    }

    

    self::response($rez);
  }








  public static function saveCaPromoteur( $date, $rewrite = false ) {
    global $db;
    $q = "
      SELECT 
      f.no_facture,
      ( CASE
        WHEN c.id_commercial_1 > 0 THEN c.id_commercial_1
        ELSE id_rep 
        END
      )
        AS id_rep,
      f.montant_facture,
      f.facture_avoir 
    FROM 
      ref_facture f 
      LEFT JOIN ref_client c ON ( f.id_client_livre = c.id_as400 OR f.id_client_cmd = c.id_as400 )
    WHERE 
        concat(f.annee_facture,'-',f.mois_facture,'-',f.jour_facture) = '$date'
    GROUP BY f.no_facture
    ";
    $db->execute($q);
    $datas = [];
    while( $r = $db->assoc() ) $datas[] = $r;
    $total = [];
    foreach( $datas as $e ) {
      if( !isset($total[$e['id_rep']])) $total[$e['id_rep']] = 0;
      if( $e['facture_avoir'] == "A" ) $e['montant_facture'] = -1 * $e['montant_facture'];
      $total[$e['id_rep']] += floatval($e['montant_facture']);
    }

    if( $rewrite )
      $db->execute("DELETE FROM stat_promoteur_ca WHERE date_stat = '$date' ");

    foreach( $total as $id_rep => $total ) {
      $db->execute("INSERT INTO stat_promoteur_ca (id_repr,date_stat,ca_facture) 
        VALUES ('".$db->escape($id_rep)."','$date','$total')
      ");
    }

  }


   /* Stats pour SalesManagement */



    # Calcul du CA d'un client du 1er de l'année jusqu'à aujourd'hui
    public static function calcCaFromJanuary( $id_client, $lastYear = false ) {
      global $db;

      $from = date('Y')."0101";
      $to = date("Ymd");

      if( $lastYear ) {
        $from = date('Y',strtotime('-1year'))."0101";
        $to = date("Ymd", strtotime("-1 year"));
      }

      $q = "SELECT DISTINCT no_facture, montant_facture as total,facture_avoir
        FROM ref_facture 
        WHERE 
          id_client_cmd = '$id_client'
          AND CONCAT(annee_facture,mois_facture,jour_facture) BETWEEN '$from' AND '$to'
      ";

      $db->execute($q);
      $sum = 0;
      while( $r = $db->assoc() ) {
        if( $r['facture_avoir'] == "A" ) $r['total'] = -1 * $r['total'];
        $sum+= floatval($r['total']);
      }
      return $sum;
    }

    # Calcul du CA de tous les clients ( du 01/01 à ajd, puis du 01/01 de l'an passé au même jour l'an dernier)
    public static function calcAllCaClients() {
      global $db;
      $db->execute("SELECT DISTINCT code_client_cmd FROM commandes_as400");
      $ids = [];
      while( $r = $db->assoc() ) $ids[] = $r['code_client_cmd'];
      foreach( $ids as $id ) self::calcCaClient($id);
      return;
    }
    public static function calcCaClient( $id_client ) {
        global $db;
        $db->execute("SELECT id FROM stat_delta_ca_client WHERE id_as400 = '".e($id_client)."' ");
        if( !$db->num() )
          $db->execute("INSERT INTO stat_delta_ca_client (id_as400) VALUES ('".e($id_client)."') ");
        
        $ca_an_passe = self::calcCaFromJanuary($id_client,true);
        $ca = self::calcCaFromJanuary($id_client);
        $db->execute("
          UPDATE 
            stat_delta_ca_client
          SET
            ca_an_passe = '".floatval($ca_an_passe)."',
            ca = '".floatval($ca)."'
          WHERE
            id_as400 = '".e($id_client)."'
        ");
        return;
    }
    public static function getAllCaClients() {
      global $db;
      $db->execute("SELECT * FROM stat_delta_ca_client");
      $datas = $db->get();
      foreach( $datas as $k=>$e ) {
        $perc = 0;
       
        if( $e['ca_an_passe'] > 0 ) {
          $perc = ( $e['ca'] - $e['ca_an_passe'] ) / $e['ca_an_passe'] * 100;
        }
        
        $perc_str = number_format( $perc, 2 ,","," " );
        $datas[$k]['p'] = $perc;
      }
      return $datas;
    }
    

    public static function getClientCASM( $id_as400, $isCumulMonth = 1 ) {
      global $db;
        
      if( $isCumulMonth ) {
        $ly = date('Y',strtotime('-1 year'));
        $db->execute("SELECT * FROM stat_ca_client WHERE id_as400 = '".e($id_as400)."' AND annee >= $ly ORDER BY mois");
        $rez = [];
        while( $r = $db->assoc() ) {
          if( !isset($rez[$r['annee']])) {
              $thisYear = $r['annee'] == date('Y');
              $rez[$r['annee']] = [
                "data" => [],
                "label" => $thisYear ? "CA" : "CA N-1",
                "backgroundColor" => $thisYear ? "#F4A5AE" : "#343434"
              ];
          }
          $rez[$r['annee']]['data'][] = intval($r['ca']);
        }   
      }
      else {

        $datas = [];

        for( $i = 0; $i <= 4; $i++ ) {
          $sum = 0;
          $year = date('Y', strtotime("- $i year") );
          $db->execute("SELECT * FROM stat_ca_client WHERE id_as400 = '".e($id_as400)."' AND annee = $year");
          while( $r = $db->assoc() ) 
            $sum += intval($r['ca']);
          $datas[$year] = intval($sum);
        }

        $rez = [
          [
            "data" => $datas,
            "label" => "CA par année",
            "backgroundColor" => "#F4A5AE"
          ]
        ];


      }  
      api::ajaxRep($rez);
    }
  
    public static function recalcCaMensuel( $y = null, $m = null ) {
      global $db;
      $db->execute("SELECT DISTINCT id_client_cmd FROM ref_facture");
      $ids = [];
      while( $r = $db->assoc() ) $ids[] = $r['id_client_cmd'];
      foreach( $ids as $id ) self::calcCaClientMensuel($id, $y, $m);     
    }
    public static function calcCaClientMensuel( $id_as400, $allowedYears,$allowedMonth ) {
      global $db;


      $years = [];
      for( $i = 0; $i <= 5; $i++ ) {
        $years[]= date('Y',strtotime("-$i years"));
      }
      
      $recalc = [date('Ym'),date('Ym',strtotime('-1 month'))];
      if( $allowedYears > 0 ) {
        $recalc = [$allowedYears.$allowedMonth];
      }

      foreach( $years as $y ) {
        for( $i = 1; $i <= 12; $i++ ) {
          $im = ( $i < 10 ? str_pad($i,2,"0",STR_PAD_LEFT) : $i );
          $rec = in_array( $y.$im ,$recalc);

          $db->execute("SELECT * FROM stat_ca_client WHERE id_as400 = '$id_as400' AND annee = $y AND mois = '$im' ");
          $hasRez = $db->num();
          if( $hasRez && !$rec ) continue;

          $db->execute("
            SELECT DISTINCT 
              no_facture,montant_facture,facture_avoir
            FROM 
              ref_facture 
            WHERE
              ( id_client_cmd = '$id_as400' OR id_client_livre = '$id_as400'  OR id_client_facture  = '$id_as400'  )
              AND annee_facture = $y 
              AND mois_facture = '$im'
          ");
          $total = 0;
          while( $r = $db->assoc() ) {
            if( $r['facture_avoir'] == "A" ) $r['montant_facture'] = -1 * $r['montant_facture'];
            $total += floatval($r['montant_facture']);
          }
          if( $hasRez ) 
            $db->execute("UPDATE stat_ca_client SET ca = '$total' WHERE id_as400 = '$id_as400' AND annee = $y AND mois = '$im' ");
          else 
            $db->execute("INSERT INTO stat_ca_client (id_as400,annee,mois,ca) VALUES ('$id_as400',$y,'$im',$total)");
        }
      }

    }

    public static function recalcCaMensuelErrors( $id_as400 = null, $year = null, $month = null ) {

      global $db;


      /* Ids clients à traiter */
      if( $id_as400 == null ) {
        $ids_as400 = [];
        $db->execute("SELECT DISTINCT id_client_cmd AS id_as400 FROM ref_facture");
        while( $r = $db->assoc() ) if( !in_array($r['id_as400'],$ids_as400) ) $ids_as400[] = $r['id_as400'];
        $db->execute("SELECT DISTINCT id_client_livre AS id_as400 FROM ref_facture");
        while( $r = $db->assoc() ) if( !in_array($r['id_as400'],$ids_as400) ) $ids_as400[] = $r['id_as400'];
        $db->execute("SELECT DISTINCT id_client_facture AS id_as400 FROM ref_facture");
        while( $r = $db->assoc() ) if( !in_array($r['id_as400'],$ids_as400) ) $ids_as400[] = $r['id_as400'];
      }
      else $ids_as400 = [$id_as400];

      /* Years */
      $years = [];
      if( $year == null ) {
        for( $i = 0; $i <= 5; $i++ ) {
          $years[]= date('Y') - $i;
        } 
      }
      else {
        if( !is_array($year) ) $years[] = $year;
        else $years = $year;
      }

      /* Months */
      $months = [1,2,3,4,5,6,7,8,9,10,11,12];
      if( $month != null ) {
        if( !is_array($month) ) $months[] = $month;
        else $months = $month;
      }

      foreach( $ids_as400 as $id_as400 ) {
        foreach( $years as $year ) {
          foreach( $months as $month ) {
            $ca = self::getTotalFactureMonthClient($id_as400,$year,$month);

            $db->execute("
              SELECT id FROM stat_ca_client
              WHERE
                id_as400 = '$id_as400'
                AND annee = '$year'
                AND mois = '".intval($month)."'
            ");
            if( $db->num() ) {
              $id = $db->assoc()['id'];
              $db->execute("
                UPDATE stat_ca_client
                SET ca = '".floatval($ca)."'
                WHERE id = $id
              ");
            }
            else {
              $db->execute("
                INSERT INTO stat_ca_client
                (id_as400,annee,mois,ca)
                VALUES
                ('$id_as400','$year','".intval($month)."',$ca)
            ");
            }
          }
        }
      }


    }

    public static function getTotalFactureMonthClient( $id_as400, $year, $month ) {
      global $db;
      $year = intval($year);
      $month = str_pad($month,2,"0",STR_PAD_LEFT);
      $db->execute("
        SELECT DISTINCT 
          no_facture,montant_facture,facture_avoir
        FROM 
          ref_facture 
        WHERE
          ( id_client_cmd = '$id_as400' OR id_client_livre = '$id_as400'  OR id_client_facture  = '$id_as400'  )
          AND annee_facture = '$year'
          AND mois_facture = '$month'
      ");
      $total = 0;
      while( $r = $db->assoc() ) {
        if( $r['facture_avoir'] == "A" ) $r['montant_facture'] = -1 * $r['montant_facture'];
        $total += floatval($r['montant_facture']);
      }      
      return $total;
    }













    public static function getMyPromoteurs() {
      if( !defined('ID_PROFILE') || self::isPromoteur() ) 
        return [api::getApiUser()['id_repr']];

      global $db;
      $id_repr = [];


      if( self::isChefSecteur() ) {
        $user = api::getApiUser();
        if( $user['id_repr'] != "" && $user['id_repr'] > 0 ) $id_repr[] = $user['id_repr'];       
        $code = "C".$user['id_repr'];
        $q = "SELECT DISTINCT id_commercial_1 as id_repr FROM ref_client WHERE id_commercial_2 = '".$db->escape($code)."' AND id_commercial_1 > 0";
      }        
      else if( self::isRegion() ) {
        $user = api::getApiUser();
        $region = $user['secteur'];
        $q = "SELECT DISTINCT id_repr FROM user WHERE id_profile = 1 AND secteur = '".e($region)."' AND id_repr > 0  AND actif = 1 AND deleted = 0";
      }  
      else if( self::isDirection() ) {
        $q = "SELECT DISTINCT id_repr FROM user WHERE id_profile = 1 AND actif = 1 AND id_repr > 0 AND deleted = 0";
      }

      $db->execute($q);
      while( $r = $db->assoc() ) {
        $id_repr[] = "'".intval($r['id_repr'])."'";
      }
      return $id_repr;
    }


    public static function formatIdRepr( $id_repr = [] ) {
      if( empty($id_repr) ) return "";
      return "id_repr IN (".implode(",",$id_repr).")";
    }
















    public static function getPromoteurCa() {
      global $db;
      $user = api::getApiUser();
      $id_repr = self::getMyPromoteurs();
      if( empty($id_repr) || (count($id_repr) == 1 && $id_repr[0] < 1 ) ) {
        api::aError("Impossible d'obtenir l'ID de représentant");
      }
      $y = date('Y');
      

      // Objectifs
      $obj = $tmp1 = [];


      if( self::isChefSecteur() ) {
        $user = api::getApiUser();
        $db->execute("SELECT mois,total FROM objectifs WHERE id_repr = '".$user['id_repr']."' AND annee = '$y' ");
      }
      else {
        $db->execute("SELECT mois,total FROM objectifs WHERE ".self::formatIdRepr($id_repr)." AND annee = $y");
      }
      while( $r = $db->assoc() ) {
        if( !isset($obj[intval($r['mois'])]) ) $obj[intval($r['mois'])] = 0;
        $obj[intval($r['mois'])] += $r['total'];
      }
      for($i = 1; $i<= 12; $i++ ) $tmp1[] = $obj[$i] ?? 0;
      
      // CA
      $ca = $tmp2 = [];
      $db->execute("SELECT date_stat,ca_facture FROM stat_promoteur_ca WHERE ".self::formatIdRepr($id_repr)." AND date_stat LIKE '$y%'");
      while( $r = $db->assoc() ) {
        $key = intval(date('m',strtotime($r['date_stat'])));
        if( !isset($ca[$key]) ) $ca[$key] = 0;
        $ca[$key] += $r['ca_facture'];
      }
      for($i = 1; $i<= 12; $i++ ) $tmp2[] = $ca[$i] ?? 0;

      // Chart
      $chart = [
        [
          "data" => $tmp1,
          "label" => "Objectifs",
          "backgroundColor" => "#343434"
        ],
        [
          "data" => $tmp2,
          "label" => "Réalisé",
          "backgroundColor" => "#F4A5AE"
        ]
      ];
      api::ajaxRep($chart);
    }



    public static function getIndicatorsPromoteur() {
      global $db;
      $user = api::getApiUser();
      $id_repr = self::getMyPromoteurs();
      if( empty($id_repr) || (count($id_repr) == 1 && $id_repr[0] < 1 ) ) {
          api::ajaxRep(["indicators" => [],"obj" => null,"dump" => null]);
      }



      

      $indicators = [];
      $today = date('d');

      $dump = [];



      /* Obj */

      $y = date('Y');
      $ly = date('Y',strtotime('-1 year'));
      $n = date('n');
      $m = date('m');

      $ss = ["'nord ouest'","'nord est'","'sud est'","'sud ouest'","'idf'"];
      $db->execute("SELECT id_repr FROM user WHERE id_profile = 1 AND LOWER(secteur) IN (".implode(",",$ss).") AND actif = 1 AND id_repr > 0");
      $id_reprs_cs = [];
      while( $r = $db->assoc() ) $id_reprs_cs[] = intval($r['id_repr']);

      if( self::isChefSecteur() ) {
        $user = api::getApiUser();
        $db->execute("SELECT total FROM objectifs WHERE id_repr = '".$user['id_repr']."' AND annee = '$y' AND mois = '$n' ");
      }
      else if( self::isDirection() ) {
        $db->execute("SELECT total FROM objectifs WHERE id_repr IN (".implode(",",$id_reprs_cs).") AND annee = '$y' AND mois = '$n' ");
      }
      else 
        $db->execute("SELECT total FROM objectifs WHERE ".self::formatIdRepr($id_repr)." AND annee = '$y' AND mois = '$n' ");
      $obj = 0;
      while( $r = $db->assoc() ) $obj += $r['total'];
      $indicators[] = [
        "libelle" => "Mon objectif", 
        "value" => number_format($obj,0,","," ")." €"
      ];


      



      /* C.A. Réalisé MTD */
      $total = $ltotal = 0;
      $today = date('d');
      $debug = [];
      $db->execute("
        SELECT 
          spca.id_repr,
          spca.ca_facture 
        FROM 
          stat_promoteur_ca spca
        WHERE 
          spca.".self::formatIdRepr($id_repr)." 
          AND spca.date_stat LIKE '$y-$m%' 
          AND spca.id_repr IN (".implode(",",$id_reprs_cs).")
      ");
      while( $r = $db->assoc() ) {
        $r['ca_facture'] = floatval($r['ca_facture']);
        $arrondi = round( $r['ca_facture'],0,PHP_ROUND_HALF_UP); 
        if( !isset($debug[ $r['id_repr'] ]) ) $debug[ $r['id_repr'] ] = ["a" => 0, "na" => 0, "l" => []];
        $debug[ $r['id_repr'] ]['a'] += $arrondi; 
        $debug[ $r['id_repr'] ]['na'] = floatval($debug[ $r['id_repr'] ]['na']) + floatval($r['ca_facture']); 
        $debug[ $r['id_repr'] ]['l'][] = $r['ca_facture']." -> " .$arrondi;
      }

      foreach( $debug as $ii => $ep ) $total += round($ep['na']);

      $tmp = ["libelle" => "C.A. Réalisé MTD", "value" => number_format($total,0,","," ")." €", "debug" => $debug];
      
      $can1q = "SELECT ca_facture FROM stat_promoteur_ca WHERE ".self::formatIdRepr($id_repr)." AND date_stat LIKE '$ly-$m%' AND date_stat < '$ly-$m-$today' ";
      $db->execute($can1q);
      while( $r = $db->assoc() ) $ltotal += round( $r['ca_facture'], 0, PHP_ROUND_HALF_UP);
      
      if( $ltotal > 0 ) {
        $perc = ( ( $total - $ltotal ) / $ltotal ) * 100;
        $tmp['desc'] = 'Différence : '.( $perc > 0 ? '+' : '').number_format($perc,2,","," ").' %';
        $tmp['arrow'] = $ltotal > $total ? 'down' : 'up';
      }
      $indicators[] = $tmp;
      $dump['ca'] = $total;
      $dump['can1'] = $ltotal;
      $dump['can1q'] = $can1q;

      $myObj = $obj ? round( $total * 100 / $obj, 2) : 0;


      /* Ca projection */
      $jourOuvresPasses = core::countFrenchBusinessDays(date('Y'),  date('n'), date('j') );
      $jourOuvresPasses = ( $jourOuvresPasses > 0 ? $jourOuvresPasses : 1 );
      $totalCAParJour = $total / $jourOuvresPasses;
      $jourOuvres = core::countFrenchBusinessDays(date('Y'),  date('n') );
      $projection = $totalCAParJour * $jourOuvres;

      $caAnPasse = 0;
      $db->execute("SELECT ca_facture FROM stat_promoteur_ca WHERE ".self::formatIdRepr($id_repr)." AND date_stat LIKE '$ly-$m%' ");
      while( $r = $db->assoc() ) $caAnPasse += floatval($r['ca_facture']);

      $tmp = [
        "libelle" => "C.A. Projection", 
        "value" => number_format($projection,0,","," ")." €",
        "jous_factures_mois" => $jourOuvresPasses,
        "jo" => $jourOuvres,
        "jop" => $jourOuvresPasses
      ];
      if( $caAnPasse > 0 ) {
        $perc = ( ( $projection - $caAnPasse ) / $caAnPasse ) * 100;
        $tmp['desc'] = 'Différence : '.( $perc > 0 ? '+' : '').number_format($perc,2,","," ").' %';
        $tmp['arrow'] = $caAnPasse > $projection ? 'down' : 'up';        
      }
      $indicators[] = $tmp;

      /* Nb visites */
      $ids = [];
      $nb_visites = 0;
      $db->execute("SELECT id FROM user WHERE ".self::formatIdRepr($id_repr));
      while( $r = $db->assoc() ) $ids[] = $r['id'];
      if( !empty($ids) ) {
        $db->execute("SELECT count(*) AS nb FROM visite WHERE id_user IN (".implode(',',$ids).") AND queue_date LIKE '$y-$m%' AND queue_date < '$y-$m-$today' AND pem = 0 AND deleted = 0");
        $nb_visites = $db->assoc()['nb'];
        $tmp = ["libelle" => "Nb de Visites", "value" => number_format($nb_visites,0,","," ")];
        $db->execute("SELECT count(*) AS nb FROM visite WHERE id_user IN (".implode(',',$ids).") AND queue_date LIKE '$ly-$m%'  AND queue_date < '$ly-$m-$today'  AND pem = 0  AND deleted = 0");
        $lnb_visites = $db->assoc()['nb'];
        if( $lnb_visites > 0 ) {
          $perc = ( ( $nb_visites - $lnb_visites ) / $lnb_visites ) * 100;
          $tmp['desc'] = 'Différence : '.( $perc > 0 ? '+' : '').number_format($perc,2,","," ").' %';
          $tmp['arrow'] = $lnb_visites > $nb_visites ? 'down' : 'up';        
        }
        $indicators[] = $tmp;
      }

      /* Ca par visites */
      if( $nb_visites > 0 ) {
        $caParVisite = $total/$nb_visites;
        $tmp = ["libelle" => "C.A./Visite", "value" => number_format($caParVisite,0,","," ")." €"];
        if( $lnb_visites > 0 && $ltotal > 0 ) {
          $lcaParVisite = $ltotal/$lnb_visites;
          $perc = ( ( $caParVisite - $lcaParVisite ) / $lcaParVisite ) * 100;
          $tmp['desc'] = 'Différence : '.( $perc > 0 ? '+' : '').number_format($perc,2,","," ").' %';
          $tmp['arrow'] = $lcaParVisite > $caParVisite ? 'down' : 'up';        
        }
        $indicators[] = $tmp;
      }

      /* Taux de transformation */
      if( $nb_visites > 0 ) {
        
        $logins = ["'".$user['login']."'"];
        $db->execute("SELECT login FROM user WHERE ".self::formatIdRepr($id_repr)." AND actif = 1");
        while( $r = $db->assoc() ) $logins[] = "'".$db->escape($r['login'])."'";
        $logins = implode(",",$logins);

        $db->execute("SELECT count(*) as nb FROM commande_apk WHERE user IN ($logins) AND queue_date LIKE '".$y."-".$m."%' ");
        $nb_cmd = $db->assoc()['nb'];
        $taux = round( $nb_cmd * 100 / $nb_visites );
        $tmp = ["libelle" => "Tx de transformation", "value" => number_format($taux,0,","," ")." %"];
        $db->execute("SELECT count(*) as nb FROM commande_apk WHERE user IN ($logins) AND queue_date LIKE '".$ly."-".$m."%' ");
        $lnb_cmd = $db->assoc()['nb'];

        if( $lnb_cmd > 0 ) {
          $perc = round( ( ( $nb_cmd - $lnb_cmd ) / $lnb_cmd ) * 100 );
          $tmp['desc'] = 'Différence : '.( $perc > 0 ? '+' : '').number_format($perc,0,","," ").' %';
          $tmp['arrow'] = $lnb_cmd > $nb_cmd ? 'down' : 'up'; 
          $tmp['info'] = "= ".($nb_visites - $nb_cmd) . " visite(s) sans cmd";
        }
        $indicators[] = $tmp;

      }

      api::ajaxRep([
        "indicators" => $indicators,
        "obj" => $myObj,
        "dump" => $dump,
        "user" => $user
      ]);
    }

    public static function getListIdHasObjectifs() {
      global $db;
      $db->execute("SELECT DISTINCT id_repr FROM objectifs");
      $ids = [];
      while( $r = $db->assoc() ) $ids[] = $r['id_repr'];
      return $ids;
    }


    public static function getTauxNational(){
      global $db;
      $rep = [
        "perc" => 0,
        "percStr" => 0,        
        "proj" => 0
      ];

      /* Perc */
      $y = date('Y');
      $n = date('n');
      $m = date('m');

      $id_reprs = [];
      $secteurs = ["'IDF'" ,"'Nord Est'" ,"'Nord Ouest'","'Sud Est'","'Sud Ouest'"];
      $db->execute("SELECT id_repr, secteur FROM user WHERE id_repr > 0 AND secteur IN (".implode(",",$secteurs).") AND actif = 1 AND deleted = 0 AND id_profile = 1");
      while( $r = $db->assoc() ) if( !in_array($r['id_repr'],$id_reprs) ) $id_reprs[] = $r['id_repr'];


      $db->execute("SELECT SUM(total) as nb FROM  objectifs WHERE annee = $y and mois = $n AND id_repr IN (".implode(",",$id_reprs).") ");
      $totalObj = $db->assoc()['nb'];

      $db->execute("SELECT SUM(CAST(ca_facture as DECIMAL(10,2))) as nb FROM  stat_promoteur_ca WHERE id_repr IN (".implode(",",$id_reprs).") AND date_stat LIKE '$y-$m%' ");
      //$db->execute("SELECT SUM(CAST(ca_facture as DECIMAL(10,2))) as nb FROM  stat_promoteur_ca WHERE  date_stat LIKE '$y-$m%' ");
      $totalCa = $db->assoc()['nb'];

      $perc = 0;
      if( $totalObj > 0 )
        $perc = round($totalCa * 100 / $totalObj,2);

      $rep['perc'] = $perc;
      $rep['percStr'] = number_format($perc,1,","," ");

      /* Proj */
      $joursOuvresPasses = core::countFrenchBusinessDays(date('Y'),  date('n'), date('j') ) ;
      $joursOuvres = core::countFrenchBusinessDays(date('Y'),  date('n') );

      $caParJour = $joursOuvresPasses ? $totalCa / $joursOuvresPasses : 0;
      $proj = $caParJour * $joursOuvres ;

      $rep['proj'] = round($proj * 100 / $totalObj,1);

      api::ajaxRep($rep);
    }

    public static function getTauxAvancementRegion() {
      global $db;
      $y = date('Y');
      $m = date('m');
      $n = date('n');
      $td = date('d');

      $joursOuvresPasses = ( core::countFrenchBusinessDays(date('Y'),  date('n'), date('j') ) );
      $joursOuvres = core::countFrenchBusinessDays(date('Y'),  date('n') );


      $colors = [
        'IDF' => '#2177c4',
        'Nord Est' => '#ffafb8',
        'Nord Ouest' => '#9edd58',
        'Sud Est' => '#fee870',
        'Sud Ouest' => '#febc8c'
      ];
      $secteurs = ['IDF' => [],'Nord Est' => [],'Nord Ouest' => [],'Sud Est' => [],'Sud Ouest' => []];
      $db->execute("SELECT id_repr, secteur FROM user WHERE id_repr > 0 AND actif = 1 AND deleted = 0 AND id_profile = 1");
      while( $r = $db->assoc() )
        if( isset($secteurs[$r['secteur']]) && !in_array($r['id_repr'],$secteurs[$r['secteur']]) ) {
          $secteurs[$r['secteur']][] = $r['id_repr'];
        }
      
      // Somme des objs
      $secteursObj = [];
      $secteursCA = [];
      $rep = [];
      foreach( $secteurs as $secteur => $ids ) {
        $db->execute("
          SELECT id_repr, total FROM 
            objectifs 
          WHERE 
            annee = $y 
            AND mois = $n 
            AND id_repr IN (".implode(",",$ids).") 
        ");
        $op = 0;
        $listed = [];
        while( $r = $db->assoc() ) {
          if( !in_array($r['id_repr'],$listed) ) {
            $op += $r['total'];
            $listed[] = $r['id_repr'];
          }
        }
        $secteursObj[$secteur] = $op;

        $db->execute("
          SELECT 
            SUM( CAST(ca_facture AS DECIMAL(10,2))) as nb 
          FROM 
            stat_promoteur_ca 
          WHERE 
            date_stat LIKE '".$y."-".$m."%' 
            AND date_stat < '$y-$m-$td' 
            AND id_repr IN (".implode(",",$ids).") 
        ");
        $secteursCA[$secteur] = $db->assoc()['nb'];

        $perc = 0;
        if( isset($secteursCA[$secteur]) && isset($secteursObj[$secteur]) && $secteursObj[$secteur] > 0 ) {
          $perc = round($secteursCA[$secteur] * 100 / $secteursObj[$secteur],2);
        }


        $joursOuvresPasses = ( $joursOuvresPasses > 0 ? $joursOuvresPasses : 1 );
        $o = $secteursCA[$secteur] / ( $joursOuvresPasses );
        $secteursObj[$secteur] = ( $secteursObj[$secteur] > 0 ? $secteursObj[$secteur] :  1 );
        $proj = round( $o * $joursOuvres * 100 / ( $secteursObj[$secteur] ), 2);

        $rep[] = [
          "libelle" => $secteur,
          "value" => round($perc,1),
          "valuecolor" => $colors[$secteur],
          "proj" => round($proj,1)."%",
          "projcolor" => $proj > 100 ? 'g' : ( $proj > 90 ? 'y' : 'r' ),
          "jop" => $joursOuvresPasses
        ];
      }

      api::ajaxRep($rep);      
    }

    public static function getClassementPromoteurs( $national = true ) {

      global $db;
      $rez = [];
      $db->execute("
        SELECT 
          DISTINCT s.id_repr,
          s.*,
          u.secteur as secteur 
        FROM 
          stat_promoteur_cumul_ca s
          LEFT JOIN user u ON s.id_repr = u.id_repr AND u.actif = 1
        WHERE 
          (s.ca > 0 OR s.obj > 0) 
          AND u.id_repr > 0
          AND u.id_profile = 1
        ORDER BY s.position
      ");
      $datas = $db->get();
      $regPos = [];
      foreach( $datas as $d ) {
        $pos = $d['position'];
        if( !$national ) {
          if( !isset($regPos[$d['secteur']]) ) $regPos[$d['secteur']] = [];
          $regPos[$d['secteur']][] = $d;    
          $pos = count($regPos[$d['secteur']]);
        }
        $rez[] = [
          "num" => $pos,
          "nom" => user::getNameFromIdRepr($d['id_repr']),
          "photo" => 'assets/avatar.png',
          "total" => number_format($d['ca'],0,","," ").' €',
          "perc" => number_format($d['perc_obj'],0,","," ").' %',
          "obj" => number_format($d['obj'],0,","," ").' €',
          "visites" => $d['visites'],
          "proj" => number_format($d['proj'],0,","," ").' €',
          "tx" => number_format($d['transfo'],0,","," ").' %',
          "color" => $d['proj'] >= $d['obj'] ? 'g' : 'r',
          "reg" => $d['secteur']
        ];       
      }
      api::ajaxRep([
        "promoteurs" => $rez,
        "regions" => array_keys($regPos),
        "national" => $national
      ]);
    }

    public static function promoteurCaCumulCalc() {
      global $db;
      $db->execute("SELECT DISTINCT id_repr FROM user WHERE id_profile = 1 ANd actif = 1 AND id_repr > 0");
      $repr = [];
      while( $r = $db->assoc() ) $repr[$r['id_repr']] = [];

      $y = date('Y');
      $n = date('n');
      $m = date('m');
      $td = date('d');

      $positions = [];

      foreach( $repr as $id => $e ) {
        $db->execute("SELECT total FROM objectifs WHERE id_repr = $id AND annee = $y AND mois = $n");
        $obj = $db->num() ? $db->assoc()['total'] : 0;       
        $db->execute("SELECT SUM( CAST(ca_facture AS DECIMAL(10,2))) as nb FROM stat_promoteur_ca WHERE date_stat LIKE '".$y."-".$m."%' AND id_repr = $id ");
        $ca = round($db->assoc()['nb'],2);
        $perc_obj = $obj > 0 ? round( $ca *100 / $obj,2) : 0;

        $joursOuvresPasses = core::countFrenchBusinessDays(date('Y'),  date('n'), date('j') );
        if( $joursOuvresPasses == 0 ) $joursOuvresPasses = 1;
        $joursOuvres = core::countFrenchBusinessDays(date('Y'),  date('n') );
        $caProj = round($ca / $joursOuvresPasses * $joursOuvres );



        $db->execute("
          SELECT 
            v.no_cmd 
          FROM 
            visite v
            LEFT JOIN user u ON v.id_user = u.id
          WHERE 
            u.id_repr = $id
            AND queue_date LIKE '".$y."-".$m."%'
        ");
        $visites = $db->num();
        $cmd = 0;
        while( $r = $db->assoc() ) if( $r['no_cmd'] == 0 ) $cmd++;
        $transfo = $visites > 0 ? round( $cmd * 100 / $visites, 2) : 0;
        

        $sens = 1;
        $db->execute("SELECT * FROM stat_promoteur_cumul_ca WHERE id_repr = $id");
        if( !$db->num() ) {
          $db->execute("
            INSERT INTO 
              stat_promoteur_cumul_ca 
              (id_repr,ca,obj,perc_obj,proj,visites,transfo,sens)
              VALUES
              ($id,'$ca','$obj','$perc_obj','$caProj','$visites','$transfo',$sens)
          ");
        }
        else {
          $datas = $db->assoc();
          $positions[$id] = $datas['position'];
          $db->execute("
            UPDATE
              stat_promoteur_cumul_ca 
            SET
              ca = '$ca',
              obj = '$obj',
              perc_obj = '$perc_obj',
              proj = '$caProj',
              visites = '$visites',
              transfo = '$transfo',
              sens = $sens
            WHERE
              id_repr = $id              
          ");
        }
      }

      /* Classement */
      $db->execute("SELECT * FROM stat_promoteur_cumul_ca ORDER BY ca DESC");
      $datas = $db->get();
      $i = 1;
      foreach( $datas as $e ) {
        $sens = $e['sens'];
        if( isset($positions[$e['id_repr']])) {
          if( $positions[$e['id_repr']] <= $i ) $sens = 1;
          else $sens = 0;
        }
        $db->execute("UPDATE stat_promoteur_cumul_ca SET position = $i, sens = $sens WHERE id_repr = ".$e['id_repr']);
        $i++;
      }
      
      die("\nEnd\n");
    }


    public static function getProduitsRemplacements( $params ) {
      global $db;
      $rez = ["datas" => []];
      
      self::response($rez);
    }
  
  

















}
