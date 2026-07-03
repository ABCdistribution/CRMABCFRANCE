<?php
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);
session_start();

# Paramêtres
define('ENV','PROD');
define('DEVMAIL', 'n.souhami@snew.fr');


global $phpInput,$userSecu;
$phpInput = file_get_contents('php://input');

# CACHE
define('CACHE', false);
define('CSTR', '?'.time() );

# Franco de port
define('FRANCO_DE_PORT', 700);


# Paths
define('APP_ROOT', ENV == "PROD" ? '/var/www/gescom/' : '/var/www/gescom/');
define('CLASS_DIR', APP_ROOT.'_class/');
define('PARTIAL', APP_ROOT.'partial/');
define('PAGES', PARTIAL.'pages/');
define('JS', APP_ROOT.'dist/js/');
define('CSS', APP_ROOT.'dist/css/');
define('FILES', APP_ROOT.'datas/');
define('DISTANT', FILES.'distant/');
define('SCRIPTS', APP_ROOT.'scripts/');
define('SCRIPTS_RESSOURCES', SCRIPTS.'ressources/');
define('TMP', APP_ROOT.'tmp/');
define('DIR_CMD', '/home/CRMAMIN/');
define('DIR_CMDJUVA', '/home/CRMAJUVA/');
define('DIR_JUVAACRM', '/home/JUVAACRM/');
define('BACKUP_CMD', FILES.'backup/commandes/');

define('DIR_REF_FILES', TMP.'ref/');

# URLs
define('URL_APP_ROOT_PROD', 'https://crm.abcosmetique.com/');
define('URL_APP_ROOT', "https://".($_SERVER['HTTP_HOST']??URL_APP_ROOT_PROD)."/");
define('URL', URL_APP_ROOT);
define('URL_JS', URL_APP_ROOT.'dist/js/');
define('URL_VENDOR', URL_APP_ROOT.'dist/js/');
define('URL_CSS', URL_APP_ROOT.'dist/css/');
define('URL_LIB', URL_APP_ROOT.'dist/lib/');
define('URL_IMG', URL_APP_ROOT.'dist/img/');

# BDD
define("DB_HOST", "localhost");
define("DB_USER", "abcdistribution");
define("DB_PASS", "i_89UYT_Op0#qsd");
define("DB_NAME", ENV == "PROD" ? "gescom" : "gescom");
define("DEBUG_DB", false);

# LDAP
define('LDAP_VERBOSE', true);
define('LDAP_HOST','192.168.10.34');
define('LDAP_PORT', 389);
define('LDAP_DOMAIN', 'abcdistribution.local');
define('LDAP_ROOT', 'DC=ABCDISTRIBUTION,DC=LOCAL');
define('LDAP_USER', 'gescomad');
define('LDAP_PASS', 'CYvul5Vgs9WvnsC71olj');

# API FILES
define('F_DATABASE', DISTANT.'database.json');

# AS400
define('AS400_DELIMITER','|');

# Token expire
define('TOKEN_VALIDITY', '30');

# Sallage
define('SALT', '^abc_distri_bution_#####$');

# File Hash séparator
define("FILE_HASH_SEPARATOR","::");


/**** REFERENTIELS ****/
define("REF_DUMP", FILES."dump_ref_as400/");
# Articles
define("REF_ARTICLE_PATH", '/home/MINACRM/');
define("REF_ARTICLE_NAME", "ART");
# Historique factures
define("REF_FACTURES_PATH", '/home/MINACRM/');
define("REF_FACTURES_NAME", "HIS");
# Tarifs
define("REF_TARIFS_PATH", '/home/MINACRM/');
define("REF_TARIFS_NAME", "TAR");
# Centrales
define("REF_ARBO_PATH", '/home/MINACRM/');
define("REF_ARBO_NAME", "ARB");
# Clients
define("REF_CLIENTS_PATH", '/home/MINACRM/');
define("REF_CLIENTS_NAME", "CLI");
# Params
define("REF_PARAMS_PATH", '/home/MINACRM/');
define("REF_PARAMS_NAME", "PAR");


# Langue
define('DEFAULT_LANG','FR');
if( !function_exists('l') ) {
  function l( $code, $lang = "" ) {
      return lang::getTrad($code,$lang);
  }
}


if( !function_exists('d') ) {
  function d( $obj ) {
      echo '<pre>';
      var_dump($obj);
  }
}
if( !function_exists('dd') ) {
  function dd( $obj ) {
      d($obj);
      exit;
  }
}
if( !function_exists('e') ) {
  function e( $str ) {
      global $db;
      return $db->escape($str);
  }
}











# Autoloader
require_once(CLASS_DIR.'__autoload.php');
spl_autoload_register('autoloader');




// ERRORS
if( ENV == "DEV" ) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}
