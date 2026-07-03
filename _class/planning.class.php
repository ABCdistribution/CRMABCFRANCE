<?php

class planning {

  public static function newEntry( $date, $id_repr, $id_magasin ) {
    global $db;
    $date = $db->escape($date);
    $id_repr = $db->escape(trim($id_repr));
    $id_magasin = $db->escape(trim($id_magasin));
    $db->execute("
      INSERT INTO
      planning (date_passage,id_repr,id_magasin)
      VALUES
      ( '".$date."', '".$id_repr."', '".$id_magasin."' )
    ");
    return $db->lastId();
  }

  public static function importFileFromUpload() {
    global $db;

    $id_file = core::getUploadedFile("filePlanning",["csv"]);
    if( !$id_file ) core::aError("Impossible de charger le fichier");
    $f = core::getUpload($id_file);
    if( !$f ) core::aError("Impossible de récupérer le fichier");

    $file = file($f['root_path']);
    $err = [];
    foreach( $file as $nb_line => $line ) {
      if( trim($line) == "" ) continue;
      $tmp = explode(";",$line);
      if( count($tmp) != 3 ) {
        $err[] = "Erreur de structure à la ligne $nb_line";
        continue;
      }

      list($date,$id_repr,$id_magasin) = $tmp;
      $id_magasin = str_replace(["\r","\n"],"",$id_magasin);
      $client = client::getByCode($id_magasin, true);
      if( !$client || strlen($id_magasin) > 10 ) {
        $err[] = "Erreur ligne  $nb_line : client introuvable  ($id_magasin)";
        continue;
      }
      $date = core::dateInput($date);
      //$t = explode("/",$date);
      //$date = "20".$t[2].'/'.$t[1].'/'.$t[0];

      if( intval($id_repr) < 1 ) continue;

      // check doublons
      $db->execute("
        SELECT id FROM planning
        WHERE
          id_repr = '".$db->escape($id_repr)."'
          AND id_magasin = '".$db->escape($id_magasin)."'
          AND date_passage LIKE '%".$db->escape($date)."%'
          AND deleted = 0
      ");
      if( !$db->num() ) {
        $db->execute("
          INSERT INTO planning (id_repr,id_magasin,date_passage)
          VALUES
          ('".$db->escape($id_repr)."','".$db->escape($id_magasin)."','".$db->escape($date)."')
        ");
      }

    }


    die('{ "total" : '.count($file).', "errTotal" : '.count($err).', "errors" : "'.rawurlencode(implode("<br/>",$err)).'"}');
  }

  public static function getPlanningFromIdRepr( $id ) {
    global $db;
    $from = date('Y-m-d',strtotime("-6 months"));
    $to = date('Y-m-d',strtotime("+6 months"));
    $q = "
      SELECT id,id_magasin,date_passage,green,raison
      FROM planning
      WHERE
        CAST(id_repr AS UNSIGNED) = '".intval($id)."'
        AND deleted = 0
        AND date_passage >= '".$from."'
        AND date_passage <= '".$to."'
      ORDER BY date_passage
    ";
    $q = str_replace(["\r","\n"],"",$q);
    $db->execute($q);
    if( !$db->num() ) return [];
    $pl = [];
    $list = $db->getArray();
    $ids_clients = [];

    foreach( $list as $r ) {
      $r['date_passage'] = date('Y-m-d', strtotime($r['date_passage']));
      if( !isset($pl[$r['date_passage']]) ) $pl[$r['date_passage']] = [];

      if( !isset($ids_clients[$r['id_magasin']]) ) {
        $db->execute("SELECT id FROM ref_client WHERE CAST(id_as400 as UNSIGNED) = '".intval($r['id_magasin'])."' ");
        if( $db->num() ) {
          $id_client = $db->assoc()['id'];
          $ids_clients[$r['id_magasin']] = $id_client;
        }
        else {
          $ids_clients[$r['id_magasin']] = 0;
        }
      }
      $id = $ids_clients[$r['id_magasin']];
      $raison = $r['raison'];
      if( intval($r['raison']) > 0 && intval($r['raison']) == $r['raison'] ) {
        $db->execute('SELECT libelle FROM apk_select_options WHERE id = '.$db->escape($r['raison']) );
        if( $db->num() ) {
          $raison = $db->assoc()['libelle'];
        }
      }
      $pl[$r['date_passage']][] = [$id,$r['green'],$raison];
    }

    return $pl;
  }


  public static function getPlanning() {
    global $db;
    if( isset($_POST['id_repr']) )
      $id_user = intval($_POST['id_repr']);
    else
      $id_user = API_ID_USER;
    $user = user::exist($id_user);

    if( $user['id_repr'] < 1 ) {
      $rep = ["isOK"=>false,"msg"=>"Identifiant de representant manquant"];
    }
    else if( $user['actif'] != 1 ) {
      $rep = ["isOK"=>false,"msg"=>"Ce promoteur n'est pas actif"];
    }
    else {
      $pl = self::getPlanningFromIdRepr($user['id_repr']);
      if( empty($pl) || (defined('APK_VERSION') && APK_VERSION < 0.40 ) ) {
        $rep = ["isOK"=>false,"msg"=>"Votre planning est vide"];
      }
      else {
        $rep = ["isOK"=>true,"planning"=>$pl];
      }
    }
    if( !API ) {
      $week = [];
      if( !isset($rep['planning']) ) core::aError("Planning vide");
      $from = isset($_POST['from']) && $_POST['from'] != "" ? strtotime(($_POST['from'])) : false;
      $to = isset($_POST['to']) && $_POST['to'] != "" ? strtotime(($_POST['to'])) : false;
      foreach( $rep['planning'] as $k=>$e ) {
        if( ( $from && $from > strtotime($k) ) || ($to && $to < strtotime($k)) ) {
          unset($rep['planning'][$k]);
          continue;
        }
        if( !isset($week[$k]) ) {
          $date = new DateTime($k);    
          $week[$k] = $date->format("W"); 
        }
        foreach( $e as $i=>$j ) {
          $c = client::get($j[0]);
          if( !$c ) {
            $c = [
            "enseigne" => "<em>Magasin non trouvé</em>",
            "id_as400" => "#".$j[0]
            ];
          }
          $rep['planning'][$k][$i][] = $c['enseigne'].' ('.$c['id_as400'].')';
        }
      }
      $rep['weeks'] = $week;
      

      $rep['plannification'] = [];
      $db->execute("
        SELECT 
          p.id,
          c.enseigne,
          p.days,
          p.rec,
          p.date_creation,
          p.start
        FROM 
          plannification p
          LEFT JOIN ref_client c ON p.id_client = c.id_as400
        WHERE 
          p.id_repr = '".intval($user['id_repr'])."' 
          AND p.deleted = 0
          AND p.rec > 0
      ");
      while( $r = $db->assoc() ) {
        $r['date_creation'] = core::dateOutput($r['date_creation']);
        $tmp = explode(",",$r['days']);

        $dayNames = [
          1 => "Lun",
          2 => "Mar",
          3 => "Mer",
          4 => "Jeu",
          5 => "Ven",
        ];
        $r['days'] = [];
        foreach( $tmp as $nb )        
          $r['days'][] = $dayNames[$nb];
        $r['days'] = implode(", ",$r['days']);
        
        $rep['plannification'][] = $r;      
      }

      die(json_encode($rep,JSON_FORCE_OBJECT));
    }

    api::ajaxRep($rep);
  }

  public static function getMagasinNonPlannifie() {
    global $db;
    $id_user = intval($_POST['id_repr']??0);
    $user = user::exist($id_user);
    $id_repr = $user['id_repr'];
    $db->execute("SELECT distinct id_magasin FROM planning WHERE deleted = 0 AND id_repr = $id_repr");
    $mags = [];
    while( $r = $db->assoc() ) $mags[] = $r['id_magasin'];

    $db->execute("SELECT id_as400 FROM ref_client WHERE deleted = 0 AND CAST(id_commercial_1 as UNSIGNED) = '".intval($id_repr)."' ");
    $all = [];
    while( $r = $db->assoc() ) $all[] = $r['id_as400'];

    $missing = [];
    foreach( $all as $id_as400 ) if(!in_array($id_as400,$mags) ) $missing[] = $id_as400;
    if( empty($missing) ) 
      die(json_encode(["count" => 0],JSON_FORCE_OBJECT));
    
    $response = [];
    foreach( $missing as $id_as400 ) {
      $p = client::getPeriodicite($id_as400);
      $c = client::getByCode($id_as400);
      $response[] = [
        "id_as400" => $id_as400,
        "n" => $c['enseigne'],
        "p" => $p['libelle'] ?? "", 
      ];
    }

    $a = [
      "count" => count($response),
      "clients" => $response
    ];

    die(json_encode($a,JSON_FORCE_OBJECT));

  }


  public static function getPlanningRepr() {
    global $db;
    $db->execute("SELECT id,id_repr,displayname FROM user WHERE id_repr > 0 AND actif = 1 ORDER BY displayname");
    $rep = [0=>""];
    while( $r = $db->assoc() )
      $rep[$r['id']]= [
        "id_repr" => $r['id_repr'],
        "name" => $r['displayname']
      ];
    return $rep;
  }

  public static function truncatePlanning() {
    global $db;
    $db->execute("DELETE FROM planning WHERE green = 0 AND date_passage >= '".date("Y-m-d")."%' ");
    die('{}');
  }

  public static function userUpdate( $o ) {
    global $db;
    $id_user = API_ID_USER;
    $user = user::exist($id_user);
    $id_rep = $user['id_repr'];
    $id_client = $o['id_client'];
    $raison = isset($o['raison']) ? $o['raison'] : '';
    $date = $o['date'];
    $value = intval($o['value']);
    $client = client::get($id_client);
    $id_as400 = $client['id_as400'];

    $db->execute("
      SELECT * FROM planning
      WHERE
        id_repr = $id_rep
        AND id_magasin = $id_as400
        AND date_passage LIKE '%$date%'
    ");

    $str = "?";
    if( $value == 0 ) $str = "Annuler la validation";
    if( $value == 1 ) $str = "Valider";
    if( $value == 2 ) $str = "Annuler la visite planifiée";

    if( !$db->num() ) {
      core::logApk($user['displayname']." essaie de [$str] dans son planning une visite du ".core::dateOutput($date)." chez ".$client['enseigne']." mais la visite est introuvable dans le planning CRM...",1);
      return;
    }

    $id_pl = $db->assoc()['id'];
    $db->execute("UPDATE planning SET green = $value, raison = '".$db->escape($raison)."' WHERE id = $id_pl");

    core::logApk($user['displayname']." vient de [$str] dans son planning la visite du ".core::dateOutput($date)." chez ".$client['enseigne']);

    $strRaison = $raison;
    if( intval($raison) == $raison && intval($raison) > 0 ) {
      $db->execute('SELECT libelle FROM apk_select_options WHERE id = '.$db->escape($raison) );
      if( $db->num() ) {
        $strRaison = $db->assoc()['libelle'];
      }
    }

    $notif = $user['displayname']." vient d'annuler la visite
             du <strong>".core::dateOutput($date)."</strong> chez <strong>".$client['enseigne']."</strong>
             car : <i>$strRaison</i>";

    // Si passage en rouge => prévenir par mail 
    if( $value == 2 ) {
      $usersMails = [
        //'Christophe.Tisset',
        'gregory.sylvestre',
        # DR ????
      ];
      $dr = user::getDR($user['id_repr']);
      if( $dr ) $usersMails[] = $dr['login'];

      // Pour la dev, on envoit les mails au dev uniquement
      if( ENV == "DEV" ) $usersMails = ['gescomtest.ludivin'];

      // Notifications
      foreach( $usersMails as $login ) {
        $mail = user::getMailFromLogin( $login );
        if( !$mail ) continue;
        error_log("Envoi d'un mail a : $mail");
        new sendmail([
          'sender' => $user,
          'to' => $mail,
          'sujet' => '[Visite annulée] '.$client['enseigne'],
          'message' => $notif
        ]);
      }
    }


    return;
  }

  public static function getMesClients() {
    global $db;
    $id_user = API_ID_USER;
    $user = user::exist($id_user);
    if( !$user ) api::aError("Impossible de récupérer mes clients depuis le serveur");
    $id_rep = $user['id_repr'];
    $ids_as400 = [];
    $db->execute("
      SELECT DISTINCT id_magasin 
      FROM planning 
      WHERE 
        id_repr = '".$db->escape($id_rep)."' 
        AND deleted = 0
      ");    
    while( $r = $db->assoc() ) $ids_as400[] = $db->escape($r['id_magasin']);

    $ids = [];
    foreach( $ids_as400 as $id_as400 ) {
      $q = "SELECT id FROM ref_client WHERE id_as400 LIKE '%".$id_as400."%' ";
      $db->execute($q);
      if( $db->num() )
        $ids[] = $db->assoc()['id'];
    }
    api::ajaxRep(["clients"=>$ids]);
  }

  public static function getPlannificationFromUniqId( $uniqueId ) {
    global $db;
    $db->execute("
      SELECT * FROM 
        plannification 
      WHERE 
        ( unique_id = '".$db->escape($uniqueId)."'  OR id = '".$db->escape($uniqueId)."' )
        AND deleted = 0
    ");
    return $db->num() ? $db->assoc() : false;
  }

  public static function plannification( $params ) {
    global $db;
    $id_user = API_ID_USER;
    $user = user::exist($id_user);
    if( !$user ) api::aError("Impossible de récupérer l'utilisateur courant");
    $id_repr = $db->escape($user['id_repr']); 
    if( !isset($params['days']) )
      api::aError("Données imcomplètes recues");
    $days = $params['days'];
    $rec = intval($params['rec']??0);
    $id_recurence = $db->escape($params['id_rec']??0);
    $id_as400 = $db->escape($params['id_client']);

    if( !$id_repr || $id_repr < 1 )
      api::aError("ID de représentant introuvable");

    $client = client::getByCode($id_as400);
    if( !$client )
      api::aError("Client introuvable");

    # Eviter de créer les plannifications en double
    if( self::getPlannificationFromUniqId($id_recurence) )
      api::aError("Cette plannification a déjà été créée");

    $dayNames = [
      1 => "Lun",
      2 => "Mar",
      3 => "Mer",
      4 => "Jeu",
      5 => "Ven",
    ];
    $d = [];
    foreach( $dayNames as $num => $name )
      if( in_array($name,$days) )
        $d[] = $num;
        

    # Si récurence, on supprime les visites futures ainsi que l'ancienne plannification
    if( $rec > 0 ) {
        $db->execute("SELECT * FROM plannification WHERE id_repr = '$id_repr' AND id_client = '$id_as400' AND rec > 0 ");
        $old = $db->getArray();
        foreach( $old as $k=>$e ) {
          $db->execute("
            DELETE FROM planning 
            WHERE
              ( id_plannification = '".$e['unique_id']."' OR id_plannification = '' )
              AND date_passage > NOW()
              AND deleted = 0
          ");   
          $db->execute("DELETE FROM plannification WHERE id = $k");       
        }
    }


    $db->execute("
      INSERT INTO 
        plannification
        (unique_id,id_repr,id_client,days,rec)
        VALUES
        ( 
          '".$id_recurence."', '$id_repr','$id_as400',
          '".implode(",",$d)."', $rec
        )
    ");
    
    self::generatePlanningFromPlannification( $id_recurence );
    api::ajaxRep([]);
  }

  public static function generatePlanningFromPlannification( $id_rec, $force = false ) {
    $rec = self::getPlannificationFromUniqId($id_rec);
    $now = strtotime('now');
    if( $rec['start'] > 0 ) {
      $dto = new DateTime();
      $dto->setISODate( $rec['annee'] ?? date('Y'), $rec['start']);
      $nowDate = $dto->format('Y-m-d');
      $now = strtotime($nowDate);
    }
    $todayNumber = date('N');
    $date = new DateTime();
    $thisWeekNumber = $date->format("W");    

    # Jusqu'à quand créé-t-on des entrées ?
    $until = strtotime('+1 years');

    if( !$rec )
      core::rep("Plannification introuvable");

    global $db;
    $db->execute("SELECT id FROM planning WHERE id_plannification = '$id_rec' AND deleted = 0");
    if( $db->num() && !$force )
      core::rep("Plannification déjà existante");



    $daysNumber = explode(",",$rec['days']);
    $enDays = [
      1 => "monday",
      2 => "tuesday",
      3 => "wednesday",
      4 => "thursday",
      5 => "friday"
    ];

    $daysWords = [];
    foreach( $daysNumber as $num )
      $daysWords[$num] = $enDays[$num];

    # Géneration des entrées
    $entries = [];
    foreach( $daysWords as $numDay => $nameDay ) { # On boucle sur chaque jour séléctionné

      # Si le jour de la semaine est > à aujourd'hui on ajoute l'entrée
      if( $numDay >= $todayNumber && $rec['rec'] < 2 )
        $entries[] = date('Y-m-d',strtotime("next $nameDay"));

      # Début de la récurence
      $jump = $rec['rec'];
      
      # Si pas de récurence 
      if( $jump == 0 ) {
        # Si le numéro du jour de la semaine est un jour passé
        if( $numDay < $todayNumber )
          $entries[] = date('Y-m-d',strtotime("next $nameDay"));
        
        continue;
      }

      # On boucle max 150 fois 
      $pos = $now;

      for( $i = 0 ; $i <= 150 ; $i++ ) {
        $count = $jump * $i;
        $next = strtotime("+$count weeks", $pos);
        if( $next >= $until ) break;
        $nextDate = new DateTime( date('Y-m-d', $next) );
        $nextWeekNumber = $nextDate->format("W");
        $dto = new DateTime();
        $dto->setISODate( date('Y',$next), $nextWeekNumber,$numDay);
        $entries[] = $dto->format('Y-m-d');
      }
    }

    # Supression des doublons d'entrées
    $db->execute("
      SELECT date_passage FROM planning WHERE 
      id_repr = '".$rec['id_repr']."' 
      AND id_magasin = '".$rec['id_client']."'
      AND deleted = 0
    ");
    $histo = [];
    while( $r = $db->assoc() ) {
      $histo[] = date('Y-m-d', strtotime($r['date_passage']));
    }
    
    if( !empty($histo) ) {
      $exist = [];
      foreach( $entries as $k=>$e )
        if( in_array($e,$histo) || in_array($e,$exist) )
          unset($entries[$k]);
        else
          $exist[] = $e;
    }


    # Insertion des entrées
    if( empty($entries) )
      return;
    
    $queryHeader = "INSERT INTO planning (id_repr,id_magasin,date_passage,id_plannification) VALUES ";
    $q = [];
    foreach( $entries as $e ) {
      $q[] = "( '".$rec['id_repr']."', '".$rec['id_client']."', '".$e."', '".$rec['unique_id']."' )";

      if(count($q) > 50 ) {
        $db->execute( $queryHeader . implode(",",$q) );
        $q = [];
      }
    }
    if( count($q) > 0 )
      $db->execute( $queryHeader . implode(",",$q) );



    // Spression des doublons
    $db->execute("
      SELECT * FROM planning
      WHERE
        id_repr = '".$rec['id_repr']."'
        AND date_passage > '".date('Y-m-d')."'
    ");
    $l = $db->getArray();
    $ex = [];
    foreach( $l as $k=>$e ) {
      if( !isset($ex[$e['date_passage']])) $ex[$e['date_passage']] = [];
      if( !isset($ex[$e['date_passage']][$e['id_magasin']])) {
        $ex[$e['date_passage']][$e['id_magasin']] = true;
      }
      else {
        $db->execute("DELETE FROM planning WHERE id = $k");
      }
    }

    return;
  }

  public static function actionPlannification() {
    $id_rec = intval($_POST['id']);
    $action = $_POST['action'];
    $rec = self::getPlannificationFromUniqId($id_rec);
    if( !$rec ) core::rep('Plannification introuvable');

    if( $action == "renouv" ) {
      self::generatePlanningFromPlannification($id_rec,true);
    }
    else {
      global $db;
      $db->execute("
        DELETE FROM planning 
        WHERE
          id_plannification = '".$rec['unique_id']."'
          AND date_passage > NOW()
          AND deleted = 0
      ");
      $db->execute("DELETE FROM plannification WHERE id = $id_rec");
    }

    core::end("");
  }

  public static function getPlannificationClientStr( $params ) {
    global $db;
    $id_user = API_ID_USER;
    $user = user::exist($id_user);
    if( !$user ) api::aError("Impossible de récupérer les informations utilisateur");
    $id_rep = $user['id_repr'];    
    $id_as400 = $params['id'];
    global $db;
    $db->execute("
      SELECT 
        p.id,
        p.unique_id,
        c.enseigne,
        p.days,
        p.rec,
        p.date_creation
      FROM 
        plannification p
        LEFT JOIN ref_client c ON p.id_client = c.id_as400
      WHERE 
        p.id_repr = '".$id_rep."' 
        AND id_client = '".$db->escape($id_as400)."'
        AND p.deleted = 0
        AND p.rec > 0
    ");
    if( !$db->num() ) api::ajaxRep(["plannification" => l('page-client-infos-no-plannification'),"plannification2"=>""]);
    $pl = $db->assoc();
    $pl['date_creation'] = core::dateOutput($pl['date_creation']);
    $tmp = explode(",",$pl['days']);
    $dayNames = [
      1 => l('date-lundi'),
      2 => l('date-mardi'),
      3 => l('date-mercredi'),
      4 => l('date-jeudi'),
      5 => l('date-vendredi'),
    ];
    $pl['days'] = [];
    foreach( $tmp as $nb )        
      $pl['days'][] = $dayNames[$nb];
    $pl['days'] = implode(", ",$pl['days']);
  
    $str = [];
    $str[] = $pl['days']." ► ";
    
    if( $pl['rec'] > 1 )
      $str[] = l('periodicite-1-semaine-sur')." ".$pl['rec'];
    else 
      $str[] = l('periodicite-toutes-semaines');

    $str = implode(" ",$str);

    $str2 = "";
    $db->execute("SELECT date_passage FROM planning WHERE id_plannification = '".$pl['unique_id']."' ORDER BY date_passage DESC LIMIT 1");
    if( $db->num() ) {
      $datas = $db->assoc();
      $str2= l('js-planning-plannification-creee-le')."\n".$pl['date_creation']." - ".l("plannification-jusquau")." ".core::dateOutput($datas['date_passage']);
    }

    api::ajaxRep(["plannification" => $str, "plannification2" => $str2 ]);
  }






  public static function getTournee( $id = null ) {
    global $db;
    $id_user = ($id ?? API_ID_USER);
    $user = user::exist($id_user);
    $id_repr = intval($user['id_repr']);
    if( !$id_repr ) api::aError("Impossible de trouver votre ID commercial");
    $db->execute("SELECT id_as400,days,weeks,start,annee FROM tournee WHERE deleted = 0 AND id_repr = '$id_repr'");
    $datas = [];
    while( $r = $db->assoc() ) $datas[] = $r;
    api::ajaxRep(["tournee"=>$datas, "id" => $id_repr]);
  }

  public static function saveTournee( $tournee ) {
    global $db;
    $id_user = ($id ?? API_ID_USER);
    $user = user::exist($id_user);
    $id_repr = intval($user['id_repr']);
    if( !$id_repr ) api::aError("Impossible de trouver votre ID commercial");

    $db->execute("SELECT id FROM tournee_a_valider WHERE id_repr = $id_repr");
    if( $db->num() ) {
      $db->execute("DELETE FROM tournee_a_valider WHERE id = ".$db->assoc()['id']);
    }

    $q = "INSERT INTO tournee_a_valider (id_repr,tournee) VALUES ($id_repr,'".$db->escape(http_build_query($tournee))."')";
    $db->execute($q);

    // Mail ? 

    $dir = user::getDirecteur($id_repr);
    if( $dir ) {
      new sendmail([
        "to" => ENV == "dev" ? DEVMAIL : $dir['mail'],
        "message" => "Veuillez vous rendre sur le CRM ABC afin de consulter et approuver ou refuser la modification de tournée.",
        "sender" => ["mail"=>$user['mail'],"displayname"=>$user['displayname']],
        "sujet" => "Modification de tournée à valider (".$user['displayname'].")",
      ]);
    }




    api::ajaxRep([]);
  }

  public static function generateTournee( $id_repr ) {
    global $db;
    $db->execute("SELECT id,unique_id FROM plannification WHERE id_repr = $id_repr");
    $rez = $db->getArray();
    foreach( $rez as $k=>$e ) {
      self::generatePlanningFromPlannification($e['unique_id']);
    }
    return;
  }
  
  public static function getFuturesVisites( $p ) {
    $id_as400 = $p['id_as400'];
    global $db;
    $end = date('Y-m-d',strtotime('+6 months'));
    $db->execute("
      SELECT 
        a.date_passage,
        b.displayname
      FROM planning a
        LEFT JOIN user b ON a.id_repr = b.id_repr
      WHERE
        a.deleted = 0
        AND a.id_magasin = '".$db->escape($id_as400)."'
        AND b.deleted = 0
        AND date_passage > NOW()
        AND date_passage < '$end'
      ORDER BY date_passage
    ");
    $rez = [];
    while( $r = $db->assoc() ) 
      $rez[] = ["user" => $r['displayname'], "date" => core::dateOutput($r['date_passage'])];
    //core::dumpLog($db->query);
    api::ajaxRep($rez);
  }


  public static function getPlanningSM( $p ) {
    global $db;
    $planning = [];
    $days = [];
    $today = strtotime(date('Y-m-d'));

    $from = strtotime($p['start'] ?? '');
    $end = strtotime($p['end'] ?? '');
    
    if( !$from || !$end ) api::aError("Veuillez choisir une periode correcte");

    $jours = ($end - $from) / 86400 ;
    if( $jours > 7 )
      api::aError("La periode ne peut pas exceder 7 jours");

    $id_user = intval($p['id']??0);
    if( !$id_user ) api::aError("Veuillez choisir un promoteur");
    $user = user::exist($id_user);
    if( !$user ) api::aError("Utilisateur introuvable");
    $id_repr = $user['id_repr'];
    if( !$id_repr ) api::aError("Cet utilisateur n'a pas d'ID de représentant");

    $db->execute("
      SELECT 
        c.id_as400,
        c.enseigne, 
        p.date_passage,
        p.green as statut,
        p.raison as statut_reason
      FROM 
        planning p
        LEFT JOIN ref_client c ON p.id_magasin = c.id_as400
      WHERE
        p.date_passage BETWEEN '".date('Y-m-d',$from)."' AND '".date('Y-m-d',$end)."'
        AND p.deleted = 0
        AND p.id_repr = $id_repr

    ");
    while( $r = $db->assoc() ) {
      if( $r['id_as400'] == "" ) continue;
      $kdate = date('Y-m-d',strtotime($r['date_passage']));
      $r['date_passage'] = $kdate;
      if( !isset($planning[$kdate])) $planning[$kdate] = [];
      $r['futur'] = ( strtotime($r['date_passage']) > $today );
      $planning[$kdate][] = $r;
      if( !in_array($kdate,array_keys($days)) ) {
        $days[$kdate] = ['day' => self::getDayName($r['date_passage']), "date" => $kdate, 'ca' => 0,'t'=>0,'r'=>0];
      }
    }

    $added = [0];
    foreach( $planning as $date=>$passages ) {
      $d1 = date("Y-m-d", strtotime("-2 day", strtotime($date)));
      $d2 = date("Y-m-d", strtotime("+2 day", strtotime($date)));
      foreach( $passages as $k=>$e ) {
        $db->execute("
          SELECT id,no_cmd,no_cmd_reason,id_commande 
          FROM visite 
          WHERE 
            queue_date BETWEEN '$d1'  AND '$d2'
            AND pem = 0
            AND id_client = '".e($e['id_as400'])."'
            AND id_user = '".$id_user."'
        ");
        if( !$db->num() ) continue;
        $v = $db->assoc();
        $added[] = $v['id'];
        $e['no_cmd'] = $v['no_cmd'];
        $e['no_cmd_reason'] = core::getReason($v['no_cmd_reason']);
        $tmp = self::getVisiteHoraires( $v['id'] );
        $e['v_start'] = $tmp['start']; 
        $e['v_end'] = $tmp['end'];
        
        $day = $tmp['day'];
        if( $v['no_cmd'] == 0 ) {
          $db->execute("SELECT total
            FROM commande_apk 
            WHERE 
              id_apk = '".$v['id_commande']."'
          ");
          if( $db->num() ) {
            $datas = $db->assoc();
            $e['total'] = $datas['total'];  
          }
        }
        $planning[$date][$k] = $e;

        if( $date != $day ) {
          unset($planning[$date][$k]);
          if( in_array($day,array_keys($planning)) )
            $planning[$day][] = $e;
        }

      }

      // Ajout des visites hors passages
      $d = date("Y-m-d", strtotime($date));
      $db->execute("
      SELECT 
        v.id,v.no_cmd,v.no_cmd_reason,v.id_commande,
        v.id_client as id_as400, c.enseigne 
      FROM visite v
      LEFT JOIN ref_client c ON v.id_client = c.id_as400 
      WHERE 
        v.queue_date LIKE '$d%'
        AND pem = 0
        AND v.id_user = '".$id_user."'
        AND v.id NOT IN (".implode(",",$added).")
      ");
      $rows = $db->getArray();
      foreach( $rows as $v ) {

        $e = [
          "id_as400" => $v['id_as400'],
          "enseigne" => $v['enseigne'], 
          "date_passage" => $d,
          "green" => 1,
          "raison" => "",
        ];

        $added[] = $v['id'];
        $e['no_cmd'] = $v['no_cmd'];
        $e['no_cmd_reason'] = core::getReason($v['no_cmd_reason']);
        $tmp = self::getVisiteHoraires( $v['id'] );
        $e['v_start'] = $tmp['start']; 
        $e['v_end'] = $tmp['end'];
        
        $day = $tmp['day'];
        if( $v['no_cmd'] == 0 ) {
          $db->execute("SELECT total
            FROM commande_apk 
            WHERE 
              id_apk = '".$v['id_commande']."'
          ");
          if( $db->num() ) {
            $datas = $db->assoc();
            $e['total'] = $datas['total'];  
          }
        }
        $planning[$d][] = $e;
      }

    }

    // Total par jour
    $real = [];
    $trans = [];
    $count = [];
    foreach( $planning as $day=>$e ) {
      if( !isset($real[$day])) $real[$day] = 0;
      if( !isset($trans[$day])) $trans[$day] = 0;
      if( !isset($count[$day])) $count[$day] = 0;

      $db->execute("SELECT ca_facture FROM stat_promoteur_ca WHERE date_stat = '".$day."' AND id_repr = ".$id_repr);
      $days[$day]['ca'] = $db->num() ? $db->assoc()['ca_facture'] : 0;

      foreach( $e as $f ) {
        $count[$day]++;
        if( ( isset($f['no_cmd']) && $f['no_cmd'] == 0 ) || ( isset($f['no_cmd_reason']) && trim($f['no_cmd_reason']) == "Commande EDI") ) {
          $real[$day]++;
          $trans[$day]++;
        }
        else if( isset($f['v_start']) ) $real[$day]++;
      }
    }
    foreach( $days as $k=>$e ) {
      $day = $e['date'];
      $days[$k]['t'] = $real[$day] > 0 ? number_format( $trans[$day]*100/$real[$day] ,0,"",",") : 0;
      $days[$k]['r'] = $count[$day] > 0 ? number_format( $real[$day]*100/$count[$day] ,0,"",",") : 0;
    }


    api::ajaxRep(["planning" => $planning, "days" => array_values($days) ]);
  }

  public static function getVisiteHoraires( $id ) {
    global $db;
    $db->execute("SELECT date_step FROM visite_step WHERE id_visite = $id");
    $min = null; 
    $max = null;
    while( $r = $db->assoc() ) {
      if( $min == null ) $min = strtotime($r['date_step']);
      if( $max == null ) $max = strtotime($r['date_step']);

      $t = strtotime($r['date_step']);
      if( $min > $t ) $min = $t;
      if( $max < $t ) $max = $t;
    }
    return [
      "day" => date("Y-m-d", $min),
      "start" => date("G-i",$min),
      "end" => date("G-i",$max)
    ];
  }

  public static function getDayName( $date ) {
    $s = strtotime($date);
    return implode(' ',[
      core::$days[date('w',$s)],
      date('d', $s),
      core::$month[date('n',$s)-1]
    ]);

  }

  public static function getTourneesAValider( $full = false) {
    global $db;
    $user = user::exist(ID);
    $ids = user::getPromoteurs(ID);
    $w = "";
    if( !empty($ids) )
      $w = " CAST(id_repr as UNSIGNED) IN (".implode(",",$ids).") AND ";
    if( securite::can(8) || securite::can(5) ) $w = "";
    $db->execute("SELECT id,id_repr,date_creation".($full?',tournee':'')." FROM tournee_a_valider WHERE $w statut = 0");
    return $db->num() ? $db->getArray() : [];
  }

  public static function getPlanningTournee( $id ) {
    $user = user::exist(ID);
		if( !in_array($user['id_profile'],[6,2,5]) ) return false;
    global $db;
    $db->execute("SELECT * FROM tournee_a_valider WHERE id = $id");
    return $db->num() ? $db->assoc() : false;
  }

  public static function validateTournee() {
    set_time_limit(0);
    $id = intval($_POST['id'] ?? 0);
    $t = self::getTourneesAValider(true)[$id];
    if( !$t ) core::ajaxError("Tournée introuvable");
    $id_repr = $t['id_repr'];
    global $db;

    // Clients actuels du promoteur
    $db->execute("SELECT 	id_as400 FROM ref_client WHERE CAST(id_commercial_1 AS UNSIGNED) LIKE '%".intval($id_repr)."%' ");
    $clients = [];
    while( $r = $db->assoc() )
      $clients[] = $r['id_as400'];

    $db->execute("UPDATE tournee_a_valider SET statut = 1 WHERE id = $id");
    parse_str($t['tournee'],$tournee);
    $db->execute("DELETE FROM tournee WHERE id_repr = $id_repr");
    $db->execute("DELETE FROM plannification WHERE id_repr = $id_repr");
    $db->execute("DELETE FROM planning WHERE id_repr = $id_repr AND date_passage > '".date('Y-m-d')."' ");
    $q = "INSERT INTO tournee (id_repr,id_as400,days,weeks,`start`,annee) VALUES ";
    $q2 = "INSERT INTO plannification (unique_id,id_repr,id_client,`days`,rec,`start`,annee) VALUES ";
    $tmp = $tmp2 = [];
    $dto = new DateTime();
    $currentWeekNumber = $dto->format("W");

    


    foreach( $tournee as $e ) {
      if( !isset($e['id_as400']) || !isset($e['weeks']) || intval($e['weeks']) == 0 ) continue;
      if( !isset($e['days']) || trim($e['days']) == "" ) continue;
      if( !in_array($e['id_as400'],$clients) ) continue;

      # Supprimer doublons 
      $tmpz = explode(',',$e['days']);
      $dayz = [];
      foreach( $tmpz as $uu ) $dayz[$uu] = true;
      $e['days'] = array_keys($dayz);
      $e['days'] = implode(",",$e['days']);

      $days = $db->escape($e['days']);
      $id_as400 = $db->escape($e['id_as400']);
      $weeks = intval($e['weeks']);
      $annee = intval($e['annee']);
      $start = isset($e['start']) && intval($e['start']) > 0 ? intval($e['start']) : intval($currentWeekNumber);
      $tmp[]= " ($id_repr,'$id_as400','$days',$weeks,$start,$annee) ";
      
      $days2 = str_replace(
        ['Lun','Mar','Mer','Jeu','Ven'],
        [1,2,3,4,5],
        $days
      );
      $tmp2[]= " ('".uniqid()."',$id_repr,'$id_as400','$days2',$weeks,$start,$annee) ";

    }
    if( !empty($tmp) ) {
      $db->execute($q.implode(",",$tmp));
      $db->execute($q2.implode(",",$tmp2));
    }

    self::generateTournee( $id_repr );

    // Envoi du message de validation

    $me = user::exist(ID);

    $user = user::getFromIdRepr($id_repr);
    $mail = $user['mail'];
    if( ENV == "DEV" )  $mail = DEVMAIL;

    new sendmail([
      "to" => $mail,
      "message" => "Bonjour ".$user['displayname'].",<br/><br/>".$me['displayname']." a validé votre planning de tournée le ".date("d/m/Y \à G\hi").".",
      "sender" => ["mail"=>$me['mail'],"displayname"=>$me['displayname']],
      "sujet" => "Validation de votre planning de tournée",
    ]);



    die('{}');
  }
  public static function refuserTournee() {
    $id = intval($_POST['id'] ?? 0);
    $t = self::getTourneesAValider()[$id];
    if( !$t ) core::ajaxError("Tournée introuvable");
    $id_repr = $t['id_repr'];

    $commantaire = trim($_POST['com'] ?? "");

    global $db;
    $db->execute("UPDATE tournee_a_valider SET statut = 2, deleted = 1 WHERE id = $id");
    $me = user::exist(ID);
    $user = user::getFromIdRepr($id_repr);
    $mail = $user['mail'];
    if( ENV == "DEV" )  $mail = DEVMAIL;

    $msg = "Bonjour ".$user['displayname'].",<br/><br/>".$me['displayname']." a refusé votre planning de tournée le ".date("d/m/Y \à G\hi").".";
    if( $commantaire != "" ) {
      $msg .= "<br/><br/>Commentaire de refus : <br/>".$commantaire;
    }


    new sendmail([
      "to" => $mail,
      "message" => $msg,
      "sender" => ["mail"=>$me['mail'],"displayname"=>$me['displayname']],
      "sujet" => "Planning de tournée refusé",
    ]);




    die('{}');
  }


}
