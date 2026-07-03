<?php

class visite {

  public static function new( $visite ) {
      if (!empty($visite['is_juva'])) {
        return self::newJuva($visite);
    }
    global $phpInput, $db;
    $exist = false;

    $id_apk_visite = $db->escape(isset($visite['id_visite']) ? $visite['id_visite'] : '');
    $pem = ( $visite['pem'] ?? 0 ) ? 1 : 0;
    $id_visite_linked = $db->escape($visite['id_visite_linked'] ?? "");

    if( defined('API_ID_USER') && API_ID_USER > 0 ) {
      $user = user::exist(API_ID_USER);
      core::logApk( "Envoi d'une nouvelle visite au serveur : ".$id_apk_visite);
    }

    #check avant insertion pour éviter doublon

    $q = "SELECT id FROM visite WHERE id_visite = '$id_apk_visite' AND deleted = 0";
    $db->execute($q);
    if( $db->num() ) {
      $id_visite = $db->assoc()['id'];
      $v = self::get($id_visite);
      $exist = true;
      if( count($v['photos']) > 0 ) {
        //core::logApk("La visite $id_apk_visite existe déjà et a déjà des photos, donc visite ignorée");
        return;
      }
      else {
        //core::logApk("La visite $id_apk_visite existe déjà mais tentative de récupération des photos...",1);
      }
    }

    if( !$exist ) {
      
     


      $force = ['id_commande', 'id_client', 'id_visite', 'no_cmd','no_cmd_reason', 'pmc_state', 'pmc_coms',
       'dn_abc', 'dn_concurence', 'queue_date'];
      foreach( $force as $f ) {
        if( !isset($visite[$f]) )
          $visite[$f] = "";
      }

      $visite_alerte_raison = $visite['alerte']['raison']??'';
      $alerte_raison = ($visite_alerte_raison != '') ? implode(",",$visite_alerte_raison) : '';
      $alerte_obs = $visite['alerte']['observations']??'';

      $q = "INSERT INTO visite( 
          id_user, id_commande, id_client, id_visite, no_cmd,no_cmd_reason, pmc_state, pmc_coms, 
          dn_abc, dn_concurence, queue_date, pem, id_visite_linked, alerte_raison, alerte_obs, is_juva
        )
      VALUES (
        '".( defined('API_ID_USER') ? API_ID_USER : 0 )."',
        '".$db->escape($visite['id_commande'])."',
        '".$db->escape($visite['id_client'])."',
        '".$db->escape($visite['id_visite'])."',
        '".$db->escape($visite['no_cmd'])."',
        '".$db->escape($visite['no_cmd_reason'])."',
        '".$db->escape($visite['pmc_state'])."',
        '".nl2br($db->escape($visite['pmc_coms']))."',
        '".$db->escape($visite['dn_abc'])."',
        '".$db->escape($visite['dn_concurence'])."',
        '".$db->escape($visite['queueDate'])."',
        $pem, '$id_visite_linked',
        '".$db->escape($alerte_raison)."',
        '".$db->escape($alerte_obs)."',
        ".intval($visite['is_juva'] ?? 0)."
      )"; 
      //error_log("Requette : ".$q);
      $db->execute($q);
      $id_visite = $db->lastId();
      core::logApk("La visite $id_apk_visite a bien été créée avec l'ID ".$id_visite);

      /* MAIL Alerte Commerciale */
      if($alerte_raison != '' && isset($user) && $user){
        $body_to = '';
        $mails = [];

        $mails[] = user::getDirecteur($user['id_repr'])['mail'];

        $db->execute("SELECT * FROM ref_client WHERE id_as400 = '".$db->escape($visite['id_client'])."'");
        if( $db->num() ){
          $cli = $db->assoc();
          $cs = $cli['id_commercial_2'];
          // TODO reg (supp le C), voir autres exemples ?
          $mail_tmp = user::getFromIdRepr($cs);
          if(isset($mail_tmp))
            $mails[] = $mail_tmp['mail'];
        }
        
        $db->execute("SELECT mail FROM user WHERE id IN (SELECT DISTINCT id_user FROM alerte_user WHERE deleted = 0)");
        while( $r = $db->assoc() ){
          $mails[] = $r['mail'];
        }
        //$mails[] = 'gregory.sylvestre@abcosmetique.com';
        //$mails[] = 'christophe.tisset@abcosmetique.com';

        if( ENV == "DEV" ) {
          $body_to = "<br/><br>".implode(",", $mails);
          $mails = [];
          $mails[] = 'j.gillard@snew.fr';
        }

        $body_raisons = '';
        $db->execute("SELECT * FROM apk_select_options WHERE id IN (".$alerte_raison.")");
        while( $r = $db->assoc() ) $body_raisons .= $r['libelle'].'<br/>';
        $body_obs = ($alerte_obs != '') ? 'Observations :<br>'.$alerte_obs : '';
        $body = "
          <p style='font-size:14px;line-height:25px;color:#444;'>
          Bonjour,<br/>
          <strong>".$user['displayname']."</strong> a saisi une alerte commerciale depuis l'application mobile ABC.<br/>
          Voici le lien de la visite :".URL_APP_ROOT.'Visites/'.$id_visite."<br/>
          aison(s) :<br/>R"
          .$body_raisons.$body_obs.$body_to."
          </p>
        ";

        $sujet_cli = isset($cli) ? $cli['enseigne'] : '';

        new sendmail([
          "to" => implode(",", $mails),
          "message" => $body,
          "sender" => ["mail"=>$user['mail'],"displayname"=>$user['displayname']],
          "sujet" => "Alerte commerciale ".$sujet_cli
        ]);
      }
    }

    # Photos

    $prePath = FILES."visites/";
    $path = $visite['id_client'].'/'.date('Y').'/';
    if( !is_dir($prePath.$path) ) mkdir($prePath.$path, 0777, true);
    $photos = json_decode($phpInput,JSON_OBJECT_AS_ARRAY);
    if( !isset($photos['id_visite']) ) {
      //core::dumpLog($photos); #
      $cp = 0;
      if( $photos && !empty($photos) ) {
        foreach( $photos as $items ) {
          foreach( $items as $p ) {
            if( !isset($p['base64']) ) continue;
            $img = base64_decode($p['base64']);
            $filename = $p['type']."-".time()."-".rand(1000,9999).".jpg";
            $full = $prePath.$path.$filename;
            file_put_contents( $full, $img);
            $cp++;
            $db->execute("INSERT INTO visite_photo (id_visite,file,size) VALUES ($id_visite,'".($path.$filename)."','".filesize($full)."')");
          }
        }
        if( $exist ) {
          core::logApk("FIN D'ERREUR : $cp photos obtenues pour la visite #".$id_visite, 3);
        }
      }
      else {
        core::logApk("ERREUR : Aucune photo recue pour la visite #".$id_visite, 1);
      }
    }

    if( $exist ) return;

    # Steps

    foreach( $visite['steps'] as $nb => $s ) {
      $tmp = explode("_",$nb);
      $nb = intval($tmp[1]);
      $db->execute("INSERT INTO visite_step (id_visite,step_nb,date_step) VALUES ($id_visite,$nb,'".$s."')");
    }

    # Promos
    foreach( $visite['promos'] as $s ) {
      $val = $db->escape($s['id_as400']);
      $db->execute("INSERT INTO visite_promo (id_visite,id_as400) VALUES ($id_visite,'$val')");
    }

    if( isset($visite['deballage']) ) {
      $db->execute("
        INSERT INTO visite_deballage (id_visite,state,debut,fin)
        VALUES
        ( 
          $id_visite,
          '".( isset($visite['deballage']['state']) && $visite['deballage']['state'] == true ? 1 : 0 )."',
          '".e( $visite['deballage']['timer']['start'] ?? "" )."',
          '".e( $visite['deballage']['timer']['end'] ?? "" )."'
        )
      ");
    }

    # DN
    /*
    if( !isset($visite['dn']) || empty($visite['dn']) || !isset($visite['dn']['abc'])) {
      if( isset($visite['dn_abc']) && isset($visite['dn_concurence']) ) {
        $visite['dn'] = [
          "abc" => $visite['dn_abc'],
          "concu" => $visite['dn_concurence'],
        ];
      }
    }*/

    if( isset( $visite['dn'] ) ) {
      //error_log("Visite DN a enregistrer...");
      //core::dumpLog($visite['dn']);
      $elems = ["abc","concu"];
      foreach( $elems as $el ) {
        if( isset( $visite['dn'][$el] ) ) {
          $type = strtoupper($el);
          foreach( $visite['dn'][$el] as $k=>$e ) {
            if( trim($e['ma']) == "" || $e['me'] == "" ) continue;
            $q = "INSERT INTO visite_dn (id_client,id_visite,type,marque,gamme,metrage)
                  VALUES
                  (
                    '".$db->escape($visite['id_client'])."',
                    $id_visite,
                    '$type',
                    '".$db->escape($e['ma'])."',
                    '".$db->escape($e['ga'])."',
                    '".$db->escape($e['me'])."'
                  )";
            $db->execute($q);
          }
        }
      }
    }

    if( isset($visite['dnPem']) ) {
      foreach( $visite['dnPem'] as $k=>$e ) {
        if( trim($e['nom']) == "" &&  trim($e['gamme']) == "" ) continue;
        $q = "INSERT INTO visite_dn (id_client,id_visite,type,marque,gamme,metrage)
              VALUES
              (
                '".$db->escape($visite['id_client'])."',
                $id_visite,
                'PEM ABC',
                '".$db->escape($e['nom'])."',
                '".$db->escape($e['gamme'])."',
                0
              )";
        $db->execute($q);
      }
    }

    if( !isset( $visite['dn'] ) && !$pem )
      core::logApk("Visite $id_visite : aucune DN n'est présente dans la visite envoyée", 1);
    if( empty( $visite['dn'] ) && !$pem )
      core::logApk("Visite $id_visite : La DN reçue par le serveur est vide", 1);


    // Questionnaire
    if( isset($visite['questionnaire']) ) {
      $q = $visite['questionnaire'];
      $buts = isset($q['but']) ? implode(',',$q['but']) : "";
      $q1 = isset($q['q1']) && $q['q1'] == 'O' ? 'O' : 'N';
      $q2 = isset($q['q2']) && $q['q2'] == 'O' ? 'O' : 'N';
      $chef = isset($q['chef']) && $q2 == 'O' ? $db->escape($q['chef']) : '';
      $obs = isset($q['obs']) ? $db->escape(str_replace("\n","<br/>",$q['obs'])) : "";
      $db->execute("
        INSERT INTO visite_questionnaire
        (id_visite,q1,q2,chef,but,obs)
        VALUES
        ($id_visite,'$q1','$q2','$chef','$buts','$obs')
      ");
    }   
    
    // Colis scannés
    if( isset($visite['scanColis']) ) {
        foreach( $visite['scanColis'] as $k=>$e ) {
          $db->execute("
            SELECT * FROM 
              commande_colis_codes 
            WHERE
              ( code = '".e($e['codes'][0]??"")."' OR code2= '".e($e['codes'][1]??"")."' )
              AND date_scan IS NULL
          ");
          if( $db->num() ) {
            $datas = $db->assoc();
            $db->execute("
              UPDATE 
                commande_colis_codes
              SET
                date_scan = '".($e['date']??"")."',
                manually = '".( isset($e['manually']) && $e['manually'] == true ? 1 : 0 )."'
              WHERE
                id = ".$datas['id']."
            ");
            $db->execute("UPDATE commande_colis SET id_visite = ".$id_visite." WHERE id = ".$datas['id_commande_colis']);
          }
        }
    }






    //core::logApk("Fin d'enregistrement de la visite #".$id_visite);

    //@api::debugVisites($visite);

    return;
  }

  public static function newJuva($visite) {
    global $phpInput, $db;
    $exist = false;

    $id_apk_visite = $db->escape(isset($visite['id_visite']) ? $visite['id_visite'] : '');
    $pem = ( $visite['pem'] ?? 0 ) ? 1 : 0;
    $id_visite_linked = $db->escape($visite['id_visite_linked'] ?? "");

    if( defined('API_ID_USER') && API_ID_USER > 0 ) {
      $user = user::exist(API_ID_USER);
      core::logApk( "Envoi d'une nouvelle visite juva au serveur : ".$id_apk_visite);
    }

    #check avant insertion pour éviter doublon

    $q = "SELECT id FROM visite_juva WHERE id_visite = '$id_apk_visite' AND deleted = 0";
    $db->execute($q);
    if( $db->num() ) {
      $id_visite = $db->assoc()['id'];
      $v = self::get($id_visite);
      $exist = true;
      if( count($v['photos']) > 0 ) {
        //core::logApk("La visite $id_apk_visite existe déjà et a déjà des photos, donc visite ignorée");
        return;
      }
      else {
        //core::logApk("La visite $id_apk_visite existe déjà mais tentative de récupération des photos...",1);
      }
    }

    if( !$exist ) {
      
     


      $force = ['id_commande', 'id_client', 'id_visite', 'no_cmd','no_cmd_reason', 'pmc_state', 'pmc_coms',
       'dn_abc', 'dn_concurence', 'queue_date'];
      foreach( $force as $f ) {
        if( !isset($visite[$f]) )
          $visite[$f] = "";
      }

      $visite_alerte_raison = $visite['alerte']['raison']??'';
      $alerte_raison = ($visite_alerte_raison != '') ? implode(",",$visite_alerte_raison) : '';
      $alerte_obs = $visite['alerte']['observations']??'';

      $q = "INSERT INTO visite_juva( 
          id_user, id_commande, id_client, id_visite, no_cmd,no_cmd_reason, pmc_state, pmc_coms, 
          dn_abc, dn_concurence, queue_date, pem, id_visite_linked, alerte_raison, alerte_obs, is_juva
        )
      VALUES (
        '".( defined('API_ID_USER') ? API_ID_USER : 0 )."',
        '".$db->escape($visite['id_commande'])."',
        '".$db->escape($visite['id_client'])."',
        '".$db->escape($visite['id_visite'])."',
        '".$db->escape($visite['no_cmd'])."',
        '".$db->escape($visite['no_cmd_reason'])."',
        '".$db->escape($visite['pmc_state'])."',
        '".nl2br($db->escape($visite['pmc_coms']))."',
        '".$db->escape($visite['dn_abc'])."',
        '".$db->escape($visite['dn_concurence'])."',
        '".$db->escape($visite['queueDate'])."',
        $pem, '$id_visite_linked',
        '".$db->escape($alerte_raison)."',
        '".$db->escape($alerte_obs)."',
        ".intval($visite['is_juva'] ?? 0)."
      )"; 
      //error_log("Requette : ".$q);
      $db->execute($q);
      $id_visite = $db->lastId();
      core::logApk("La visite juva $id_apk_visite a bien été créée avec l'ID ".$id_visite);

      /* MAIL Alerte Commerciale */
      if($alerte_raison != '' && isset($user) && $user){
        $body_to = '';
        $mails = [];

        $mails[] = user::getDirecteur($user['id_repr'])['mail'];

        $db->execute("SELECT * FROM ref_client WHERE id_as400 = '".$db->escape($visite['id_client'])."'");
        if( $db->num() ){
          $cli = $db->assoc();
          $cs = $cli['id_commercial_2'];
          // TODO reg (supp le C), voir autres exemples ?
          $mail_tmp = user::getFromIdRepr($cs);
          if(isset($mail_tmp))
            $mails[] = $mail_tmp['mail'];
        }
        
        $db->execute("SELECT mail FROM user WHERE id IN (SELECT DISTINCT id_user FROM alerte_user WHERE deleted = 0)");
        while( $r = $db->assoc() ){
          $mails[] = $r['mail'];
        }
        //$mails[] = 'gregory.sylvestre@abcosmetique.com';
        //$mails[] = 'christophe.tisset@abcosmetique.com';

        if( ENV == "DEV" ) {
          $body_to = "<br/><br>".implode(",", $mails);
          $mails = [];
          $mails[] = 'j.gillard@snew.fr';
        }

        $body_raisons = '';
        $db->execute("SELECT * FROM apk_select_options WHERE id IN (".$alerte_raison.")");
        while( $r = $db->assoc() ) $body_raisons .= $r['libelle'].'<br/>';
        $body_obs = ($alerte_obs != '') ? 'Observations :<br>'.$alerte_obs : '';
        $body = "
          <p style='font-size:14px;line-height:25px;color:#444;'>
          Bonjour,<br/>
          <strong>".$user['displayname']."</strong> a saisi une alerte commerciale depuis l'application mobile ABC.<br/>
          Voici le lien de la visite :".URL_APP_ROOT.'Visites/'.$id_visite."<br/>
          aison(s) :<br/>R"
          .$body_raisons.$body_obs.$body_to."
          </p>
        ";

        $sujet_cli = isset($cli) ? $cli['enseigne'] : '';

        new sendmail([
          "to" => implode(",", $mails),
          "message" => $body,
          "sender" => ["mail"=>$user['mail'],"displayname"=>$user['displayname']],
          "sujet" => "Alerte commerciale ".$sujet_cli
        ]);
      }
    }

    # Photos

    $prePath = FILES."visites_juva/";
    $path = $visite['id_client'].'/'.date('Y').'/';
    if( !is_dir($prePath.$path) ) mkdir($prePath.$path, 0777, true);
    $photos = json_decode($phpInput,JSON_OBJECT_AS_ARRAY);
    if( !isset($photos['id_visite']) ) {
      // core::dumpLog($photos); #

      $cp = 0;
      if( $photos && !empty($photos) ) {
        foreach( $photos as $items ) {
          foreach( $items as $p ) {
            if( !isset($p['base64']) ) continue;
            $img = base64_decode($p['base64']);
            $filename = $p['type']."-".time()."-".rand(1000,9999).".jpg";
            $full = $prePath.$path.$filename;
            file_put_contents( $full, $img);
            $cp++;
            $db->execute("INSERT INTO visite_photo_juva (id_visite,file,size) VALUES ($id_visite,'".($path.$filename)."','".filesize($full)."')");
          }
        }
        if( $exist ) {
          core::logApk("FIN D'ERREUR : $cp photos obtenues pour la visite #".$id_visite, 3);
        }
      }
      else {
        core::logApk("ERREUR : Aucune photo recue pour la visite #".$id_visite, 1);
      }
    }

    if( $exist ) return;

    # Steps

    foreach( $visite['steps'] as $nb => $s ) {
      $tmp = explode("_",$nb);
      $nb = intval($tmp[1]);
      $db->execute("INSERT INTO visite_step_juva (id_visite,step_nb,date_step) VALUES ($id_visite,$nb,'".$s."')");
    }

    # Promos
    foreach( $visite['promos'] as $s ) {
      $val = $db->escape($s['id_as400']);
      $db->execute("INSERT INTO visite_promo_juva (id_visite,id_as400) VALUES ($id_visite,'$val')");
    }

    if( isset($visite['deballage']) ) {
      $db->execute("
        INSERT INTO visite_deballage_juva (id_visite,state,debut,fin)
        VALUES
        ( 
          $id_visite,
          '".( isset($visite['deballage']['state']) && $visite['deballage']['state'] == true ? 1 : 0 )."',
          '".e( $visite['deballage']['timer']['start'] ?? "" )."',
          '".e( $visite['deballage']['timer']['end'] ?? "" )."'
        )
      ");
    }

    # DN
    /*
    if( !isset($visite['dn']) || empty($visite['dn']) || !isset($visite['dn']['abc'])) {
      if( isset($visite['dn_abc']) && isset($visite['dn_concurence']) ) {
        $visite['dn'] = [
          "abc" => $visite['dn_abc'],
          "concu" => $visite['dn_concurence'],
        ];
      }
    }*/

    if( isset( $visite['dn'] ) ) {
      //error_log("Visite DN a enregistrer...");
      //core::dumpLog($visite['dn']);
      $elems = ["abc","concu"];
      foreach( $elems as $el ) {
        if( isset( $visite['dn'][$el] ) ) {
          $type = strtoupper($el);
          foreach( $visite['dn'][$el] as $k=>$e ) {
            if( trim($e['ma']) == "" || $e['me'] == "" ) continue;
            $q = "INSERT INTO visite_dn_juva (id_client,id_visite,type,marque,gamme,metrage,unite)
                  VALUES
                  (
                    '".$db->escape($visite['id_client'])."',
                    $id_visite,
                    '$type',
                    '".$db->escape($e['ma'])."',
                    '".$db->escape($e['ga'])."',
                    '".$db->escape($e['me'])."',
                    '".$db->escape($e['unite'])."'
                   
                  )";
            $db->execute($q);
          }
        }
      }
    }

    if( isset($visite['dnPem']) ) {
      foreach( $visite['dnPem'] as $k=>$e ) {
        if( trim($e['nom']) == "" &&  trim($e['gamme']) == "" ) continue;
        $q = "INSERT INTO visite_dn_juva (id_client,id_visite,type,marque,gamme,metrage)
              VALUES
              (
                '".$db->escape($visite['id_client'])."',
                $id_visite,
                'PEM ABC',
                '".$db->escape($e['nom'])."',
                '".$db->escape($e['gamme'])."',
                0
              )";
        $db->execute($q);
      }
    }

    if( !isset( $visite['dn'] ) && !$pem )
      core::logApk("Visite $id_visite : aucune DN n'est présente dans la visite envoyée", 1);
    if( empty( $visite['dn'] ) && !$pem )
      core::logApk("Visite $id_visite : La DN reçue par le serveur est vide", 1);


    // Questionnaire
    if( isset($visite['questionnaire']) ) {
      $q = $visite['questionnaire'];
      $buts = isset($q['but']) ? implode(',',$q['but']) : "";
      $q1 = isset($q['q1']) && $q['q1'] == 'O' ? 'O' : 'N';
      $q2 = isset($q['q2']) && $q['q2'] == 'O' ? 'O' : 'N';
      $chef = isset($q['chef']) && $q2 == 'O' ? $db->escape($q['chef']) : '';
      $obs = isset($q['obs']) ? $db->escape(str_replace("\n","<br/>",$q['obs'])) : "";
      $db->execute("
        INSERT INTO visite_questionnaire_juva
        (id_visite,q1,q2,chef,but,obs)
        VALUES
        ($id_visite,'$q1','$q2','$chef','$buts','$obs')
      ");
    }   
    
    // Colis scannés
    if( isset($visite['scanColis']) ) {
        foreach( $visite['scanColis'] as $k=>$e ) {
          $db->execute("
            SELECT * FROM 
              commande_colis_codes 
            WHERE
              ( code = '".e($e['codes'][0]??"")."' OR code2= '".e($e['codes'][1]??"")."' )
              AND date_scan IS NULL
          ");
          if( $db->num() ) {
            $datas = $db->assoc();
            $db->execute("
              UPDATE 
                commande_colis_codes
              SET
                date_scan = '".($e['date']??"")."',
                manually = '".( isset($e['manually']) && $e['manually'] == true ? 1 : 0 )."'
              WHERE
                id = ".$datas['id']."
            ");
            $db->execute("UPDATE commande_colis SET id_visite = ".$id_visite." WHERE id = ".$datas['id_commande_colis']);
          }
        }
    }






    //core::logApk("Fin d'enregistrement de la visite #".$id_visite);

    //@api::debugVisites($visite);

    return;
}

  public static function countTotal( $today = false ) {
    global $db;
    $w = ( $today ? " AND  date_creation LIKE '%".date("Y-m-d")."%' " : '' );
    $db->execute("SELECT count(*) as nb FROM visite WHERE deleted = 0 ".$w);
    return core::n($db->assoc()['nb']);
  }
  public static function countTotalPhotos( $today = false ) {
    global $db;
    $w = ( $today ? " WHERE  date_creation LIKE '%".date("Y-m-d")."%' " : '' );
    $db->execute("SELECT count(*) as nb FROM visite_photo ".$w);
    return core::n($db->assoc()['nb']);
  }

  public static function getBoard( $params = [] ) {
    global $db;
    $w = [
      " v.deleted = 0 ",
    ];
    $db->execute("
      SELECT
        v.id,
        c.enseigne as client,
        cmd.id as commande,
        cmd.total as  total_commande,
        v.queue_date as date,
        u.displayname as commercial,
        (select count(*) as nb from visite_photo where id_visite = v.id) as photos
      FROM
        visite v
        LEFT JOIN commande_apk cmd ON v.id_commande = cmd.id_apk
        LEFT JOIN user u ON v.id_user = u.id
        LEFT JOIN ref_client c ON v.id_client = c.id
      WHERE
        ".implode(" AND ", $w)."
      ORDER BY id DESC
      LIMIT
        500
    ");
    $tmp = [];
    while( $r = $db->assoc() ) {
        $r['date'] = core::dateOutput($r['date']);
        $r['photos'] = $r['photos'] == 0 ? '<em>aucune photo</em>' : $r['photos']." photo".($r['photos']>1?'s':'');
        $r['commande'] = ( $r['commande'] > 0 ? '#'.$r['commande'].' <em>('.$r['total_commande'].'€)</em>' : '<em>'.l('cmd-found-not').'</em>');
        $tmp[] = $r;
    }
    return $tmp;
  }
  
  public static function get( $id ) {
    global $db;
    $db->execute("SELECT * FROM visite WHERE id = ".intval($id)." and deleted = 0");
    $v = $db->assoc();
    if( !$v ) return false;
    $db->execute("SELECT * FROM visite_step WHERE id_visite = ".intval($id));
    $v['steps'] = $db->getArray();
    $db->execute("SELECT * FROM visite_photo WHERE id_visite = ".intval($id));
    $v['photos'] = $db->getArray();
    $db->execute("SELECT * FROM visite_promo WHERE id_visite = ".intval($id));
    $v['promos'] = $db->getArray();
    $db->execute("SELECT * FROM commande_apk WHERE id_apk = '".$db->escape($v['id_commande'])."' ");
    $v['cmd'] = ( $db->num() ? $db->assoc() : false );
    return $v;
  }

  public static function getJuva($id) {
    global $db;
    $db->execute("SELECT * FROM visite_juva WHERE id = ".intval($id)." and deleted = 0");
    $v = $db->assoc();
    if( !$v ) return false;
  
    $db->execute("SELECT * FROM visite_step_juva WHERE id_visite = ".intval($id));
    $v['steps'] = $db->getArray();
  
    $db->execute("SELECT * FROM visite_photo_juva WHERE id_visite = ".intval($id));
    $v['photos'] = $db->getArray();
  
    $db->execute("SELECT * FROM visite_promo_juva WHERE id_visite = ".intval($id));
    $v['promos'] = $db->getArray();
  
    $db->execute("SELECT * FROM commande_apk WHERE id_apk = '".$db->escape($v['id_commande'])."' ");
    $v['cmd'] = ( $db->num() ? $db->assoc() : false );
  
    return $v;
  }
  

  public static function getLastClientVisites( $id_as400, $limit = 10 ) {
    global $db;
    $db->execute("SELECT id FROM visite WHERE id_client = '$id_as400' ORDER BY id DESC LIMIT $limit");
    if( !$db->num() ) return false;
    $ids = [];
    while( $r = $db->assoc() ) $ids[] = $r['id'];
    return $ids;
  }

  public static function getLastClientVisitesPrintable() {
    $id_as400 = $_POST['id_as400'];
    $limit = $_POST['limit'] ?? 100;
    $tpl = [];
    $v = visite::getLastClientVisites($id_as400,$limit);
    if( !$v ) 
      $tpl[] = '<p class="tc text-secondary">'.l('no-results').'</p>';
    else {
        $tmp = ['<div class="list-group" style="max-height:500px;overflow:auto;">'];
        $tmp[]= '';
        foreach( $v as $id_visite ) {
            $visite = visite::get($id_visite);
            $tmp[] = '<a href="'.URL_APP_ROOT.'Visites/'.$id_visite.'" target="_blank" class="list-group-item list-group-item-action">';
            $tmp[] = '<span class="badge badge-primary"><i class="fas fa-user-tie"></i> '.user::getNameFromId($visite['id_user']).'</span>';
            $tmp[] = ' le '.core::dateOutput($visite['queue_date']).' <em class="small">(il y a '.core::dateFrom($visite['queue_date']).')</em>';
            $tmp[] = '</a>';
        }
        $tmp[] = '</div>';
        $tpl[] = implode($tmp);
    }
    core::ajaxReturnHtml($tpl);
  }

  public static function getVisiteDn( $id_visite ) {
    global $db;
    $db->execute("SELECT * FROM visite_dn WHERE id_visite = ".intval($id_visite));
    if( !$db->num() ) return [];
    $rez = $db->getArray();
    return $rez;
  }
  public static function getVisiteDnJuva( $id_visite ) {
    global $db;
    $db->execute("SELECT * FROM visite_dn_juva WHERE id_visite = ".intval($id_visite));
    if( !$db->num() ) return [];
    $rez = $db->getArray();
    return $rez;
  }

  public static function getPhotosVisite() {
    global $params,$db;
    $id_visite = intval($params['id_visite']);
    $v = self::get($id_visite);
    if( !$v ) api::aError("Visite introuvable");
    $p = [];
    foreach( $v['photos'] as $k=> $e ) {
      $tmp = explode("/",$e['file']);
      $name = array_pop($tmp);
      $tmp = explode("-",$name);
      $type = $tmp[0];
      if( !isset($p[$type])) $p[$type] = [];
      $p[$type][] = URL."datas/visites/".$e['file'];
    }
    api::ajaxRep($p);
  }
  public static function getPhotosVisiteJuva($id_visite) {
    global $db;
    $db->execute("SELECT * FROM visite_photo_juva WHERE id_visite = ".intval($id_visite));
    $photos = $db->getArray();
  
    $p = [];
    foreach( $photos as $e ) {
      $tmp = explode("/", $e['file']);
      $name = array_pop($tmp);
      $tmp = explode("-", $name);
      $type = $tmp[0];
      if( !isset($p[$type])) $p[$type] = [];
      $p[$type][] = URL."datas/visites_juva/".$e['file'];
    }
  
    api::ajaxRep($p);
  }
  

  public static function getVisitesClient() {
    global $params,$db;
    $id_client = $db->escape($params['id_client']);
    $visites = [];
    $db->execute("
      SELECT id,queue_date FROM visite
      WHERE
        id_client = '".$id_client."'
      ORDER BY queue_date DESC
      LIMIT 30
    ");
    //error_log($db->query);
    while( $r = $db->assoc() ) {
      $visites[] = [
        "id" => $r['id'],
        "date" => core::dateOutput($r['queue_date'])
      ];
    }
    //core::dumpLog($visites);
    api::ajaxRep(["visites"=>$visites]);
  }

  public static function getDailyVisits() {
    global $db;
    $d = date('Y-m-d');
    $ids = [];
    $db->execute("SELECT id FROM visite WHERE queue_date LIKE '$d%' AND deleted = 0 ORDER BY date_creation DESC LIMIT 15");
    while( $r = $db->assoc() ) $ids[] = $r['id'];
    return $ids;
  }

  public static function searchBoard() {
    global $cpQueries;
    $cpQueriesBefore = $cpQueries;


    $str = trim(strtolower($_POST['str']));
    $limit = intval($_POST['limit']);
    $offset = intval($_POST['offset']);
    $from = $_POST['from'];
    $to = $_POST['to'];

    $w = [];
    $w[] = "v.deleted = 0";
    $w[] = "v.pem = 0";

    $checkDate = true;
    if( $from == "" || $to == "" ) $checkDate = false;
    $to = date('Y-m-d',strtotime('+1 day',strtotime($to)));

    if( $checkDate )
      $w[] = " v.queue_date >= '$from' AND v.queue_date <= '$to' ";

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
        LOWER(c.enseigne) LIKE '%".$str."%'
        OR
        LOWER(c.id_as400) LIKE '%".$str."%'
        OR
        v.date_creation LIKE '%".$date."%'
      )
      ";
    }

    /* Nb total de résultats */
    $queryCount = " 
      SELECT 
        count(*) as nb
      FROM
        visite v
        LEFT JOIN user u ON v.id_user = u.id
        LEFT JOIN ref_client c ON v.id_client = c.id_as400
      WHERE 
        ".implode(" AND ", $w);
    $db->execute($queryCount);
    $count = $db->assoc()['nb'];


    /* Résultats à afficher */
    $q = "
      SELECT 
        v.id,
        v.no_cmd_reason,
        v.queue_date,
        c.enseigne as enseigne,
        v.id_client as id_as400,
        u.displayname as user,
        cmd.id as id_cmd,
        cmd.total as total,
        vpem.id AS vpem,
        rcp.id_periodicite,
        v.is_juva,
        ( SELECT count(*) AS nb FROM visite_photo WHERE id_visite = v.id ) as photo,
        ( SELECT count(*) AS nb FROM visite_promo WHERE id_visite = v.id ) as promo,
        ( SELECT count(*) AS nb FROM visite_dn WHERE id_visite = v.id ) as dn
      FROM
        visite v
        LEFT JOIN user u ON v.id_user = u.id
        LEFT JOIN commande_apk cmd ON v.id_commande = cmd.id_apk
        LEFT JOIN ref_client c ON v.id_client = c.id_as400
        LEFT JOIN visite vpem ON vpem.id_visite_linked = v.id_visite AND vpem.pem = 1
        LEFT JOIN ref_client_periodicite rcp ON rcp.id_client_as400 = c.id_as400
      WHERE
        ".implode(" AND ", $w)."
      ORDER BY
        v.queue_date DESC
      LIMIT
        ".($offset*$limit).", $limit
    ";
    $db->execute($q);
    if( !$db->num() ) 
      core::ajax(["html"=>"<tr><td class='text-center' colspan='9'>".l('no-results')."</td></tr>"]);

    $ids = [];
    while( $r = $db->assoc() ) $ids[$r['id']] = $r;
    $datas = [];
    foreach( $ids as $id_visite => $vis ) {

      $clientPeriodicite = client::getPeriodicite($vis['id_as400'],$vis['id_periodicite']);
      $periodicite = !empty($clientPeriodicite) ? $clientPeriodicite['libelle'] : '';



      $cmd = $vis['id_cmd'] > 0;
      $raison = "";
      $linkCmd = '<i>'.trim(l('cmd-found-not')).'</i>';
      if( !$cmd ) {
        $raison = core::getReason($vis['no_cmd_reason']);
        if( mb_strlen($raison) > 30 ) $raison = mb_substr($raison,0,30)."...";
      }
      else $linkCmd = '<a href="'.URL.'Commandes/'.$vis['id'].'" target="_blank">#'.$vis['id'].' ('.core::n($vis['total']).'€)</a>';
      
      $datas[] = [
        $id_visite,
        core::dateOutput($vis['queue_date'],true),
        $vis['id_as400'],
        $vis['enseigne'],
        $periodicite,
        $vis['user'],
        $linkCmd,
        $cmd ? '' : trim($raison),
        $vis['photo'],
        $vis['promo'],
        $vis['dn'],
        $vis['vpem'] > 0,
        $vis['is_juva'] > 0
      ];
    }

    core::ajax(["datas"=>$datas,"count"=> core::n($count)]);
  }

  public static function searchBoardJuva() {
    global $cpQueries;
    $cpQueriesBefore = $cpQueries;


    $str = trim(strtolower($_POST['str']));
    $limit = intval($_POST['limit']);
    $offset = intval($_POST['offset']);
    $from = $_POST['from'];
    $to = $_POST['to'];

    $w = [];
    $w[] = "v.deleted = 0";
    $w[] = "v.pem = 0";
    $w[] = "v.is_juva = 1";


    $checkDate = true;
    if( $from == "" || $to == "" ) $checkDate = false;
    $to = date('Y-m-d',strtotime('+1 day',strtotime($to)));

    if( $checkDate )
      $w[] = " v.queue_date >= '$from' AND v.queue_date <= '$to' ";

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
        LOWER(c.enseigne) LIKE '%".$str."%'
        OR
        LOWER(c.id_as400) LIKE '%".$str."%'
        OR
        v.date_creation LIKE '%".$date."%'
      )
      ";
    }

    /* Nb total de résultats */
    $queryCount = " 
      SELECT 
        count(*) as nb
      FROM
        visite_juva v
        LEFT JOIN user u ON v.id_user = u.id
        LEFT JOIN ref_client c ON v.id_client = c.id_as400
      WHERE 
        ".implode(" AND ", $w);
    $db->execute($queryCount);
    $count = $db->assoc()['nb'];


    /* Résultats à afficher */
    // LEFT JOIN juva_commande cmd ON v.id_visite = cmd.id_abc
    // pour afficher les cmd liéees
    //ajouter total a commandejuva
    $q = "
      SELECT 
        v.id,
        v.no_cmd_reason,
        v.queue_date,
        c.enseigne as enseigne,
        v.id_client as id_as400,
        u.displayname as user,
        cmd.id as id_cmd,
        cmd.total as total,
        vpem.id AS vpem,
        rcp.id_periodicite,
        v.is_juva,
        ( SELECT count(*) AS nb FROM visite_photo_juva WHERE id_visite = v.id ) as photo,
        ( SELECT count(*) AS nb FROM visite_promo_juva WHERE id_visite = v.id ) as promo,
        ( SELECT count(*) AS nb FROM visite_dn_juva WHERE id_visite = v.id ) as dn
      FROM
        visite_juva v
        LEFT JOIN user u ON v.id_user = u.id
       LEFT JOIN juva_commande cmd ON v.id_commande = cmd.id_abc
        LEFT JOIN ref_client c ON v.id_client = c.id_as400
        LEFT JOIN visite vpem ON vpem.id_visite_linked = v.id_visite AND vpem.pem = 1
        LEFT JOIN ref_client_periodicite rcp ON rcp.id_client_as400 = c.id_as400
      WHERE
        ".implode(" AND ", $w)."
      ORDER BY
        v.queue_date DESC
      LIMIT
        ".($offset*$limit).", $limit
    ";
    $db->execute($q);
    if( !$db->num() ) 
      core::ajax(["html"=>"<tr><td class='text-center' colspan='9'>".l('no-results')."</td></tr>"]);

    $ids = [];
    while( $r = $db->assoc() ) $ids[$r['id']] = $r;
    $datas = [];
    foreach( $ids as $id_visite => $vis ) {

      $clientPeriodicite = client::getPeriodicite($vis['id_as400'],$vis['id_periodicite']);
      $periodicite = !empty($clientPeriodicite) ? $clientPeriodicite['libelle'] : '';



      $cmd = $vis['id_cmd'] > 0;
      $raison = "";
      $linkCmd = '<i>'.trim(l('cmd-found-not')).'</i>';
      if( !$cmd ) {
        $raison = core::getReason($vis['no_cmd_reason']);
        if( mb_strlen($raison) > 30 ) $raison = mb_substr($raison,0,30)."...";
      }
      else {
        // if ((float)$vis['total'] == 0) {
        //     $linkCmd = '<a href="'.URL.'VisitesJuva/'.$vis['id'].'" target="_blank">#'.$vis['id'].'</a>';
        // } else {
            $linkCmd = '<a href="'.URL.'CommandesJuva/'.$vis['id_cmd'].'" target="_blank" class="cmd-link">#'.$vis['id_cmd'].' ('.core::n($vis['total']).'€)</a>';
        // }
        //remplacer id par id_cmd
    }
    
      
      $datas[] = [
        $id_visite,
        core::dateOutput($vis['queue_date'],true),
        $vis['id_as400'],
        $vis['enseigne'],
        $periodicite,
        $vis['user'],
        $linkCmd,
        $cmd ? '' : trim($raison),
        $vis['photo'],
        $vis['promo'],
        $vis['dn'],
        $vis['vpem'] > 0,
        $vis['is_juva'] > 0
      ];
    }

    core::ajax(["datas"=>$datas,"count"=> core::n($count)]);
  }



  public static function getQuestionnaire( $id ) {
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM visite_questionnaire WHERE id_visite = $id");
    return $db->num() ? $db->assoc() : false;
  }
  public static function getQuestionnaireJuva( $id ) {
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM visite_questionnaire_juva WHERE id_visite = $id");
    return $db->num() ? $db->assoc() : false;
  }

  public static function getButVisite() {
    global $db;
    $db->execute("SELECT * FROM apk_select_options WHERE type = 'BUTVISITE' AND deleted = 0");
    $buts = [];
    while( $r = $db->assoc() ) $buts[$r['id']] = $r['libelle'];
    return $buts;
  }
  public static function getButVisiteJuva() {
    global $db;
    $db->execute("SELECT * FROM apk_select_options WHERE type = 'BUTVISITE' AND deleted = 0");
    $buts = [];
    while( $r = $db->assoc() ) $buts[$r['id']] = $r['libelle'];
    return $buts;
  }

  public static function getButAlerteRaison() {
    global $db;
    $db->execute("SELECT * FROM apk_select_options WHERE type = 'ALERT_CLIENT' AND deleted = 0");
    $buts = [];
    while( $r = $db->assoc() ) $buts[$r['id']] = $r['libelle'];
    return $buts;
  }

  public static function noPem($params) {
    global $db;
    $db->execute("
      INSERT INTO visite_no_pem
      (id_visite,reason,date_reason)
      VALUES
      ('".$db->escape($params['visite'])."','".$db->escape($params['reason'])."','".$db->escape($params['date'])."')    
    ");
    api::ajaxRep([]);
  }

  public static function getPem($visite) {
    global $db;
    $db->execute("SELECT reason FROM visite_no_pem WHERE id_visite = '".$db->escape($visite['id_visite'])."' ");
    if( $db->num() ) return ["nopem" => core::getReason($db->assoc()['reason']) ];
    
    $db->execute("SELECT id FROM visite WHERE pem = 1 AND id_visite_linked = '".$db->escape($visite['id_visite'])."'");
    if( !$db->num() ) return ["bug" => "Visite PEM rattachée introuvable ou en cours d'envoi"];

    return ["visite" => self::get($db->assoc()['id'])];
  }

  public static function getPemJuva($visite) {
    global $db;
    $db->execute("SELECT reason FROM visite_no_pem_juva WHERE id_visite = '".$db->escape($visite['id_visite'])."' ");
    if( $db->num() ) return ["nopem" => core::getReason($db->assoc()['reason']) ];
  
    $db->execute("SELECT id FROM visite_juva WHERE pem = 1 AND id_visite_linked = '".$db->escape($visite['id_visite'])."'");
    if( !$db->num() ) return ["bug" => "Visite PEM juva rattachée introuvable ou en cours d'envoi"];
  
    return ["visite" => self::getJuva($db->assoc()['id'])];
  }
  

  public static function getPhotosVisiteSM( $id, $getProduits = false ) {

    global $db;
    $db->execute("SELECT `file` FROM visite_photo WHERE id_visite = ".intval($id));
    if( !$db->num() ) {
      $p = [];
    }
    else {
      $photos = $db->get();

      $p = [];
      foreach( $photos as $k=> $e ) {
        $tmp = explode("/",$e['file']);
        $name = array_pop($tmp);
        $tmp = explode("-",$name);
        $type = $tmp[0];
        $p[] = [
          "url" => URL_APP_ROOT_PROD."datas/visites/".$e['file'],
          "type" => self::getTypePhotoLibelle($type)
        ];
      }
    }

    $produits = [];
    if( $getProduits ) {
      $v = visite::get($id);
      $db->execute("SELECT id FROM commande_apk WHERE id_apk = '".e($v['id_commande'])."' ");
      if( $db->num() ) {
        $id_commande = $db->assoc()['id'];
        $db->execute("
          SELECT
            c.id_produit as id_as400,
            c.quantite,
            c.prix_unitaire,
            c.prix_total,
            a.libelle
          FROM
            commande_apk_produits c 
            LEFT JOIN ref_article a ON c.id_produit = a.id_as400
          WHERE
            c.id_commande_apk = '".e($id_commande)."'
          ORDER BY
            c.prix_total DESC
        ");
        $produits = array_values($db->get());
      }
    }

    api::ajaxRep(["photos" => $p, "produits" => $produits]);
  }

  public static function getTypePhotoLibelle( $type ) {
    switch( $type ) {
      case 'photoArrivee' : return "Arrivée";
      case 'photoRayon' : return "Rayon";
      case 'photoPlanogramme' : return "Vue d'ensemble";
      case 'photoFin' : return "Fin de visite";
      default : return $type;
    }
  }

  public static function getDeballage( $v ) {
    global $db;
    $db->execute("SELECT * FROM visite_deballage WHERE id_visite = ".$v['id']);
    return $db->num() ? $db->assoc() : false;
  }
  public static function getDeballageJuva( $v ) {
    global $db;
    $db->execute("SELECT * FROM visite_deballage_juva WHERE id_visite = ".$v['id']);
    return $db->num() ? $db->assoc() : false;
  }
  

  public static function getDetailScanColis( $id_visite ) {
    global $db;
    $db->execute("SELECT * FROM commande_colis WHERE id_visite = ".intval($id_visite));
    if( !$db->num() ) return [];

    $c = $db->get();
    foreach( $c as $k=>$e ) {
      $c[$k]['liste_colis'] = [];
      $db->execute("SELECT * FROM commande_colis_codes WHERE id_commande_colis = ".$e['id']." ORDER BY date_scan");
      if( $db->num() ) $c[$k]['liste_colis'] = $db->get();
    }
    return $c;

  }

}
