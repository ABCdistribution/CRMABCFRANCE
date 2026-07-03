<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-dl-apk');?></h5>
  </div>
  <div class="card-body m-250">
    <div class="row">
        <?php
        if( ENV == "PROD") {
        $versions = ["stable" =>"Stable/Prod","trunk"=>"Trunk/Dev"];
        foreach( $versions as $ver => $libelle ) {
          $path = FILES.'apk/'.$ver.'/';
          echo '<div class="col"><h5><span>'.$libelle.'</span></h5>';
          $dir = core::getDir($path);
          if( empty($dir) ) {
            echo '<p class="tc small text-secondary">'.l('admin-dl-apk-vide').'</p></div>';
            continue;
          }
          else {
            $d = [];
            foreach( $dir as $k=>$e ) {
              $d[$e] = $e;
            }
            krsort($d);
            foreach( $d as $k=>$e ) {
              if( is_dir($path.$e) ) continue;
              $t = filemtime($path.$e)+3600; // +3600 car serveur reterade d'une heure
              echo '<div class="card clat">
                <a href="'.URL.'datas/apk/'.$ver.'/'.$e.'" target="_blank">
                  <div class="card-body">
                    <i class="fas fa-cloud-download-alt"></i> '.$e.'
                    <em class="small text-secondary" style="float:right;padding-top:3px;">'.date ("d/m/Y \à G\hi", $t ).'</em><br/>
                  </div>
                </a>
              </div>';
            }
          }
          echo '</div>';
        }
        }
        else {
          echo '<p class="text-center text-secondary" style="margin:15px auto;">'.l('admin-dl-apk-warn').'</p>';
        }
        ?>
    </div>
  </div>
</div>
