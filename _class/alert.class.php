<?php

class alert {
	public function __construct() {
		$args = func_get_args();
		if( count($args) == 2 ) {
			global $db;
			$type = $args[0];
			$content = $args[1];
			if( !self::alertTypeExist($type) ) $type = 'critical';
			$db->execute("INSERT INTO alert (type,content) VALUES ('$type','".$db->escape($content)."')");
		}
		return;
	}
	public static function getAlertTypes() {
		global $db;
		$db->execute("SELECT * FROM alert_type");
		$rez = [];
		while( $r = $db->assoc() )
			$rez[$r['type']] = $r['libelle'];
		return $rez;
	}
	public static function getAlertType( $id ) {

	}
	public static function getAlertLibelle( $type ) {
		return  self::alertTypeExist($type) ? self::getAlertTypes()[$type] : $type;
	}
	public static function alertTypeExist( $type ) {
		return array_key_exists( $type, self::getAlertTypes());
	}


	public static function getAlertColor( $type ) {
		global $db;
		$db->execute("SELECT color FROM alert_type WHERE type = '".$db->escape($type)."' ");		
		return $db->num() ? $db->assoc()['color'] : '';
	}

	public static function readAlert( $id = false ) {
		global $db;
		if( $id || isset($_POST['id']) ) {
			if( !$id ) $id = intval($_POST['id']);
			$db->execute("UPDATE alert SET statut = 1 WHERE id = ".intval($id));
		}
		if( AJAX ) die('{}');
		return;
	}

	public static function printableAlerts() {
		$n = self::getAlerts();
		if( empty($n) ) return;
		$html = [];
		foreach( $n as $k=>$e ) {
			$html[] = '
			<div class="alert alert-'.$e['type'].'" onclick="readAlert(this,'.$k.')" style="background-color:'.self::getAlertColor($e['type']).'">
				<span class="alert-title"> <i class="fas fa-exclamation-triangle"></i> '.self::getAlertLibelle($e['type']).'</span>
				<p class="alert-content">'.$e['content'].'</p>
			</div>
			';
		}
		return implode($html);
	}
	public static function getAlerts() {
		global $db;
		$db->execute("SELECT * FROM alert WHERE statut = 0 ORDER BY id DESC");
		return $db->num() ? $db->getArray() : [];
	}

}