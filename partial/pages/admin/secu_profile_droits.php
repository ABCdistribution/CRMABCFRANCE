<div class="card card-primary card-outline" id="editDroitsProfile">
  <div class="card-header">
    <h5 class="m-0" id="titleEditDroits"><?php echo l('admin-droits-titre');?></h5>
  </div>
  <div class="card-body" style="padding-bottom: 30px;">

    <div class="tc text-secondary mt-3 disclaimerEditProfile">
      <?php echo l('admin-droits-disclaimer');?>
      
    </div>

    <div class="hidden wrapperSelectProfile">


      <div class="accordion" id="accordionDroits">
        <?php
        $dd = securite::getListDroits();
        $cp = 0;
        foreach( $dd as $k=>$e  ) {
          if( empty($e['droits']) ) continue;
          $name = "_$k";
          $active = ( $cp == 0 ? 'show' : '' );
          echo '<div class="card card-droit">';

          echo '<div class="card-header noFullSize" id="heading'.$name.'"><h2 class="mb-0">';
          echo '<button class="btn btn-link" type="button" data-toggle="collapse" ';
          echo 'data-target="#collapse'.$name.'" aria-expanded="true" aria-controls="collapse'.$name.'">';
          echo $e['libelle'].'</button></h2></div>';

          echo '<div id="collapse'.$name.'" class="collapse '.$active.'" aria-labelledby="heading'.$name.'" data-parent="#accordionDroits">';
          echo '<div class="card-body">';

          foreach( $e['droits'] as $id_droit => $droit ) {
            $n = 'droit_'.$id_droit;
            echo '<p><input type="checkbox" name="droits_'.$id_droit.'" value="'.$id_droit.'" id="'.$n.'"/> ';
            echo '<label for="'.$n.'">'.$droit.'</label></p>';
          }

          echo '</div></div></div>';
          $cp++;
        }
        ?>
      </div>


    </div>


  </div>
</div>
