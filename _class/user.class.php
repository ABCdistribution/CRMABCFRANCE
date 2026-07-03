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

	public static function getPromoteursSecteur() {
		global $db;
		
		// Récupérer l'utilisateur connecté
		$user = user::exist(ID);
		if( !$user ) {
			die(json_encode([]));
		}
		
		// Vérifier si c'est un administrateur (peut voir tous les promoteurs)
		if( securite::isAdmin() ) {
			$db->execute("SELECT id, displayname, id_repr FROM user WHERE id_profile = 1 AND actif = 1 ORDER BY displayname");
		}
		// Sinon, récupérer les promoteurs du même secteur
		else {
			// Vérifier que l'utilisateur a un secteur défini
			if( empty($user['secteur']) ) {
				die(json_encode([]));
			}
			$db->execute("SELECT id, displayname, id_repr FROM user WHERE id_profile = 1 AND secteur = '".$db->escape($user['secteur'])."' AND actif = 1 ORDER BY displayname");
		}
		
		$promoteurs = [];
		while( $r = $db->assoc() ) {
			$promoteurs[] = [
				'id' => $r['id'],
				'displayname' => $r['displayname'],
				'id_repr' => $r['id_repr']
			];
		}
		
		die(json_encode($promoteurs));
	}

	public static function searchPromoteursSecteur() {
		global $db;
		
		$search = $_POST['search'];
		if( empty($search) ) {
			die(json_encode([]));
		}
		
		// Récupérer l'utilisateur connecté
		$user = user::exist(ID);
		if( !$user ) {
			die(json_encode([]));
		}
		
		// Vérifier si c'est un administrateur (peut voir tous les promoteurs)
		if( securite::isAdmin() ) {
			$db->execute("SELECT id, displayname, id_repr FROM user WHERE id_profile = 1 AND actif = 1 AND (displayname LIKE '%".$db->escape($search)."%' OR id = '".$db->escape($search)."') ORDER BY displayname LIMIT 20");
		}
		// Sinon, récupérer les promoteurs du même secteur
		else {
			// Vérifier que l'utilisateur a un secteur défini
			if( empty($user['secteur']) ) {
				die(json_encode([]));
			}
			$db->execute("SELECT id, displayname, id_repr FROM user WHERE id_profile = 1 AND secteur = '".$db->escape($user['secteur'])."' AND actif = 1 AND (displayname LIKE '%".$db->escape($search)."%' OR id = '".$db->escape($search)."') ORDER BY displayname LIMIT 20");
		}
		
		$promoteurs = [];
		while( $r = $db->assoc() ) {
			$promoteurs[] = [
				'id' => $r['id'],
				'displayname' => $r['displayname'],
				'id_repr' => $r['id_repr']
			];
		}
		
		die(json_encode($promoteurs));
	}
	
	/**
	 * Récupère le planning d'un promoteur
	 */
	public static function getPlanningPromoteur() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		
		if(empty($id_repr)) {
			die(json_encode([]));
		}
		
		// Récupérer le planning avec les informations du magasin (2 mois à venir)
		$db->execute("
			SELECT 
				p.id,
				p.date_passage,
				p.raison,
				p.green,
				p.id_plannification,
				rc.enseigne as nom_magasin,
				rc.id_as400 as code_magasin
			FROM planning p
			LEFT JOIN ref_client rc ON p.id_magasin = rc.id
			WHERE p.id_repr = '".$db->escape($id_repr)."' 
			AND p.deleted = 0
			AND p.date_passage >= CURDATE()
			AND p.date_passage <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
			ORDER BY p.date_passage ASC
		");
		
		$planning = [];
		while( $r = $db->assoc() ) {
			// Ajouter le statut basé sur la date
			$datePassage = new DateTime($r['date_passage']);
			$aujourdhui = new DateTime();
			
			if($datePassage < $aujourdhui) {
				$statut = 'Terminé';
			} elseif($datePassage->format('Y-m-d') == $aujourdhui->format('Y-m-d')) {
				$statut = 'Aujourd\'hui';
			} else {
				$statut = 'Planifié';
			}
			
			$planning[] = [
				'id' => $r['id'],
				'date_passage' => $r['date_passage'],
				'raison' => $r['raison'],
				'green' => $r['green'],
				'id_plannification' => $r['id_plannification'],
				'nom_magasin' => $r['nom_magasin'],
				'code_magasin' => $r['code_magasin'],
				'statut' => $statut
			];
		}
		
		die(json_encode($planning));
	}
	
	/**
	 * Récupérer les plannings existants d'un promoteur pour un magasin
	 */
	public static function getPlanningMagasin() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		$id_magasin = $_POST['id_magasin'] ?? '';
		
		if(empty($id_repr) || empty($id_magasin)) {
			die(json_encode([]));
		}
		
		// Récupérer les plannings récurrents (plannification) ET les plannings ponctuels (planning)
		$plannings = [];
		
		// 1. Récupérer les plannings récurrents (plannification)
		$db->execute("
			SELECT 
				p.days,
				p.rec,
				p.start,
				p.annee,
				rc.enseigne,
				'plannification' as type
			FROM plannification p
			LEFT JOIN ref_client rc ON p.id_client = rc.id_as400
			WHERE p.id_repr = '".$db->escape($id_repr)."' 
			AND p.id_client = '".$db->escape($id_magasin)."'
			AND p.deleted = 0
		");
		
		while( $r = $db->assoc() ) {
			$plannings[] = [
				'days' => $r['days'],
				'rec' => $r['rec'],
				'start' => $r['start'],
				'annee' => $r['annee'],
				'enseigne' => $r['enseigne'],
				'type' => $r['type']
			];
		}
		
		// 2. Récupérer les plannings ponctuels (planning) et les regrouper par jours
		$db->execute("
			SELECT 
				DAYOFWEEK(date_passage) as day_of_week,
				rc.enseigne,
				'planning' as type
			FROM planning p
			LEFT JOIN ref_client rc ON p.id_magasin = rc.id_as400
			WHERE p.id_repr = '".$db->escape($id_repr)."' 
			AND p.id_magasin = '".$db->escape($id_magasin)."'
			AND p.deleted = 0
			AND p.date_passage >= CURDATE()
			AND p.date_passage <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
		");
		
		$planningDays = [];
		while( $r = $db->assoc() ) {
			$dayOfWeek = $r['day_of_week'];
			// Convertir dimanche (1) en 7, lundi (2) en 1, etc.
			$day = ($dayOfWeek == 1) ? 7 : $dayOfWeek - 1;
			$planningDays[] = $day;
		}
		
		// Si on a des plannings ponctuels, créer un planning récurrent factice
		if (!empty($planningDays)) {
			$uniqueDays = array_unique($planningDays);
			sort($uniqueDays);
			$plannings[] = [
				'days' => implode(',', $uniqueDays),
				'rec' => 1,
				'start' => 1,
				'annee' => date('Y'),
				'enseigne' => $r['enseigne'] ?? '',
				'type' => 'planning'
			];
		}
		
		die(json_encode($plannings));
	}
	
	/**
	 * Supprimer un planning récurrent d'un magasin
	 */
	public static function supprimerPlanningMagasin() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		$id_magasin = $_POST['id_magasin'] ?? '';
		$week = $_POST['week'] ?? '';
		$year = $_POST['year'] ?? '';
		$frequency = $_POST['frequency'] ?? '';
		
		error_log("Suppression planning - id_repr: $id_repr, id_magasin: $id_magasin, week: $week, year: $year, frequency: $frequency");
		
		// Utiliser isset() au lieu de empty() car empty() retourne true pour 0
		if(!isset($_POST['id_repr']) || !isset($_POST['id_magasin']) || !isset($_POST['week']) || !isset($_POST['year']) || !isset($_POST['frequency'])) {
			error_log("Paramètres manquants pour suppression planning");
			die(json_encode(['success' => false, 'message' => 'Paramètres manquants']));
		}
		
		// Validation des valeurs (après isset)
		if($id_repr === '' || $id_magasin === '') {
			error_log("id_repr ou id_magasin vide");
			die(json_encode(['success' => false, 'message' => 'ID représentant ou magasin manquant']));
		}
		
		try {
			// Calculer la date de début basée sur la semaine et l'année sélectionnées
			$dateDebut = self::getDateFromWeek($week, $year, 1); // Lundi de la semaine
			error_log("Date de début calculée: $dateDebut");
			
			// 1. Supprimer TOUS les plannings récurrents pour ce magasin (peu importe les paramètres)
			$db->execute("
				UPDATE plannification 
				SET deleted = 1, date_modification = NOW()
				WHERE id_repr = '".$db->escape($id_repr)."' 
				AND id_client = '".$db->escape($id_magasin)."'
			");
			
			$plannification_deleted = mysqli_affected_rows($db->link);
			error_log("Plannifications supprimées: $plannification_deleted");
			
			// 2. Supprimer les plannings individuels à partir de la date de début sélectionnée
			$db->execute("
				UPDATE planning 
				SET deleted = 1, date_modification = NOW()
				WHERE id_repr = '".$db->escape($id_repr)."' 
				AND id_magasin = '".$db->escape($id_magasin)."'
				AND date_passage >= '".$db->escape($dateDebut)."'
			");
			
			$planning_deleted = mysqli_affected_rows($db->link);
			error_log("Plannings individuels supprimés: $planning_deleted");
			
			if($plannification_deleted > 0 || $planning_deleted > 0) {
				die(json_encode(['success' => true, 'message' => 'Planning supprimé avec succès à partir de la semaine ' . $week . ' de ' . $year]));
			} else {
				die(json_encode(['success' => false, 'message' => 'Aucun planning trouvé à supprimer pour ces paramètres']));
			}
		} catch (Exception $e) {
			die(json_encode(['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()]));
		}
	}
	
	/**
	 * Récupérer les magasins d'un promoteur
	 */
	public static function getMagasinsPromoteur() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		
		if(empty($id_repr)) {
			die(json_encode([]));
		}
		
		// Debug
		error_log("getMagasinsPromoteur - id_repr: " . $id_repr);
		
		// Récupérer les magasins du promoteur via id_commercial_1 dans ref_client
		$db->execute("
			SELECT 
				rc.id,
				rc.enseigne,
				rc.id_as400,
				rc.ville,
				rc.code_postal
			FROM ref_client rc
			WHERE rc.id_commercial_1 = '".$db->escape($id_repr)."' 
			AND rc.deleted = 0
			ORDER BY rc.enseigne ASC
		");
		
		$magasins = [];
		while( $r = $db->assoc() ) {
			$magasins[] = $r;
		}
		
		error_log("getMagasinsPromoteur - count: " . count($magasins));
		
		die(json_encode($magasins));
	}
	
	/**
	 * Sauvegarde les plannings pour plusieurs magasins
	 */
	public static function savePlanningMagasins() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		$planningsJson = $_POST['plannings'] ?? '[]';
		
		error_log("savePlanningMagasins - id_repr: $id_repr");
		error_log("savePlanningMagasins - planningsJson: $planningsJson");
		
		if(empty($id_repr)) {
			error_log("savePlanningMagasins - ID promoteur manquant");
			die(json_encode(['success' => false, 'message' => 'ID promoteur manquant']));
		}
		
		$plannings = json_decode($planningsJson, true);
		error_log("savePlanningMagasins - plannings décodés: " . print_r($plannings, true));
		
		if(!$plannings || !is_array($plannings)) {
			error_log("savePlanningMagasins - Données de planning invalides");
			die(json_encode(['success' => false, 'message' => 'Données de planning invalides']));
		}
		
		$totalCreated = 0;
		
		foreach($plannings as $planning) {
			$id_magasin = $planning['id_magasin'] ?? '';
			$frequency = $planning['frequency'] ?? 1;
			$week = $planning['week'] ?? 1;
			$year = $planning['year'] ?? date('Y');
			$isModification = $planning['is_modification'] ?? false;
			$planningId = $planning['planning_id'] ?? null;
			
			// Gérer le nouveau format avec 'jours' (array) ou l'ancien avec 'jour' (int)
			$jours = [];
			if(isset($planning['jours']) && is_array($planning['jours'])) {
				$jours = $planning['jours'];
			} elseif(isset($planning['jour']) && is_numeric($planning['jour'])) {
				$jours = [$planning['jour']];
			}
			
			if(empty($id_magasin)) {
				continue;
			}
			
			// Si c'est une modification, supprimer les anciens plannings
			if($isModification && $planningId) {
				error_log("Modification du planning $planningId pour magasin $id_magasin");
				self::supprimerPlanningsMagasin($id_repr, $id_magasin, $planningId);
			}
			
			// Si des jours sont sélectionnés, créer les nouveaux plannings
			if(!empty($jours)) {
				// Traiter chaque jour sélectionné
				foreach($jours as $jour) {
					if($jour < 1 || $jour > 7) {
						continue;
					}
					
					error_log("Traitement jour $jour pour magasin $id_magasin");
					
					// Calculer la date de début basée sur la semaine et l'année
					$dateDebut = self::getDateFromWeek($week, $year, $jour);
					error_log("Date de début calculée: $dateDebut");
					
					// Générer les plannings pour 8 semaines
					$created = self::genererPlanningsMagasin($id_repr, $id_magasin, $dateDebut, $jour, $frequency);
					error_log("Plannings créés pour jour $jour: $created");
					$totalCreated += $created;
				}
			} else {
				// Aucun jour sélectionné, juste supprimer les anciens plannings
				error_log("Aucun jour sélectionné pour magasin $id_magasin - suppression des anciens plannings");
			}
		}
		
		die(json_encode([
			'success' => true, 
			'message' => 'Plannings sauvegardés avec succès',
			'count' => $totalCreated
		]));
	}
	
	/**
	 * Génère les plannings pour un magasin sur 8 semaines
	 */
	private static function genererPlanningsMagasin($id_repr, $id_magasin, $dateDebut, $jour, $frequency) {
		global $db;
		
		error_log("genererPlanningsMagasin - id_repr: $id_repr, id_magasin: $id_magasin, dateDebut: $dateDebut, jour: $jour, frequency: $frequency");
		
		$created = 0;
		$date = new DateTime($dateDebut);
		
		// Générer 8 semaines de planning
		for($i = 0; $i < 8; $i++) {
			// Calculer la date pour cette occurrence
			$weeksToAdd = $i * $frequency;
			$currentDate = clone $date;
			$currentDate->modify('+' . $weeksToAdd . ' weeks');
			
			// Vérifier si le planning existe déjà
			$db->execute("
				SELECT COUNT(*) as count 
				FROM planning 
				WHERE id_repr = '".$db->escape($id_repr)."' 
				AND id_magasin = '".$db->escape($id_magasin)."' 
				AND date_passage = '".$currentDate->format('Y-m-d')."'
				AND deleted = 0
			");
			$exists = $db->assoc();
			
			if($exists['count'] == 0) {
				// Insérer le planning
				$db->execute("
					INSERT INTO planning (id_repr, id_magasin, date_passage, raison, green, id_plannification, deleted, date_creation) 
					VALUES (
						'".$db->escape($id_repr)."',
						'".$db->escape($id_magasin)."',
						'".$currentDate->format('Y-m-d')."',
						'Planning automatique',
						0,
						0,
						0,
						NOW()
					)
				");
				$created++;
			}
		}
		
		return $created;
	}
	
	/**
	 * Supprime les plannings d'un magasin basés sur l'ID de plannification
	 */
	private static function supprimerPlanningsMagasin($id_repr, $id_magasin, $planningId) {
		global $db;
		
		error_log("supprimerPlanningsMagasin - id_repr: $id_repr, id_magasin: $id_magasin, planningId: $planningId");
		
		// Supprimer les plannings liés à cette plannification
		$db->execute("
			UPDATE planning 
			SET deleted = 1 
			WHERE id_repr = '".$db->escape($id_repr)."' 
			AND id_magasin = '".$db->escape($id_magasin)."' 
			AND id_plannification = '".$db->escape($planningId)."'
			AND deleted = 0
		");
		
		$deleted = $db->affectedRows();
		error_log("Plannings supprimés: $deleted");
		
		return $deleted;
	}
	
	/**
	 * Calcule la date d'un jour spécifique dans une semaine donnée
	 */
	private static function getDateFromWeek($week, $year, $dayOfWeek) {
		// Jour 1 = Lundi, Jour 2 = Mardi, etc.
		$dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
		$dayName = $dayNames[$dayOfWeek] ?? 'Monday';
		
		// Premier lundi de l'année
		$firstMonday = new DateTime($year . '-01-01');
		$firstMonday->modify('first Monday of January');
		
		// Ajouter les semaines (week - 1 car on commence à la semaine 1)
		$weeksToAdd = $week - 1;
		$firstMonday->modify('+' . $weeksToAdd . ' weeks');
		
		// Trouver le jour de la semaine correspondant
		$currentDay = $firstMonday->format('N'); // 1 = lundi, 7 = dimanche
		$daysToAdd = $dayOfWeek - $currentDay;
		$firstMonday->modify('+' . $daysToAdd . ' days');
		
		error_log("getDateFromWeek - week: $week, year: $year, dayOfWeek: $dayOfWeek, result: " . $firstMonday->format('Y-m-d'));
		
		return $firstMonday->format('Y-m-d');
	}
	
	/**
	 * Sauvegarde un planning (ponctuel ou récurrent)
	 */
	public static function savePlanning() {
		global $db;
		
		$id_repr = $_POST['id_repr'] ?? '';
		$id_magasin = $_POST['id_magasin'] ?? '';
		$date_passage = $_POST['date_passage'] ?? '';
		$periodicite = $_POST['periodicite'] ?? 'ponctuel';
		$jours = $_POST['jours'] ?? [];
		
		if(empty($id_repr) || empty($id_magasin) || empty($date_passage)) {
			die(json_encode(['success' => false, 'message' => 'Données manquantes']));
		}
		
		$plannings_crees = 0;
		
		if($periodicite === 'recurrent' && !empty($jours)) {
			// Planning récurrent - générer les plannings sur 8 semaines
			$dateStart = new DateTime($date_passage);
			$dateEnd = new DateTime($dateStart);
			$dateEnd->add(new DateInterval('P8W')); // 8 semaines
			
			$currentDate = new DateTime($dateStart);
			
			while($currentDate <= $dateEnd) {
				$jourSemaine = $currentDate->format('N'); // 1 = lundi, 7 = dimanche
				
				if(in_array($jourSemaine, $jours)) {
					$db->execute("
						INSERT INTO planning (id_repr, id_magasin, date_passage, green, raison, date_creation) 
						VALUES ('".$db->escape($id_repr)."', '".$db->escape($id_magasin)."', '".$currentDate->format('Y-m-d H:i:s')."', 0, 'Planning récurrent', NOW())
					");
					$plannings_crees++;
				}
				
				$currentDate->add(new DateInterval('P1D'));
			}
		} else {
			// Planning ponctuel
			$db->execute("
				INSERT INTO planning (id_repr, id_magasin, date_passage, green, raison, date_creation) 
				VALUES ('".$db->escape($id_repr)."', '".$db->escape($id_magasin)."', '".$date_passage." 09:00:00', 0, 'Planning ponctuel', NOW())
			");
			$plannings_crees = 1;
		}
		
		die(json_encode(['success' => true, 'message' => 'Planning enregistré avec succès', 'count' => $plannings_crees]));
	}

	public static function supprimerPlanningPromoteur() {
		global $db;
		
		// Récupérer l'ID du promoteur depuis la requête
		$id_user = intval($_POST['id_repr'] ?? 0);
		
		if (!$id_user) {
			core::ajaxError("ID utilisateur manquant");
		}
		
		// Récupérer l'utilisateur
		$user = self::exist($id_user);
		if (!$user) {
			core::ajaxError("Utilisateur introuvable");
		}
		
		// IMPORTANT : Dans la table planning, id_repr contient l'ID de l'utilisateur (pas user.id_repr)
		// On utilise donc directement $id_user
		$id_repr_table = $id_user;
		$id_repr_display = $user['id_repr'];
		
		// Supprimer tout le planning futur (à partir d'aujourd'hui)
		$today = date('Y-m-d');
		
		// Log avant suppression
		error_log("========================================");
		error_log("SUPPRESSION PLANNING - Début");
		error_log("ID utilisateur reçu: $id_user");
		error_log("Champ id_repr de l'user: $id_repr_display");
		error_log("ID à utiliser pour requête: $id_repr_table");
		error_log("Date du jour: $today");
		
		// Vérifier d'abord ce qui existe dans la table planning
		$query_check = "SELECT id, id_repr, id_magasin, date_passage FROM planning WHERE id_repr = $id_repr_table AND date_passage >= '$today' LIMIT 5";
		error_log("Requête de vérification: $query_check");
		$db->execute($query_check);
		$samples = [];
		while($r = $db->assoc()) {
			$samples[] = "ID:".$r['id']." | id_repr:".$r['id_repr']." | date:".$r['date_passage'];
		}
		error_log("Exemples trouvés: " . (count($samples) > 0 ? implode(" || ", $samples) : "AUCUNE"));
		
		// Compter d'abord combien d'entrées seront supprimées
		$query_count = "SELECT COUNT(*) as total FROM planning WHERE id_repr = $id_repr_table AND date_passage >= '$today'";
		error_log("Requête de comptage: $query_count");
		$db->execute($query_count);
		$count_planning = $db->assoc()['total'];
		error_log("Nombre d'entrées planning à supprimer: $count_planning");
		
		// Compter les plannifications récurrentes
		$query_count_planif = "SELECT COUNT(*) as total FROM plannification WHERE id_repr = $id_repr_table";
		error_log("Requête plannifications: $query_count_planif");
		$db->execute($query_count_planif);
		$count_plannif = $db->assoc()['total'];
		error_log("Nombre de plannifications récurrentes: $count_plannif");
		
		// Supprimer les entrées de planning
		$query_delete = "DELETE FROM planning WHERE id_repr = $id_repr_table AND date_passage >= '$today'";
		error_log("Requête DELETE planning: $query_delete");
		$db->execute($query_delete);
		error_log("Entrées planning supprimées");
		
		// Supprimer aussi les plannifications récurrentes pour éviter qu'elles ne regénèrent le planning
		$query_delete_planif = "DELETE FROM plannification WHERE id_repr = $id_repr_table";
		error_log("Requête DELETE plannification: $query_delete_planif");
		$db->execute($query_delete_planif);
		error_log("Plannifications récurrentes supprimées");
		error_log("========================================");
		
		// Log de l'action
		$total_supprime = $count_planning + $count_plannif;
		core::logApk("Suppression du planning futur du promoteur #".$id_repr_display." (".$user['displayname'].") - ".$count_planning." entrées planning + ".$count_plannif." plannifications supprimées", 0, ID);
		
		die(json_encode(['success' => true, 'message' => 'Planning supprimé', 'count' => $count_planning, 'plannifications' => $count_plannif]));
	}

}
