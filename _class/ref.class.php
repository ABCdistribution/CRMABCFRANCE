<?php

class ref {

  public static function checkExists( $table, $libelle, $id_as400, $opt = "" ) {
    global $db;
    $db->execute('
      SELECT
        id
      FROM
        '.$table.'
      WHERE
        deleted = 0
        AND id_as400 = "'.$db->escape($id_as400).'"
        AND libelle = "'.$db->escape($libelle).'"
        '.($opt!="" ? " AND ".$opt : '' ).'
    ');
    if( !$db->num() ) return false;
    return $db->assoc()['id'];
  }

  public static function get( $table, $id, $field = "libelle" ) {
    global $db;
    $db->execute("SELECT ".e($field)." FROM ".e($table)." WHERE id = ".intval($id));
    return $db->num() ? $db->getLine()[$field] : false;
  }

  public static function createMarque( $libelle, $id_as400 ) {
    global $db;
    $id_marque = self::checkExists('ref_marque',$libelle, $id_as400);
    if( $id_marque ) return $id_marque;
    $db->execute("
      INSERT INTO ref_marque
        (id_as400,libelle)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."')
    ");
    return $db->lastId();
  }

  public static function createGamme( $libelle, $id_as400, $id_marque ) {
    global $db;
    $id_gamme = self::checkExists('ref_gamme',$libelle, $id_as400, ' id_marque = '.$id_marque);
    if( $id_gamme ) return $id_gamme;
    $db->execute("
      INSERT INTO ref_gamme
        (id_as400,libelle,id_marque)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."',".$id_marque.")
    ");
    return $db->lastId();
  }


  public static function createFamille( $libelle, $id_as400, $id_gamme ) {
    global $db;
    $id_famille = self::checkExists('ref_famille',$libelle, $id_as400, ' id_gamme = '.$id_gamme);
    if( $id_famille ) return $id_famille;
    $db->execute("
      INSERT INTO ref_famille
        (id_as400,libelle,id_gamme)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."',".$id_gamme.")
    ");
    return $db->lastId();
  }

  public static function createArticle( $libelle, $id_as400, $id_famille ) {
    global $db;
    $id_article = self::checkExists('ref_article',$libelle, $id_as400, ' id_famille = '.$id_famille);
    if( $id_article ) return $id_article;
    $db->execute("
      INSERT INTO ref_article
        (id_as400,libelle,id_famille)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."',".$id_famille.")
    ");
    return $db->lastId();
  }



  // CENTRALES
  public static function createCentrale( $libelle, $id_as400 ) {
    global $db;
    $id = self::checkExists('ref_centrale',$libelle, $id_as400);
    if( $id ) return $id;
    $db->execute("
      INSERT INTO ref_centrale
        (id_as400,libelle)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."')
    ");
    return $db->lastId();
  }

  public static function createSousCentrale( $libelle, $id_as400, $id_centrale ) {
    global $db;
    $id = self::checkExists('ref_sous_centrale',$libelle, $id_as400, ' id_centrale = '.$id_centrale);
    if( $id ) return $id;
    $db->execute("
      INSERT INTO ref_sous_centrale
        (id_as400,libelle, id_centrale)
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."',".$id_centrale.")
    ");
    return $db->lastId();
  }

  public static function createClient( $libelle, $id_as400, $id_sous_centrale, $id_client_facture = 0, $id_commercial = 0 ) {
    global $db;
    $id = self::checkExists('ref_client',$libelle, $id_as400, ' id_sous_centrale = '.$id_sous_centrale);
    if( $id ) return $id;
    $db->execute("
      INSERT INTO ref_client
        (id_as400,libelle, id_sous_centrale, id_client_facture, id_commercial )
      VALUES
        ('".$id_as400."','".$db->escape($libelle)."',".$id_sous_centrale.", ".$id_client_facture.", ".$id_commercial.")
    ");
    return $db->lastId();
  }





  public static function getLastClients( $count = 10, $orderby = "date_creation" ) {
    $clients = [];
    global $db;
    $db->execute("SELECT * FROM ref_client WHERE deleted = 0 AND actif = 1 ORDER BY $orderby DESC LIMIT ".intval($count));
    if( !$db->num() ) return [];
    while( $r = $db->assoc() ) $clients[$r['id']] = $r;
    return $clients;
  }
  public static function getLastProspects( $count = 10, $orderby = "date_creation" ) {
    $prospects = [];
    global $db;
    $db->execute("SELECT * FROM prospect WHERE deleted = 0 ORDER BY $orderby DESC LIMIT ".intval($count));
    if( !$db->num() ) return [];
    while( $r = $db->assoc() ) $prospects[$r['id']] = $r;
    return $prospects;
  }


  public static function searchClient( $count = 50 ) {
    $str = $_POST['search'];
    $clients = [];
    global $db;
    $q = " AND ( enseigne like '%".$db->escape($str)."%' OR id_as400 like '%".$db->escape($str)."%' ) ";
    $db->execute("SELECT * FROM ref_client WHERE deleted = 0 AND actif = 1 $q ORDER BY date_creation DESC LIMIT ".intval($count));
    if( !$db->num() ) core::ajax([]);
    while( $r = $db->assoc() ) $clients[] = ["libelle"=>$r['enseigne'],"id"=>$r['id']];
    core::ajax($clients);
  }

  public static function countTotalClient() {
    global $db;
    $db->execute("SELECT COUNT(*) as 'nb' FROM ref_client WHERE deleted = 0 AND actif = 1 ");
    return $db->assoc()['nb'];
  }
  public static function countTotalProspects() {
    global $db;
    $db->execute("SELECT COUNT(*) as 'nb' FROM prospect WHERE deleted = 0 ");
    return $db->assoc()['nb'];
  }  

  public static function getClient( $id ) {
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM ref_client WHERE id = $id AND deleted = 0 AND actif = 1");
    return $db->num() ? $db->getLine() : false;
  }

  public static function saveForm() {
    global $db;
    $table = $db->escape(trim($_POST['table']));
    $id = intval($_POST['id']);

    $fields = $db->getTableFields($table);
    unset($fields['id'],$fields['date_creation'],$fields['deleted']);
    foreach( $_POST as $k=>$f ) {
      if( !in_array($k, array_keys($fields)) ) {
        unset($_POST[$k]);
      }
    }


    $query = "UPDATE $table SET ";
    $q = [];
    foreach( $_POST as $k=>$f ) {
      $q[] = " $k = '".$db->escape($f)."' ";
    }
    $query .= implode(" , ", $q);
    $query .= " WHERE id = $id";
    $db->execute($query);

    die('{}');
  }










  public static function getLastProduits( $count = 10, $orderby = "date_creation" ) {
    $produits = [];
    global $db;
    $db->execute("SELECT * FROM ref_article WHERE deleted = 0 AND actif = 1 ORDER BY $orderby DESC LIMIT ".intval($count));
    if( !$db->num() ) return [];
    while( $r = $db->assoc() ) $produits[$r['id']] = $r;
    return $produits;
  }

  public static function searchProduit( $count = 30 ) {
    $str = strtolower($_POST['search']);
    global $db;
    $produits = [];
    $strEscaped = $db->escape($str);
    $q = " AND ( 
      LOWER(libelle) like '%".$strEscaped."%' 
      OR LOWER(id_as400) LIKE '%".$strEscaped."%' 
      OR LOWER(id_ita) LIKE '%".$strEscaped."%' 
      OR LOWER(gencode) LIKE '%".$strEscaped."%'
    ) ";
    $db->execute("SELECT * FROM ref_article WHERE deleted = 0 AND actif = 1 $q ORDER BY id_as400 ASC LIMIT ".intval($count));
    if( !$db->num() ) core::ajax([]);
    while( $r = $db->assoc() ) $produits[] = ["libelle"=> '<strong>'.$r['id_as400'].'</strong> : '.$r['libelle'],"id"=>$r['id']];
    core::ajax($produits);
  }

  public static function countTotalProduit() {
    global $db;
    $db->execute("SELECT COUNT(*) as 'nb' FROM ref_article WHERE deleted = 0 AND actif = 1 ");
    return $db->assoc()['nb'];
  }

  public static function getProduit( $id ) {
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM ref_article WHERE id = $id AND deleted = 0 AND actif = 1");
    return $db->num() ? $db->getLine() : false;
  }


  public static function getProduitFamille( $id ) {
    global $db;
    $id = intval($id);
    $db->execute("SELECT * FROM ref_famille WHERE id = $id AND deleted = 0");
    return $db->num() ? $db->getLine() : false;
  }


    public static function getProduitGamme( $id ) {
      global $db;
      $id = intval($id);
      $db->execute("SELECT * FROM ref_gamme WHERE id = $id AND deleted = 0");
      return $db->num() ? $db->getLine() : false;
    }

    public static function getProduitMarque( $id ) {
      global $db;
      $id = intval($id);
      $db->execute("SELECT * FROM ref_marque WHERE id = $id AND deleted = 0");
      return $db->num() ? $db->getLine() : false;
    }


    public static function getReferentielValue( $nature, $code = "" ) {
      global $db;
      if( $code == "" ) return false;
      $db->execute("SELECT * FROM referentiels WHERE nature = '$nature' AND valeur = '".$db->escape($code)."' ");
      return $db->num() ? $db->assoc() : false;
    }

    public static function getRefArticleInfos( $id_as400 ) {
      global $db;
      $id = $db->escape($id_as400);
      $db->execute("SELECT * FROM ref_article_infos WHERE id_as400 = '".$id."' ");
      if( !$db->num() ) {
        $db->execute("INSERT INTO ref_article_infos (id_as400) VALUES ('".$id."')");
        $db->execute("SELECT * FROM ref_article_infos WHERE id_as400 = '".$id."' ");
      }
      return $db->assoc();
    }


}
?>
