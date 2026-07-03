<?php
global $params;
$id_news = intval(array_pop($params));
$news = news::get($id_news);
if( !$news ) {
  echo core::alert("Erreur", l('no-page'));
  return;
}
?>

<h1>
  <?php echo l("page-news-edit-titre");?>
  
  <a class="float-right btn-xs abc" href="<?php echo URL_APP_ROOT;?>News"><?php echo l("page-news-edit-back");?></a>
</h1>

<div class="wrapper-page">


  <div id="wrapperListNews" class="show">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l("page-news-edit-sous-titre");?> #<?php echo $id_news;?></h5>
      </div>
      <div class="card-body">

        <form method="post" action="" id="formCreateNews" onsubmit="return false;">
          <input type="hidden" name="id" value="<?php echo $id_news;?>"/>
          <input type="hidden" name="id_photo" value="<?php echo $news['id_photo'];?>"/>
          <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label><?php echo l('page-news-create-news-titre');?></label>
                  <input type="text" class="form-control abc" name="titre_news" value="<?php echo $news['titre'];?>"/>
                </div>
                <div class="form-group">
                  <label><?php echo l('page-news-create-news-redaction');?></label>
                  <div id="editor"><?php echo $news['contenu'];?></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="labelInput" for="photo"><?php echo l('page-news-create-news-photo');?></label>
                  <input id="photo" type="file" class="form-control abc" name="photo"/>
                </div>
                <div class="photoPreview">
                  <img src="<?php echo core::getPublicFileLink($news['id_photo']);?>" id="img"/>
                </div>
              </div>
          </div>
        </form>

        <div class="text-center">
          <button class="btn abc" onclick="saveNews()">Enregistrer les modifications</button>
        </div>



      </div>
    </div>
  </div>
<?php core::includeEditor();?>
