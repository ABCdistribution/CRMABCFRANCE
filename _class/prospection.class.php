<?php

class prospection {


    public static function getStep( $code ) {
      $steps = [
        "PROS_START" => l('prospection-steps-nouvelle'),
        "PROS_END" => l('prospection-steps-fin-etape-une'),
        "PROS_ENV" => l('prospection-steps-proposition-envoyee'),
        "PROS_CREACLI" => l('prospection-steps-proposition-acceptee')
      ];
      return $steps[$code];
    }

    public static function getFromIdProspect( $id_prospect ) {
      global $db;
      $db->execute("SELECT id FROM prospection WHERE id_prospect = '".$db->escape($id_prospect)."' AND deleted = 0");
      return $db->num() ? self::get($db->assoc()['id']) : false;
    }

    public static function get( $appid ) {
        global $db;
        $db->execute("
            SELECT * FROM 
                prospection 
            WHERE 
                ( 
                    id = '".( intval($appid) == $appid ? intval($appid) : -1 )."' 
                    OR appid = '".$db->escape($appid)."' 
                ) 
                AND deleted = 0
        ");
        if( !$db->num() ) return false;
        $p = $db->assoc();
        $p['dn'] = self::getOthers( 'dn', $p['id'] );
        $p['steps'] = self::getOthers( 'step', $p['id'] );
        $p['photos'] = self::getOthers( 'photo', $p['id'] );
        return $p;
    }

    public static function getOthers( $what, $id ) {
        global $db;
        $db->execute("SELECT * FROM prospection_$what WHERE id_prospection = '".intval($id)."' AND deleted = 0");
        return $db->num() ? $db->getArray() : [];
    }

    public static function create( $datas ) {
        global $db;
        $idp = $datas['appid'];
        # Test existance
        if( self::get($idp) ) return;

        # User
        $id_user = ( defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : 0);

        # Prospect
        $id_prospect = prospect::getIdFromAppId( $datas['prospect'] );

        # Préparation
        $i = $db->enquote([
            "id_user" => $id_user,
            "appid" => $idp,
            "id_prospect" => $id_prospect,
            "step" => $datas['currentStep']
        ]);

        # Insertion
        $db->execute("
            INSERT INTO
                prospection
                (".implode(",",array_keys($i)).",date_modification)
            VALUES
                (".implode(",",$i).", NOW() )
        ");
        $id_prospection = $db->lastId();

        # Mise à jour du prospect
        $db->execute("UPDATE prospect SET step = 2 WHERE id = $id_prospect");        

        # DN
        if( !empty($datas['dn']) ) {
            foreach( $datas['dn'] as $dn ) {
                if( $dn['nom'] == "" && $dn['gamme'] == "" ) continue;
                unset($dn['id']);
                $dn['id_prospection'] = $id_prospection;
                $dn = $db->enquote($dn);
                $db->execute("
                INSERT INTO
                    prospection_dn
                    (".implode(",",array_keys($dn)).")
                VALUES
                    (".implode(",",$dn).")
                ");
            }
        }

        # Steps
        if( !empty($datas['steps']) ) {
            foreach( $datas['steps'] as $step ) {
                unset($step['id']);
                $step['id_prospection'] = $id_prospection;
                $step = $db->enquote($step);
                $db->execute("
                INSERT INTO
                    prospection_step
                    (".implode(",",array_keys($step)).")
                VALUES
                    (".implode(",",$step).")
                ");
            }            
        }



        return;
    }

    public static function savePhoto( $params ) {
        global $db;
        $photo = isset($params['photo2']) ? $params['photo2'] : base64_decode($params['photo']);
        $name = $params['name'];
        
        # Existence de la photo
        $db->execute('SELECT id FROM prospection_photo WHERE `name` = "'.$db->escape($name).'"');
        if( $db->num() ) { core::logApk("(Prospection) La photo existe déjà : $name"); return; }

        # Existence de la prospection
        $tmp = explode("_photo",$name);
        $appid_prospection = $tmp[0];
        $prospection = prospection::get($appid_prospection);
        if( !$prospection ) { core::logApk("(Prospection) Envoi d'une photo, mais la prospection n'existe pas : $name"); return; }

        # Création du répertoire de la photo
        $prePath = FILES."prospection/";
        $path = date('Y').'/'.date('m').'/'.date('d').'/';
        if( !is_dir($prePath.$path) ) mkdir($prePath.$path, 0777, true);  

        # Déplacement de la photo
        $filename = time()."-".rand(1000,9999).".jpg";
        $full = $prePath.$path.$filename;
        file_put_contents( $full, $photo);

        # Sauvegarde en base
        $db->execute("
          INSERT INTO 
            prospection_photo 
            (id_prospection,name,path,size)
          VALUES 
            (
                '".$prospection['id']."','".$db->escape($name)."',
                '".($path.$filename)."','".filesize($full)."'
            )
        ");
    
        core::logApk("(Prospection) Photo bien récéptionnée $filename (".core::readableSize(filesize($full)).")");

        return;

    }


    public static function searchBoard() {
        $str = trim(strtolower($_POST['str']));
        $limit = intval($_POST['limit']);
        $offset = intval($_POST['offset']);
        $from = $_POST['from'];
        $to = $_POST['to'];
    
        $w = [];
        $w[] = "p.deleted = 0";
    
        $checkDate = true;
        if( $from == "" || $to == "" ) $checkDate = false;
        else $to = date('Y-m-d',strtotime('+1 day',strtotime($to)));
    
        if( $checkDate )
          $w[] = " p.date_modification >= '$from' AND p.date_modification <= '$to' ";
    
        global $db;
        if( $str != "" ) {
          $str = $db->escape($str);
    
          $date = $str;
          if( strpos($date,"/") > 0 ) {
            $tmp = explode("/",$str);
            $date = implode("-",array_reverse($tmp));
          }
    
          $w[] = "
          (
            LOWER(u.displayname) LIKE '%".$str."%'
            OR
            LOWER(c.enseigne) LIKE '%".$str."%'
            OR
            LOWER(c.id_as400) LIKE '%".$str."%'
            OR
            v.date_creation LIKE '%".$date."%'
          )
          ";
        }

        
        $count = 0;
        $z = "
        FROM
          prospection p
          LEFT JOIN user u ON p.id_user = u.id
          LEFT JOIN prospect pr ON p.id_prospect = pr.id
        WHERE
          ".implode(" AND ", $w);
        $qStart = "SELECT count(*) as nb ".$z;
        $db->execute($qStart);
        $count = $db->assoc()['nb'];
    
    
        $q = "
          SELECT p.id ".$z."
          ORDER BY
            p.date_modification DESC
          LIMIT
            ".($offset*$limit).", $limit
        ";
        $db->execute($q);
        if( !$db->num() ) {
          $q = str_replace(["\r","\n"],"",$q);
          core::ajax(["html"=>"<tr><td class='text-center' colspan='9'>Aucun résultat</td></tr>","query"=>$q]);
        }
    

        $ids = [];
        while( $r = $db->assoc() ) $ids[] = $r['id'];
        $html = [];
        foreach( $ids as $id_prospection ) {
          $p = prospection::get($id_prospection);
          $db->execute('select count(*) as nb from prospection_dn WHERE id_prospection = '.$id_prospection);
          $dn = ( $db->assoc()['nb'] > 0 ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' );
    
          $pr = prospect::get($p['id_prospect']);
    
          $cp = count($p['photos']);
          $html[] = '<tr data-id="'.$id_prospection.'">';
          $html[] = '<td>'.core::dateOutput($p['date_modification'],true).'</td>';
          $html[] = '<td>'.$pr['nom'].'</td>';
          $html[] = '<td>'.$pr['ptype'].'</td>';
          $html[] = '<td>'.user::getNameFromId($p['id_user']).'</td>';
          $html[] = '<td>'.self::getStepName($p['step']).'</td>';
          $html[] = '<td class="tc">'.( $cp == 0 ? '<i class="fas fa-times text-danger"></i>' : $cp." <i class='fas fa-camera'></i>" ).'</td>';
          $html[] = '<td class="tc">'.$dn.'</td>';
          $html[] = '</tr>';
        }
        core::ajax(["html"=>implode($html),"count"=> core::n($count)]);

    }

    public static function getStepName( $step ) {
        switch( $step ) {
            case 1 : return "Démarrage prospection";
            case 2 : return "Prospection terminée, attente proposition";
            case 3 : return "Proposition envoyée";
            case 4 : return "Proposition acceptée, création client";
            case 5 : return "Prospect devenu client";
            default : return "???";
        }
    }

    public static function getDeportedProspections() {
      $p = [];
      $ids = [];
      global $db;
      $db->execute("
        SELECT 
          p.* ,
          pr.appid as prospect
        FROM 
          prospection p
          LEFT JOIN prospect pr ON p.id_prospect = pr.id
        WHERE 
          p.deleted = 0 
        ORDER BY p.id DESC
      ");
      while( $r = $db->assoc() ) {
        if( !in_array($r['id_prospect'],$ids) ) {
          $p[] = [
            'id' => $r['id'],
            'appid' => $r['appid'],
            'date_creation' => $r['date_creation'],
            'prospect' => $r['prospect'],
            'currentStep' => $r['step'],
            'steps' => [], // server only
            'photos' => [],// server only
            'dn' => []     // server only 
          ];
          $ids[] = $r['id_prospect'];
        }
      }
      return $p;
    }


    public static function validerEnvoi( $params ) {
      global $db;
      $appid = $params['appid'];
      $step = $params['step'];
      $currentStep = intval($params['currentStep']);

      $db->execute("SELECT * FROM prospection WHERE appid = '".$db->escape($appid)."' ");
      if( !$db->num() || $appid == "") api::aError("Prospection introuvable...");
      $p = $db->assoc();
      if( $p['step'] == 3 ) api::ajaxRep("");
      $id_prospection = $p['id'];
      $db->execute("UPDATE prospection SET step = $currentStep WHERE id = $id_prospection");
      $db->execute("UPDATE prospect SET step = $currentStep WHERE id = ".$p['id_prospect']);
      $step['id_prospection'] = $id_prospection;
      //$step = $db->enquote($step);
      $db->execute("
      INSERT INTO prospection_step (id_prospection,step,name,value,remarque)
      VALUES
          (
            '".$step['id_prospection']."',
            '".$step['step']."',
            '".$step['name']."',
            '".$step['value']."',
            '".$db->escape($step['remarque'])."'
          )
      ");      
      api::ajaxRep("");
    }

    public static function creationClient( $params ) {
      global $db;
      $appid = $params['appid'];
      $step = $params['step'];
      $currentStep = intval($params['currentStep']);
      $promoteur = $db->escape($params['id_commercial']);

      $db->execute("SELECT * FROM prospection WHERE appid = '".$db->escape($appid)."' ");
      if( !$db->num() || $appid == "") api::aError("Prospection introuvable...");
      $p = $db->assoc();
      if( $p['step'] == 4 ) api::ajaxRep("");
      $id_prospection = $p['id'];
      $db->execute("UPDATE prospection SET step = $currentStep WHERE id = $id_prospection");
      $db->execute("UPDATE prospect SET step = $currentStep WHERE id = ".$p['id_prospect']);
      $step['id_prospection'] = $id_prospection;
      //$step = $db->enquote($step);
      $db->execute("
      INSERT INTO prospection_step (id_prospection,step,name,value)
      VALUES
          (
            '".$step['id_prospection']."',
            '".$step['step']."',
            '".$step['name']."',
            '".$step['value']."'
          )
      ");
      
      // Génération du code client unique
      $code_client = core::generateNewPass( 6, true );
      $db->execute("UPDATE prospect SET code_client = '$code_client', id_commercial = '$promoteur' WHERE id = ".$p['id_prospect']);
      
      // Envoi du mail à la DV
      $prospect = prospect::get($p['id_prospect']);
      $prospect['id_commercial'] = $promoteur;


      $id_user = ( defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : 0);
      $user = user::exist($id_user);
      $body = "
         <p style='font-size:14px;line-height:25px;color:#444;'>
         Bonjour,<br/>
         <strong>".$user['displayname']."</strong> a formulé une demande de création de fiche client
         depuis l'application mobile ABC.<br/>
         Le prospect <strong>".$prospect['nom']." (".$prospect['ville'].")</strong> 
         a accepté la proposition commerciale et sa fiche client est à créer.<br/>
         Voici les informations necessaires à la création du client :<br/>
         </p>
         <br/>
         <table style='width:100%;font-size:12px; color:#222;' cellpadding='5' cellspacing='0' border='0'>
            <tr><th>CODE PROSPECT : </th><td>".$code_client." (Lien CRM entre prospect et client)</td></tr>
            <tr><td colspan='2'><hr/></td></tr>            
            <tr><th>Promoteur en charge : </th><td>".$prospect['id_commercial']."</td></tr>
            <tr><th>Type : </th><td>".$prospect['ptype']."</td></tr>
            <tr><th>Catégorie : </th><td>".$prospect['categorie']."</td></tr>
            <tr><th>Ancien client ?</th><td>".($prospect['ancien']?'OUI':'NON')."</td></tr>
            <tr><th>Nom : </th><td>".$prospect['nom']."</td></tr>
            <tr><th>Enseigne : </th><td>".$prospect['enseigne']."</td></tr>
            <tr><th>Adresse : </th><td>".$prospect['adresse']."</td></tr>
            <tr><th>Code postal : </th><td>".$prospect['cp']."</td></tr>
            <tr><th>Ville : </th><td>".$prospect['ville']."</td></tr>
            <tr><th>Téléphone : </th><td>".$prospect['telephone']."</td></tr>
            <tr><th>Email : </th><td>".$prospect['email']."</td></tr>
            <tr><th>SIRET : </th><td>".$prospect['siret']."</td></tr>
            <tr><th>CNUD (EAN) : </th><td>".$prospect['cnud']."</td></tr>
            <tr><th>N°TVA : </th><td>".$prospect['no_tva']."</td></tr>
            <tr><td colspan='2'><hr/></td></tr>
            <tr><th>Adresse de livraison différente ?</th><td>".($prospect['adresse_livraison']?'OUI':'NON')."</td></tr>
            <tr><th>Adresse livraison : </th><td>".$prospect['al_adresse']."</td></tr>
            <tr><th>Code postal livraison : </th><td>".$prospect['al_cp']."</td></tr>
            <tr><th>Ville livraison : </th><td>".$prospect['al_ville']."</td></tr>
            <tr><th>CNUD livraison : </th><td>".$prospect['al_cnud']."</td></tr>
            <tr><td colspan='2'><hr/></td></tr>
            <tr><th>Code centrale : </th><td>".$prospect['code_centrale']."</td></tr>
            <tr><th>Code sous-centrale : </th><td>".$prospect['code_scentrale']."</td></tr>
            <tr><th>Code interne magasin : </th><td>".$prospect['code_magasin']."</td></tr>
            <tr><th>CA Potentiel : </th><td>".$prospect['ca_potentiel']."</td></tr>
         </table>
      ";

      $to = "service.adv@abcosmetique.com";
      if( ENV == "DEV" ) {
        $to = "n.souhami@snew.fr";
      }

      new sendmail([
        "sender" => ["mail"=>$user['mail'],"displayname"=>$user['displayname']],
        "sujet" => "Création de fiche client : ".$prospect['nom']." (".$prospect['ville'].")",
        "message" => $body,
        "to" => $to
      ]);


      api::ajaxRep("");
    }    


    public static function injectCA() {
      $datas = json_decode($_POST['datas'],true);
      if( empty($datas) ) core::aError("Fichier vide");
      $m = date('m');
      $y = date('Y');

      global $db;
      $repr = [];
      $db->execute("SELECT DISTINCT id_repr FROM user WHERE deleted = 0");
      while( $r = $db->assoc() ) $repr[] = $r['id_repr'];

      foreach( $datas as $line => $e ) {
        $nb = $line+1;
        $an = intval($e['annee'] ?? $y);
        $mois = intval($e['mois'] ?? 0);
        if( strlen($an) != 4 ) core::aError("Ligne $nb : L'année doit être formatée sur 4 chiffres");
        if( $an <= $y && $mois < $m ) continue; // ignore le passé
        $id_repr = intval($e['id_repr'] ?? 0);
        if( !in_array($id_repr,$repr) ) core::aError("Ligne $nb : ID de représentant inconnu");
        $ca = intval( str_replace([","," "],[".",""],$e['ca']??0));

        $db->execute("
          SELECT id FROM objectifs_prospection 
          WHERE 
            id_repr = '$id_repr'
            AND annee = '$an'
            AND mois = '$mois'
            AND deleted = 0
        ");
        if( $db->num() ) {
          $db->execute("UPDATE objectifs_prospection SET total = '$ca' WHERE id = ".$db->assoc()['id']);
        }
        else {
          $db->execute("
            INSERT INTO objectifs_prospection
            (annee,mois,id_repr,total)
            VALUES
            ('$an','$mois','$id_repr','$ca')
          ");
        }
      }
      die('{}');
    }

    public static function getCSOptions() {
      global $db;
      $profiles = [4,2];
      $db->execute("SELECT id_repr,displayname FROM user WHERE id_profile IN (".implode(",",$profiles).") AND actif = 1 AND deleted = 0 ORDER BY displayname");
      $rez = [];
      while( $r = $db->assoc() ) $rez[$r['id_repr']] = $r['displayname'];
      return $rez;
    }

    public static function getCACS() {
      $id_repr = intval($_POST['id_repr']);
      $m = date('m');
      $y = date('Y');
      global $db;
      $db->execute("
        SELECT 
          id,id_repr,annee,mois,total 
        FROM 
          objectifs_prospection
        WHERE
          id_repr = '$id_repr'
          AND 
            ( annee > '$y' OR ( annee = '$y' AND mois >= '$m') )
          AND deleted = 0  
        ORDER BY annee,mois
      ");
      $rez = [];
      while( $r = $db->assoc() ) {
        $r['mois'] = core::$month[ $r['mois'] - 1 ];
        $r['total'] = number_format($r['total'],0,","," ");
        $rez[] = $r;
      }
      die(json_encode($rez));
    }

    public static function getObjCS() {
      global $db;
      $id_user = ( defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : 0);
      if( !$id_user ) api::aError("Utilisateur introuvable");
      $user = user::exist($id_user);
      $id_repr = intval($user['id_repr']);
      if( !$id_repr ) api::aError("ID de représentant introuvable");
      $q = "
        SELECT total
        FROM objectifs_prospection
        WHERE
          id_repr = '$id_repr'
          AND annee = '".date('Y')."'
          AND mois = '".date('m')."'
          AND deleted = 0
          ORDER BY id DESC LIMIT 1
      ";
      $db->execute($q);
      $obj = [
        "objectif" => 0,
        "realise" => 0
      ];
      if( $db->num() ) {
        $obj['objectif'] = number_format($db->assoc()['total'],0,","," ");
      }
      api::ajaxRep(["obj" => $obj]);
    }


    public static function saveVisiteCommerciale( $params ) {
      global $db;
      
      $datas = [
        "id_client" => $params['id_as400'] ?? null,
        "id_user" => intval($params['user'] ?? 0),
        "creation" => $params['visite']['date_creation_apk'],
        "rdv" => $params['visite']['rdv']['rdv'] ? 1 : 0,
        "objets" => implode(",", $params['visite']['rdv']['objets'] ?? []),
        "responsable" => $params['visite']['rdv']['responsable'] ?? "",
        "compte_rendu" => $params['visite']['rdv']['compte_rendu'] ?? "",
        "date_prochain_rdv" => core::dateInput(core::apkDate($params['visite']['rdv']['date_prochain_rdv'] ?? "")),
      ];
      $id_vc = $db->insertArray('cs_visite_commerciale', $datas);

      $params['visite']['form']['end'] = [
        "name" => "end",
        "value" => 1,
        "remarque" => ""
      ];


      foreach( $params['visite']['form'] as $step_num => $step ) {
        $step['id_vc'] = $id_vc;
        if( $step['value'] == "" ) $step['value'] = 0;
        unset($step['libelle']);
        foreach( $params['visite']['steps'] as $s ) {
          if( $s['libelle'] == $step_num ) {
            $step['timer'] = $s['timer'];
            break;
          }
        }
        $db->insertArray('cs_visite_commerciale_steps',$step);
      }

      api::ajaxRep([]);
    }

    public static function getNextVisiteCs( $id_as400 ) {
      global $db;
      $db->execute("
        SELECT date_prochain_rdv 
        FROM cs_visite_commerciale 
        WHERE 
          date_prochain_rdv > NOW() 
          AND id_client = '".$db->escape($id_as400)."'
        ORDER BY date_prochain_rdv 
        LIMIT 1");
      if( !$db->num() ) api::ajaxRep(["hasDate" => false]); 
      $d = $db->assoc()['date_prochain_rdv'];
      api::ajaxRep(["hasDate" => true, "date" => core::dateOutput($d) ]);
    }



    public static function searchBoardVisitesCs() {
      $str = trim(strtolower($_POST['str']));
      $limit = intval($_POST['limit']);
      $offset = intval($_POST['offset']);
      $from = e($_POST['from']);
      $to = e($_POST['to']);
  
      $w = [' v.id > 0 '];
  
      $checkDate = true;
      if( $from == "" || $to == "" ) $checkDate = false;
      else $to = date('Y-m-d',strtotime('+1 day',strtotime($to)));
  
      if( $checkDate )
        $w[] = " v.creation >= '$from' AND v.creation <= '$to' ";
  
      global $db;
      if( $str != "" ) {
        $str = $db->escape($str);
  
        $date = $str;
        if( strpos($date,"/") > 0 ) {
          $tmp = explode("/",$str);
          $date = implode("-",array_reverse($tmp));
        }
  
        $w[] = "
        (
          LOWER(u.displayname) LIKE '%".$str."%'
          OR
          LOWER(c.enseigne) LIKE '%".$str."%'
          OR
          LOWER(c.id_as400) LIKE '%".$str."%'
          OR
          v.creation LIKE '%".$date."%'
        )
        ";
      }
  
  
      $count = 0;
      $z = "
      FROM
      cs_visite_commerciale v
        LEFT JOIN user u ON v.id_user = u.id
        LEFT JOIN ref_client c ON v.id_client = c.id_as400
      WHERE
        ".implode(" AND ", $w);
      $qStart = "SELECT count(*) as nb ".$z;
      $db->execute($qStart);
      $count = $db->assoc()['nb'];
  
  
      $q = "
        SELECT 
          v.id,
          v.id_client as id_as400,
          v.creation,
          v.date_prochain_rdv,
          c.enseigne,
          u.displayname
        ".$z."
        ORDER BY
          v.creation DESC
        LIMIT
          ".($offset*$limit).", $limit
      ";
      $db->execute($q);
      if( !$db->num() ) {
        $q = str_replace(["\r","\n"],"",$q);
        core::ajax(["html"=>"<tr><td class='text-center' colspan='9'>Aucun résultat</td></tr>","query"=>$q]);
      }
  
      $ids = [];
      while( $r = $db->assoc() ) $ids[] = $r;
      $html = [];
      foreach( $ids as $v ) {
        
        $db->execute("
          SELECT creation 
          FROM cs_visite_commerciale
          WHERE
            creation < '".$v['creation']."'
            AND id_client = '".$v['id_as400']."'
        ");
        $last = $db->num() ? core::dateOutput($db->assoc()['creation'],true) : "<em>jamais</em>";


        $html[] = '<tr data-id="'.$v['id'].'">';
        $html[] = '<td>'.core::dateOutput($v['creation'],true).'</td>';
        $html[] = '<td>'.$last.'</td>';
        $html[] = '<td>'.core::dateOutput($v['date_prochain_rdv'],true).'</td>';
        $html[] = '<td>'.$v['id_as400'].'</td>';
        $html[] = '<td>'.$v['enseigne'].'</td>';
        $html[] = '<td>'.$v['displayname'].'</td>';
        $html[] = '</tr>';
      }
      core::ajax(["html"=>implode($html),"count"=> core::n($count)]);
    }

    public static function getVisiteCs( $id ) {
      global $db;
      $db->execute("SELECT * FROM cs_visite_commerciale WHERE id = ".intval($id));
      return $db->num() ? $db->assoc() : false;
    }
    
    public static function getVisiteCsSteps( $id ) {
      global $db;

      $stepsLibelles = [
        "valid_repart_lineaire" => l("cs-step-repart-lineaire"),
        "valid_assort" => l("cs-step-validation-assort"),
        "respect_pvc_balisage" => l("cs-step-respects-pvc"),
        "point_gestion_lineaire" => l("cs-step-lineaire"),
        "ruptures" => l("cs-step-rupture"),
        "balisage" => l("cs-step-balisage"),
        "end" => l("cs-step-fin"),
      ];

      $db->execute("SELECT * FROM cs_visite_commerciale_steps WHERE id_vc = ".intval($id));
      $steps = $db->getArray();
      foreach( $steps as $k=>$e ) {
        $steps[$k]['libelle'] = $stepsLibelles[$e['name']];
        $steps[$k]['heure'] = core::dateOutput($e['timer'],true);
      }
      return $steps;
    }

}