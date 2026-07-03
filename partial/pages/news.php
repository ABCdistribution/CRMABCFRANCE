<?php if( !securite::can(7) ) return core::restricted();?>
<?php
global $params;
if( in_array('Edition',$params) ) {
  include(PAGES.'news/edition.php');
  return;
}
?>
<h1>
  <?php echo l('page-news-titre');?>
  <a class="float-right btn-xs abc" id="btnNavCreate"> <?php echo l('page-news-create');?></a>
</h1>

<div class="wrapper-page">


  <div id="wrapperListNews" class="show">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-news-liste');?></h5>
      </div>
      <div class="card-body">

        <?php
        $news = news::getAll();
        if( empty($news) ) {
            echo core::alert( l('page-news-liste'), l('page-news-liste-vide'),false);
        }
        else {
        ?>
        <table class="table table-striped table-bordered tnews">
          <thead class="thead-dark">
            <tr>
              <th scope="col" style="width:120px;"><?php echo l('page-news-table-photo');?></th>
              <th scope="col"><?php echo l('page-news-table-titre');?></th>
              <th scope="col" style="width:250px;"><?php echo l('page-news-table-createur');?></th>
              <th scope="col" style="width:200px;"><?php echo l('page-news-table-date');?></th>
              <th scope="col" style="width:100px;"><?php echo l('page-news-table-publiee');?></th>
              <th scope="col" style="width:100px;"><?php echo l('page-news-table-actions');?></th>
            </tr>
          </thead>
          <tbody>
          <?php
            $tmp = [];
            foreach( $news as $n ) {
              $photo = core::getUpload($n['id_photo']);


              $dd = '<div class="dropdown dropleft">
                      <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cog"></i>
                      </button>
                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="javascript:void(0)" onclick="news_publish(this,'.$n['id'].')">
                          '.( !$n['published'] ? l('page-news-action-publish') : l('page-news-action-nopublish') ).'
                        </a>
                        <a class="dropdown-item" href="'.URL_APP_ROOT.'News/Edition/'.$n['id'].'">'. l('page-news-action-edit').'</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="news_delete(this,'.$n['id'].')">'. l('page-news-action-delete').'</a>
                      </div>
                    </div>';

              $tmp[] = '<tr data-id="'.$n['id'].'" data-state="'.$n['published'].'">
                <td class="tImg">
                  <a href="'.core::getPublicFileLink($n['id_photo']).'" target="_blank">
                    <img src="'.$photo['link'].'" class="media img-thumbnail" alt="'.$photo['filename'].'">
                  </a>
                </td>
                <td><strong>'.$n['titre'].'</strong><br/><span class="d">'.news::getDesc($n['contenu'],100).'</span></td>
                <td><i class="fas fa-user icon"></i> '.user::getNameFromId($n['createur']).'</td>
                <td><i class="fas fa-calendar-plus icon"></i> '.core::dateOutput($n['date_creation']).'</td>
                <td class="tc pub">'.($n['published'] ? '<i class="fas fa-check green"></i>' : '<i class="fas fa-times"></i>' ).'</td>
                <td class="tc">'.$dd.'</td>
              </tr>';
            }
            echo implode($tmp);
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>


  <div id="wrapperCreateNews" class="hidden">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-news-create-titre');?></h5>
      </div>
      <div class="card-body">

        <div class="alert alert-dismissible" style="margin-bottom:20px;">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-info"></i> <?php echo l('page-news-create-publication');?></h4>
          <?php echo l('page-news-create-info');?>
          
        </div>

        <?php include(PAGES."news/creation.php"); ?>

        <div class="text-center">
          <button class="btn abc" onclick="saveNews()"><?php echo l('page-news-create-terminer');?></button>
        </div>

      </div>
    </div>


  </div>


</div>
<?php core::includeEditor();?>
