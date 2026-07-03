<?php

class sendmail {

    public $datas;
    public $headers;
    public $boundary;
    public $message;
    public $success = false;
    public $sujet;

    public function __construct( $datas ) {
        $this->datas = $datas;
        $this->boundary = md5(uniqid(microtime(), TRUE));

        $this->sujet = $this->datas['sujet'] ?? 'Sujet vide';

        $this
            ->buildHeaders() 
            ->buildMessage()
            ->addFiles()
            ->sendMail();
    }
    public function buildHeaders() {
        $sender = $this->datas['sender'] ? $this->datas['sender']['displayname'] :  'CRM';
        $senderMail = $this->datas['sender'] ? $this->datas['sender']['mail'] :  'crm@abcosmetique.com';
        $this->headers = 'From: "'.$sender.'" <'.$senderMail.'>'."\n"; 
        $this->headers .= 'Return-Path: <'.$senderMail.'>'."\n"; 
        $this->headers .= 'MIME-Version: 1.0'."\n"; 
        $this->headers .= 'Content-Type: multipart/mixed; boundary='.$this->boundary."\r\n"; 
        $this->headers .= "\r\n";
        return $this;
    }
    public function buildMessage() {
        $this->message = [ '--'.$this->boundary."\r\n" ];

        $this->message[] = 'Content-Type: text/html; charset="utf-8"'."\n"; 
        $this->message[] = 'Content-Transfer-Encoding: 8bit'."\n\n"; 
        $this->message[] = $this->datas['message'] ? $this->datas['message'] : 'Message vide'; 
        $this->message[] = "\n\n"; 
    
        $this->message[] = '--'.$this->boundary."\r\n"; 


        return $this;
    }
    public function addFiles() {
        if( !isset($this->datas['files']) || empty($this->datas['files']) ) return $this;
        foreach( $this->datas['files'] as $file ) {
            if( !file_exists($file) || !is_readable($file) ) continue;
            $this->message[] = 'Content-Type: '.mime_content_type($file).' ; name="'.basename($file).'"'."\n"; 
            $this->message[] = 'Content-Transfer-Encoding: base64'."\n"; 
            $this->message[] = 'Content-Disposition:attachement; filename="'.basename($file).'"'."\n\n"; 
            $this->message[] = chunk_split(base64_encode($file))."\n";     
            $this->message[] = '--'.$this->boundary."\r\n"; 
        }
        return $this;
    }
    public function sendMail() {
        if( !isset($this->datas['to'])  ) return $this;
        if( is_string($this->datas['to']) ) $this->datas['to'] = [$this->datas['to']];
        if( empty($this->datas['to']) ) return $this;
        $subject =  mb_encode_mimeheader($this->sujet, 'UTF-8');
        foreach( $this->datas['to'] as $mail ) 
            $this->success = mail($mail,$subject,implode($this->message),$this->headers);

        return $this;
    }

}