<?php 
// (c) vavok.net - Aleksandar Vranesevic
require_once"../include/strtup.php";

if (!checkPermissions('adminpanel', 'show')) {
    header("Location: ../");
    exit;
}

if (!empty($_GET['action'])) {
    $action = check($_GET['action']);
} else {
    $action = 'main';
}

function getAdminLinks($file) {
	global $lang_home;

	$handle = fopen("../used/dataadmin/" . $file, "r");
	if ($handle) {
	    while (($line = fgets($handle)) !== false) {
	    	$fileData = explode('||', $line);

	        $linkName = trim($fileData[1]);
	        $linkNameArray = array(trim($fileData[1]) => 'zero');
	        $linkNames = array_replace($linkNameArray, $lang_home);

	        if (file_exists($fileData[0]) && checkPermissions(trim($fileData[2]), 'show')) {
	        	echo '<a href="' . $fileData[0] . '" class="sitelink">' . $lang_home[$linkName] . '</a><br />' . "\n";
	    	}
	    }

	    fclose($handle);
	}
}

if ($action == 'refver') {
    $vavokStableVersionURL = "http://www.vavok.net/cms/version.txt";
    $key = 'stableversion'; // key to save cache with    

    // refresh latest version
    $currentVersion = @fopen($vavokStableVersionURL); 
    
    if (!empty($currentVersion)) {
        Cache::save($key, $currentVersion); // save data to cache file
  	} else {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $vavokStableVersionURL);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $currentVersion = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            echo "\n<br />";
            $currentVersion = '';
        } else {
            curl_close($ch);

            Cache::save($key, $currentVersion); // save data to cache file
        }
        if (!is_string($currentVersion) || !strlen($currentVersion)) {
            $currentVersion = '';
        }
    }

    // check license
    // $data = file_get_contents('http://www.vavok.net/cms/v.php?s=' . $my_license[1] . '&amp;ps=' . $my_license[2] . '&amp;i=' . $my_license[0] . '');

    header("Location: index.php");
    exit;
} 

$my_license = @file_get_contents('../used/licensekey.dat');
$my_license = explode('||', $my_license);


$my_title = $lang_home['admpanel'];
include_once"../themes/" . $config_themes . "/index.php";
if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 
if ($action == 'main') {
	// moderator links
	getAdminLinks("moderator_pages.dat");

    $totalUsers = $db->count_row('vavok_users');
    $totalUsers = $totalUsers - 1; // do not count "System"
    echo '<a href="../pages/userlist.php" class="sitelink">' . $lang_admin['userlist'] . ' (' . $totalUsers . ')</a><br />';

    if ($users->is_moderator(103) || $users->is_moderator(105) || $users->is_administrator() && file_exists('reports.php')) {
        echo '<a href="reports.php" class="sitelink">' . $lang_admin['usrcomp'] . '</a><br />';
    }

    if ($accessr == 101 || $accessr == 102 || $accessr == 103) {
        echo '<hr>';
        if (file_exists('upload.php')) {
        echo '<a href="upload.php" class="sitelink">' . $lang_admin['upload'] . '</a><br />';
        }
        echo '<a href="addban.php" class="sitelink">' . $lang_admin['banunban'] . '</a><br />';
        echo '<a href="banlist.php" class="sitelink">' . $lang_admin['banlist'] . '</a><br />';
    } 

    if (is_administrator(101) || $users->is_administrator(102)) {
        echo '<hr>';
        if (file_exists('forumadmin.php')) {
        echo '<a href="forumadmin.php?action=fcats" class="sitelink">' . $lang_admin['forumcat'] . '</a><br />';
        echo '<a href="forumadmin.php?action=forums" class="sitelink">' . $lang_admin['forums'] . '</a><br />';
        }
        if (file_exists('gallery/manage_gallery.php')) {
            echo'<a href="gallery/manage_gallery.php" class="sitelink">' . $lang_admin['gallery'] . '</a><br />';
        } 
        if (file_exists('votes.php')) {
            echo'<a href="votes.php" class="sitelink">' . $lang_admin['pools'] . '</a><br />';
        }
        if (file_exists("antiword.php")) {
        echo '<a href="antiword.php" class="sitelink">' . $lang_admin['badword'] . '</a><br />';
        }
        if (file_exists("uplfiles.php")) {
            echo '<a href="uplfiles.php" class="sitelink">' . $lang_admin['uplFiles'] . '</a><br />';
            echo '<a href="upl_search.php" class="sitelink">Search uploaded files</a><br />'; // update lang
        }
        echo '<a href="statistics.php" class="sitelink">' . $lang_home['statistic'] . '</a><br />';
    } 
    if (file_exists('news.php') && (is_administrator()) || (is_reg() && chkcpecprm('news', 'show'))) {
        echo '<a href="news.php" class="sitelink">' . $lang_admin['sitenews'] . '</a><br />';
    } 

    if ($users->is_administrator(101, $user_id)) {
        echo '<hr>';
        echo '<a href="setting.php" class="sitelink">' . $lang_admin['syssets'] . '</a><br />';
        echo '<a href="users.php" class="sitelink">' . $lang_admin['mngprof'] . '</a><br />';
        echo '<a href="ban.php" class="sitelink">' . $lang_admin['ipbanp'] . ' (' . counter_string(BASEDIR . 'used/ban.dat') . ')</a><br />';
        if (file_exists('subscribe.php')) {
            echo '<a href="subscribe.php" class="sitelink">' . $lang_admin['subscriptions'] . '</a><br />';
        } 
        echo '<a href="index.php?action=sysmng" class="sitelink">' . $lang_admin['sysmng'] . '</a><br />';
        if (file_exists('logfiles.php')) {
            echo '<a href="logfiles.php" class="sitelink">' . $lang_admin['logcheck'] . '</a><br />';
        }
        if (file_exists('email-queue.php')) {
            echo '<a href="email-queue.php" class="sitelink">Add to email queue</a><br />';
        } 
    } 
    if (file_exists('files.php') && (is_administrator() || checkPermissions('pageedit'))) {
        echo '<a href="files.php" class="sitelink">' . $lang_admin['mngpage'] . '</a><br />';
    } 
} 
if ($action == 'clear' && $users->is_administrator(101)) {
	if (file_exists('delusers.php')) {
    	echo '<a href="delusers.php" class="sitelink">' . $lang_admin['cleanusers'] . '</a><br />';
	}
    echo '<a href="./?action=clrmlog" class="sitelink">' . $lang_admin['cleanmodlog'] . '</a><br />';

    echo '<br /><br /><a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
} 

if ($action == "clrmlog" && $users->is_administrator(101)) {
    $sql = "DELETE FROM mlog";
    $db->query($sql);

    echo '<img src="../images/img/open.gif" alt="" /> ' . $lang_admin['mlogcleaned'] . '';

    echo '<br /><br /><a href="index.php" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
} 

if ($action == "sysmng" && $users->is_administrator(101)) {
    echo '<a href="systems.php" class="sitelink">' . $lang_admin['chksystem'] . '</a><br />';
    echo '<a href="./?action=clear" class="sitelink">' . $lang_admin['cleansys'] . '</a><br />';
    if (file_exists('backup.php')) {
        echo '<a href="backup.php" class="sitelink">' . $lang_admin['backup'] . '</a><br />';
    }
    echo '<a href="serverbenchmark.php" class="sitelink">Server benchmark</a><br />';
    // update
    // echo '<a href="index.php?action=opttbl">Optimize tables</a><br />'; // update lang
    echo '<br /><br /><a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
} 

if ($action == "opttbl" && $users->is_administrator(101)) {
    $alltables = mysql_query("SHOW TABLES");

    while ($table = mysql_fetch_assoc($alltables)) {
        foreach ($table as $db => $tablename) {
            $sql = "OPTIMIZE TABLE `" . $tablename . "`";
            $db->query($sql);
        } 
    } 

    echo '<br /><img src="../images/img/reload.gif" alt="" /> Optimized successfully!<br /><br /><br />'; // update lang
    echo '<br /><br /><a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
}
// check vavok cms version
if ($action == 'version') {
$version = $vavok_version;
$key = 'checkver'; // key to save cache with 
// get cached data from file cache, also check if cached data is not old

    echo'<div class="b">Vavok CMS ' . $lang_home['version'] . ': <b>' . $vavok_version . '</b>';

    if ($version != $last_ver && !empty($last_ver)) {
        echo '<br /><img src="../images/img/close.gif" alt="" /> ' . $lang_admin['newver'] . '!<br />';
        echo '<img src="../images/img/reload.gif" alt="" /> Latest version: ' . $last_ver . '<br />';
        echo '<a href="index.php?action=refver" class="sitelink">Refresh</a><br />';
    } else {
        echo '<br /><img src="../images/img/reload.gif" alt=""> ' . $lang_admin['hnewver'] . '!<br />';
    } 

    echo '</div><br />';


    echo '<br /><br /><a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br />';
}

echo '<p><a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>