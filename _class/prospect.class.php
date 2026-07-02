<?php

class prospect {

    public static function creation( $params ) {
        global $db;

        // Test existe déjà
        $db->execute("SELECT id FROM prospect WHERE appid = '".$db->escape($params['appid'])."' AND deleted = 0");
        if( $db->num() ) return;
        
        // Prospect
        $params['ig']['ancien'] = $params['ig']['ancien'] ? 1 : 0;
        $params['ig']['appid'] = $params['appid'];

        $upperCase = ['enseigne','nom','ville'];
        foreach( $upperCase as $f )
            $params['ig'][$f] = strtoupper($params['ig'][$f]);

        $datas = array_merge($params['ig'], $params['rdv'],$params['ca']);
        $datas['id_user'] = ( defined('API_ID_USER') ? API_ID_USER : 0 );
        $u = $db->enquote($datas);
        $db->execute("
            INSERT INTO prospect
            (".implode(",",array_keys($datas)).")
            VALUES
            (".implode(",",$u).")
        ");
        $idp = $db->lastId();

        // Horaires
        $params['horaires']['id_prospect'] = $idp;
        $u = $db->enquote($params['horaires']);
        $db->execute("
        INSERT INTO prospect_horaires
        (".implode(",",array_keys($params['horaires'])).")
        VALUES
        (".implode(",",$u).")
        ");   
        
        // Jours
        foreach( $params['jours'] as $k=>$e ) $params['jours'][$k] = ( $e ? 1 : 0 );
        $params['jours']['id_prospect'] = $idp;
        $u = $db->enquote($params['jours']);
        $db->execute("
        INSERT INTO prospect_jours
        (".implode(",",array_keys($params['jours'])).")
        VALUES
        (".implode(",",$u).")
        ");     
        
        // Contacts
        foreach( $params['contacts'] as $c ) {
            $c = $c['c'];
            if( $c['n'] == "" && $c['p'] == "" ) continue;
            $o = [
                "id_prospect" => $idp,
                "nom" => strtoupper($c['n']),
                "prenom" => strtoupper($c['p']),
                "fonction" => $c['f'],
                "telephone" => $c['t'],
                "mail" => $c['m'],
            ];
            $u = $db->enquote($o);
            $db->execute("
            INSERT INTO prospect_contact
            (".implode(",",array_keys($o)).")
            VALUES
            (".implode(",",$u).")
            ");  
        }

        return;
    }

    public static function edition( $params ) {
        global $db;
        $db->execute("SELECT id FROM prospect WHERE appid = '".$db->escape($params['appid'])."' AND deleted = 0");
        if( !$db->num() ) return;
        $idp = $db->assoc()['id'];

        $db->execute("
            UPDATE prospect
            SET
                ptype = '".$db->escape($params['ig']['ptype'])."',
                ancien = '".$db->escape($params['ig']['ancien'])."',
                enseigne = '".$db->escape($params['ig']['enseigne'])."',
                nom = '".$db->escape($params['ig']['nom'])."',
                adresse = '".$db->escape($params['ig']['adresse'])."',
                cp = '".$db->escape($params['ig']['cp'])."',
                ville = '".$db->escape($params['ig']['ville'])."',
                telephone = '".$db->escape($params['ig']['telephone'])."',
                email = '".$db->escape($params['ig']['email'])."',
                siret = '".$db->escape($params['ig']['siret']??"")."',
                cnud = '".$db->escape($params['ig']['cnud']??"")."',
                no_tva = '".$db->escape($params['ig']['no_tva']??"")."',
                adresse_livraison = '".$db->escape($params['ig']['adresse_livraison']??"")."',
                al_adresse = '".$db->escape($params['ig']['al_adresse']??"")."',
                al_cp = '".$db->escape($params['ig']['al_cp']??"")."',
                al_ville = '".$db->escape($params['ig']['al_ville']??"")."',
                al_cnud = '".$db->escape($params['ig']['al_cnud']??"")."',
                categorie = '".$db->escape($params['ig']['categorie'])."',
                code_centrale = '".$db->escape($params['ig']['code_centrale'])."',
                code_scentrale = '".$db->escape($params['ig']['code_scentrale'])."',
                code_magasin = '".$db->escape($params['ig']['code_magasin'])."',
                type_rdv = '".$db->escape($params['rdv']['type_rdv'])."'            
            WHERE
                id = $idp
        ");  
        
        $db->execute("
            UPDATE prospect_horaires
            SET 
                am_start = '".$db->escape($params['horaires']['am_start'])."',
                am_end = '".$db->escape($params['horaires']['am_end'])."',
                pm_start = '".$db->escape($params['horaires']['pm_start'])."',
                pm_end = '".$db->escape($params['horaires']['pm_end'])."'
            WHERE
                id_prospect = $idp
        ");  

        $db->execute("
            UPDATE prospect_jours
            SET 
                lundi = '".($params['jours']['lundi']?1:0)."',
                mardi = '".($params['jours']['mardi']?1:0)."',
                mercredi = '".($params['jours']['mercredi']?1:0)."',
                jeudi = '".($params['jours']['jeudi']?1:0)."',
                vendredi = '".($params['jours']['vendredi']?1:0)."'
            WHERE
                id_prospect = $idp
        ");     
        
        // Contacts
        $db->execute("DELETE FROM prospect_contact WHERE id_prospect = $idp");
        foreach( $params['contacts'] as $c ) {
            if( !isset($c['c']) ) continue;
            $c = $c['c'];
            if( $c['n'] == "" && $c['p'] == "" ) continue;
            $o = [
                "id_prospect" => $idp,
                "nom" => strtoupper($c['n']),
                "prenom" => strtoupper($c['p']),
                "fonction" => $c['f'],
                "telephone" => $c['t'],
                "mail" => $c['m'],
            ];
            $u = $db->enquote($o);
            $db->execute("
            INSERT INTO prospect_contact
            (".implode(",",array_keys($o)).")
            VALUES
            (".implode(",",$u).")
            ");  
        }   
        
        return;
    }

    public static function getDeportedProspects() {
        global $db;
        $db->execute("
            SELECT 
                p.id,
                p.id_user,
                p.appid,
                p.step,
                p.ptype,
                p.enseigne,
                p.nom,
                p.adresse,
                p.cp,
                p.ville,
                p.telephone,
                p.email,
                p.categorie,
                p.code_centrale,
                p.code_scentrale,
                p.code_magasin,
                p.type_rdv,
                p.ca_potentiel,
                ph.am_start,
                ph.am_end,
                ph.pm_start,
                ph.pm_end,
                pj.lundi,
                pj.mardi,
                pj.mercredi,
                pj.jeudi,
                pj.vendredi,
                u.id_repr
            FROM 
                prospect p
                LEFT JOIN prospect_horaires ph ON ph.id_prospect = p.id
                LEFT JOIN prospect_jours pj ON pj.id_prospect = p.id
                LEFT JOIN user u ON p.id_user = u.id
            WHERE 
                p.deleted = 0
        ");
        $prospects = [];
        while( $r = $db->assoc() ) {
            $p = [
                "id" => $r['id'],
                "id_user" => $r['id_user'],
                "id_repr" => $r['id_repr'],
                "appid" => $r['appid'],
                "step" => $r['step'],
                "ig" => [
                    "ptype" => $r['ptype'],
                    "ancien" => false,
                    "enseigne" => $r['enseigne'],
                    "nom" => $r['nom'],
                    "adresse" => $r['adresse'],
                    "cp" => $r['cp'],
                    "ville" => $r['ville'],
                    "telephone" => $r['telephone'],
                    "email" => $r['email'],
                    "siret" => $r['siret']??"",
                    "cnud" => $r['cnud']??"",
                    "no_tva" => $r['no_tva']??"",
                    "categorie" => $r['categorie'],
                    "code_centrale" => $r['code_centrale'],
                    "code_scentrale" => $r['code_scentrale'],
                    "code_magasin" => $r['code_magasin'],
                    "adresse_livraison" => $r['adresse_livraison']??"",
                    "al_adresse" => $r['al_adresse']??"",
                    "al_cp" => $r['al_cp']??"",
                    "al_ville" => $r['al_ville']??"",
                    "al_cnud" => $r['al_cnud']??"",
                ],
                "contacts" => [],
                "rdv" => [
                    "type_rdv" => $r['type_rdv'],
                ],
                "horaires" => [
                    "am_start" => $r['am_start'],
                    "am_end" => $r['am_end'],
                    "pm_start" => $r['pm_start'],
                    "pm_end" => $r['pm_end'],         
                ],
                "jours" => [
                    "lundi" => $r['lundi'] == 1,
                    "mardi" => $r['mardi'] == 1,
                    "mercredi" => $r['mercredi'] == 1,
                    "jeudi" => $r['jeudi'] == 1,
                    "vendredi" => $r['vendredi'] == 1
                ],
                "ca" => [
                    "ca_potentiel" => $r['ca_potentiel'],
                ]
            ];
            $prospects[$r['appid']] = $p;
        }

        // Contacts
        foreach( $prospects as $k=>$e ) {
            $db->execute("SELECT * FROM prospect_contact WHERE id_prospect = ".$e['id']." AND deleted = 0 ");
            $nb = 1;
            while( $r = $db->assoc() ) {
                $prospects[$k]['contacts'][] = [
                    "num" => $nb,
                    "c" => [
                        "n" => $r['nom'],
                        "p" => $r['prenom'],
                        "f" => $r['fonction'],
                        "t" => $r['telephone'],
                        "m" => $r['mail']
                    ]
                ];
                $nb++;
            }
        }

        return $prospects;
    }   

    public static function getIdFromAppId( $appid ) {
        global $db;
        $db->execute("SELECT id FROM prospect WHERE appid = '".$db->escape($appid)."' AND deleted = 0");
        return $db->num() ? $db->assoc()['id'] : 0;
    }
    public static function get( $id ) {
        global $db;
        $db->execute("SELECT * FROM prospect WHERE ( id = '".$db->escape($id)."' OR appid = '".$db->escape($id)."' ) AND deleted = 0");
        return $db->num() ? $db->assoc() : null;
    }   
    public static function getFull( $id ) {
        global $db;
        $p = self::get($id);
        if( !$p ) return false;
        
        foreach( ['contact','horaires','jours'] as $table ) {
            $db->execute("SELECT * FROM prospect_$table WHERE id_prospect = $id AND deleted = 0");
            $p[$table] = $db->num() ? $db->getArray() : [];
        }

        // DN
        $db->execute("SELECT id FROM prospection WHERE id_prospect = $id AND deleted = 0 ORDER BY id desc limit 1");
        if( $db->num() ) {
            $id_prospection = $db->assoc()['id'];
            $db->execute("SELECT * FROM prospection_dn WHERE id_prospection = $id_prospection AND deleted = 0");
            if( $db->num() ) $p['dn'] = $db->getArray();
        }

        return $p;
    }        


    public static function getListePromoteurs() {
        global $db;
        $db->execute("SELECT DISTINCT displayname FROM user WHERE id_profile = 1 AND actif = 1 ORDER BY displayname");
        $rep = [""];
        while( $r = $db->assoc() ) $rep[] = $r['displayname'];
        api::ajaxRep($rep);
    }

}