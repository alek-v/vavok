<!DOCTYPE html>
<html{@page_language}}>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/node_modules/bootstrap/dist/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/framework.min.css" />
		<link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/style.min.css" />
		{@head_metadata}} {@cookie_consent}}
		<title>{@title}}</title>
	</head>
	<body class="d-flex flex-column">
		<nav class="navbar navbar-expand-lg navbar-light bg-light top-navigation">
			<div class="container-fluid">
				<a class="navbar-brand" href="{@HOMEDIR}}"><img src="{@HOMEDIR}}themes/default/images/logo.png" width="30" height="30" alt="Logo"></a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				  <span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
				  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
					  {@authentication}}
				  </ul>
					<div class="dropdown">
						<button class="btn btn-secondary sitelink dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							{@website_language[lang]}}
						</button>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=en" rel="nofollow"><img src="{@HOMEDIR}}themes/default/images/flag_great_britain_32.png" alt="english language" /> English</a></li>
							<li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=sr" rel="nofollow"><img src="{@HOMEDIR}}themes/default/images/serbia_flag_32.png" alt="српски језик" /> Српски</a></li>
						</ul>
					</div>
					<span><a class="btn btn-primary sitelink navi-contact" href="{@HOMEDIR}}contact">{@website_language[contact]}}</a></span>
				</div>
			</div>
		</nav>
		<div class="container">