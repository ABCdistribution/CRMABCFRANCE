<?php

/*


public static function getVisiteParPromoteurs( $params ) {
    global $db;
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $q = "
    SELECT 
      u.displayname as Promoteur, 
      u.secteur as Région,
      count(*) as 'total visite',
      v.id_user
    FROM visite v
    LEFT JOIN user u ON v.id_user = u.id
    WHERE
      v.deleted = 0
      AND v.queue_date >= '$from'
      AND v.queue_date < '$to'
      AND u.actif = 1
    GROUP BY v.id_user
    ORDER BY `total visite` DESC
    ";
    $q = trim(preg_replace('/\s\s+/', ' ', $q));
    $db->execute($q);
    $rez = ["datas" => [], "query" => $db->query];
    $count = 0;
    $prev = 0;
    $total = 0;
    $ids = [0];
    while( $r = $db->assoc() ) {
      if( $prev != $r['total visite'] || $prev == 0 ) {
        $prev = $r['total visite'];
        $count++;
      }
      if( $r['id_user'] > 0 ) $ids[] = $r['id_user'];
      $r['Classement'] = $count;
      $rez["datas"][] = $r;
      $total += $r['total visite'];
    }
    
    $db->execute("
      SELECT 
        u.displayname as Promoteur, 
        0 as 'total visite',
        u.id as id_user
      FROM user u 
      WHERE
        id NOT IN (".implode(",",$ids).") 
        AND id_profile = 1
        AND u.displayname IS NOT NULL
        AND u.actif = 1
        AND deleted = 0
    ");
    while( $r = $db->assoc() ) {
      $r['Classement'] = "-";
      $rez["datas"][] = $r;
    }



    foreach( $rez['datas'] as $k=>$e ) {
      $id_user = $e['id_user'];
      $user = user::exist($id_user);
      $login = $user['login'];
      unset($rez['datas'][$k]['id_user']);


      $db->execute("
        SELECT count(*) as nb FROM planning
        WHERE
          id_repr = '".$user['id_repr']."'
          AND date_passage >= '$from'
          AND date_passage < '$to'          
          AND deleted = 0
      ");
      $rez['datas'][$k]['visites prévues'] = $db->assoc()['nb'];

      $db->execute("
        SELECT count(*) as nb FROM visite
        WHERE
          id_user  = '".$id_user."'
          AND queue_date >= '$from'
          AND queue_date < '$to'    
          AND no_cmd_reason = 3      
          AND deleted = 0
      ");
      $rez['datas'][$k]['rayons pleins'] = $db->assoc()['nb'];


      $db->execute("
      SELECT 
        count(*) as nb
      FROM commande_apk cmd
          LEFT JOIN user u ON u.login = '$login'
      WHERE
        cmd.deleted = 0
        AND cmd.queue_date >= '$from'
        AND cmd.queue_date < '$to'
        AND cmd.user = '$login'
        AND u.actif = 1
      ");
      $rez['datas'][$k]['total commande'] = $db->assoc()['nb'];


      
      

      $count = $e['Classement'];
      unset($rez['datas'][$k]['Classement']);
      $rez['datas'][$k]['Classement'] = $count;

      $rez['datas'][$k]['%'] = number_format($e['total visite'] * 100 / $total,2,","," ")."%";

    }

    self::response($rez);
  }

  */

















  /*


    public static function getCommandesParPromoteurs( $params ) {
    global $db;
    $from = self::formatDate($params['from']);
    $to = date("Y-m-d",strtotime(self::formatDate($params['to'])) + 86400);
    $q = "
    SELECT 
      u.displayname as Promoteur, 
      u.secteur as Région,
      0 as 'Total visite',
      count(*) as 'Cmd CRM', 
      SUM( CAST(total as UNSIGNED) ) as 'total CRM' ,
      c.user,
      u.id as id_user
    FROM commande_apk c
    LEFT JOIN user u ON c.user = u.login
    WHERE
      c.deleted = 0
      AND c.queue_date >= '$from'
      AND c.queue_date < '$to'
      AND u.actif = 1
    GROUP BY c.user
    ORDER BY `total CRM` DESC
    ";
    $q = trim(preg_replace('/\s\s+/', ' ', $q));
    $db->execute($q);
    $rez = ["datas" => [], "query" => $db->query];
    $count = 0;
    $prev = 0;
    $total = 0;
    $ids = [0];
    while( $r = $db->assoc() ) {
      if( $prev != $r['total CRM'] || $prev == 0 ) {
        $prev = $r['total CRM'];
        $count++;
      }
      if( $r['id_user'] > 0 ) $ids[] = $r['id_user'];
      $r['Classement'] = $count;
      $rez["datas"][] = $r;
      $total += $r['total CRM'];
    }

    
    $db->execute("
      SELECT 
        u.displayname as Promoteur, 
        u.secteur as Région,
        0 as 'Total visite',
        0 as 'Cmd CRM', 
        0 as 'total CRM' ,
        u.login as user
      FROM user u 
      WHERE
        id NOT IN (".implode(",",$ids).") 
        AND id_profile = 1
        AND deleted = 0
        AND u.actif = 1
    ");
    while( $r = $db->assoc() ) {
      $r['Classement'] = "-";
      $rez["datas"][] = $r;
    }


    foreach( $rez['datas'] as $k=>$e ) {
      $user = $e['user'];
      unset($rez['datas'][$k]['user'],$rez['datas'][$k]['id_user']);
      $user = user::getUserFromLogin($user);
      $id = $user['id'];

      $db->execute("
        SELECT count(*) as nb FROM planning
        WHERE
          id_repr = '".$user['id_repr']."'
          AND date_passage >= '$from'
          AND date_passage < '$to'          
          AND deleted = 0
      ");
      $rez['datas'][$k]['visites prévues'] = $db->assoc()['nb'];

      $db->execute("
        SELECT count(*) as nb FROM visite
        WHERE
          id_user  = '".$id."'
          AND queue_date >= '$from'
          AND queue_date < '$to'    
          AND no_cmd_reason = 3      
          AND deleted = 0
      ");
      $rez['datas'][$k]['rayons pleins'] = $db->assoc()['nb'];

      $tmp = $rez['datas'][$k];
      $rez['datas'][$k] = [
        "Promoteur" => $tmp['Promoteur'],
        "Région" => $tmp['Région'],
        "Total visite" => $tmp['Total visite'],
        "visites prévues" => $tmp['visites prévues'],
        "rayons pleins" => $tmp['rayons pleins'],
        "Cmd CRM" => $tmp['Cmd CRM'],
        "total CRM" => $tmp['total CRM']
      ];

      $db->execute("
      SELECT 
        count(*) as nb
      FROM visite v
          left join user u ON u.id = v.id_user
      WHERE
        v.deleted = 0
        AND v.queue_date >= '$from'
        AND v.queue_date < '$to'
        AND id_user = '$id'
        AND u.actif = 1
      ");
      $rez['datas'][$k]['Total visite'] = $db->assoc()['nb'];

      $rez['datas'][$k]['%'] = $total > 0 ? number_format($e['total CRM'] * 100 / $total,2,","," ")."%" : '0%';

    }

    self::response($rez);
  }


  */