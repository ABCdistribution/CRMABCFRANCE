<?php

class login {

  public $ldap;
  public $attempt;

  public static function __init() {
    if( isset($_SESSION['id']) && $_SESSION['id'] > 0 ) {
      define('CONNECTED', true);
      define('ID', $_SESSION['id']);
      securite::defineUserSecu();
      return;
    }
    self::checkLoginAttempt();
    return;
  }


  public function __construct() {
    $args = func_get_args();
    if( count($args) == 2 ) {
      list($login,$pass) = $args;
      $this->ldap = new ldap( $login, $pass );
      $this->attempt = !$this->ldap->error;
    }
    return $this;
  }

  public static function checkLoginAttempt() {
    global $errorLoginMsg;
    if( isset($_POST['login'],$_POST['pass']) ) {
      if( mb_strlen($_POST['login']) < 5 || mb_strlen($_POST['pass']) < 3 ) {
        $errorLoginMsg = l("login-error-msg1");
      }
      else {
        $objLogin = new login($_POST['login'],$_POST['pass']);
        if( !$objLogin->attempt ) {
          $errorLoginMsg = l("login-error-msg1");
        }
        else {
          $user = self::initLoginUser($objLogin->ldap->ldap_entries);
          if( $user['actif'] == 1 ) {
            self::logHystory($user);
            self::initSession($user);
          }
          else {
            $errorLoginMsg = l("login-error-msg2");
          }
        }
      }
    }
    if( !defined('CONNECTED') ) {
      define('CONNECTED', false);
      define('ID', false);
    }
    return;
  }

  public static function initLoginUser( $ldapDatas ) {
    global $db;

    $datas = [
      "secteur"     => $ldapDatas['st'][0] ?? NULL,
      "poste"       => $ldapDatas['title'][0] ?? NULL,
      "uid"         => $ldapDatas['description'][0] ?? NULL,
      "givenname"   => $ldapDatas['givenname'][0] ?? NULL,
      "name"        => $ldapDatas['name'][0] ?? NULL,
      "displayname" => $ldapDatas['displayname'][0] ?? NULL,
      "login"       => $ldapDatas['samaccountname'][0] ?? NULL,
      "mail"        => $ldapDatas['mail'][0] ?? NULL,
      "dn"          => $ldapDatas['dn'] ?? NULL,
      "id_repr"     => $ldapDatas['facsimiletelephonenumber'][0] ?? NULL,
      "actif"       => intval(trim($ldapDatas['useraccountcontrol'][0])) == 514 ? 0 : 1
    ];

    $user = self::userExists($datas['login']);
    if( !$user ) {
      return self::addUser($datas);
    } else {
      $db->execute("
        UPDATE user
        SET
          secteur = '".$db->escape($datas['secteur'])."',
          poste = '".$db->escape($datas['poste'])."',
          uid = '".$db->escape($datas['uid'])."',
          givenname = '".$db->escape($datas['givenname'])."',
          name = '".$db->escape($datas['name'])."',
          displayname = '".$db->escape($datas['displayname'])."',
          login = '".$db->escape($datas['login'])."',
          mail = '".$db->escape($datas['mail'])."',
          dn = '".$db->escape($datas['dn'])."',
          id_repr = '".$db->escape($datas['id_repr'])."',
          actif = ".$db->escape($datas['actif'])."
        WHERE
            id = '".$user['id']."'
      ");
      return self::userExists($datas['login']);
    }
  }

  public static function userExists( $login ) {
    global $db;
    $db->execute("SELECT * FROM user WHERE login = '".$db->escape($login)."' AND deleted = 0");
    return $db->assoc() ?? false;
  }
  public static function addUser( $datas ) {
    global $db;
    $datas = $db->escapeArray($datas);
    $db->execute("
      INSERT INTO user
      (login,mail,name,givenname,displayname,dn,uid,poste,secteur,id_repr,id_profile,actif)
      VALUES
      (
      '".$datas['login']."','".$datas['mail']."','".$datas['name']."','".$datas['givenname']."','".$datas['displayname']."',
      '".$datas['dn']."','".$datas['uid']."','".$datas['poste']."','".$datas['secteur']."','".$datas['id_repr']."',
      '".(securite::getDefaultProfile())."',".$datas['actif']."
      )
    ");
    $idUser = $db->lastId();
    return self::userExists($datas['login']);
  }
  public static function logHystory( $user, $mobile = 0 ) {
    global $db;
    $db->execute("INSERT INTO log_user_login (id_user, mobile) VALUES (".$user['id'].", ".intval($mobile).")");
    return;
  }
  public static function initSession( $user ) {
      define('CONNECTED', true);
      define('ID', $user['id']);
      $_SESSION['id'] = $user['id'];
      $_SESSION['user'] = $user;
  }

  public static function registerNavigation() {
    global $db;
    $location = $_SERVER['REQUEST_URI'];
    if( $location == "/" || $location == "/?" ) return;
    $except = ['favicon','.jpg'];
    $log = true;
    foreach( $except as $e )  {
      if( strpos($location,$e) !== false ) {
        $log = false;
        break;
      }
    }
    if( $log ) {
      $db->execute("SELECT location FROM log_user_navigation WHERE id_user = ".ID." ORDER BY id DESC LIMIT 1");
      if( $db->num() ) {
        $line = $db->getLine();
        if( $line['location'] == $db->escape($location) )
          return;
      }
      $db->execute("INSERT INTO log_user_navigation (id_user,location) VALUES ( ".ID.", '".$db->escape($location)."' )");
    }
    return;
  }

  public static function disconnect() {
    $_SESSION = [];
    session_destroy();
    session_start();
    die('{"disconnected":true}');
  }







  /* Token */

  public static function tokenCheck( $token ) {
    global $db;
    $db->execute("SELECT id FROM user WHERE token = '".$db->escape($token)."' "); //  AND token_expires > NOW()
    return ( $db->num() ? $db->assoc()['id'] : false );
  }

  public static function generateToken() {
    global $db;
    $token = md5(time().rand(10000,90000).SALT);
    $db->execute("SELECT id FROM user WHERE token = '$token' ");
    return ( $db->num() ? self::generateToken() : $token );
  }

  public static function setToken( $id_user, $token = null ) {
    global $db;
    if( $token == null ) $token = self::generateToken();
    $expires = date('Y-m-d',strtotime('+'.TOKEN_VALIDITY.' days'));
    $db->execute("UPDATE user SET token = '$token', token_expires = '$expires' WHERE id = $id_user  ");
    return $token;
  }



}
?>
