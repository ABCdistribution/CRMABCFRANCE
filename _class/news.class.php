<?php

class news {

  public static function getAll( $limit = 100 ) {
    global $db;
    $db->execute("SELECT * FROM news WHERE deleted = 0 ORDER BY id DESC LIMIT $limit");
    return $db->getArray();
  }

  public static function get( $id ) {
    global $db;
    $db->execute("SELECT * FROM news WHERE id = ".intval($id)." AND deleted = 0");
    return $db->assoc();
  }

  public static function create() {
    global $db;
    if( !isset($_POST['titre_news']) ) core::aError("Le titre de la news est obligatoire");
    $titre = $db->escape(trim($_POST['titre_news']));

    if( !isset($_POST['editor']) ) core::aError("Le contenu de la news est obligatoire");
    $editor = $db->escape(trim($_POST['editor']));

    $id_news = ( isset($_POST['id']) && intval($_POST['id']) > 0 ? intval($_POST['id']) : false );
    $news = (news::get($id_news) ?? false);
    $id_photo = ( isset($news['id_photo']) ? $news['id_photo'] : 0 );

    if( isset($_FILES['photo']) ) {
      $tmp_photo = core::getUploadedFile( 'photo', ['jpg','jpeg','png'], 5242880 );
      if( !$tmp_photo && !$id_photo ) core::aError("Impossible de charger la photo");
      if( $tmp_photo && $tmp_photo != $id_photo ) $id_photo = $tmp_photo;
    }

    if( !$news ) {
      $db->execute("
      INSERT INTO news
        (createur,titre,contenu,id_photo)
      VALUES
        (".ID.",  '".$titre."', '".$editor."', $id_photo )
      ");
    }
    else {
      $db->execute("
      UPDATE news SET
        titre = '$titre',
        contenu = '$editor',
        id_photo = $id_photo
      WHERE
        id = $id_news
      ");
    }

    die('{}');
  }

  public static function getDesc( $desc, $length = 200 ) {
    $desc = str_replace(["<br/>","<br>","<br />","\r","\n"]," ",$desc);
    $str = mb_substr(strip_tags($desc),0,$length);
    return str_replace([",",";","."],[", ","; ",". "],$str).( mb_strlen($desc) > $length ? '...':'');
  }

  public static function togglePublish() {
    $id = intval($_POST['id']);
    $news = self::get($id);
    if( !$news ) core::aError("Cette news n'a pas été trouvée");
    global $db;
    $db->execute("UPDATE news SET published = ".( $news['published'] == 0 ? 1 : 0 ).", date_publication = NOW() WHERE id = $id");
    die('{}');
  }

  public static function deleteNews() {
    $id = intval($_POST['id']);
    $news = self::get($id);
    if( !$news ) core::aError("Cette news n'a pas été trouvée");
    global $db;
    $db->execute("UPDATE news SET deleted = 1 WHERE id = $id");
    die('{}');
  }

}
