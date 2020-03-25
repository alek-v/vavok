<?php 
// (c) vavok.net

// custom page tempates directory for this theme
// if you want to use custom template dir
// set here template dir for this theme
// dir must be under template main folder
$themeCustomTpl = '';

// get page title
if (!isset($my_title)) { $my_title = page_title($phpself); }

// if we still dont have a page title use website main title
if (empty($my_title)) { $my_title = $config["title"]; }

// load head tags for specific page
$head_tag = '';
// if we are not installing cms
if (!stristr($phpself, 'install/install.php')) {
    // is this regular page
    if (!empty($pg)) {
        $vk_page = $db->select('pages', "pname='" . $pg . "'", '', 'headt');
        $head_tag .= $vk_page['headt'];
    } 
    // if data not found, contnue...
    // this is used if this is custom page
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $clean_requri . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag .= $vk_page['headt'];
        } 
    } 
    // no data using $clean_requri, try PHP_SELF :)
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $phpself . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag .= $vk_page['headt'];
        } 
    } 
}

// append head tags for specific pages
if (!empty($genHeadTag)) { $head_tag .= $genHeadTag; }

// tell bots what is our preferred page
if (stristr($head_tag, 'rel="canonical"') === false && isset($pg)) { $head_tag .= "\n" . '<link rel="canonical" href="' . $connectionProtocol . $config_srvhost . '/' . $pg . '/" />'; }


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
echo '<link rel="stylesheet" type="text/css" href="' . HOMEDIR . 'themes/default/style.css?v=' . filemtime(BASEDIR . 'themes/default/style.css') . '" />';

// include custom (user made) <head> tags for all pages
echo file_get_contents(BASEDIR . 'used/headmeta.dat');

// include head tags specified for current page
if (!empty($head_tag)) { echo $head_tag; }

// cookie consent
if ($config['cookieConsent'] == 1) { include_once BASEDIR . "include/plugins/cookie-consent/cookie-consent.php"; }

echo "\r\n<!-- Vavok CMS http://www.vavok.net -->
<title>" . $my_title . "</title>
</head>
<body>\r\n";

// site body

echo '<div id="wrapper">';

echo '<header><div class="c"><strong>' . $config["title"] . '</strong></div></header>';

echo '<div id="container"><p>';
if (is_reg()) {
    echo '<a href="' . HOMEDIR . 'pages/inbox.php">' . $lang_home['inbox'] . '</a>(' . user_mail($user_id) . ')';
    echo ' <a href="' . HOMEDIR . 'pages/mymenu.php">' . $lang_home['mymenu'] . '</a>';
    if (isadmin()) {
        echo' <a href="' . HOMEDIR . '' . $config["mPanel"] . '/">' . $lang_home['admpanel'] . '</a>';
    } 
    if (ismod()) {
        echo ' <a href="' . HOMEDIR . '' . $config["mPanel"] . '/">' . $lang_home['modpanel'] . '</a>';
    } 
} else {
    echo '<a href="' . HOMEDIR . 'pages/login.php">' . $lang_home['login'] . '</a>';
    echo ' <a href="' . HOMEDIR . 'pages/registration.php">' . $lang_home['register'] . '</a>';
    echo ' <a href="' . HOMEDIR . 'mail/lostpassword.php">' . $lang_home['lostpass'] . '</a>';
} 
echo '</p>';

/*
// change language
<div id="select_lang">
<a href="/pages/chlng.php?lang=english&amp;ptl=<?php echo urlencode($clean_requri); ?>"><img src="/themes/web_vavok2/images/english_icon.gif" width="16" alt="english" /> EN</a>  <a href="/pages/chlng.php?lang=serbian_latin&amp;ptl=<?php echo urlencode($clean_requri); ?>"><img src="/themes/web_vavok2/images/serbia.png" height="15" alt="serbian" /> SR</a>
</div>
*/
?>