<?php

class promo {

  public static function get( $id ) {
    global $db;
    $db->execute("SELECT * FROM ref_promo WHERE ( id = ".intval($id)." OR id_as400 = '".$db->escape($id)."' )");
    return $db->num() ? $db->assoc() : false;
  }

  public static function getAll( $allFields = true ) {
    global $db;
    $fields = "*";
    if( !$allFields ) $fields = " id,id_as400,libelle,id_createur,date_creation,actif ";
    $db->execute("SELECT $fields FROM ref_promo WHERE deleted = 0 ORDER BY ordre");
    return $db->getArray();
  }

  public static function getFormated() {
    $a = self::getAll(false);
    $b = [];
    foreach( $a as $e ) {
      //$e['actif'] = ( $e['actif'] ? '<strong>ACTIF</strong>' : '<em>Inactif</em>' );
      $e['user'] = user::getNameFromId($e['id_createur']);
      $e['date_creation'] = core::dateOutput($e['date_creation']);
      unset($e['id_user']);
      $b[]= $e;
    }
    return $b;
  }

  public static function getJson() {
    die(json_encode(self::getFormated()));
  }

  public static function delPromo() {
    $id = intval($_POST['id']);
    $p = self::get($id);
    if( !$p ) core::ajaxError();
    global $db;
    $db->execute('UPDATE ref_promo SET deleted = 1, ordre = 0, actif = 0 WHERE id = '.$id);
    die('{}');
  }

  public static function new( $id_as400 = null, $libelle = null, $actif = 1 ) {
    if( $id_as400 == null ) {
      if( isset($_POST['id_as400']) )
        $id_as400 = $_POST['id_as400'];
    }
    if( $libelle == null ) {
      if( isset($_POST['libelle']) )
        $libelle = $_POST['libelle'];
    }
    if( isset($_POST['actif']) )
      $actif = intval($_POST['actif']);

    if( !$id_as400 || !$libelle ) return core::error();

    if( strlen($id_as400) < 2 || strlen($id_as400) > 15 )
      return core::error("Le code designation article n'a pas la bonne valeur (entre 2 et 15 caractères)");
    if( strlen($libelle) < 2 || mb_strlen($libelle) > 100 )
      return core::error("La designation n'a pas la bonne valeur (entre 2 et 100 caractères)");

    $promo = self::get($id_as400);
    if( $promo ) core::error("Ce code OP existe déjà");

    global $db;

    $db->execute("SELECT ordre FROM ref_promo ORDER BY ordre DESC LIMIT 1");
    $ordre = $db->assoc()['ordre'] + 1;

    $q = "INSERT INTO ref_promo (id_createur,id_as400,libelle,actif,ordre) ";
    $q .= " VALUES ('".ID."','".$db->escape($id_as400)."','".$db->escape($libelle)."','".$db->escape($actif)."',$ordre) ";
    $db->execute($q);
    core::end();
  }

  public static function editPromo( $id = null, $field = null, $value = null ) {
    global $db;

    if( $id == null ) {
      if( isset($_POST['id']) )
        $id = $_POST['id'];
    }
    if( $field == null ) {
      if( isset($_POST['field']) )
        $field = $_POST['field'];
    }
    if( $value == null ) {
      if( isset($_POST['value']) )
        $value = $_POST['value'];
    }


    $promo = self::get($id);
    if( !$promo ) core::error();
    $fields = [
      "id_as400" => [2,15],
      "libelle" => [2,100],
      "actif" => "int"
    ];
    if( !in_array($field,array_keys($fields)) ) core::error();

    $tmp = $fields[$field];
    if( is_string($tmp) )
      if( $tmp == "int" && $value != intval($value) ) core::error();

    else if( is_array($tmp) && count($tmp) == 2)
      if( strlen($value) < $tmp[0] || mb_strlen($value) > 100 )
        return core::error("Ce champ doit contenir entre ".$tmp[0]." et ".$tmp[1]." caractères");

    global $db;
    $db->execute("UPDATE ref_promo SET $field = '".$db->escape($value)."' WHERE id = ".$promo['id']);
    core::end();
  }

  public static function order() {
    $order = explode("-",$_POST['list']);
    $cp = 1;
    global $db;
    foreach( $order as $id_promo ) {
      $db->execute("UPDATE ref_promo SET ordre = $cp WHERE id = ".intval($id_promo));
      $cp++;
    }
    die('{}');
  }

}
