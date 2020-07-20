<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   20.07.2020. 16:14:08
*/

require_once"../include/strtup.php";

if (!$users->check_permissions('adminpanel', 'show')) { redirect_to("../"); }

$action = isset($_GET['action']) ? check($_GET['action']) : '';

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

    header("Location: index.php");
    exit;
}

$my_title = $lang_home['admpanel'];
include_once"../themes/" . $config_themes . "/index.php";
 
if (empty($action)) {

	/*
	Moderator access level or bigger
	*/

	echo '<a href="adminchat.php" class="btn btn-outline-primary sitelink">' . $lang_admin['admchat'] . '</a>';
	echo '<a href="adminlist.php" class="btn btn-outline-primary sitelink">' . $lang_admin['modlist'] . '</a>';
	echo '<a href="reglist.php" class="btn btn-outline-primary sitelink">' . $lang_admin['notconf'] . '</a>';

    $totalUsers = $db->count_row('vavok_users') - 1; // - 1 - do not count "System"
    echo '<a href="../pages/userlist.php" class="btn btn-outline-primary sitelink">' . $lang_admin['userlist'] . ' (' . $totalUsers . ')</a>';

    /*
    Super moderator access level or bigger
    */

    if ($users->is_moderator(103) || $users->is_moderator(105) || $users->is_administrator()) {

    	if (file_exists('reports.php')) {
        	echo '<a href="reports.php" class="btn btn-outline-primary sitelink">' . $lang_admin['usrcomp'] . '</a>';
    	}

        if (file_exists('upload.php')) {
        	echo '<a href="upload.php" class="btn btn-outline-primary sitelink">' . $lang_admin['upload'] . '</a>';
        	echo '<a href="uploaded_files.php" class="btn btn-outline-primary sitelink">' . $lang_admin['uplFiles'] . '</a>';
            echo '<a href="search_uploads.php" class="btn btn-outline-primary sitelink">Search uploaded files</a>'; // update lang
        }

    }

    /*
    Head moderator access level or bigger
    */

    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102 || $_SESSION['permissions'] == 103) {

        echo '<hr>';

        echo '<a href="addban.php" class="btn btn-outline-primary sitelink">' . $lang_admin['banunban'] . '</a>';
        echo '<a href="banlist.php" class="btn btn-outline-primary sitelink">' . $lang_admin['banlist'] . '</a>';

    } 

    /*
    Administrator access level or bigger
    */

    if ($users->is_administrator()) {

        echo '<hr>';

        if (file_exists('forumadmin.php')) {
	        echo '<a href="forumadmin.php?action=fcats" class="btn btn-outline-primary sitelink">' . $lang_admin['forumcat'] . '</a>';
	        echo '<a href="forumadmin.php?action=forums" class="btn btn-outline-primary sitelink">' . $lang_admin['forums'] . '</a>';
        }
        if (file_exists('gallery/manage_gallery.php')) {
            echo'<a href="gallery/manage_gallery.php" class="btn btn-outline-primary sitelink">' . $lang_admin['gallery'] . '</a>';
        } 
        if (file_exists('votes.php')) {
            echo'<a href="votes.php" class="btn btn-outline-primary sitelink">' . $lang_admin['pools'] . '</a>';
        }
        if (file_exists("antiword.php")) {
        	echo '<a href="antiword.php" class="btn btn-outline-primary sitelink">' . $lang_admin['badword'] . '</a>';
        }

        echo '<a href="statistics.php" class="btn btn-outline-primary sitelink">' . $lang_home['statistic'] . '</a>';
        echo '<a href="users.php" class="btn btn-outline-primary sitelink">' . $lang_admin['mngprof'] . '</a>';

    }

    if (file_exists('news.php') && ($users->is_administrator()) || $users->check_permissions('news', 'show')) {
        echo '<a href="news.php" class="btn btn-outline-primary sitelink">' . $lang_admin['sitenews'] . '</a>';
    } 

    if (file_exists('files.php') && ($users->is_administrator() || $users->check_permissions('pageedit'))) {
        echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_admin['mngpage'] . '</a>';
    }

    /*
    Head administrator access level
    */

    if ($users->is_administrator(101)) {

        echo '<hr>';

        echo '<a href="settings.php" class="btn btn-outline-primary sitelink">' . $lang_admin['syssets'] . '</a>';
        echo '<a href="ban.php" class="btn btn-outline-primary sitelink">' . $lang_admin['ipbanp'] . ' (' . counter_string(BASEDIR . 'used/ban.dat') . ')</a>';
        if (file_exists('subscribe.php')) {
            echo '<a href="subscribe.php" class="btn btn-outline-primary sitelink">' . $lang_admin['subscriptions'] . '</a>';
        } 
        echo '<a href="index.php?action=sysmng" class="btn btn-outline-primary sitelink">' . $lang_admin['sysmng'] . '</a>';
        if (file_exists('logfiles.php')) {
            echo '<a href="logfiles.php" class="btn btn-outline-primary sitelink">' . $lang_admin['logcheck'] . '</a>';
        }
        if (file_exists('email-queue.php')) {
            echo '<a href="email-queue.php" class="btn btn-outline-primary sitelink">Add to email queue</a>';
        } 
    }

}

if ($action == 'clear' && $users->is_administrator(101)) {

	if (file_exists('delusers.php')) {
    	echo '<a href="delusers.php" class="btn btn-outline-primary sitelink">' . $lang_admin['cleanusers'] . '</a>';
	}
    echo '<a href="./?action=clrmlog" class="btn btn-outline-primary sitelink">' . $lang_admin['cleanmodlog'] . '</a>';

    echo '<a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a>';

} 

if ($action == "clrmlog" && $users->is_administrator(101)) {
    $sql = "DELETE FROM mlog";
    $db->query($sql);

    echo '<img src="../images/img/open.gif" alt="" /> ' . $lang_admin['mlogcleaned'] . '';

    echo '<a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a>';
} 

if ($action == "sysmng" && $users->is_administrator(101)) {
    echo '<p>';
    echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $lang_admin['chksystem'] . '</a>';
    echo '<a href="./?action=clear" class="btn btn-outline-primary sitelink">' . $lang_admin['cleansys'] . '</a>';
    if (file_exists('backup.php')) {
        echo '<a href="backup.php" class="btn btn-outline-primary sitelink">' . $lang_admin['backup'] . '</a>';
    }
    echo '<a href="serverbenchmark.php" class="btn btn-outline-primary sitelink">Server benchmark</a>';
    // update
    // echo '<a href="index.php?action=opttbl">Optimize tables</a>'; // update lang
    echo '</p>';
    
    echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a></p>';
} 

if ($action == "opttbl" && $users->is_administrator(101)) {
    $alltables = mysql_query("SHOW TABLES");

    while ($table = mysql_fetch_assoc($alltables)) {
        foreach ($table as $db => $tablename) {
            $sql = "OPTIMIZE TABLE `" . $tablename . "`";
            $db->query($sql);
        } 
    } 

    echo '<p><img src="../images/img/reload.gif" alt="" /> Optimized successfully!</p>'; // update lang
    echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a></p>';
}

// check vavok cms version
if ($action == 'version') {
$version = $vavok_version;
$key = 'checkver'; // key to save cache with 
// get cached data from file cache, also check if cached data is not old

    echo'<div class="b">Vavok CMS ' . $lang_home['version'] . ': <b>' . $vavok_version . '</b>';

    if ($version != $last_ver && !empty($last_ver)) {
        echo '<p><img src="../images/img/close.gif" alt="" /> ' . $lang_admin['newver'] . '!</p>';
        echo '<p><img src="../images/img/reload.gif" alt="" /> Latest version: ' . $last_ver . '</p>';
        echo '<a href="index.php?action=refver" class="btn btn-outline-primary sitelink">Refresh</a>';
    } else {
        echo '<p><img src="../images/img/reload.gif" alt=""> ' . $lang_admin['hnewver'] . '!</p>';
    } 

    echo '</div>';


    echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a></p>';
}

echo '<p><a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>