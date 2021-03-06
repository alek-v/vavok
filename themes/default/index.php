<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

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
<script src="<?php echo HOMEDIR; ?>themes/default/node_modules/jquery/dist/jquery.min.js"></script>
<script src="<?php echo HOMEDIR; ?>themes/default/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
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
if ($vavok->get_configuration('cookieConsent') == 1) { include_once BASEDIR . "include/plugins/cookie-consent/cookie-consent.php"; }
?>
</head>
<body class="d-flex flex-column">

	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="container container-header">
	    	<a class="navbar-brand" href="<?php echo HOMEDIR; ?>">
	    		<img src="/themes/default/images/logo.png" width="30" height="30" alt="Logo">
	  		</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav mr-auto">
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
				<div class="nav-contact">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="btn btn-primary sitelink navi-contact" href="/mail/"><?php echo $vavok->go('localization')->string('contact'); ?></a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $vavok->go('localization')->string('lang'); ?></a>
							<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					          <a class="dropdown-item" href="/pages/change_lang.php?lang=en" rel="nofollow"><img src="/themes/default/images/flag_great_britain_32.png" alt="english language" /></a>
					          <a class="dropdown-item" href="/pages/change_lang.php?lang=sr" rel="nofollow"><img src="/themes/default/images/serbia_flag_32.png" alt="српски језик" /></a>
					        </div>
			      		</li>
					</ul>
				</div>
			</div>
		</div>
	</nav>

<div class="container">

<?php echo $vavok->get_isset(); /* get message from url */ ?>