<form method="post" action="" id="formCreateNews" onsubmit="return false;">
  <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label><?php echo l('page-news-create-news-titre');?></label>
          <input type="text" class="form-control abc" name="titre_news"/>
        </div>
        <div class="form-group">
          <label><?php echo l('page-news-create-news-redaction');?></label>
          <div id="editor"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label class="labelInput" for="photo"><?php echo l('page-news-create-news-photo');?></label>
          <input id="photo" type="file" class="form-control abc" name="photo"/>
        </div>
        <div class="photoPreview">
          <p><i class="far fa-image"></i></p>
          <img src="#" id="img" class="hidden"/>
        </div>
      </div>
  </div>
</form>
