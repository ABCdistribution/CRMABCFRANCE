<?php global $db; $total = 0; ?>
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-stockage-titre');?></h5>
  </div>
  <div class="card-body m-250">
    <h5 style="margin-top:0"><span><?php echo l('admin-stockage-crm');?></span></h5>
    <div class="row tc" style="font-size:12px;">
      <div class="col">
        <strong><?php echo l('admin-stockage-visites');?></strong><br/>
        <?php
        $db->execute('SELECT SUM(size) as s FROM visite_photo');
        $size = $db->assoc()['s'];
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-prospections');?></strong><br/>
        <?php
        $db->execute('SELECT SUM(size) as s FROM prospection_photo');
        $size = $db->assoc()['s'];
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-uploads');?></strong><br/>
        <?php
        $db->execute('SELECT SUM(size) as s FROM uploaded_files');
        $size = $db->assoc()['s'];
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-apk');?></strong><br/>
        <?php
        $size = 0;
        $root = FILES.'apk/';
        $p = core::getDir($root.'stable/');
        foreach( $p as $k=>$e ) $size+= filesize($root.'stable/'.$e);
        $p = core::getDir($root.'trunk/');
        foreach( $p as $k=>$e ) $size+= filesize($root.'trunk/'.$e);
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-bdd');?></strong><br/>
        <?php
        $size = 0;
        $root = FILES.'distant/';
        $p = core::getDir($root);
        foreach( $p as $k=>$e ) $size+= filesize($root.$e);
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-backupas400');?></strong><br/>
        <?php
        $size = 0;
        $root = FILES.'dump_ref_as400/';
        $p = core::getDir($root);
        foreach( $p as $k=>$e ) $size+= filesize($root.$e);
        $total += $size;
        echo core::readableSize($size);
        ?>
      </div>
      <div class="col">
        <strong><?php echo l('admin-stockage-crm-code');?></strong><br/>
        <?php
        $c = intval(shell_exec("du -s /var/www/gescom/")) * 1024 ;
        $total += $c;
        echo core::readableSize($c);
        ?>
      </div>
    </div>

    <div class="tc bg-dark text-white" style="margin: 10px 0;">
      <strong><?php echo l('admin-stockage-total-crm');?> : <?php echo core::readableSize($total);?></strong>
    </div>


    <h5 style="margin:20px 0 15px 0"><span><i class="fas fa-server mr-3"></i> /dev/mapper/LVM-lv--home</span></h5>
    <div class="tc row">
      <?php
      $c = shell_exec("df -h | grep 'LVM-lv--home'");
      $c = preg_replace('/\s+/', ' ', $c);
      $c = explode(" ",$c);
      echo '<div class="col"><strong>Total</strong><br/>'.$c[1].'</div>';
      echo '<div class="col"><strong>Libre</strong><br/>'.$c[3].'</div>';
      echo '<div class="col"><strong>Occupé</strong><br/>'.$c[2].'</div>';
      echo '<div class="col bg-danger text-white"><strong>Occupation</strong><br/>'.$c[4].'</div>';
      ?>
    </div>

    <h5 style="margin:20px 0 15px 0"><span><i class="fas fa-server mr-3"></i> /dev/mapper/LVM-lv--racine</span></h5>
    <div class="tc row">
      <?php
      $c = shell_exec("df -h | grep 'racine'");
      $c = preg_replace('/\s+/', ' ', $c);
      $c = explode(" ",$c);
      echo '<div class="col"><strong>Total</strong><br/>'.$c[1].'</div>';
      echo '<div class="col"><strong>Libre</strong><br/>'.$c[3].'</div>';
      echo '<div class="col"><strong>Occupé</strong><br/>'.$c[2].'</div>';
      echo '<div class="col bg-danger text-white"><strong>Occupation</strong><br/>'.$c[4].'</div>';
      ?>
    </div>
  </div>
</div>
