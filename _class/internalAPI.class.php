<?php

class internalAPI {

    public $params;
    public $response = [];

    public function __construct($params) {
        $this->params = $params;
        $this->checkAuthToken( $params['authToken'] ?? "");
        $this->processQuery();
        die('ok');
    }

    public function checkAuthToken( $token ) {
        if( trim($token) == "" ) core::ajaxError("No Token");
        $token = base64_decode($token);
        $tmp = explode("|",$token);
        if( count($tmp) != 2 ) core::ajaxError("Token broken");
        if( $tmp[0] != "LITIGES" ) core::ajaxError("Token broken");
        if( strtotime('now') - $tmp[1]  > 3 ) core::ajaxError("Token too old");
        return;
    }

    public function processQuery() {
        switch( $this->params['method'] ?? "" ) {
            case 'searchClient' : {
                $this->searchClient();
                break;
            }
            case 'getClient' : {
                $this->getClient( $this->params['id_as400'] ?? null );
                break;
            }
            default :
                core::ajaxError("No method");
        }
        die(json_encode($this->response,JSON_FORCE_OBJECT));
    }

    public function searchClient() {
        global $db;

        $str = trim(strtolower($this->params['terms']));
        $terms = [];
        $tmp = explode(" ",$str);
        foreach( $tmp as $word ) {
            $terms[] = "(
                rc.enseigne like '%".$db->escape($word)."%' 
                OR rc.
                id_as400 like '%".$db->escape($word)."%' 
            )";
        }
        $terms = implode(" AND ",$terms);
        

        $clients = [];
        $q = "
            SELECT 
                rc.enseigne,
                rc.id_as400,
                CONCAT(rc.adresse1,' ',rc.ville,' ',rc.code_postal,' ', rc.pays ) as addr,
                u.displayname as commercial,
                u.id_repr as id_commercial,
                rc.statut_commande_par as sc,
                rc.statut_livre as sl,
                rc.statut_facture as sf
            FROM 
                ref_client rc
                LEFT JOIN user u ON u.id = (SELECT id FROM user WHERE id_repr = rc.id_commercial_1 AND actif = 1 LIMIT 1)
            WHERE 
                rc.deleted = 0 
                AND rc.actif = 1  
                AND ($terms) 
            ORDER BY 
                rc.enseigne
            DESC LIMIT 50
        ";
        $db->execute($q);
        while( $r = $db->assoc() ) {
            $clients[] = $r;
        }
        $this->response = ["clients" => $clients];
        return;
    }

    public function getClient( $id_as400 ) {
        $cli =  client::getByCode($id_as400);
        if( !$cli ) err('Erreur');
        
        $cli['contacts'] = [];
        global $db;
        $db->execute("SELECT * FROM ref_client_contact WHERE id_ref_client = '$id_as400' AND deleted = 0");
        while( $r = $db->assoc() )
            $cli['contacts'][] = $r;

        
        $cli['region'] = "Région";
        if( $cli['id_commercial_1'] > 0 ) {
            $user = user::getFromIdRepr($cli['id_commercial_1']);
            if( $user ) $cli['region'] = $user['secteur'];
        }

        $this->response = ["client" => $cli ];
    }


}