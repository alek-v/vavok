<?php 
// (c) Aleksandar Vranešević - vavok.net

// custom page templates directory is named "templates" and it must be under template main folder
// default page templates directory is /themes/templates/

include BASEDIR . "include/prepare_header.php";

// header
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/bootstrap/css/bootstrap.min.css" />
<script src="<?php echo HOMEDIR; ?>themes/default/js/jquery-3.5.1.min.js"></script>
<script src="<?php echo HOMEDIR; ?>themes/default/bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/framework.css?v=<?php echo filemtime(BASEDIR . 'themes/default/framework.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo HOMEDIR; ?>themes/default/style.css?v=<?php echo filemtime(BASEDIR . 'themes/default/style.css'); ?>" />

<?php
// load data for header
include BASEDIR . "include/load_header.php";
?>
</head>
<body class="d-flex flex-column">

	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="container container-header">
	    	<a class="navbar-brand" href="<?php echo HOMEDIR; ?>">
	    		<img src="/themes/default/images/logo.png" height="30" alt="Logo">
	  		</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav mr-auto">
				<?php

				if ($users->is_reg()) {
				    echo '<li class="nav-item"><a href="' . HOMEDIR . 'pages/inbox.php" class="btn btn-primary sitelink">' . $lang_home['inbox'] . ' (' . $users->user_mail($user_id) . ')</a></li>';
				    echo '<li class="nav-item"><a href="' . HOMEDIR . 'pages/mymenu.php" class="btn btn-primary sitelink">' . $lang_home['mymenu'] . '</a></li>';
				    if ($users->is_administrator()) {
				        echo'<li class="nav-item"><a href="' . HOMEDIR . $config["mPanel"] . '/" class="btn btn-primary sitelink">' . $lang_home['admpanel'] . '</a></li>';
				    } 
				    if ($users->is_moderator()) {
				        echo '<li class="nav-item"><a href="' . HOMEDIR . $config["mPanel"] . '/" class="btn btn-primary sitelink">' . $lang_home['modpanel'] . '</a></li>';
				    } 
				} else {
				    echo '<li class="nav-item"><a href="' . HOMEDIR . 'pages/login.php" class="btn btn-primary sitelink">' . $lang_home['login'] . '</a></li>';
				    echo '<li class="nav-item"><a href="' . HOMEDIR . 'pages/registration.php" class="btn btn-primary sitelink">' . $lang_home['register'] . '</a></li>';
				    //echo '<li class="nav-item"><a href="' . HOMEDIR . 'mail/lostpassword.php" class="btn btn-primary sitelink">' . $lang_home['lostpass'] . '</a></li>';
				} 

				?>
				</ul>
				<div class="nav-contact">
					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav">
							<li class="nav-item">
								<a class="btn btn-primary sitelink navi-contact" href="/mail/"><?php echo $lang_home['contact']; ?></a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</nav>

<div class="container">