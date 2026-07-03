<?php

class securite {

  public static function getProfils() {
    global $profiles,$db;
    if( !$profiles ) {
      $db->execute("SELECT * FROM secu_profile ORDER BY libelle");
      $profiles = $db->getArray();
    }
    return $profiles;
  }

  public static function get( $id ) {
    return self::getProfils()[$id];
  }

  public static function newProfil() {
    $v = trim($_POST['v']);
    if( mb_strlen($v) < 5 || mb_strlen($v) > 80 )
      core::aError("Le libellé du profil doit contenir entre 5 et 80 caractères");
    $list = self::getProfils();
    $ex = false;
    foreach( $list as $k=>$e ) {
      if( mb_strtolower($v) == mb_strtolower($e['libelle']) )
        $ex = true;
    }
    if( $ex ) core::aError("Ce profil existe déjà");
    global $db;
    $db->execute("INSERT INTO secu_profile (libelle) VALUES ('".$db->escape($v)."')");
    die('{}');
  }

  public static function getDefaultProfile() {
    global $db;
    $db->execute('SELECT id FROM secu_profile WHERE defaut = 1 AND deleted = 0');
    if( !$db->num() ) return 0;
    return $db->assoc()['id'];
  }

  public static function getUserProfile( $id ) {
    global $db;
    $user = user::exist($id);
    return $user['id_profile'];
  }

  public static function getUserProfileLibelle( $id ) {
    $p = self::getProfils();
    $id_user_profile = self::getUserProfile($id);
    if( !isset($p[$id_user_profile]) ) return "#";
    return $p[$id_user_profile]['libelle'];
  }

  public static function editProfile() {
    $libelle = trim($_POST['libelle']);
    $homepage = trim($_POST['homepage']);
    $defaut = intval($_POST['defaut']);
    $id_profile = intval($_POST['id']);
    if( mb_strlen($libelle) < 5 || mb_strlen($libelle) > 80 )
      core::aError("Le libellé du profil doit contenir entre 5 et 80 caractères");
    $list = self::getProfils();
    $ex = false;
    foreach( $list as $k=>$e ) {
      if( mb_strtolower($libelle) == mb_strtolower($e['libelle']) && $k != $id_profile )
        $ex = true;
    }
    if( $ex ) core::aError("Un autre profil du même libelle existe déjà");
    global $db;

    if( $defaut == 1 )
      $db->execute("UPDATE secu_profile SET defaut = 0 WHERE deleted = 0");

    $db->execute("
    UPDATE secu_profile
    SET
      libelle = '".$db->escape($libelle)."',
      defaut = $defaut,
      homepage = '".$db->escape($homepage)."'
    WHERE
      id = $id_profile
    ");
    die('{}');
  }

  public static function deleteProfile() {
    $id_profile = intval($_POST['id']);
    $id_defaut = self::getDefaultProfile();
    if( $id_defaut == $id_profile ) core::aError("Impossible de supprimer le profil par défaut");

    $profile = self::get($id_profile);
    if( $profile['protected'] ) core::aError("Ce profil est protégé contre la supression");

    global $db;
    $db->execute("UPDATE user SET id_profile = $id_defaut WHERE id_profile = $id_defaut");
    $db->execute("DELETE FROM secu_profile WHERE id = $id_profile");
    die('{}');
  }


  public static function getCategoriesDroits() {
    global $db;
    $db->execute("SELECT * FROM secu_droit_categorie");
    return $db->getArray();
  }

  public static function getAllDroits() {
    global $db;
    $db->execute("SELECT * FROM secu_droit WHERE deleted = 0 ORDER BY libelle");
    return $db->getArray();
  }

  public static function getListDroits() {
    $categories = self::getCategoriesDroits();
    $droits = self::getAllDroits();

    $tab = [];
    foreach( $categories as $id_categorie => $cat ) {
      $tmp = [
        "id_categorie" => $id_categorie,
        "libelle" => $cat['libelle'],
        "droits" => []
      ];
      foreach( $droits as $id_droit => $droit ) {
        if( $droit['id_categorie'] != $id_categorie ) continue;
        $tmp["droits"][$id_droit] = $droit['libelle'];
      }
      $tab[] = $tmp;
    }
    return $tab;
  }

  public static function changeDroit() {
    $id_profile = intval($_POST['id_profile']);
    $id_droit = intval($_POST['id_droit']);
    $state = intval($_POST['state']);
    if( !$id_profile || !$id_droit ) core::aError();
    global $db;
    $db->execute("SELECT * FROM secu_profile_droit WHERE id_profile = $id_profile AND id_droit = $id_droit");
    if( !$state && $db->num() ) {
      $id = $db->assoc()['id'];
      $db->execute("DELETE FROM secu_profile_droit WHERE id = $id");
    }
    if( $state && !$db->num() ) {
      $db->execute("INSERT INTO secu_profile_droit (id_profile,id_droit) VALUES ($id_profile,$id_droit)");
    }
    die('{}');
  }

  public static function getDroitsProfile() {
    $id_profile = intval($_POST['id_profile']);
    global $db;
    $db->execute("SELECT id_droit FROM secu_profile_droit WHERE id_profile = $id_profile");
    $droits = [];
    while( $r = $db->assoc() ) $droits[] = $r['id_droit'];
    die(json_encode($droits));
  }

  public static function can( $id_droit ) {
    global $userSecu;
    if( !$userSecu || $userSecu === null || empty($userSecu) ) self::defineUserSecu();
    return in_array($id_droit,$userSecu['droits']);
  }

  public static function defineUserSecu() {
    if( !defined('ID') || !ID ) return false;
    global $userSecu;
    $userSecu = [];
    global $db;
    $db->execute("SELECT id_profile FROM user WHERE id = ".ID);
    $userSecu['id_profile'] = $db->assoc()['id_profile'];

    $db->execute("SELECT admin FROM secu_profile WHERE id = ".$userSecu['id_profile']);
    $userSecu['isAdmin'] = ( $db->assoc()['admin'] == 1 );

    $db->execute("SELECT id_droit FROM secu_profile_droit WHERE id_profile = ".$userSecu['id_profile']);
    $userSecu['droits'] = [];
    while( $r = $db->assoc() )
      $userSecu['droits'][] = $r['id_droit'];
    return;
  }

  public static function isAdmin() {
    if( !defined('ID') || !ID ) return false;
    global $userSecu;
    if( !$userSecu || $userSecu === null || empty($userSecu) ) self::defineUserSecu();
    return $userSecu['isAdmin'];
  }

  public static function changeProfile() {
    if( !securite::isAdmin() ) core::aError();
    $id_profile = intval($_POST['id_profile']);
    $id_user = intval($_POST['id_user']);
    global $db;
    $db->execute("UPDATE user SET id_profile = $id_profile WHERE id = $id_user");
    die('{}');
  }

  public static function printProfileName() {
    global $userSecu;
    if( !$userSecu || $userSecu === null || empty($userSecu) ) self::defineUserSecu();
    $libelle = self::get($userSecu['id_profile'])['libelle'];
    return $libelle;
  }

}
