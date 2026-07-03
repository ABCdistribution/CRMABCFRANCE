
<div class="row">
  <div class="col-3">
    <div class="list-group">
      <?php
        $path = FILES."debug/".date('Y')."/".date("m")."/".date('d')."/";
        if( is_dir($path) ) {
          $list = core::getDir($path);
          $list2 = [];
          //krsort($list);
          foreach( $list as $k=>$e ) {
            $name = $e;
            $content = file_get_contents($path.$e);
      			$obj = json_decode($content,true);

            if( is_array($obj) ) {

              if( isset($obj['user']) ) {
                $tmp2 = explode("_",$e);
                $end = explode(".",array_pop($tmp2));
                $hours = array_shift($end);
                $e = ( $obj['user']['displayname'] ?? "")." - ".$hours;
                $hours = intval(str_replace(["h","m"],"",$hours));
                $list2[$hours] = [
                  "name" => $name,
                  "e" => $e
                ];
              }
              else $list2[] = $e;
            }
            else {
              echo "<small>$e : fichier tronqué</small><br/>";
              $list2[] = $e;
            }

            //echo '<a href="#" class="list-group-item list-group-item-action logName" onclick="readlog(this)" data-name="'.$name.'">'.$e.'</a>';
          }
          krsort($list2);
          foreach( $list2 as $k=>$e ) {
            echo '<a href="#" class="list-group-item list-group-item-action logName" onclick="readlog(this)" data-name="'.($e['name']??$e).'">'.($e['e']??$e).'</a>';
          }

        }
      ?>
    </div>
  </div>
  <div class="col ">
    <div class="container logReader">

    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-jsonview/1.2.3/jquery.jsonview.min.js" integrity="sha512-ff/E/8AEnLDXnTCyIa+l80evPRNH8q5XnPGY/NgBL645jzHL1ksmXonVMDt7e5D34Y4DTOv+P+9Rmo9jBSSyIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-jsonview/1.2.3/jquery.jsonview.min.css" integrity="sha512-aM9sVC1lVWwuuq38iKbFdk04uGgRyr7ERRnO990jReifKRrYGLugrpLCj27Bfejv6YnAFW2iN3sm6x/jbW7YBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
