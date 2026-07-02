<?php if( !securite::can(8) ) return core::restricted();?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 20px;">
  <a class="navbar-brand" href="#"><?php echo l('admin-titre');?></a>
  <div class="collapse navbar-collapse adminNavBar" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo URL;?>Admin"><i class="fas fa-chalkboard-teacher"></i> <?php echo l('admin-home');?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo URL;?>Admin/Securite"><i class="fas fa-user-lock"></i> <?php echo l('admin-secu');?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo URL;?>Admin/LiveLogs"><i class="fas fa-exchange-alt"></i> <?php echo l('admin-live');?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo URL;?>Admin/Debug"><i class="fas fa-solid fa-bug"></i> <?php echo l('admin-debug');?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="uploadObj()"><i class="fas fa-hand-holding-usd"></i> <?php echo l('js-admin-objectifs');?></a>
      </li>
	  <li class="nav-item">
		<a class="nav-link" href="<?php echo URL;?>Admin/GestionExportStatistique">
		 <i class="fas fa-file-export"></i> Gestion Export statistique
        </a>
      </li>
    </ul>
  </div>
</nav>

<?php
  global $params;
  $p = "admin";
  $path = PAGES.'admin/';
  if( isset($params[1]) ) {
    $f = strtolower($params[1]);
    if( file_exists($path.$f.'.php') )
      $p = $f;
  }
  include($path.$p.'.php');
?>
