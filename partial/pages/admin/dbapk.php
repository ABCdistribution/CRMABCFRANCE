<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-dbapk-titre');?></h5>
  </div>
  <div class="card-body m-250 noverflow">
    <?php
    global $db;
    $db->execute("SELECT * FROM dd_history ORDER BY id DESC LIMIT 1");
    $i = $db->assoc();
    ?>
    <?php echo l('admin-dbapk-date');?>
    
    <strong><?php echo core::dateFrom($i['date_creation']);?></strong>.<br/>
    <?php echo l('admin-dbapk-disclaimer');?>
    <a href="#" onclick="generateDB()" class="btn btn-default btn-xs"><?php echo l('admin-dbapk-click');?></a>
    <br/><br/>
    <?php echo l('admin-dbapk-fichier');?> : <?php echo '<strong>'.$i['name'].'</strong> <em><i class="fas fa-file-archive" style="margin:0 10px 0 20px"></i>'.core::readableSize($i['size']).'</em>';?>
  </div>
</div>
