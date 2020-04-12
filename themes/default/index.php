<?php 
// (c) vavok.net

// custom page tempates directory for this theme
// if you want to use custom template dir
// set here template dir for this theme
// dir must be under template main folder
$themeCustomTpl = '';

include BASEDIR . "include/prepare_header.php";

// header
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php

// head tags <head></head>

// head tags for current theme
echo '<link rel="stylesheet" type="text/css" href="' . HOMEDIR . 'themes/default/bootstrap/css/bootstrap.min.css" />';
echo '<link rel="stylesheet" type="text/css" href="' . HOMEDIR . 'themes/default/framework.css?v=' . filemtime(BASEDIR . 'themes/default/style.css') . '" />';
echo '<link rel="stylesheet" type="text/css" href="' . HOMEDIR . 'themes/default/style.css?v=' . filemtime(BASEDIR . 'themes/default/style.css') . '" />';


// load data for header
include BASEDIR . "include/load_header.php";

// site body
?>
</head>
<body class="d-flex flex-column">
  <nav class="navbar navbar-expand-lg" id="mainNav">
    <div class="container">
      <a class="navbar-brand js-scroll-trigger" style="text-transform: lowercase;" href="/"><img src="/themes/default/images/logo.png" height="48" alt="logo" /></a>

    <?php

    if ($users->is_reg()) {
        echo '<a href="' . HOMEDIR . 'pages/inbox.php" class="btn btn-primary sitelink">' . $lang_home['inbox'] . ' (' . $users->user_mail($user_id) . ')</a>';
        echo ' <a href="' . HOMEDIR . 'pages/mymenu.php" class="btn btn-primary sitelink">' . $lang_home['mymenu'] . '</a>';
        if ($users->is_administrator()) {
            echo' <a href="' . HOMEDIR . '' . $config["mPanel"] . '/" class="btn btn-primary sitelink">' . $lang_home['admpanel'] . '</a>';
        } 
        if ($users->is_moderator()) {
            echo ' <a href="' . HOMEDIR . '' . $config["mPanel"] . '/" class="btn btn-primary sitelink">' . $lang_home['modpanel'] . '</a>';
        } 
    } else {
        echo '<a href="' . HOMEDIR . 'pages/login.php" class="btn btn-primary sitelink">' . $lang_home['login'] . '</a>';
        echo '<a href="' . HOMEDIR . 'pages/registration.php" class="btn btn-primary sitelink">' . $lang_home['register'] . '</a>';
        echo '<a href="' . HOMEDIR . 'mail/lostpassword.php" class="btn btn-primary sitelink">' . $lang_home['lostpass'] . '</a>';
    } 

    ?>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <div class="navbar-nav ml-auto">
            <a class="btn btn-primary sitelink" href="/mail/"><?php echo $lang_home['contact']; ?></a>
        </div>
      </div>
    </div>
  </nav>

<div class="container">