<?php

class produit {

  public static function get( $id = false, $field = "id", $isJuva = false ) {
    if( !$id ) return false;
    global $db;
    //$id = intval($id);


    if ($isJuva) {
      // Ici $field peut être "idoriginal" ou "gencod" si nécessaire
      $db->execute("SELECT * FROM juva_produit WHERE $field = '".$db->escape($id)."'");
      if (!$db->num()) return false;
      $p = $db->getLine();

      // Harmonisation des noms de colonnes pour réutiliser le même template PDF
      return [
          'id_as400' => $p['idoriginal'],
          'libelle' => $p['libelle'],
          'gencode' => $p['gencod'],
          'pcb' => $p['pcb'],
          'tarif' => $p['prix'], // si besoin dans le futur
          // tu peux ajouter d'autres champs ici si tu les utilises
      ];
  }






    $db->execute("SELECT * FROM ref_article WHERE $field = '".$db->escape($id)."' AND deleted = 0 AND actif = 1");
    if( !$db->num() ) return false;
    $produit = $db->getLine();
    $produit['famille'] = ref::getReferentielValue( 'FAMA', $produit['code_famille'] );
    $produit['ss_famille'] = ref::getReferentielValue( 'SFAMA', $produit['sous_famille'] );
    $produit['type_article'] = ref::getReferentielValue( 'TYPA', $produit['type_article'] );
    $produit['marque'] = ref::getReferentielValue( 'CMAR', $produit['code_marque'] );
    $produit['tva'] = ref::getReferentielValue( 'TVA', $produit['code_tva'] );
    $produit['gamme'] = ref::getReferentielValue( 'FAMS', $produit['gamme'] );
    $produit['famille_acd'] = ref::getReferentielValue( 'FAMB', $produit['famille_acd'] );
    $produit['ss_famille_acd'] = ref::getReferentielValue( 'LIBM', $produit['sous_famille_acd'] );
    $produit['famille_plan'] = ref::getReferentielValue( 'FAMI', $produit['famille_plan'] );
    $produit['tarif'] = self::getTarif($produit['id_as400']);

    $produit['stock'] = self::getProduitStock($produit['id_as400']);
    $produit['switch'] = self::getProduitSwitch($produit['id_as400']);


    return $produit;
  }
  public static function getByCode( $code ) {
    global $db;
    $db->execute("SELECT id FROM ref_article WHERE id_as400 = '".$db->escape($code)."' ");
    return $db->num() ? self::get($db->assoc()['id']) : false;
  }
  public static function getByCodeJuva( $code ) {
    global $db;
    $code = $db->escape($code);
    $db->execute("SELECT * FROM juva_produit WHERE idoriginal = '$code'");
    return $db->num() ? $db->assoc() : false;
  }
  public static function isValid( $id_as400 = "", $return = false) {
    global $db;
    if( $id_as400 == "" ) $id_as400 = $db->escape($_POST['id_as400'] ?? $id_as400);
    $db->execute("SELECT id FROM ref_article WHERE id_as400 = '".$db->escape($id_as400)."' ");
    if( !$return ) die('{"valid" : '.($db->num() ? 'true':'false').' }');
    return $db->num() ;
  }

  public static function getProduitStock( $id_as400 ) {
    global $db;
    $db->execute("SELECT * FROM ref_article_stock WHERE id_as400 = '".$db->escape($id_as400)."' ");
    return $db->num() ? $db->assoc() : false;
  }
  public static function getProduitSwitch( $id_as400 ) {
    global $db;
    $db->execute("SELECT * FROM ref_article_switch WHERE id_as400 = '".$db->escape($id_as400)."' ");
    return $db->num() ? $db->assoc() : false;
  }


  public static function getTarif( $code ) {
    global $db;
    if( $code == "" ) return false;
    $db->execute("SELECT * FROM ref_tarif WHERE code_article = '".$db->escape($code)."' AND deleted = 0");
    return $db->num() ? $db->assoc()['tarif'] : false;
  }

  public static function saveInfosSupp() {
    global $db;
    $params = ["details","avantages"];
    $q = [];
    foreach( $params as $p )
      $q[] = " $p = '".nl2br($db->escape($_POST[$p]))."' ";
    $db->execute("UPDATE ref_article_infos SET ".implode(",",$q)." WHERE id_as400 = '".$_POST['id_as400']."' ");
    die('{}');
  }

  public static function saveSwitchArticle() {
    $seuil = intval( $_POST['seuil'] ?? 0);
    $id_switch = trim( $_POST['id_switch'] ?? 0);
    if( !self::isValid($id_switch, true) ) core::ajaxError("Code article de remplacement invalide");
    $id_produit = trim( $_POST['id_produit'] ?? 0);
    $produit = produit::get($id_produit);
    if( !$produit ) core::ajaxError("Produit introuvable");
    if( $produit['id_as400'] == $id_switch ) core::ajaxError("Un article ne peut pas se remplacer lui même");
    $switch = self::getProduitSwitch($produit['id_as400']);
    global $db;
    if( !$switch ) {
      $db->execute("
        INSERT INTO ref_article_switch (id_as400,id_switch,seuil,id_user)
        VALUES ('".$db->escape($produit['id_as400'])."','".$db->escape($id_switch)."',$seuil,".ID.")
      ");
    }
    else {
      $db->execute("
        UPDATE ref_article_switch
        SET
          seuil = $seuil,
          id_switch = '".$db->escape($id_switch)."',
          id_user = ".ID."
        WHERE
          id_as400 =  '".$db->escape($produit['id_as400'])."'
      ");
    }
    die('{}');
  }

  public static function killSwitchArticle() {
    $id_produit = trim( $_POST['id_produit'] ?? 0);
    $produit = produit::get($id_produit);
    if( !$produit ) core::ajaxError("Produit introuvable");
    global $db;
    $db->execute("DELETE FROM ref_article_switch WHERE id_as400 = '".$db->escape($produit['id_as400'])."'");
    die('{}');
  }

  public static function getListePem() {
    global $db;
    $db->execute("SELECT * FROM pem_article WHERE deleted = 0 ORDER BY id_as400");
    return $db->getArray();
  }

  public static function getProduitPem() {
    global $db;
    $id = e($_POST['id'] ?? "");
    if( !$id ) core::aError("Code introuvable");
    $p = self::getByCode($id);
    if( !$p ) core::aError("Code introuvable");
    core::ajax(["id_as400" => $id, "libelle" => $p['libelle']]);
  }

  public static function addProduitPem() {
    global $db;
    $id = e($_POST['id'] ?? "");
    if( !$id ) core::aError("Code introuvable");
    $p = self::getByCode($id);
    if( !$p ) core::aError("Code introuvable");

    foreach( self::getListePem() as $e ) {
      if( $e['id_as400'] == $id ) core::aError("Ce produit est déjà dans la liste des produits PEM");
    }

    $db->execute("
      INSERT INTO pem_article 
      (id_as400,libelle,actif) 
      VALUES
      ('".e($id)."','".e($p['libelle'])."',1)
    ");

    die('{}');
  }

  public static function changeStatePem() {
    global $db;
    $id = intval($_POST['id'] ?? "");
    if( !$id ) core::aError("Code introuvable");
    $db->execute("SELECT * FROM pem_article WHERE id = $id");
    if( !$db->num() ) core::aError("Code introuvable");
    $p = $db->assoc();
    $n = ( $p['actif'] == 1 ? 0 : 1 );
    $db->execute("UPDATE pem_article SET actif = $n WHERE id = $id");
    core::ajax(["state" => $n]);
  }



  public static function addStratPem() {
    global $db;
    $lib = e(trim($_POST['libelle']??""));
    if( $lib == "") core::aError("Libellé introuvable");
    $db->execute("SELECT id FROM strats_pem WHERE libelle = '$lib' AND deleted = 0");
    if($db->num()) core::aError("Cette strat PEM existe déjà");
    $db->execute("INSERT INTO strats_pem (libelle) VALUES ('$lib')");
    die('{}');
  }

  public static function getStratsPem() {
    global $db;
    $db->execute("SELECT * FROM strats_pem WHERE deleted = 0 ORDER BY libelle");
    $strats = $db->get();
    foreach( $strats as $k=>$s ) {
      $db->execute("SELECT * FROM strats_pem_line WHERE deleted = 0 AND id_strat_pem = ".$s['id']);
      $s['lines'] = $db->get();
      $strats[$k] = $s;
    } 
    return $strats;
  }

  public static function apiGetStratsPem() {
    core::ajax(["strats" => self::getStratsPem()]);
  }

  public static function pemSaveLineField() {
    $id_line = intval($_POST['id']??0);
    $name = trim($_POST['name']??"");
    $value = trim($_POST['value']??"");
    $id_strat = intval($_POST['id_strat']??0);
    if( !$id_strat ) core::ajaxError("Erreur");
    global $db;
    if( $id_line == 0 ) {
      $db->execute("INSERT INTO strats_pem_line (id_strat_pem) VALUES ($id_strat)");
      $id_line = $db->lastId();
    }

    $db->execute("UPDATE strats_pem_line SET $name = '".e($value)."' WHERE id = $id_line");

    core::ajax(["id"=>$id_line]);
  }

  public static function delLinePem() {
    $id = intval($_POST['id']??0);
    if( $id > 0 ) {
      global $db;
      $db->execute("DELETE FROM strats_pem_line WHERE id = $id");
    }
    die('{}');
  }
  public static function delStrat() {
    $id = intval($_POST['id']??0);
    if( $id > 0 ) {
      global $db;
      $db->execute("DELETE FROM strats_pem_line WHERE id_strat = $id");
      $db->execute("DELETE FROM strats_pem WHERE id = $id");
    }
    die('{}');
  }
  public static function editStratName() {
    global $db;
    $lib = e(trim($_POST['libelle']??""));
    $id = e(intval($_POST['id']??""));
    if( $lib == "") core::aError("Libellé introuvable");
    $db->execute("SELECT id FROM strats_pem WHERE libelle = '$lib' AND id != $id AND deleted = 0");
    if($db->num()) core::aError("Une autre strat PEM porte déjà ce nom");
    $db->execute("UPDATE strats_pem SET libelle = '$lib' WHERE id = $id ");
    die('{}');
  }


  public static function getListComp( $id_as400 ) {
    global $db;
    $db->execute("SELECT * FROM ref_article_comp WHERE id_as400 = '".e($id_as400)."' ORDER BY actif DESC ");
    return $db->getArray();
  }

  public static function getListArticlesComp() {
    $id_as400 = trim($_POST['id_as400']??"");
    $rep = ["count" => 0];
    $list = self::getListComp($id_as400);
    if( empty($list) ) core::ajax($rep);
    $produits = [];
    foreach($list as $k=>$e) {
      $p = produit::getByCode($e['id_as400_comp']);
      $produits[] = [
        "id" => $p['id'],
        "id_as400" => $p['id_as400'],
        "libelle" => $p['libelle'],
        "qte" => $e['qte'],
        "actif" => $e['actif'],
      ];
    }
    core::ajax($produits);
  }

  public static function addProduitComp() {
    $id_as400 = trim($_POST['id_as400']??"");
    $p = self::getByCode($id_as400);
    if( !$p ) core::ajax(["ko"=> l('page-produit-comp-produit-introuvable')]);

    $code = trim($_POST['code']??"");
    $pc = self::getByCode($code);
    if( !$pc ) core::ajax(["ko"=> l('page-produit-comp-produit-introuvable-code')." : $code"]);

    $qte = intval($_POST['pcb']??0);
    if( $qte == 0 ) core::ajax(["ko"=> l('page-produit-comp-produit-qte')]);

    $all = self::getListComp($id_as400);
    foreach( $all as $u ) 
      if( $u['id_as400_comp'] == $code ) 
        core::ajax(["ko"=> l('page-produit-comp-exist')." ".$p['libelle']]);

    global $db;
    $db->execute("
      INSERT INTO 
        ref_article_comp 
        (id_as400,id_as400_comp,qte,actif)
      VALUES
        ('".e($id_as400)."','".e($code)."','$qte',1)
    ");
    core::ajax(["ok"=>true]);
  }

  public static function produitCompChangeStatut() {
    $id_as400 = trim($_POST['id_as400']??"");
    $p = self::getByCode($id_as400);
    if( !$p ) core::ajax(["ko"=>l('page-produit-comp-produit-introuvable')]);

    $code = trim($_POST['id_as400_comp']??"");
    $pc = self::getByCode($code);
    if( !$pc ) core::ajax(["ko"=>l('page-produit-comp-produit-introuvable-code')." : $code"]);

    $all = self::getListComp($id_as400);
    global $db;
    foreach( $all as $u ) {
      if( $u['id_as400_comp'] == $code ) {
        $db->execute("UPDATE ref_article_comp SET actif = ".($u['actif'] ? 0 : 1)." WHERE id = ".$u['id']);
        break;
      }
    }
    core::end();
  }

  public static function produitCompDelete() {
    $id_as400 = trim($_POST['id_as400']??"");
    $p = self::getByCode($id_as400);
    if( !$p ) core::ajax(["ko"=>l('page-produit-comp-produit-introuvable')]);

    $code = trim($_POST['id_as400_comp']??"");
    $pc = self::getByCode($code);
    if( !$pc ) core::ajax(["ko"=>l('page-produit-comp-produit-introuvable-code')." : $code"]);

    $all = self::getListComp($id_as400);
    global $db;
    foreach( $all as $u ) {
      if( $u['id_as400_comp'] == $code ) {
        $db->execute("DELETE FROM ref_article_comp WHERE id = ".$u['id']);
        break;
      }
    }
    core::end();
  }




}
