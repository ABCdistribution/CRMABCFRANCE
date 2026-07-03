<?php

require APP_ROOT.'/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
set_time_limit(0);

class commandePDF {

    public $datas = [];
    public $html = [];
    public $pdf;
    public $pdf_name;
    public $success;
    public $output;
    public $isJuva = false;

    public function __construct( $id_commande, $output = false, $isJuva = false ) {
        $this->output = $output;
        $this->isJuva = $isJuva; 
        $this->gatherDatas( $id_commande );
        $this->setHTML();
        $this->generatePDF();

        if( !$this->output )
            $this->sendMail();
        else exit;
    }

    public function error( $msg = "Une erreur est survenue lors de la génération du PDF" ) {
        die( $msg );
    }

    public function gatherDatas( $id_commande ) {
        global $db;

        
        error_log("isJuva : ".$this->isJuva, 0);
        error_log("id_commande : ".$id_commande, 0);
        if ($this->isJuva) {
        
            $db->execute("SELECT * FROM juva_commande WHERE id = $id_commande");
            if (!$db->num()) $this->error("Commande Juva #$id_commande introuvable");
            error_log("Commande Juva #$id_commande trouvée", 0);
            $this->datas['commande'] = $db->assoc();
        
            $db->execute("SELECT * FROM juva_commandeligne WHERE commande = '".$db->escape($this->datas['commande']['code'])."'");
            $this->datas['produits'] = $db->get();
        
            //$this->datas['client'] = ['enseigne' => $this->datas['commande']['client']];
            // $this->datas['user'] = ['displayname' => $this->datas['commande']['utilisateur'], 'mail' => ''];

        //    $db->execute("SELECT * FROM juva_client WHERE IdOriginal = '".$db->escape($this->datas['commande']['code'])."' ");
        //    error_log("Client Juva #".$this->datas['commande']['client']." trouvée", 0);
        //       //if( !$db->num() ) $this->error("Commande #$id_commande : client introuvable");
        //     $this->datas['client'] = $db->assoc();

        // Étape 1 : récupère depuis ref_client_infos + ref_client
            $db->execute("
                SELECT c.* , i.num_juva
                FROM ref_client_infos i 
                JOIN ref_client c ON c.id_as400 = i.id_ref_client 
                WHERE i.num_juva = '".$db->escape($this->datas['commande']['client'])."'
            ");
            if (!$db->num()) $this->error("Client JUVA introuvable (num_juva = ".$this->datas['commande']['client'].")");
            $this->datas['client'] = $db->assoc();

            //recupere l'utilisateur
            $db->execute("SELECT * FROM user WHERE login = '".$this->datas['commande']['utilisateur']."' ");
            if( !$db->num() ) $this->error("Commande #$id_commande : promoteur introuvable");
            $this->datas['user'] = $db->assoc();      
        
            return $this;

        }else {

        $db->execute("SELECT * FROM commande_apk WHERE id = $id_commande AND externe = 1");
        if( !$db->num() ) $this->error("Commande #$id_commande introuvable ou non externe");
        $this->datas['commande'] = $db->assoc();

        $db->execute("SELECT * FROM commande_apk_produits WHERE id_commande_apk = $id_commande");
        $this->datas['produits'] = $db->getArray();

        /*for( $i = 0; $i < 3; $i++ ) {
            foreach( $this->datas['produits'] as $k=>$e )
                $this->datas['produits'][] = $e;
        }*/

        $db->execute("SELECT * FROM ref_client WHERE id_as400 = '".$db->escape($this->datas['commande']['id_magasin'])."' ");
        if( !$db->num() ) $this->error("Commande #$id_commande : client introuvable");
        $this->datas['client'] = $db->assoc();

        $db->execute("SELECT * FROM user WHERE login = '".$this->datas['commande']['user']."' ");
        if( !$db->num() ) $this->error("Commande #$id_commande : promoteur introuvable");
        $this->datas['user'] = $db->assoc();        

        return $this;
        }
    }

    public function setHTML() {
        ob_start();
        $cmd = $this->datas;
        if ($this->isJuva) {
            include(PARTIAL."pdf/commande-juva.php");
        } else {
           include(PARTIAL."pdf/commande.php");
        }
        $this->html = ob_get_contents();
        ob_end_clean();
        return $this;
    }




    public function getHTML() {
        return $this->html;
    }

    public function generatePDF() {
        $html2pdf = new Html2Pdf('P', 'A4', 'fr');
        $html2pdf->writeHTML( $this->getHTML() );
        $this->pdf_name = 'ABC_BDC_'.date("d-m-Y").'_'.$this->datas['commande']['id'].'.pdf';
        if( $this->output ) {
            header("Content-type:application/pdf");
            $html2pdf->output( $this->pdf_name);
            exit;
        }
        $this->pdf = $html2pdf->output( $this->pdf_name , 'S');
        return $this;
    }

    public function sendMail() {
        $domain = $_SERVER['SERVER_NAME'];
        $email_expediteur = 'noreply@'.$domain; 
        $email_reply='noreply@'.$domain; 
        $destinataire = $this->datas['commande']['externeMail'];
        if( $this->datas['user']['mail'] != "" && $destinataire != $this->datas['user']['mail'] )
            $destinataire .= ",".$this->datas['user']['mail'];
        $no = str_pad($this->datas['commande']['id'],15,"0",STR_PAD_LEFT);
        $sujet = "Bon de commande #".$no;        

        $message_texte= 'Bonjour,'."\n\n".'Veuillez trouver ci-joint le bon de commande #'.$no; 
    
        $message_html = 'Bonjour,<br/><br/>'."\n\n".'Veuillez trouver ci-joint le bon de commande <strong>#'.$no.'</strong>'; 
    
        //----------------------------------------------- 
        //GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
        //----------------------------------------------- 
    
        $frontiere = md5(uniqid(microtime(), TRUE));
    
        //----------------------------------------------- 
        //HEADERS DU MAIL 
        //----------------------------------------------- 
    // Vérifier si c'est une commande JUVA
        $userLogin = $this->isJuva ? $this->datas['commande']['utilisateur'] : $this->datas['commande']['user'];

        // Récupérer les données de l'utilisateur
        $u = user::getUserFromLogin($userLogin);

        //$u = user::getUserFromLogin( $this->datas['commande']['user'] );

        $headers = 'From: "'.$u['displayname'].'" <'.$u['mail'].'>'."\n"; 
        $headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
        $headers .= 'MIME-Version: 1.0'."\n"; 
        $headers .= 'Content-Type: multipart/mixed; boundary='.$frontiere."\r\n"; 
        $headers .= "\r\n";

        //$headers = mb_encode_mimeheader($headers,"UTF-8");

    
        //----------------------------------------------- 
        //MESSAGE TEXTE 
        //----------------------------------------------- 
        $message = 'This is a multi-part message in MIME format.'."\n\n"; 
    
        /*$message .= '--'.$frontiere."\n"; 
        $message .= 'Content-Type: text/plain; charset="utf-8"'."\n"; 
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
        $message .= $message_texte."\n\n"; */
    
        //----------------------------------------------- 
        //MESSAGE HTML 
        //----------------------------------------------- 
        $message .= '--'.$frontiere."\r\n"; 
    
        $message .= 'Content-Type: text/html; charset="utf-8"'."\n"; 
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
        $message .= $message_html."<br/><br/>Cordialement,<br/>".$u['displayname']."<br/>".$u['mail']."\n\n"; 
    
        $message .= '--'.$frontiere."\r\n"; 
    
        //----------------------------------------------- 
        //PIECE JOINTE 
        //----------------------------------------------- 
    
        $message .= 'Content-Type: application/pdf ; name="'.$this->pdf_name.'"'."\n"; 
        $message .= 'Content-Transfer-Encoding: base64'."\n"; 
        $message .= 'Content-Disposition:attachement; filename="'.$this->pdf_name.'"'."\n\n"; 
        $message .= chunk_split(base64_encode($this->pdf))."\n";
        
        $this->success = mail($destinataire.",".$u['mail'],$sujet,$message,$headers);
        return $this;
    }

}