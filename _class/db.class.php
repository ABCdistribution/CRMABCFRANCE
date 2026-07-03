<?php
class db {

	public $link;
	private $base_link;

	private $host;
	private $user;
	private $pass;
	private $base;

	public $query;
	public $result;
	public $log_request = [];

	public function __construct() {
		if( !defined('DB_HOST') ) {
			die('_db_ define not included');
		}
		$this->connect();
	}
	private function connect() {
		$this->link = @mysqli_connect( DB_HOST, DB_USER, DB_PASS);
		if( !$this->link ) {
			die("_db_ can't connect");
		}
		$this->base_link = @mysqli_select_db( $this->link, DB_NAME);
		if( !$this->base_link ) {
			die("_db_ can't select db");
		}
		mysqli_set_charset($this->link,"UTF8");
		mysqli_query($this->link,"SET sql_mode = '';");
		return $this;
	}
	public function execute( $query ) {
		global $cp, $cpQueries;
		$cp++;
		if( !$cpQueries ) $cpQueries = 0;
		$cpQueries++;
		
		$this->query = $query;
		$this->result = mysqli_query( $this->link, $query );
		if( !$this->result )
			error_log(mysqli_error($this->link)." (Query : $query)");
		# Debug
		if( DEBUG_DB ) {
			$info = debug_backtrace();
			$this->log_request[] = "Méthode [".$info[1]["function"]."] Query : ".$query;
		}
		if( !$this->result ) {
			if( defined("AJAX") && AJAX ) {
				core::ajaxError("Erreur SQL ! ".(isset($_POST['methode'])?"(".$_POST['methode'].")":"")." \r\n\r\n ".mysqli_error($this->link)."\r\n\r\n".$this->query);
			}
			else {
				die("_db_ ".mysqli_error($this->link)."<br/>".$this->query );
			}
		}
		return $this;
	}
	public function lastId() {
		return mysqli_insert_id( $this->link );
	}
	public function assoc() {
		return mysqli_fetch_assoc( $this->result );
	}
	public function row() {
		return mysqli_fetch_row( $this->result );
	}
	public function getLine() {
		return $this->assoc();
	}
	public function getArray() {
		$datas = [];
		while( $r = $this->assoc() ) {
			$datas[$r['id']] = $r;
		}
		return $datas;
	}
	public function get() {
		$datas = [];
		while( $r = $this->assoc() ) $datas[] = $r;
		return $datas;
	}
	public function escape( $str ) {
		return mysqli_real_escape_string( $this->link, $str );
	}
	public function escapeArray( $tab ) {
		foreach( $tab as $k=>$e ) $tab[$k] = $this->escape($e);
		return $tab;
	}
	public function num() {
		return mysqli_num_rows( $this->result );
	}
	public function getTableFields( $table_name ) {
		$this->execute("SELECT COLUMN_NAME,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = '".$this->escape($table_name)."'");
		if( !$this->num() ) {
			return [];
		}
		$f = [];
		$remove = ['id','date_creation','date_modification','deleted'];
		while( $r = $this->assoc() ) {
			if( !in_array($r['COLUMN_NAME'],$remove) )
				$f[$r['COLUMN_NAME']] = $r;
		}
		return $f;
	}
	public function getTableFieldsList( $table_name ) {
		return array_keys($this->getTableFields($table_name));
	}

	public function insertArray( $table, $array, $returnQuery = false ) {
		$fields = $this->getTableFieldsList($table);
		if( !$fields || !count($fields) ) return false;

		// nettoyage
		$keys = [];
		foreach ($array as $field => $value) {
			if( !in_array($field,$fields) ) unset($array[$field]);
			else $keys[] = $field;
		}

		if( empty($array) ) return false;

		$q = [" INSERT INTO $table "];
		$q[] = " ( ".implode(",",$keys)." ) ";
		$q[] = " VALUES ( ";
		$tmp = [];
		foreach( $keys as $key ) {
			$tmp[] = " '".$this->escape($array[$key])."' ";
		}
		$q[]= implode(" , ",$tmp);
		$q[] = " ); ";
		$query = implode(" ",$q);

		if( $returnQuery ) return $query;

		$this->execute($query);
		return $this->lastId();
	}

	public function enquote( $datas ) {
		foreach( $datas as $k=>$e ) $datas[$k] = "'".$this->escape($e)."'";
		return $datas;
	}


}
