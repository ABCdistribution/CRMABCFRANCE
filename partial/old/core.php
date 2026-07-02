<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <link rel="icon" type="image/png" href="<?php echo URL;?>dist/img/favicon.png" />

  <title><?php echo $this->getPageName( $this->page );?> &bull; ABC Distribution</title>

  <?php /* Theme */ ?>
  <link href="<?php echo URL_APP_ROOT;?>/css/font.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo URL;?>dist/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?php echo URL_CSS;?>theme/adminlte.min.css">
  <link rel="stylesheet" href="<?php echo URL_JS;?>theme/jvectormap/jquery-jvectormap-2.0.3.css">
  <script src="<?php echo URL;?>dist/plugins/jquery/jquery.min.js"></script>
  <script src="<?php echo URL;?>dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo URL_JS;?>theme/adminlte.min.js"></script>
  <script src="<?php echo URL_JS;?>theme/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
  <script src="<?php echo URL_JS;?>theme/jvectormap/fr.js"></script>
  <script src="<?php echo URL;?>dist/plugins/datatables/jquery.dataTables.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+EAN13+Text&display=swap" rel="stylesheet">


  <?php /* Styles */ ?>
  <link rel="stylesheet" href="<?php echo URL;?>css/style.css<?php echo CSTR;?>">
  <?php $this->getPageStyles( $this->page );?>

  <?php /* Scripts */ ?>
  <script src="<?php echo URL;?>dist/js/global.js<?php echo CSTR;?>"></script>
  <?php $this->getPageScript( $this->page );?>
  <script>
    let _global = [];
    _global.app_url = '<?php echo URL_APP_ROOT;?>';
  </script>


</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <?php include(PARTIAL.'inc/nav_top.php');?>
  <?php include(PARTIAL.'inc/nav_left.php');?>

  <div class="content-wrapper">


    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?php echo $this->getPageName( $this->page );?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo URL;?>">Accueil</a></li>
              <li class="breadcrumb-item active"><?php echo $this->getPageName( $this->page );?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>

    <div class="content">
      <div class="container-fluid">
		<?php
			include(PAGES.$this->page.".php");
		?>
	</div>
  </div>

</div>

  <aside class="control-sidebar control-sidebar-dark alertBar">
      <div class="p-3">
      <h5>Notifications</h5>
      <p>Historique des notifications</p>
      <?php echo alert::printableAlerts(); ?>
      </div>
  </aside>



  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      ABC Distribution
    </div>
    <strong>Copyright &copy; <?php echo date('Y');?> <a href="http://snew.fr" color="orange">SNEW</a></strong>
  </footer>




<div class="modal" id="mainModal" tabindex="-1" role="dialog" aria-labelledby="mainModalTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mainModalTitle">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="mainModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" id="mainModalOkBtn">Ok</button>
      </div>
    </div>
  </div>
</div>






</body>
</html>
