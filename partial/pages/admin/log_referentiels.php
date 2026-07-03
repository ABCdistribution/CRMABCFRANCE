<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <?php echo l('admin-logref-titre');?>
      
      <div class="float-right">
        <button class="btn btn-primary btn-sm nom" style="padding: 0px 8px;" onclick="injectRef();">
          <i class="fas fa-sitemap" rel="tooltip" title="Importer manuellement les fichiers AS400"></i> <?php echo l('admin-logref-import');?>
        </button>
      </div>
    </h5>
  </div>
  <div class="card-body m-250">
    <table class="table table-condensed tc">
      <thead>
        <tr>
          <th class="tl"><?php echo l('admin-logref-fichier');?></th>
          <th><?php echo l('admin-logref-poids');?></th>
          <th><?php echo l('admin-logref-lignes');?></th>
          <th><?php echo l('admin-logref-date');?></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $logs = admin::getLogReferentiels( 100 );
        foreach( $logs as $log ) {
          echo '
          <tr>
            <td class="tl">'.$log['filename'].'</td>
            <td>'.core::readableSize($log['size']).'</td>
            <td>'.core::n($log['nb_lines']).'</td>
            <td>'.core::dateFrom($log['date_traitement']).' ('.core::dateOutput($log['date_traitement'],true).')</td>
          </tr>
          ';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
