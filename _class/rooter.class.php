<?php
class rooter {

	public $params;
	public $rooterPage;
	public $page;
	public $defaultPage = 'accueil';


	public function __construct() {
		self::registerParams();
		global $params,$file;

		lang::detectLang();

		# Application & API
		if( in_array("api",$params)) {
			define('API', TRUE);
			define('APISM', FALSE);
			new api();
			exit;
		}
		if( in_array("apism",$params)) {
			define('API', TRUE);
			define('APISM', TRUE);
			new api();
			exit;
		}
		define('API', FALSE);
		define('APISM', FALSE);
		if( in_array("dl",$params)) {
			new download();
			exit;
		}
		if( in_array("Media",$params)) {
			new media();
			exit;
		}
		if( in_array("CmdPDF",$params)) {
			new commandePDF( array_pop($params), true );
			exit;
		}
		if( in_array("CmdPDFJuva",$params)) {
			new commandePDF( array_pop($params), true, true ); // id, output=true, isJuva=true
		exit;
}		

		if( in_array("pingServer",$params)) {
			die('{ "reponse" : "pong" }');
		}

		# Web
		login::__init();

		# Ajax
		if( !empty($params) && $params[0] == "async" ) {
			define('AJAX',true);
			self::doAjax();
		}
		else define('AJAX',false);

		if( CONNECTED ) login::registerNavigation();


		$this->initNavigation();
	}

	public function registerParams() {
		global $params;
		$params = [];
		if( isset($_REQUEST['URI_STRING']) && $_REQUEST['URI_STRING'] != "" )
		  $params = explode("/",$_REQUEST['URI_STRING']);
		$this->params = $params;
		return;
	}



	public function doAjax() {
		if( !CONNECTED ) {
			if( isset($_POST['methode']) && $_POST['methode'] == "lang::setLang") {}
			else core::ajaxError("Vous devez être connecté pour réaliser cette action");
		}
		if( !isset($_POST['methode']) ) core::ajaxError("Action non précisée");
		$tmp =  explode("::",$_POST['methode']);
		if( count($tmp) != 2 ) core::ajaxError("Action erronée");
		$class = $tmp[0];
		$methode = $tmp[1];
		if( !class_exists($class) ) core::ajaxError("Action impossible");
		$obj = new $class();
		if( !method_exists($obj, $methode) ) core::ajaxError("Action inexistante");
		$obj->$methode();
		exit;
	}

	public function initNavigation() {
		global $pageLink;
		if( !defined('CONNECTED') || !CONNECTED ) {
		  $this->rooterPage = 'login';
		  $this->page = false;
		}
		else {
			$this->setDefaultpage();
			if( isset($this->params[0]) ) {
				$p = strtolower($this->params[0]);
				if( $this->pageExists($p) ) {
					$pageLink = $p;
					$this->page = $p;
				}
			}
			if( !isset($this->params[0]) && CONNECTED ) {
				$id_profile = securite::getUserProfile( ID );
				if( $id_profile ) {
					$s = securite::get( $id_profile );
					if( isset($s['homepage']) && $s['homepage'] != "" ) {
						$redirect = URL.$s['homepage'];
						die('<script>window.location.href = "'.$redirect.'";</script>');
						exit;
					}
				}
			}
		}
		include(PARTIAL.$this->rooterPage.".php");
		return $this;
	}
	public function setDefaultpage() {
		$this->rooterPage = 'core';
		$this->page = $this->defaultPage;
		return $this;
	}

	public function pageExists( $page ) {
		return file_exists(PAGES.strtolower($page).".php");
	}

	public function getPageScript( $page ) {
		$name = $this->page.".js";
		$pageScript = JS.$name;
		if( !file_exists($pageScript) ) return;
		echo '<script src="'.URL_JS.$name.CSTR.'"></script>';
	}
	public function getPageStyles( $page ) {
		$name = $this->page.".css";
		$pageStyle = CSS.$name;
		if( !file_exists($pageStyle) ) return;
		echo '<link href="'.URL_CSS.$name.CSTR.'" rel="stylesheet">';
	}
	public function getPageName( $page ) {
		if( !$this->pageExists($page) ) return $page;
		global $db;
		$p = $db->escape($page);
		$db->execute('SELECT libelle FROM page_name WHERE file = "'.$p.'" ');
		if ($db->num() ) return $db->assoc()['libelle'];
		$db->execute('INSERT INTO page_name (file,libelle) VALUES ("'.$p.'","'.$p.'")');
		new alert("warning_conf", "Le titre de la page n'est pas défini : ".$page);
		return $p;
	}













}
