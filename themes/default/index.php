<?php 
// (c) vavok.net

// custom page tempates directory for this theme
// if you want to use custom template dir
// set here template dir for this theme
$themeCustomTpl = '';

// get page title
if (!isset($my_title)) {
    $my_title = page_title($phpself);
}
// if we still dont have a page title
if (empty($my_title)) {
	$my_title = $config["title"];
}

// load head tags for specific page
// if we are not installing script
if (!stristr($phpself, 'install/install.php')) {
    // check is this user made page
    if (!empty($pg)) {
        $vk_page = $db->select('pages', "pname='" . $pg . "'", '', 'headt');
        $head_tag = $vk_page['headt'];
    } 
    // data not found, contnue...
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $clean_requri . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag = $vk_page['headt'];
        } 
    } 
    // no data using $clean_requri, try PHP_SELF :)
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $phpself . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag = $vk_page['headt'];
        } 
    } 
} 
// append system generated head tags
if (empty($head_tag) && !empty($genHeadTag)) {
    $head_tag = $genHeadTag;
} 
// if $head_tag is already set
elseif (!empty($head_tag) && !empty($genHeadTag)) {
    $head_tag .= $genHeadTag;
} 
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
echo '<link rel="stylesheet" type="text/css" href="' . HOMEDIR . 'themes/default/style.css?v=' . filemtime(BASEDIR . 'themes/default/style.css') . '" />
';

// include custom (user made) <head> tags for all pages
echo file_get_contents(BASEDIR . 'used/headmeta.dat');

// include head tags specified for current page
if (!empty($head_tag)) {
    echo $head_tag;
}

// cookie consent
if ($config['cookieConsent'] == 1) {
    echo '
    <style type="text/css">
    .cookieConsentContainer {
        z-index: 999;
        width: 350px;
        min-height: 20px;
        box-sizing: border-box;
        padding: 30px 30px 30px 30px;
        background: #232323;
        overflow: hidden;
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: none;
    }
    .cookieConsentContainer .cookieTitle a {
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 22px;
        line-height: 20px;
        display: block;
    }
    .cookieConsentContainer .cookieDesc p {
        margin: 0;
        padding: 0;
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 13px;
        line-height: 20px;
        display: block;
        margin-top: 10px;
    } .cookieConsentContainer .cookieDesc a {
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        text-decoration: underline;
    }
    .cookieConsentContainer .cookieButton a {
        display: inline-block;
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 14px;
        font-weight: bold;
        margin-top: 14px;
        background: #000000;
        box-sizing: border-box; 
        padding: 15px 24px;
        text-align: center;
        transition: background 0.3s;
    }
    .cookieConsentContainer .cookieButton a:hover { 
        cursor: pointer;
        background: #3E9B67;
    }

    @media (max-width: 980px) {
        .cookieConsentContainer {
            bottom: 0px !important;
            left: 0px !important;
            width: 100%  !important;
        }
    }</style>
    ';
    echo '<script>
    var purecookieTitle = "' . $lang_home['cookies'] . '"; // Title
    var purecookieDesc = "' . $lang_home['purecookieDesc']  . '"; // Description
    var purecookieLink = \'' . $lang_home['purecookieLink']  . '\'; // Cookie policy link
    var purecookieButton = "' . $lang_home['purecookieButton']  . '"; // Button text
    </script>';
    echo '<script src="' . $connectionProtocol . $config_srvhost . '/js/purecookie.js" async></script>';
}

// tell bots what is our preferred page
echo '<link rel="canonical" href="' . $connectionProtocol . $config_srvhost . $clean_requri . '" />';

echo "\r\n<!-- Vavok CMS http://www.vavok.net -->
<title>" . $my_title . "</title>
</head>
<body>\r\n";

// site body

echo '
<div id="wrapper">
';

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