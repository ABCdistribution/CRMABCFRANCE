<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>ABC Distribution</title>
	<link rel="stylesheet" href="<?php echo URL;?>dist/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="<?php echo URL;?>css/font.css">
	<link rel="stylesheet" href="<?php echo URL;?>css/global.css">
	<link rel="stylesheet" href="<?php echo URL;?>css/login.css">
	<link rel="stylesheet" href="<?php echo URL_CSS;?>bootstrap.css">
	<script src="<?php echo URL;?>dist/js/jquery.js"></script>
	<script src="<?php echo URL;?>dist/js/vendor/bootstrap.bundle.min.js"></script>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script
		src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"
		integrity="sha256-hlKLmzaRlE8SCJC1Kw8zoUbU8BxA+8kR3gseuKfMjxA="
		crossorigin="anonymous"></script>	
	<script src="<?php echo URL;?>dist/js/global.js"></script>
	<script>
    let _global = [];
    _global.app_url = '<?php echo URL_APP_ROOT;?>';
  </script>	
</head>
<body>
	<div id="logo-wrapper">
		<img src="<?Php echo URL;?>img/logo.png" />
		<form method="POST" action="/" id="loginBox" class="form">
			<?php
			global $errorLoginMsg;
			if( isset($errorLoginMsg) && $errorLoginMsg != "" ) {
				echo '
				<div class="alert alert-info alert-dismissible bg-danger">
				  <i class="fas fa-exclamation-circle" style="margin-right:10px;"></i> '.$errorLoginMsg.'
				</div>';
			}
			?>
			<h2><?php echo l('login-welcome-msg');?></h2>
			<input type="text" name="login" value="" placeholder="<?php echo l('login-input-login');?>" class="abc" autocomplete="OFF" />
			<input type="password" name="pass" value="" placeholder="<?php echo l('login-input-password');?>" class="abc" autocomplete="OFF"/>
			<button type="submit" class="abc"><?php echo l('login-button');?></button>
		</form>

		
		<div id="lang-switcher">
			<?php
				$lang = lang::getLang();
				foreach( lang::getLangues() as $l ) {
				echo '<img 
					src="'.URL_APP_ROOT.'img/lang/'.$l['code'].'.png" 
					alt="'.$l['libelle'].'" 
					data-code="'.$l['code'].'" 
					class="'.($l['code'] == $lang ? 'active' : '').'"
				>';
				}
			?>
		</div>

	</div>
</body>
</html>
