<?php

class ldap {

  private $login;
  private $pass;
  private $ldap_connection;
  public $ldap_entries;
  public $error;

  public function __construct( $login = null, $pass = null ) {
    if( !$login ) return;
    $this->login = $login;
    $this->pass = $pass;
    $this->ldap_entries = [];
    $this->error = ( $this->login() === false );
    return;
  }

  public function login() {
    $retrieve = ['name','mail','samaccountname'];
    $this->ldap_connection = ldap_connect(LDAP_HOST,LDAP_PORT) or die("Connexion LDAP impossible");
    if ($this->ldap_connection) {
      $user = $this->login."@".LDAP_DOMAIN;
      ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
      $ldapbind = @ldap_bind($this->ldap_connection, $user, $this->pass);
      if ($ldapbind) {
        $search = ldap_search($this->ldap_connection,LDAP_ROOT,'sAMAccountName='.$this->login);
        if( $search && !empty($search) ) {
          $entries = ldap_get_entries($this->ldap_connection, $search);
          $this->ldap_entries = array_pop($entries);
          return true;
        }
        else {
          if( LDAP_VERBOSE ) return false;
        }
      }
      else {
        if( LDAP_VERBOSE ) return false;
      }
    }
    else {
      if( LDAP_VERBOSE ) return false;
    }
    return false;
  }


  public function dump() {
    $retrieve = ['name','mail','samaccountname'];
    $this->ldap_connection = ldap_connect(LDAP_HOST,LDAP_PORT) or die("Connexion LDAP impossible");
    if ($this->ldap_connection) {
      $user = LDAP_USER."@".LDAP_DOMAIN;
      ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
      $ldapbind = @ldap_bind($this->ldap_connection, $user, LDAP_PASS);
      if ($ldapbind) {
        $search = ldap_search($this->ldap_connection,LDAP_ROOT,'sAMAccountName=*',$retrieve);
        if( $search && !empty($search) ) {
          $entries = ldap_get_entries($this->ldap_connection, $search);
          $this->ldap_entries = array_pop($entries);
          echo '<pre>';
          print_r($entries[99]);
        }
        else {
          if( LDAP_VERBOSE ) echo 'Aucune entrée trouvée pour ce compte dans le ldap';
        }
      }
      else {
        if( LDAP_VERBOSE ) echo 'Impossble de se bind au ldap';
      }
    }
    else {
      if( LDAP_VERBOSE ) echo 'Impossble de se connecter au ldap';
    }
    return false;
  }


  public static function ldapSync() {
    $retrieve = ['name','mail','samaccountname','useraccountcontrol'];
    $logins = user::getAllLogins();
    $ldap_connection = ldap_connect(LDAP_HOST,LDAP_PORT) or die("Connexion LDAP impossible");
    // dd('ko');
    if ($ldap_connection) {
      $user = LDAP_USER."@".LDAP_DOMAIN;
      ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);
    }
    $ldapbind = @ldap_bind($ldap_connection, $user, LDAP_PASS);
    if ($ldapbind) {
      foreach( $logins as $k=>$login ) {
          $search = ldap_search($ldap_connection,LDAP_ROOT,'sAMAccountName='.$login,$retrieve);
          if( $search && !empty($search) ) {
            $entries = ldap_get_entries($ldap_connection, $search);
            $ldap_entries = array_pop($entries);    
            user::updateDatasFromAD( $login, $ldap_entries );
          }
          else echo "\nLogin introuvable dans l'AD";
      }
    }
    else die('LDAP BIND KO');
  }


}

?>
