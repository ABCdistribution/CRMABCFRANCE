<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-montage-titre');?></h5>
  </div>
  <div class="card-body m-250 noverflow">
    <table class="table">
      <thead>
        <tr>
          <th><?php echo l('admin-montage-nom');?></th>
          <th><?php echo l('admin-montage-chemin');?></th>
          <th><?php echo l('admin-montage-fichiers');?></th>
          <th><?php echo l('admin-montage-etat');?></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $points = [
            [ "nom" => l('admin-montage-crmamin'), "path" => DIR_CMD ],
            [ "nom" => l('admin-montage-minacrm'), "path" => REF_ARTICLE_PATH ],
          ];
          global $db;
          foreach( $points as $e ) {
            $cond = is_dir($e['path']) && is_readable($e['path']);
            $dir = core::getDir($e['path']);
            $count = ( $cond ? count($dir) : "?" );
            foreach( $dir as $d ) {
              $db->execute("SELECT * FROM log_referentiel WHERE filename = '$d' ");
              if( $db->num() ) $count--;
            }
            if( $count == 0 ) $count = '<em>'.l('admin-montage-vide').'</em>';
            if( $count > 0 ) $count .= ' <em>'.l('admin-montage-fichiers-presents').'</em>';
            echo '
            <tr>
              <td>'.$e['nom'].'</td>
              <td>'.$e['path'].'</td>
              <td class="tc">'.$count.'</td>
              <td class="tc">'.( $cond ? '<i class="fas fa-check green"></i>' : '<span class="red blink"><i class="fas fa-exclamation-triangle"></i> HS</span>').'</td>
            </tr>
            ';
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
