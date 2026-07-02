<?php

class user {


	public static function getAll( $all = false ) {
		global $db;
		$w = "";
		if ($all) $w = 'WHERE actif = 1';
		$db->execute("SELECT * FROM user $w ORDER BY login");
		return $db->getArray();
	}
	public static function getList() {
		$u = self::getAll();
		$rep = [];
		foreach( $u as $k=>$e )
			$rep[$k] = $e['displayname'];
		if( defined('API_ID_USER') )
            api::ajaxRep($rep);    
		die(json_encode(["list"=>$rep]));
	}

	public static function getUserFromLogin( $login ) {
		global $db;
		$db->execute("SELECT * FROM user WHERE login = '".$db->escape($login)."'");
		return $db->assoc();
	}
	public static function getNameFromLogin( $login ) {
		return self::getUserFromLogin($login)['displayname'];
	}
	public static function getNameFromId( $id ) {
		global $db;
		$db->execute("SELECT * FROM user WHERE id = '".$db->escape($id)."'");
		return $db->num() ? $db->assoc()['displayname'] : "";
	}
	public static function getNameFromIdRepr( $id ) {
		global $db;
		$db->execute("SELECT * FROM user WHERE CAST(id_repr as UNSIGNED) = '".$db->escape($id)."' ORDER BY id DESC");
		if( $db->num() == 1 )
			return $db->assoc()['displayname'];

		$last = null;
		while( $r = $db->assoc() ) {
			$last = $r;
			if( $r['actif'] == 1 )  return $r['displayname'];
		}
		return $last['displayname'];
	}
	public static function getAllLogins() {
		global $db;
		$db->execute("SELECT `login` FROM user WHERE deleted = 0");
		$l = [];
		while( $r = $db->assoc() ) $l[] = $r['login'];
		return $l;
	}

	public static function getLastLoginDate( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT date_creation FROM log_user_login WHERE id_user = $id ORDER BY date_creation DESC LIMIT 1");
		return $db->num() ? $db->assoc()['date_creation'] : '';
	}
	public static function getLastLoginApk( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT app_db_update FROM user WHERE id = $id");
		return $db->num() ? $db->assoc()['app_db_update'] : '';
	}
	public static function exist( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT * FROM user WHERE id = $id");
		return $db->num() ? $db->assoc() : false;
	}
	public static function getFromIdRepr( $id_repr ) {
		global $db;
		$id = intval($id_repr);
		$db->execute("SELECT * FROM user WHERE id_repr = $id_repr ORDER BY id DESC LIMIT 1");
		return $db->num() ? $db->assoc() : false;
	}
	public static function getDR( $id_repr ) {
		global $db;
		$db->execute("SELECT id_dr FROM hierarchie WHERE id_repr = '".$db->escape($id_repr)."' ");
		if( !$db->num() ) return false;
		$id_dr = $db->assoc()['id_dr'];
		$dr = self::getFromIdRepr($id_dr);
		return $dr ?? false;
	}
	public static function getMailFromLogin( $login ) {
		$user = self::getUserFromLogin($login);
		return $user ? $user['mail'] : false;
	}

	public static function getFullUser( $id ) {
		$user = self::exist($id);
		if( !$user ) return false;
		$user['histo'] = [
			"login" => self::getHistoLogin( $id ),
			"navigation" => self::getHistoNav( $id ),
			"application" => self::getHistoLoginApplication( $id )
		];
		return $user;
	}

	public static function getHistoLogin( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT * FROM log_user_login WHERE id_user = $id ORDER BY id DESC LIMIT 20");
		return $db->num() ? $db->getArray() : [];
	}
	public static function getHistoNav( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT * FROM log_user_navigation WHERE id_user = $id ORDER BY id DESC LIMIT 20");
		return $db->num() ? $db->getArray() : [];
	}

	public static function getHistoLoginApplication( $id ) {
		global $db;
		$id = intval($id);
		$db->execute("SELECT * FROM user_device WHERE id_user = $id ORDER BY id DESC LIMIT 20");
		return $db->num() ? $db->getArray() : [];
	}

	public static function storeDeviceInfo( $user, $device ) {
		$info = json_decode($device,JSON_OBJECT_AS_ARRAY);
		$info['id_user'] = $user['id'];
		global $db;
		$db->insertArray("user_device",$info);
		return;
	}

	public static function delete( $id = false ) {
		if( !$id && !isset($_POST['id']) ) return core::end();
		if( !$id ) $id = $_POST['id'];
		$id = intval($id);
		if( !user::exist($id) ) return core::end();

		global $db;
		$userTables = ['log_user_login','log_user_navigation','user_device'];
		foreach( $userTables as $table ) {
			$db->execute("DELETE FROM $table WHERE id_user = $id");
		}
		$db->execute("DELETE FROM user WHERE id = $id");

		if( $id == ID ) {
			login::disconnect();
		}

		return core::end();
	}


	public static function calcCA() {
		$id = intval($_POST['id']);
		$user = self::getFromIdRepr($id);
		if( !$user || $id < 1) core::aError("Impossible de trouver l'ID de représentant : $id");
		global $db;

		$no_fac = [];
		$y = date('Y');
		$m = date('m');
		$current = 0;
		$db->execute("SELECT montant_facture,no_facture,facture_avoir FROM ref_facture WHERE annee_facture = $y AND mois_facture = $m AND id_rep = ".$user['id_repr']);
		while( $r = $db->assoc() ) {
			if( in_array($r['no_facture'],$no_fac) ) continue;
			$current += ( $r['facture_avoir'] == 'F' ? $r['montant_facture'] : -$r['montant_facture'] );
			$no_fac[] = $r['no_facture'];
		}

		$rep = [
			"ca" => core::n($current),
			"user" => $user['displayname'],
			"date" => $m."/".$y,
			"count" => count($no_fac)
		];
		core::ajax($rep);
	}

	public static function updateDatasFromAD( $login, $datas ) {
		global $db, $updateQueries;
		if( !$updateQueries ) $updateQueries = 0;
		$user = self::getUserFromLogin($login);
		if( !$user ) return;
		$mail = $datas['mail'][0] ?? "";
		$name = $datas['name'][0] ?? "";
		$actif = ( $datas['useraccountcontrol'][0] == 514 ? 0 : 1);
		$query = "
			UPDATE user
			SET
				mail = '".$db->escape($mail)."',
				name = '".$db->escape($name)."',
				displayname = '".$db->escape($name)."',
				actif = '".$db->escape($actif)."'
			WHERE
				id = ".$user['id']."
			LIMIT 1
		";
		$db->execute($query);
		$updateQueries++;
		//echo $query."<br/>";
		return;
	}

	public static function getAllProfiles() {
		global $db;
		$db->execute("SELECT id,libelle FROM secu_profile WHERE deleted = 0");
		$p = [];
		while( $r = $db->assoc() ) 
			$p[$r['id']] = $r['libelle'];
		return $p;
	}

	public static function getAllCS() {
		global $db;
		$db->execute("
			SELECT id,id_repr,displayname 
			FROM user 
			WHERE 
				actif = 1 
				AND deleted = 0 
				AND id_profile IN (2,4) 
				AND id_repr > 0
		");
		return $db->getArray();
	}

	public static function getAllDR() {
		global $db;
		$profiles = implode(',', stats::$PROFILE_REGION);
		$db->execute("
			SELECT id, id_repr, displayname, secteur
			FROM user
			WHERE
				actif = 1
				AND deleted = 0
				AND id_profile IN ($profiles)
				AND id_repr > 0
			ORDER BY displayname
		");
		return $db->getArray();
	}

	public static function getKilometres() {
		global $db;
		$id_user = ($id ?? API_ID_USER);
		$user = user::exist($id_user);
		$id_repr = intval($user['id_repr']);
		if( !$id_repr ) api::aError("Impossible de trouver votre ID commercial");
		$rep = [ "km" => [], "stats" => self::getKilometresStats( $id_repr )];
		$db->execute("
			SELECT date_km,km 
			FROM kilometres 
			WHERE 
				id_repr = '$id_repr'
			ORDER BY date_km DESC LIMIT 50 
		");
		while( $r = $db->assoc() ) {
			$s = strtotime($r['date_km']);
			$rep['km'][] = [
				"d" => $r['date_km'],
				"d2" => core::$days[date('w',$s)].' '.date('d',$s).' '.core::$month[date('m',$s)-1],
				"km" => $r['km'],
			];
		}
		api::ajaxRep($rep);
	}

	public static function getKilometresStats( $id_repr ) {
		global $db;
		if( !$id_repr ) return null;
		// Par an
		$db->execute("
			SELECT 
				SUM(km) as km 
			FROM kilometres
			WHERE 
				id_repr = $id_repr
				AND deleted = 0
				AND date_km LIKE '".date('Y')."%'
		");
		$stats['y'] = core::n($db->assoc()['km']);

		// Par mois
		$db->execute("
			SELECT 
				SUM(km) as km 
			FROM kilometres
			WHERE 
				id_repr = $id_repr
				AND deleted = 0
				AND date_km LIKE '".date('Y-m')."%'
		");
		$stats['m'] = core::n($db->assoc()['km']);
		
		// Par semaine
		$dto = new DateTime();
		$week = $dto->format("W");
		$dto->setISODate(date('Y'), $week);
		$from = $dto->format('Y-m-d');
		$dto->modify('+5 days');
		$to = $dto->format('Y-m-d');
		$db->execute("
			SELECT 
				SUM(km) as km 
			FROM kilometres
			WHERE 
				id_repr = $id_repr
				AND deleted = 0
				AND date_km BETWEEN '$from' AND '$to'
		");
		$stats['w'] = core::n($db->assoc()['km']);
		return $stats;
	}

	public static function sendKilometres( $params ) {
		global $db;
		$id_user = ($id ?? API_ID_USER);
		$user = user::exist($id_user);
		$id_repr = intval($user['id_repr']);
		if( !$id_repr ) api::aError("Impossible de trouver votre ID commercial");
		if( !isset($params['km']) || !isset($params['date']))
			api::aError("Données manquantes");
		$km = floatval($params['km']);
		$d = $db->escape($params['date']);

		$db->execute("SELECT id FROM kilometres WHERE id_repr = $id_repr AND date_km = '$d' ");
		if( $db->num() ) {
			$id = $db->assoc()['id'];
			$db->execute("UPDATE kilometres SET km = $km WHERE id = $id");
		}
		else 
			$db->execute("INSERT INTO kilometres (id_repr,date_km,km) VALUES ($id_repr,'$d',$km)");

		api::ajaxRep([]);
	}

	public static function getDirecteur( $id_repr ) {
		$id_profile = 6;
		$user = user::getFromIdRepr($id_repr);
		global $db;
		$db->execute("SELECT * FROM user WHERE id_profile = $id_profile AND secteur = '".$user['secteur']."'");
		return $db->num() ? $db->assoc() : false;
	}
	public static function getPromoteurs( $id_dir ) {
		global $db;
		$user = user::exist($id_dir);
		if( !in_array($user['id_profile'],[6,2]) ) return false;
		$db->execute("SELECT DISTINCT id_repr FROM user WHERE secteur = '".$user['secteur']."'");
		if( !$db->num() ) return false;
		$ids = [];
		while( $r = $db->assoc() ) $ids[]= intval($r['id_repr']);
		return $ids;
	}

}
