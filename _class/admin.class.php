<?php

class admin {

  public static function getLogReferentiels( $limit = 30 ) {
    global $db;
    $db->execute("SELECT * FROM log_referentiel ORDER BY id DESC LIMIT $limit");
    return $db->getArray();
  }

  public static function getListeUserMailAlerteCom() {
    global $db;
    $db->execute("SELECT id, displayname FROM `user` WHERE `id_profile` = 5 AND actif = 1 AND deleted = 0 AND id NOT IN (SELECT id_user FROM alerte_user WHERE deleted = 0) ORDER BY `displayname` ASC");
    return $db->getArray();
  }

  public static function getListeMailAlerteCom() {
    global $db;
    $db->execute("SELECT a.id, u.name, u.mail, a.deleted FROM alerte_user a LEFT JOIN user u ON a.id_user = u.id WHERE a.deleted = 0 ORDER BY u.name ASC");
    return $db->getArray();
  }

  public static function addMailAlerteCom() {
    $html_tab = '';
    $html_select = '';
    $id = intval($_POST['user_id']??0);
    if($id>0){
      global $db;
      $db->execute("INSERT INTO alerte_user (id_user) VALUES (".intval($id).")");
      $html_select = self::getHtmlSelectMailAlerteCom();
      $html_tab = self::getHtmlTabMailAlerteCom();
    }
    die(json_encode(['html_select' => $html_select, 'html_tab' => $html_tab],JSON_FORCE_OBJECT));
  }

  public static function getHtmlTabMailAlerteCom() {
    global $db;    
    $html = '';    
    $users = admin::getListeMailAlerteCom();
    foreach( $users as $user ) {
      $html .= '
      <tr class="userMail'.$user['id'].'">
        <td class="tl">'.$user['name'].'</td>
        <td>'.$user['mail'].'</td>
        <td><i class="fas fa-times" onclick="deleteMailAlerteCom('.$user['id'].')"></i></td>
      </tr>
      ';
    }
    return $html;
  }

  public static function getHtmlSelectMailAlerteCom() {
    global $db;    
    $users_select = self::getListeUserMailAlerteCom();
    $html = '<option selected="true" disabled="disabled">Sélectionner l\'utilisateur à ajouter</option>';
    foreach( $users_select as $user_select ) {
        $html .= '<option id="option'.$user_select["id"].'" value="'.$user_select["id"].'">'.$user_select['displayname'].'</option>';
    }
    return $html;
  }

  public static function deleteMailAlerteCom() {
    $id = intval($_POST['id']??0);
    global $db;
    $db->execute("UPDATE alerte_user SET deleted = 1 WHERE id = $id");
    $html = self::getHtmlSelectMailAlerteCom();
    die(json_encode(["html_select" => $html],JSON_FORCE_OBJECT));
  }
  
  public static function getNbAlerteCom() {
    global $db;
    $db->execute("
    SELECT
      id
    FROM
      visite v
    WHERE
       v.deleted = 0
       AND alerte_raison > 0
      AND v.queue_date LIKE '".date("Y-m-d")."%'
    ");
    return $db->num();
  }

  public static function injectObjectif() {
    $csv = $_POST['csv'];
    if( empty($csv) ) core::ajaxError();
    foreach( $csv as $k=>$line) {
      if( $line == "" ) continue;
      $tmp = explode(";",trim($line));
      if( count($tmp) != 3) core::ajaxError( l('admin-objectifs-error-2') . ($k+1) );
      $obj = floatval(str_replace([" ",","],["","."],trim($tmp[2])));
      $y = date('Y', strtotime(trim($tmp[0])));
      $m = date('n', strtotime(trim($tmp[0])));
      self::updateObjectif( intval($tmp[1]), $y, $m, $obj);
    }
    core::end();
  }

  public static function updateObjectif( $id_repr, $y, $m, $obj ) {
    global $db;
    $db->execute("
      SELECT id 
      FROM objectifs 
      WHERE
        id_repr = ".intval($id_repr)."
        AND annee = ".intval($y)."
        AND mois = ".intval($m)."  
    ");
    if( $db->num() ) {
      $id = $db->assoc()['id'];
      $db->execute("UPDATE objectifs SET total = ".intval($obj)." WHERE id = $id");
    }
    else {
      $db->execute("
        INSERT INTO objectifs
        (id_repr,annee,mois,total)
        VALUES
        (".intval($id_repr).",".intval($y).",".intval($m).",".intval($obj).")
      ");
    }
  }

  public static function getHistoriqueIntegrationFactures() {
    global $db;
    $db->execute("SELECT * FROM log_referentiel WHERE `filename` LIKE 'HIS%' ORDER BY date_traitement DESC LIMIT 100");
    $rez = [];
    while( $r = $db->assoc() ) {
      $rez[] = [
        "file" => $r['filename'],
        "date" => core::dateOutput($r['date_traitement'],true),
        "from" => core::dateFrom($r['date_traitement']),
        "lines" => $r['nb_lines']
      ];
    }
    return $rez;
  }


  public static function getDaysOff() {
    $y = intval($_POST['year']);
    global $db;
    $db->execute("SELECT * FROM jours_off WHERE date_off LIKE '$y%' ORDER BY date_off ASC");
    $datas = $db->get();

    die(json_encode(["results" => $datas],JSON_FORCE_OBJECT));
  }
  public static function addJourOff() {
    $date = strtotime($_POST['date']??"");
    $remarque = trim($_POST['remarque']??"");
    if( !$date ) core::aError("Date incorrecte");
    global $db;
    $d = date("Y-m-d",$date);
    $db->execute("SELECT * FROM jours_off WHERE date_off LIKE '$d' ");
    if( $db->num() ) core::aError("Ce jour a déjà été renseigné");
    $db->execute("INSERT INTO jours_off (date_off,remarque) VALUES ('$d','".$db->escape($remarque)."') ");
    die('{}');
  }
  public static function deleteJourOff() {
    $id = intval($_POST['id']??0);
    global $db;
    $db->execute("DELETE FROM jours_off WHERE id = $id");
    die('{}');
  }
  public static function addAutoJourOff() {
    $year = intval($_POST['year'] ?? 0);
    if( $year < 1 ) core::aError();
    $addDates = FrenchHolidays::getDaysAndNames($year);
    global $db;
    foreach( $addDates as $day => $remarque ) {
      $db->execute("SELECT * FROM jours_off WHERE date_off LIKE '$day%' ");
      if( !$db->num() )
        $db->execute("INSERT INTO jours_off (date_off,remarque) VALUES ('$day','".$db->escape($remarque)."') ");
    }
    die('{}');
  }

}



class FrenchHolidays {
 
  public static function isHoliday($day=false, $alsacemoselle=false){
      if(!$day) $day = date('Y-m-d');
      // Validation de la date
      $dt = DateTime::createFromFormat('Y-m-d', $day);
      if(!$dt || $dt->format('Y-m-d')!=$day){
          trigger_error('Date invalide', E_USER_WARNING);
          return false;
      }
      $year = date("Y", strtotime($day));
      $days = self::getDays($year, $alsacemoselle);
      if(in_array($day, $days)) return true;
      return false;
  }

  public static function getDaysAndNames($year=false, $alsacemoselle=false){
      if(!$year) $year = date('Y');
      if(!is_numeric($year)){
          trigger_error('Année invalide', E_USER_WARNING);
          return false;
      }

      // Etape 1 : religion (écrasé par les date laïques)
      $days = array(
          self::dimanchePaques($year) => "Pâques",
          self::lundiPaques($year) => "Lundi de Pâques",
          self::jeudiAscension($year) => "Jeudi de l'Ascension",
          self::lundiPentecote($year) => "Lundi de Pentecôte",
          $year.'-08-15' => "Assomption",
          $year.'-11-01' => "La Toussaint",
          $year.'-12-25' => "Noël",
      );

      // Etape 1 bis : dates religieuses supplémentaires Alsace-Moselle
      if($alsacemoselle){
          $days = array_merge($days, array(
              $year.'-12-26' => "Saint-Etienne",
              self::vendrediSaint($year) => "Vendredi Saint"
          ));
      }

      // Etape 2 : dates laïques
      $days = array_merge($days, array(
          $year.'-01-01' => "Jour de l'an",
          $year.'-05-01' => "Fête du travail",
          $year.'-05-08' => "Victoire des Alliés",
          $year.'-07-14' => "Fête nationale",
          $year.'-11-11' => "Armistice",
      ));
      
      ksort($days);
      return $days;
  }

  public static function getDays($year=false, $alsacemoselle=false){
      $daysnames = self::getDaysAndNames($year, $alsacemoselle);
      return (is_array($daysnames)) ? array_keys($daysnames) : false;
  }

  ///////

  private static function dimanchePaques($year){
      return date("Y-m-d", easter_date($year));
  }

  private static function vendrediSaint($year){
      $dimanche_paques = self::dimanchePaques($year);
      return date("Y-m-d", strtotime("$dimanche_paques -2 days"));
  }

  private static function lundiPaques($year){
      $dimanche_paques = self::dimanchePaques($year);
      return date("Y-m-d", strtotime("$dimanche_paques +1 day"));
  }

  private static function jeudiAscension($year){
      $dimanche_paques = self::dimanchePaques($year);
      return date("Y-m-d", strtotime("$dimanche_paques +39 days"));
  }

  private static function lundiPentecote($year){
      $dimanche_paques = self::dimanchePaques($year);
      return date("Y-m-d", strtotime("$dimanche_paques +50 days"));
  }

}