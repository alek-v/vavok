<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

// Disable direct access to this file
if (!defined('BASEDIR')) exit;

/**
 * Custom page templates directory is named "templates" and it must be under template main folder
 * default page templates directory is /themes/templates/
 */

header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html<?php if (defined('PAGE_LANGUAGE')) echo PAGE_LANGUAGE; ?>>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/node_modules/bootstrap/dist/css/bootstrap.min.css" />
		<script src="<?php echo HOMEDIR; ?>themes/default/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
		<script src="<?php echo HOMEDIR; ?>themes/default/node_modules/jquery/dist/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/css/framework.min.css?v=<?php echo filemtime(BASEDIR . 'themes/default/css/framework.min.css'); ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/css/style.min.css?v=<?php echo filemtime(BASEDIR . 'themes/default/css/style.min.css'); ?>" />
		<?php
		/**
		 * Load data for header
		 */
		$vavok->go('current_page')->load_head_tags();

		/**
		 * Cookie consent
		 */
		if ($vavok->get_configuration('cookieConsent') == 1) include_once BASEDIR . 'include/plugins/cookie-consent/cookie-consent.php';
		?>
	</head>
	<body class="d-flex flex-column">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="container-fluid">
				<a class="navbar-brand" href="<?php echo HOMEDIR; ?>"><img src="<?php echo HOMEDIR; ?>themes/default/images/logo.png" width="30" height="30" alt="Logo"></a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				  <span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
				  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<?php
					if ($vavok->go('users')->is_reg()) {
					    echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . 'pages/inbox.php', $vavok->go('localization')->string('inbox') . ' (' . $vavok->go('users')->user_mail($vavok->go('users')->user_id) . ')') . '</li>';
					    echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . 'pages/mymenu.php', $vavok->go('localization')->string('mymenu')) . '</li>';
					    if ($vavok->go('users')->is_administrator()) {
					    	echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . $vavok->get_configuration('mPanel') . '/', $vavok->go('localization')->string('admpanel')) . '</li>';
					    } 
					    if ($vavok->go('users')->is_moderator()) {
					    	echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . $vavok->get_configuration('mPanel') . '/', $vavok->go('localization')->string('modpanel')) . '</li>';
					    } 
					} else {
					    echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . 'pages/login.php', $vavok->go('localization')->string('login')) . '</li>';
					    echo '<li class="nav-item">' . $vavok->sitelink(HOMEDIR . 'pages/registration.php', $vavok->go('localization')->string('register')) . '</li>';
					}
					?>
				  </ul>
					<div class="dropdown">
						<button class="btn btn-secondary sitelink dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<?php echo $vavok->go('localization')->string('lang'); ?>
						</button>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo HOMEDIR; ?>pages/change_lang.php?lang=en" rel="nofollow"><img src="<?php echo HOMEDIR; ?>themes/default/images/flag_great_britain_32.png" alt="english language" /> English</a></li>
							<li><a class="dropdown-item" href="<?php echo HOMEDIR; ?>pages/change_lang.php?lang=sr" rel="nofollow"><img src="<?php echo HOMEDIR; ?>themes/default/images/serbia_flag_32.png" alt="српски језик" /> Српски</a></li>
						</ul>
					</div>
					<span><a class="btn btn-primary sitelink navi-contact" href="<?php echo HOMEDIR; ?>mail/"><?php echo $vavok->go('localization')->string('contact'); ?></a></span>
				</div>
			</div>
		</nav>
		<div class="container">
			<?php echo $vavok->get_isset(); /* get message from url */ ?>