<?php 
// (c) vavok.net
if (!empty($_SESSION['log'])) {
    $permissions = $db->select('vavok_users', "id='" .  getidfromnick(check($_SESSION['log'])) . "'", '', 'perm');

    $log = $_SESSION['log']; // username
    $user_id = getidfromnick($log); // user id
    $accessr = $permissions['perm']; // access rights

    $vavok_users = $db->select('vavok_users', "id='" . $user_id . "'", '', 'skin, banned, timezone, lang, mskin');
    $user_profil = $db->select('vavok_profil', "uid='" . $user_id . "'", '', 'regche');
    $db->update('vavok_profil', 'lastvst', $time, "uid='" . $user_id . "'");

    if (!empty($vavok_users['mskin']) && $userDevice == 'phone') {
        $config_themes = check($vavok_users['mskin']);
    } elseif (!empty($vavok_users['skin'])) {
        $config_themes = check($vavok_users['skin']);
    } // skin
    if (!empty($vavok_users['timezone'])) {
        $config["timeZone"] = check($vavok_users['timezone']);
    } // time zone
    if (!empty($vavok_users['lang'])) {
        $config["language"] = check($vavok_users['lang']);
    } // language
    if ($vavok_users['banned'] == "1" && !strstr($phpself, 'pages/ban.php')) {
        header("Location: " . BASEDIR . "pages/ban.php");
        exit;
    } // banned?
    if ($user_profil['regche'] == 1 && !strstr($phpself, 'pages/key.php')) {
        setcookie('cookpass', '');
        setcookie('cooklog', '');
        setcookie(session_name(), '');
        unset($_SESSION['log']);
        session_destroy();
    } // activate account?      
    // check session life
    if ($config["sessionLife"] > 0) {
        if (($_SESSION['my_time'] + $config["sessionLife"]) < $time && $_SESSION['my_time'] > 0) {
            session_unset();
            setcookie(session_name(), '');
            session_destroy();
            header("Location: " . BASEDIR . $request_uri);
            exit;
        } 
    }
} else {
    // if subdomain is www
    if (substr($_SERVER['HTTP_HOST'], 0, 3) == 'www') {
        $config_themes = $config["webtheme"];
    } 
    // if subdomain is mobile
    elseif (substr($_SERVER['HTTP_HOST'], 0, 2) == 'm.') {
        $config_themes = $config["mTheme"];
    } 
    // else
    else {
        if ($userDevice == 'phone' && $config["redbrow"] == 1) {
            header("Location: " . $connectionProtocol . "m." . $config["homeBase"] . $request_uri); 
            // header("Location: http://m.".$config["homeBase"]."".$request_uri."", TRUE, 301); // 301 Moved Permanently
            exit;
        } elseif ($userDevice == 'computer' && $config["redbrow"] == 1) {
            header("Location: " . $connectionProtocol . "www." . $config["homeBase"] . $request_uri); 
            // header("Location: http://www.".$config["homeBase"]."".$request_uri."", TRUE, 301); // 301 Moved Permanently
            exit;
        } elseif ($userDevice == 'phone') {
            $config_themes = $config["mTheme"];
        } else {
            $config_themes = $config["webtheme"];
        } 
    } 
} 
// if skin not found
if (!file_exists(BASEDIR . "themes/" . $config_themes . "/index.php")) {
    $config_themes = 'default';
} 
// language settings
// use language from session
if (!empty($_SESSION['lang'])) {
    $config["language"] = $_SESSION['lang'];
} 
// if there is no language chosen by user
// use browser language
if (empty($_SESSION['lang']) && empty($user_id)) {
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $v_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    } else {
    $v_lang = '';
    }

    switch ($v_lang) {
        case "en": 
            // english
            if (file_exists(BASEDIR . 'lang/english/index.php')) {
                $_SESSION['lang'] = 'english';
                $config["language"] = 'english';
            } else {
                $_SESSION['lang'] = $config["language"];
            } 
            break;
        case "sr": 
            // serbian
            if (file_exists(BASEDIR . 'lang/serbian_cyrillic/index.php')) {
                $_SESSION['lang'] = 'serbian_cyrillic';
                $config["language"] = 'serbian_cyrillic';
            } elseif (file_exists(BASEDIR . 'lang/serbian_latin/index.php')) {
                $_SESSION['lang'] = 'serbian_latin';
                $config["language"] = 'serbian_latin';
            } else {
                $_SESSION['lang'] = $config["language"];
            } 
            break;
        default: 
            // include default language in all other cases of different lang detection
            $_SESSION['lang'] = $config["language"];
            break;
    } 
} 

if ($config["noCache"] == "0") {
    header("Expires: Sat, 25 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
} 

if ($config["siteOff"] == 1 && !strstr($phpself, 'pages/maintenance.php') && !strstr($phpself, 'input.php') && !isadmin() && !strstr($phpself, 'pages/login.php')) {
    header ("Location: " . $config["homeUrl"] . "/pages/maintenance.php");
    exit;
} 

?>