<?php
global $db;
$db->execute("SELECT * FROM logs_apk ORDER BY id DESC LIMIT 500");
$logs = $db->getArray();
$users = [];
?>
<p class="bg-info text-white" style="padding: 10px;">
   <i class="fas fa-info-circle" style="margin-right:8px;"></i>
   <?php echo l('admin-live-titre');?>
   
</p>
<table class="table table-striped table-condensed" id="logsTable" data-id="">
  <thead>
    <tr>
      <th width="130px"><?php echo l('admin-live-date');?></th>
      <th width="200px"><?php echo l('admin-live-user');?></th>
      <th><?php echo l('admin-live-log');?>Log</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $tmp = [];
    foreach( $logs as $k=>$e ) {
      $class = "";
      if( $e['error'] == 1 ) $class = 'danger';
      if( $e['error'] == 2 ) $class = 'secondary';
      if( $e['error'] == 3 ) $class = 'success';

      $c = ( $e['error'] > 0 ? 'class="bg-'.$class.' text-white"' : '' );
      $tmp[] = '<tr '.$c.' data-id="'.$k.'"><td>'.core::dateOutput($e['date_creation'],true).'</td><td>';
      if( !in_array($e['id_user'],array_keys($users)) )
        $users[$e['id_user']] = user::getNameFromId($e['id_user']);
      $tmp[] = $users[$e['id_user']].'</td><td>'.$e['message'].'</td></tr>';
    }
    echo implode($tmp);
    ?>
  </tbody>
</table>
<script>
  $(document).ready(function() {
    initLiveLogs();
  })
</script>
