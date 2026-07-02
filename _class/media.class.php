<?php

class media {

  public function __construct() {
    global $params,$db;

    if( $params[0] == "Media" && ( !isset($params[1]) || $params[1] == "" ) )self::error();
    $arg = $params[1];
    $tmp = explode(FILE_HASH_SEPARATOR,$arg);
    if( count($tmp) != 2 ) self::error();
    $md5 = $tmp[0];
    $id_file = $db->escape(intval($tmp[1]));
    if( $tmp[1] != $id_file || $id_file == 0 ) self::error();
    $db->execute("SELECT * FROM uploaded_files WHERE id = $id_file");
    if( !$db->num() ) self::error();
    $file = $db->assoc();
    $path = FILES.$file['fullpath'].$file['filename'];
    if( !file_exists($path) ) self::error();
    if( md5($file['filename']) != $md5 ) self::error();


    // Affichage ou DL
    if( strpos($file['type'],"image") > -1 ) {
      if( in_array("mini",$params) ) {
        $percent = 0.20;
        header('Content-Type: '.$file['type']);
        $isPng = false;

        $mime = $file['type'];
        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func = 'imagejpeg';
                break;
            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func = 'imagepng';
                $isPng = true;
                break;
            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func = 'imagegif';
                break;
            default: 
                die('resizeToMax : Unknown image type.');
        }


        list($width, $height) = getimagesize($path);
        $newwidth = 500;
        $diff = $width / $newwidth;
        $newheight = $height / $diff;   
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        if( $isPng ) {
          $background = imagecolorallocate($thumb, 0, 0, 0);
          imagecolortransparent($thumb, $background);
          imagealphablending($thumb, false);      
          imagesavealpha($thumb, true);          
        }
        $source = $image_create_func($path);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $image_save_func($thumb);
        imagedestroy($thumb);
      }
      else {
        header('Content-Type:'.$file['type']);
        header('Content-Length:'.$file['size']);
        readfile($path);
      }
    }
    else {
      header('Content-Type: application/octet-stream');
      header("Content-Transfer-Encoding: Binary");
      header("Content-disposition: attachment; filename=\"" . $file['filename'] . "\"");
      readfile($path);
    }
    exit;
  }

  public static function error() {
    header('HTTP/1.0 404 Not Found');
    exit;
  }

}
