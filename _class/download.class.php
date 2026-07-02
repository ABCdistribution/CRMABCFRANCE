<?php

class download {

  public function __construct() {
    global $db,$params;
    $k = array_pop($params);
    $db->execute("SELECT * FROM downloads WHERE hash = '".$db->escape($k)."'");
    if( !$db->num() ) {
      core::error404();
      exit;
    }
    $f = $db->assoc();
    $db->execute("DELETE FROM downloads WHERE id = ".$f['id']);
    if( !file_exists($f['fullpath']) || $f['content_type'] == "" ) {
      core::error404();
      exit;
    }
    //sleep(100); # test timeout
    header('Content-Description: File Transfer');
    header('Content-Type: '.$f['content_type']);
    header('Content-Disposition: attachment; filename="'.basename($f['fullpath']).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($f['fullpath']));
    readfile($f['fullpath']);
    exit;
  }

  public static function new( $fullpath, $content_type = null ) {
    if( !$content_type ) return false;
    if( !file_exists($fullpath) ) return false;
    $hash = md5($fullpath.time());
    global $db;
    $db->execute("
      INSERT INTO downloads
      (hash,fullpath,content_type,size)
      VALUES
      ('$hash','".$db->escape($fullpath)."', '".$db->escape($content_type)."', ".filesize($fullpath).")
    ");
    return $hash;
  }
}
