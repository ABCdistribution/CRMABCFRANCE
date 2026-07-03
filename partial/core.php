<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=0.5">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="icon" type="image/png" href="<?php echo URL;?>dist/img/favicon.png" />
  <title><?php echo $this->getPageName( $this->page );?> &bull; ABC Distribution</title>

  <?php /* Theme */ ?>
  <link href="<?php echo URL_APP_ROOT;?>/css/font.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo URL;?>dist/js/vendor/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?php echo URL_JS;?>vendor/jvectormap/jquery-jvectormap-2.0.3.css">
  <link rel="stylesheet" href="<?php echo URL_CSS;?>bootstrap.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"/>
  <script src="<?php echo URL;?>dist/js/vendor/jquery/jquery.min.js"></script>
  <script src="<?php echo URL;?>dist/js/jquery/js.cookie.min.js"></script>
  <script
  src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"
  integrity="sha256-hlKLmzaRlE8SCJC1Kw8zoUbU8BxA+8kR3gseuKfMjxA="
  crossorigin="anonymous"></script>
  <script src="<?php echo URL;?>dist/js/vendor/bootstrap.bundle.min.js"></script>
  <script src="<?php echo URL_JS;?>vendor/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
  <script src="<?php echo URL_JS;?>vendor/jvectormap/fr.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/dt-1.10.22/datatables.min.css"/>
  <script src="<?php echo URL;?>dist/js/vendor/datatables/jquery.dataTables.min.js"></script>
  <link href="<?php echo URL_APP_ROOT;?>/css/global.css" rel="stylesheet">
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+EAN13+Text&display=swap" rel="stylesheet">


  <link href="<?php echo URL_APP_ROOT;?>/css/mobile.css?<?php echo time();?>" rel="stylesheet">

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


  <?php lang::jsTrad();?>

</head>
<body>
  <div id="wrapper">

    <?php include(PARTIAL.'inc/navigation.php');?>

    <div id="page">
      <?php core::checkMountPoints(); ?>
      <div id="page-inner">
        <?php include(PAGES.$this->page.".php"); ?>
      </div>
    </div>

    <div id="photoViewer">
      <h1 id="photoViewerTitle"></h1>
      <div class="bg"></div>
      <div class="po left"><i class="fas fa-caret-left"></i></div>
      <div class="po right"><i class="fas fa-caret-right"></i></div>
      <button class="btn btn-share btn-success" id="sharePhoto"><i class="fas fa-share-alt"></i> <?php echo l('bouton-partager');?></button>
    </div>

  </div>
</body>
</html>
