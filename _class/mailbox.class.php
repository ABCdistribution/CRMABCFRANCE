<?php

class mailbox {

    public static function rep( $obj ) {
        die(json_encode($obj));
    }

    public static function getMessages() {
        global $db;
        
        $id = defined('API_ID_USER') ? API_ID_USER : ID;
        $db->execute("SELECT * FROM messagerie WHERE  (id_from = $id OR id_to = $id) AND deleted = 0 ORDER BY id DESC");

        if( !$db->num() ) self::rep(["nomsg" => true]);

        $msg = [];
        $datas = $db->getArray();
        $userAdded = [];
        
        foreach( $datas as $k=>$e ) {
            $id_partenaire = ( $e['id_from'] != $id ? $e['id_from'] : $e['id_to'] );
            $name =  $id_partenaire == 0 ? 'Système' : user::getNameFromId($id_partenaire);
            if( in_array($name,$userAdded) ) continue;
            $userAdded[] = $name;
            $message = [ 
                "id" => $id_partenaire,
                "n" => $name,
                "m" => mb_substr(strip_tags($e['message']),0,40),
                "d" => core::dateFrom($e['date_creation']),
                "r" => $e['is_read'] == 0 && $e['id_from'] != $id ? 0 : 1
            ];
            if( $message['m'] != $e['message'] ) $message['m'] .= "...";
            $msg[] = $message;
        }

        if( defined('API_ID_USER' ) )
		    api::ajaxRep($msg);

        self::rep($msg);
    }


    public static function getEchanges( $id = null ) {
        $rep = [];
        $id_partenaire = $id ?? intval($_POST['id']);
        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;
        global $db;

        $name = $id_partenaire == 0 ? 'Système' : user::getNameFromId($id_partenaire);
        $rep['id_partenaire'] = $id_partenaire;
        $rep['name'] = $name;
        $rep['msgs'] = [];

        $db->execute("
            SELECT * FROM messagerie 
            WHERE 
                (
                    (id_from = $id_partenaire AND id_to = $id_user)
                    OR
                    (id_from = $id_user AND  id_to = $id_partenaire)
                )
                AND deleted = 0
            ORDER BY id DESC 
            LIMIT 20
        ");
        if( !$db->num() ) {
            if( defined('API_ID_USER' ) ) api::ajaxRep($rep);
            self::rep($rep);
        }

        $read = [];
        while( $r = $db->assoc() ) {
            $rep['msgs'][] = [
                "id" => $r['id'],
                "o" => ($r['id_from'] == $id_user),
                "m" => $r['message'],
                "i" => ($r['photo'] != "" ? $r['photo'] : 0),
                "d" => core::dateOutput($r['date_creation'],true),
                "r" => $r['is_read']
            ];

            if( $r['id_to'] == $id_user && $r['is_read'] == 0 )
                $read[] = $r['id'];
        }

        if( !empty($read) )
            $db->execute("UPDATE messagerie SET is_read = 1 WHERE id IN (".implode(",",$read).")");

        $rep['notif'] = self::getNotification();
        

        $rep['msgs'] = array_reverse($rep['msgs']);

        if( defined('API_ID_USER' ) ) api::ajaxRep($rep);        
        
        self::rep($rep);

    }

    public static function getLastMsg() {
        $rep = [];
        $id_partenaire = intval($_POST['id_partenaire']);
        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;
        $id_last_msg = intval($_POST['id_lastMsg']);
        global $db;      

        $db->execute("
            SELECT * FROM messagerie 
            WHERE 
                (
                    (id_from = $id_partenaire AND id_to = $id_user)
                    OR
                    (id_from = $id_user AND  id_to = $id_partenaire)
                )
                AND deleted = 0
                AND id  > $id_last_msg
            ORDER BY id DESC
        ");
        if( !$db->num() )
            self::rep($rep);        
        
        $read = [];
        while( $r = $db->assoc() ) {
            $rep[] = [
                "id" => $r['id'],
                "o" => ($r['id_from'] == $id_user),
                "m" => $r['message'],
                "d" => core::dateOutput($r['date_creation'],true),
                "r" => $r['is_read'],
                "i" => $r['photo'] ?? 0
            ];

            if( $r['id_to'] == $id_user && $r['is_read'] == 0 )
                $read[] = $r['id'];
        }

        if( !empty($read) )
            $db->execute("UPDATE messagerie SET is_read = 1 WHERE id IN (".implode(",",$read).")");
        

        $rep = array_reverse($rep);

        
        self::rep($rep);            

    }

    public static function sendMsg( $params = [] ) {

        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;

        $id_partenaire = isset($params['id_dest']) ? intval($params['id_dest']) : intval($_POST['id_partenaire']);
        $msg = isset($params['m']) ? $params['m'] :  trim($_POST['msg']);

        if( $id_partenaire == 0 ) {
            defined('API_ID_USER') ? api::aError("Impossible de répondre au système") : core::aError();
        }

        if( !$id_partenaire || $msg == "" )
            defined('API_ID_USER') ? api::aError("Une erreur est survenue") : core::aError();


        global $db;
        $db->execute("
            INSERT INTO messagerie
            (id_from,id_to,message)
            VALUES
            ('".$id_user."','".intval($id_partenaire)."','".$db->escape($msg)."')
        ");
        $idMsg = $db->lastId();

        if( isset($params['rd']) )
            $db->execute("UPDATE messagerie SET date_creation = '".$db->escape($params['rd'])."' WHERE id = $idMsg");

        $r = [
            "msg" => [
                "id" => $idMsg,
                "o" => true,
                "m" => $msg,
                "i" => 0,
                "d" => core::dateOutput(date("Y-m-d G:i:s"),true)                 
            ]
        ];

        if( defined('API_ID_USER') )
            api::ajaxRep($r);       

        self::rep($r);
    }

    public static function newConversation() {
        $ids = $_POST['ids'];
        $msg = trim($_POST['msg']);
        if( !is_array($ids) || empty($ids) || $msg == "" ) core::aError();
        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;
        
        global $db;
        foreach( $ids as $id_partenaire ) {
            $db->execute("
                INSERT INTO messagerie
                (id_from,id_to,message)
                VALUES
                ('".$id_user."','".intval($id_partenaire)."','".$db->escape($msg)."')
            ");
        }
        die('{}');
    }


    public static function getNotification() {
        global $db;
        $id = defined('API_ID_USER') ? API_ID_USER : ID;
        $db->execute("
            SELECT DISTINCT 
                id_from 
            FROM 
                messagerie 
            WHERE
                deleted = 0
                AND is_read = 0
                AND id_to = ".$id." 
        ");
        $ids = [];
        while( $r = $db->assoc() ) $ids[] = $r['id_from'];
        return $ids;
    }

    public static function sendImage() {
        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;
        $id_partenaire = intval($_POST['id_dest']);

        $msg = trim($_POST['msg']);        
        $src = trim($_POST['src']);  

        $link = '<a href="'.$src.'" target="_blank">Ouvrir la photo en grand</a><br/>';
        $msg = $link.$msg;

        global $db;
        $db->execute("
        INSERT INTO messagerie
            (id_from,id_to,message,photo)
            VALUES
            ('".$id_user."','".intval($id_partenaire)."','".$db->escape($msg)."','".$db->escape($src)."')
        ");   
        die('{}');
    }

    public static function haveMessage() {
        $id_user = defined('API_ID_USER') ? API_ID_USER : ID;
        global $db;
        $db->execute("SELECT id_from FROM messagerie WHERE id_to = $id_user AND is_read = 0 AND deleted = 0");
        if( !$db->num() )
            api::ajaxRep(["count"=>0]);      
        
        $total = $db->num();
        $ids = [];
        while( $r = $db->assoc() )
            $ids[$r['id_from']] = "";
        foreach( $ids as $id_dest => $e ) {
            if( intval($id_dest) < 1 ) continue;
            $ids[$id_dest] = user::getNameFromId($id_dest);
        }
        api::ajaxRep(["count"=>count($ids),"ids" =>$ids]);    
    }


    public static function notifSysteme( $id_user, $message ) {
        global $db;
        $db->execute("
            INSERT INTO messagerie
            (id_from,id_to,message)
            VALUES
            ('0','".intval($id_user)."','".$db->escape($message)."')
        ");     
        return;   
    }



}