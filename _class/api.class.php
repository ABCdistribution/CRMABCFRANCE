<?php


class api {

  public function __construct() {
    
    if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
        http_response_code(200);  
        return 0;    
    }  

    global $db,$params,$phpInput;
    header('Content-Type: application/json');
    
    if( APISM ) {
      # PHP Input
      global $phpInput;
      $phpInput = file_get_contents('php://input');  
      $_POST = json_decode($phpInput, true);    
    }

    if( ENV == "DEV" && empty($_POST) ) $_POST = $_REQUEST;
    if( !isset($_POST['d']) && isset($_GET['d']) )
      $_POST['d'] = $_GET['d'];

    if( empty($_POST) || !isset($_POST['d']) ) {
      global $params;
      if( APISM && in_array('getCommande',$params) ) {
        return commande::getDetailsCommandePDF( base64_decode(array_pop($params)) );
      }
      api::aError("Requête vide");
      exit;
    }
   

		try {
			$params = json_decode(base64_decode($_POST['d']),true);
      if( is_string($params) ) $params = json_decode($params,true);
		}
		catch( Exception $e ) {
			self::aError("Impossible de décoder les datas");
		}

    $_POST['lang'] = $params['lang'] ?? DEFAULT_LANG;
    lang::setLang(false);



		if( !isset($params['methode']) ) self::aError('Aucune méthode recue');
		$rep = [];
    


		if( $params['methode'] == "auth" ) {
      if( ENV == "DEV" ) error_log("Authentification...");
      if( !isset($params['login']) || !isset($params['pass']) )
        self::aError("Il manque des parametres pour l'authentification");
      self::auth($params['login'],$params['pass'], isset($params['device'])?$params['device']:null);
      exit;
		}

    if( $params['methode'] == "debug" )
      self::debug();

    if( !isset($params['token']) ) {
      self::aError("token-expire");
    }

    $token = $params['token'];
    $id_user = login::tokenCheck($token);
    if( $params['methode'] == "checkTokenValidity" ) {
      if( $id_user < 1 )
        self::ajaxRep(["expire" => true ]);
      $user = user::exist($id_user);
      self::ajaxRep(["expire" => false, "user" => $user ]);
    }


    if( !$id_user ) self::aError("token-expire");
    
    # fakeuser pour ABCSM
    if( !isset($params['fakeID']) ) $params['fakeID'] = 0;
    define('APISM_FAKEID', $params['fakeID'] ?? 0);
    $fakeUser = user::exist(APISM_FAKEID);

    $user = user::exist($id_user);
    api::setApiUser($user);
    define("API_ID_USER", $user['id']);
    define("ID_PROFILE", $fakeUser['id_profile'] ?? $user['id_profile']);

    # Mise à jour du APK version
    if( isset($params['apk_version']) &&  API_ID_USER ) {
      $db->execute("UPDATE user SET apk_version = '".$db->escape($params['apk_version'])."' WHERE id = ".API_ID_USER);
      define('APK_VERSION', floatval($params['apk_version']));
    }

    if( isset($params['position']) &&  API_ID_USER ) {
      $po = $params['position'];
      if( isset($po['latitude'],$po['longitude'],$po['altitude'],$po['precision'],$po['date']) ) {
        $d = date('Y-m-d', strtotime($po['date']));
        $db->execute("SELECT id FROM user_location WHERE date_position = '".$db->escape($d)."' AND id_user = ".API_ID_USER);
        if( !$db->num() ) {
          $db->execute("
            INSERT INTO user_location
            (id_user,latitude,longitude,altitude,prec,date_position)
            VALUES 
            (
              ".API_ID_USER.", '".$db->escape($po['latitude'])."','".$db->escape($po['longitude'])."', 
              '".$db->escape($po['altitude'])."','".$db->escape($po['precision'])."','".$d."'            
            )
          ");
        }
      }
    }

    if( APISM ) {
      new apism($user,$params);
      die();
    }
    

    if( defined('APK_VERSION') && APK_VERSION == "0.32" )
      self::aError("Version d'application obselete");


    # Update du dernier API Query
    $db->execute("UPDATE user SET api_query = NOW() WHERE id = ".API_ID_USER);

    $tmp = $params;
    unset($tmp['token']);
    unset($tmp['apk_version']);
    $str = str_replace("&"," &bull; ",http_build_query($tmp));
    /*
    if( $params['methode'] != "haveMessage" ) {
      if( $params['methode'] == "syncQueueV2") {
        core::logApk("Synchronisation d'un élément avec le serveur. (Traitement de la file d'attente)");
      }
      else core::logApk("Appel au serveur API pour la methode : [".$params['methode']."] <i>".$str."</i>");
    }
    */

    global $db;
    switch( $params['methode'] ) {
      case 'database' : {
        $db->execute("UPDATE user SET app_db_update = NOW() WHERE id = ".$user['id']);
        self::syncDatabase();
        break;
      }
      /*
      case 'syncQueue' : {
        if( !isset($params['queue']) ) self::aError("Serveur : File de traitement vide");
        self::processQueue( $params['queue'] );
      }
      */
      case 'getHistoriqueCommande' : {
        commande::getHistoriqueCommande();
      }
      case 'getCommandeDetails' : {
        commande::getHistoriqueCommandeDetails();
      }
      case 'getDetailsCommande' : {
        commande::getDetailsCommande();
      }
      case 'getPhotosVisite' : {
        visite::getPhotosVisite();
      }
      case 'getVisitesClient' : {
        visite::getVisitesClient();
      }
      case 'getFuturesVisites' : {
        planning::getFuturesVisites( $params );
      }
      case 'getPlanning' : {
        planning::getPlanning();
      }
      case 'mesClients' : {
        planning::getMesClients();
      }
      case 'getObj' : {
        self::getObj();
      }
      case 'getMailList' : {
        mailbox::getMessages();
      }
      case 'plannification' : {
        planning::getPlannificationClientStr( $params );
      }
      case 'getConversation' : {
        $id = $params['id'];
        mailbox::getEchanges( intval($params['id']) );
      }
      case 'debug' : {
        self::debug( $params['obj'] ?? null );
        break;
      }
      case 'syncQueueV2' : {
        self::syncQueueV2( $params );
        break;
      }
      case 'haveMessage' : {
        mailbox::haveMessage();
      }
      case 'getListUsers' : {
        user::getList();
      }
      case 'getTasks' : {
        task::getTasks();
      }
      case 'getStatsClient' : {
        client::getStatsClient( $params );
        break;
      }
      case 'getTournee' : {
        planning::getTournee();
      }

      case 'getKilometres' : {
        user::getKilometres();
      }
      case 'getObjCS' : {
        prospection::getObjCS();
      }
      case 'getPromoteurs' : {
        prospect::getListePromoteurs();
      }
      case 'getNextVisiteCs' : {
        prospection::getNextVisiteCs($params['id']);
      }
      case 'saveTournee' : {
        planning::saveTournee( $params['tournee'] );
      }
      default : {
        self::aError('Méthode inexistante : '.$params['methode']);
      }
    }

		self::ajaxRep($rep);
	}

  public static function setApiUser( $user ) {
    global $apiUser;
    $apiUser = $user;
  }
  public static function getApiUser() {
    global $apiUser;
    if(defined('APISM_FAKEID') && APISM_FAKEID > 0 ) return user::exist(APISM_FAKEID)??$apiUser;
    return $apiUser;
  }

	public static function aError($str) {
    if( is_array($str) ) {
      //core::dumpLog($str);
    }
    else error_log("[API ERROR] : ".$str);

    if( defined('APISM') && APISM ) {
      http_response_code(400);
      die(json_encode(["error" => $str], JSON_FORCE_OBJECT));
    }

    $rep = [
			"error" => true,
			"errorMsg" => $str
		];
    //core::dumpLog($rep);
		self::ajaxRep($rep);
	}
	public static function ajaxRep( $params = [], $json_encode = true ) {
    if( defined('APISM') && APISM ) {
      die(json_encode($params));
    }
    else 
      $rep = '{ "d" : "'.base64_encode(utf8_encode(json_encode($params))).'"}';
    //error_log($rep);
		die($rep);
	}

  public static function auth( $login, $pass, $device = null ) {

    if( trim($login) == "" ) self::aError("Veuillez saisir un identifiant");
    if( trim($pass) == "" ) self::aError("Veuillez saisir un mot de passe");



    $objLogin = new login($login,$pass);
    if( !$objLogin->attempt ) self::aError( APISM ? "Identifiants erronés" : "error-login");
    $user = login::initLoginUser($objLogin->ldap->ldap_entries);
    /*if( $user['id_repr'] < 1 )
      self::ajaxRep(["msg" => "Vous n'avez pas d'ID de représentant, connexion impossible."]);*/
    $token = login::generateToken();
    login::setToken($user['id'],$token);


    //Device info
    /*if( $device ) {
      try {
        user::storeDeviceInfo($user,$device);
      } catch( Exception $e ) {}
    }*/
    // Security
    $security = [
      'profiles' => user::getAllProfiles(),
      'user' => []
    ];
    foreach( $security['profiles'] as $id_profile => $profile ) {
      if( $user['id_profile'] == $id_profile || $user['id_profile'] == 2 )
        $security['user'][] = $id_profile;
    }
    /* Admin => CS */
    //if( $user['id_profile'] == 2 ) $security['user'] = [4];


    $user['security'] = $security;

    $response = ["token"=>$token,"user"=>$user];
    //error_log($response);
    self::ajaxRep($response);
  }

  public static function syncDatabase() {
    global $db;
    error_log('1');
    $db->execute("SELECT * FROM dd_history ORDER BY date_creation DESC LIMIT 1");
    if( !$db->num() ) self::aError("Impossible de trouver une base de donnée");
    $rep = $db->assoc();
    $file = DISTANT.$rep['name'];

    $hash = download::new( $file, 'application/json' );
    if( strpos($hash,"undefined") ) self::aError("Impossible de générer le lien de téléchargement");
    if( !$hash ) self::aError("Impossible de mettre à jour la base de données");
    error_log($hash);
    self::ajaxRep(["hash" => $hash]);
  }


  /*
  public static function processQueue( $obj ) {
    $obj = json_decode($obj,TRUE);
    foreach( $obj as $o ) {
      switch( $o['type'] ) {
        case 'commande' : {
          commande::new($o);
          break;
        }
        case 'visite' : {
          visite::new($o);
          break;
        }
        case 'planning-update' : {
          planning::userUpdate($o);
          break;
        }
        case 'addContact' : {
          if( client::addContact($o) ) 
            self::ajaxRep([]);
          else 
            self::aError("Impossible de créer le contact sur le serveur");
          break;
        }
        default :
          break;
      }
    }


    self::ajaxRep(["nb" => count($obj)]);
  }
  */

  public static function getObj() {
    $obj = -1;
    $current = -1;

    $id = API_ID_USER;
    $user = user::exist($id);
    if( $user && $user['id_repr'] ) {
      global $db;
      $y = date('Y');
      $m = date('n');
      $db->execute("SELECT total FROM objectifs WHERE deleted = 0 AND annee = $y AND mois = $m AND id_repr = ".$user['id_repr']);
      $obj = $db->num() ? $db->assoc()['total'] : 0;

      $no_fac = [];
      $db->execute("SELECT montant_facture,no_facture,facture_avoir FROM ref_facture WHERE annee_facture = $y AND mois_facture = $m AND id_rep = ".$user['id_repr']);
      while( $r = $db->assoc() ) {
        if( in_array($r['no_facture'],$no_fac) ) continue;
        $current += ( $r['facture_avoir'] == 'F' ? $r['montant_facture'] : -$r['montant_facture'] );
        $no_fac[] = $r['no_facture'];
      }
    }
    self::ajaxRep(["obj" => core::n($obj), "cur" => core::n($current)]);
  }


  public static function debug() {
    global $phpInput;
    if( $phpInput == "" ) die('{}');
    $path = FILES."debug/".date('Y')."/".date("m")."/".date('d')."/";
    if( !is_dir($path) )
      mkdir($path,0777,true);
    $name = "debug_du_".date("Y-m-d_G\hi\ms\s").".txt";
    if( touch($path.$name) ) {
      chmod($path.$name,0777);
      file_put_contents($path.$name,$phpInput);
    }
    die('{}');
  }


  public static function debugVisites( $visite ) {
    $path = FILES."debug-visite/".date('Y')."/".date("m")."/".date('d')."/";
    if( !is_dir($path) )
      mkdir($path,0777,true);
    $name = "debug_visite_du_".date("Y-m-d_G\hi\ms\s").".txt";
    if( touch($path.$name) ) {
      chmod($path.$name,0777);
      file_put_contents($path.$name, json_encode($visite) );
    }
    die('{}');
  }

  public static function syncQueueV2() {
    global $phpInput;
    $params = json_decode($phpInput,JSON_OBJECT_AS_ARRAY);

    #self::aError("Break intentionnel");

    if( !isset($params['type']) ) {
      error_log("syncQueueV2 | Aucun type de parametre recu pour cet appel");
      core::dumpLog($params);
      self::aError("Type non recu");
    }

    switch( $params['type']) {
      case 'commande' : {
        core::logApk("Envoi d'une nouvelle commande depuis l'application");
        commande::new( $params );
        break;
      }
      
      //commande juva
      case 'commandeJuva': {
        commande::newJuva($params); 
        error_log(" Envoi d'une commande JUVA depuis l'application");
        break;
      }
      
      case 'visite' : {
        core::logApk("Envoi d'une visite commande depuis l'application");
        visite::new($params);
        break;
      }
      case 'photo' : {
        //core::logApk("Réception d'une photo de visite");
        self::getPhoto($params);
        break;
      }
      case 'planningGreen' : 
      case 'planning-update' : {
        planning::userUpdate($params);
        break;
      }
      case 'client-remarque' : {
        client::addNoteFromApk($params);
        break;
      }
      case 'client-edit-remarque' : {
        client::editNoteFromApk($params);
        break;
      }
      case 'client-delete-remarque' : {
        client::deleteNoteFromApk($params);
        break;
      }
      case 'sendMsg' : {
        core::logApk("Envoi de message via la messagerie");
        mailbox::sendMsg( $params);
        break;
      }
      case 'editContact' : {
        client::editContact($params);
        break;
      }  
      case 'addContact' : {
        if( client::addContact($params) ) 
          self::ajaxRep([]);
        else 
          self::aError("Impossible de créer le contact sur le serveur");
        break;
      }      
      case 'deleteContact' : {
        client::deleteContact($params);
      }
      case 'plannification' : {
        planning::plannification($params);
      }
      case 'updateInfoClient' : {
        client::updateInfoSup($params);
      }
      case 'creation-prospect' : {
        prospect::creation($params);
        break;
      }
      case 'creation-prospection' : {
        prospection::create($params);
        break;
      }
      case 'prospection-envoi' : {
        prospection::validerEnvoi( $params );
        break;
      }
      case 'creation-client' : {
        prospection::creationClient( $params );
        break;
      }      
      case 'edition-prospect' : {
        prospect::edition($params);
        break;
      }    
      case 'create-task' : {
        task::creation($params);
        break;
      }
      case 'validate-task' : {
        task::validate($params);
        break;
      }
      case 'edit-task' : {
        task::edit($params);
        break;
      }
      case 'delete-task' : {
        task::delete($params);
      }
      case 'sendKilometres' : {
        user::sendKilometres($params);
      }
      case 'noPem' : {
        visite::noPem($params);
      }
      case 'visite-commerciale' : {
        prospection::saveVisiteCommerciale($params);
      }
      case 'saveTournee' : {
        planning::saveTournee( $params['tournee'] );
      }
      default : {
        error_log("syncQueueV2 | type non reconnu : ".$params['type']);
        self::aError("Type non reconnu");
      }
    }

    self::ajaxRep([]);
  }

  public static function getPhoto( $params ) {
    global $db;
    if( isset($params['photo2']) )
      $photo = $params['photo2'];
    else 
      $photo = base64_decode($params['photo']);
    $name = $params['name'];

    if( strpos($name,'photo_debut') > 0 ) {
      return prospection::savePhoto( $params );
    }

    $q = 'SELECT id FROM visite_photo WHERE app_name = "'.$db->escape($name).'"';
    $db->execute($q);
    if( $db->num() ) {
      core::logApk("[Erreur] getPhoto : cette photo existe déjà sur le CRM ( app_name = $name)");
      self::ajaxRep([]);
    }

    $tmp = explode("_photo",$name);
    $id_visite = $tmp[0];

    // Vérifie si c'est une visite juva
    $db->execute("SELECT id FROM visite_juva WHERE id_visite = '".$db->escape($id_visite)."' AND deleted = 0");
    if ($db->num()) {
        // Appelle un traitement spécifique JUVA
        self::handlePhotoJuva($params); 
        return;
    }
        


    $ext = $tmp[1];
    $tmp = explode("_",$ext);
    $type = "photo".$tmp[0];

    $q = 'SELECT * FROM visite WHERE id_visite = "'.$db->escape($id_visite).'" AND deleted = 0 ';
    $db->execute($q);
    if( !$db->num() ) {
      $id = 0;
      $prePath = FILES."visites/";
      $path = 'tmp/'.date('Y').'/';
      if( !is_dir($prePath.$path) ) mkdir($prePath.$path, 0777, true);            
    }
    else {
      $visite = $db->assoc();
      $id = $visite['id'];
      $prePath = FILES."visites/";
      $path = $visite['id_client'].'/'.date('Y').'/';
      if( !is_dir($prePath.$path) ) mkdir($prePath.$path, 0777, true);      
    }


    $filename = $type."-".time()."-".rand(1000,9999).".jpg";
    $full = $prePath.$path.$filename;
    file_put_contents( $full, $photo);
    $db->execute("
      INSERT INTO visite_photo (id_visite,file,size,app_name,id_visite_apk)
      VALUES ($id,'".($path.$filename)."','".filesize($full)."','".$db->escape($name)."','".$db->escape($id_visite)."')
    ");

    //core::logApk("Photo bien récéptionnée #$id (en attente de visite) : $filename (".core::readableSize(filesize($full)).")");

    return;
  }
  public function handlePhotoJuva($params) {
    global $db;

    $photo = isset($params['photo2']) ? $params['photo2'] : base64_decode($params['photo']);
    $name = $params['name'];

    $tmp = explode("_photo", $name);
    $id_visite = $tmp[0];
    $ext = $tmp[1];
    $tmp = explode("_", $ext);
    $type = "photo".$tmp[0];

    $q = 'SELECT * FROM visite_juva WHERE id_visite = "'.$db->escape($id_visite).'" AND deleted = 0';
    $db->execute($q);

    if (!$db->num()) {
        $id = 0;
        $prePath = FILES."visites_juva/";
        $path = 'tmp/'.date('Y').'/';
        if (!is_dir($prePath.$path)) mkdir($prePath.$path, 0777, true);
    } else {
        $visite = $db->assoc();
        $id = $visite['id'];
        $prePath = FILES."visites_juva/";
        $path = $visite['id_client'].'/'.date('Y').'/';
        if (!is_dir($prePath.$path)) mkdir($prePath.$path, 0777, true);
    }

    $filename = $type."-".time()."-".rand(1000,9999).".jpg";
    $full = $prePath.$path.$filename;
    file_put_contents($full, $photo);

    $db->execute("
        INSERT INTO visite_photo_juva (id_visite, file, size, app_name, id_visite_apk)
        VALUES ($id, '".$path.$filename."', '".filesize($full)."', '".$db->escape($name)."', '".$db->escape($id_visite)."')
    ");
}



}


?>
