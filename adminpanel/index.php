<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 3:04:34
*/

require_once"../include/startup.php";

if (!$users->check_permissions('adminpanel', 'show')) { $vavok->redirect_to("../"); }

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

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

$current_page->page_title = $localization->string('admpanel');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";
 
if (empty($action)) {

	/*
	Moderator access level or bigger
	*/

	echo '<a href="adminchat.php" class="btn btn-outline-primary sitelink">' . $localization->string('admchat') . '</a>';
	echo '<a href="adminlist.php" class="btn btn-outline-primary sitelink">' . $localization->string('modlist') . '</a>';
	echo '<a href="reglist.php" class="btn btn-outline-primary sitelink">' . $localization->string('notconf') . '</a>';

    $totalUsers = $db->count_row('vavok_users') - 1; // - 1 - do not count "System"
    echo '<a href="../pages/userlist.php" class="btn btn-outline-primary sitelink">' . $localization->string('userlist') . ' (' . $totalUsers . ')</a>';

    /*
    Super moderator access level or bigger
    */

    if ($users->is_moderator(103) || $users->is_moderator(105) || $users->is_administrator()) {

    	if (file_exists('reports.php')) {
        	echo '<a href="reports.php" class="btn btn-outline-primary sitelink">' . $localization->string('usrcomp') . '</a>';
    	}

        if (file_exists('upload.php')) {
        	echo '<a href="upload.php" class="btn btn-outline-primary sitelink">' . $localization->string('upload') . '</a>';
        	echo '<a href="uploaded_files.php" class="btn btn-outline-primary sitelink">' . $localization->string('uplFiles') . '</a>';
            echo '<a href="search_uploads.php" class="btn btn-outline-primary sitelink">Search uploaded files</a>'; // update lang
        }

    }

    /*
    Head moderator access level or bigger
    */

    if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102 || $_SESSION['permissions'] == 103) {

        echo '<hr>';

        echo '<a href="addban.php" class="btn btn-outline-primary sitelink">' . $localization->string('banunban') . '</a>';
        echo '<a href="banlist.php" class="btn btn-outline-primary sitelink">' . $localization->string('banlist') . '</a>';

    } 

    /*
    Administrator access level or bigger
    */

    if ($users->is_administrator()) {

        echo '<hr>';

        if (file_exists('forumadmin.php')) {
	        echo '<a href="forumadmin.php?action=fcats" class="btn btn-outline-primary sitelink">' . $localization->string('forumcat') . '</a>';
	        echo '<a href="forumadmin.php?action=forums" class="btn btn-outline-primary sitelink">' . $localization->string('forums') . '</a>';
        }
        if (file_exists('gallery/manage_gallery.php')) {
            echo'<a href="gallery/manage_gallery.php" class="btn btn-outline-primary sitelink">' . $localization->string('gallery') . '</a>';
        } 
        if (file_exists('votes.php')) {
            echo'<a href="votes.php" class="btn btn-outline-primary sitelink">' . $localization->string('pools') . '</a>';
        }
        if (file_exists("antiword.php")) {
        	echo '<a href="antiword.php" class="btn btn-outline-primary sitelink">' . $localization->string('badword') . '</a>';
        }

        echo '<a href="statistics.php" class="btn btn-outline-primary sitelink">' . $localization->string('statistics') . '</a>';
        echo '<a href="users.php" class="btn btn-outline-primary sitelink">' . $localization->string('mngprof') . '</a>';

    }

    if (file_exists('news.php') && ($users->is_administrator() || $users->check_permissions('news', 'show'))) {
        echo '<a href="news.php" class="btn btn-outline-primary sitelink">' . $localization->string('sitenews') . '</a>';
    } 

    if (file_exists('files.php') && ($users->is_administrator() || $users->check_permissions('pageedit'))) {
        echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $localization->string('mngpage') . '</a>';
    }

    /*
    Head administrator access level
    */

    if ($users->is_administrator(101)) {

        echo '<hr>';

        echo '<a href="settings.php" class="btn btn-outline-primary sitelink">' . $localization->string('syssets') . '</a>';
        echo '<a href="ban.php" class="btn btn-outline-primary sitelink">' . $localization->string('ipbanp') . ' (' . $vavok->counter_string(BASEDIR . 'used/ban.dat') . ')</a>';
        if (file_exists('subscribe.php')) {
            echo '<a href="subscribe.php" class="btn btn-outline-primary sitelink">' . $localization->string('subscriptions') . '</a>';
        } 
        echo '<a href="index.php?action=sysmng" class="btn btn-outline-primary sitelink">' . $localization->string('sysmng') . '</a>';
        if (file_exists('logfiles.php')) {
            echo '<a href="logfiles.php" class="btn btn-outline-primary sitelink">' . $localization->string('logcheck') . '</a>';
        }
        if (file_exists('email-queue.php')) {
            echo '<a href="email-queue.php" class="btn btn-outline-primary sitelink">Add to email queue</a>';
        } 
    }

}

if ($action == 'clear' && $users->is_administrator(101)) {

	echo '<p>';
	if (file_exists('delusers.php')) {
    	echo '<a href="delusers.php" class="btn btn-outline-primary sitelink">' . $localization->string('cleanusers') . '</a>';
	}
    echo '<a href="./?action=clrmlog" class="btn btn-outline-primary sitelink">' . $localization->string('cleanmodlog') . '</a>';

	echo '</p>';

} 

if ($action == "clrmlog" && $users->is_administrator(101)) {
    $sql = "DELETE FROM mlog";
    $db->query($sql);

    echo '<p><img src="../images/img/open.gif" alt="" /> ' . $localization->string('mlogcleaned') . '</p>';

} 

if ($action == "sysmng" && $users->is_administrator(101)) {
    echo '<p>';
    echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $localization->string('chksystem') . '</a>';
    echo '<a href="./?action=clear" class="btn btn-outline-primary sitelink">' . $localization->string('cleansys') . '</a>';
    if (file_exists('backup.php')) {
        echo '<a href="backup.php" class="btn btn-outline-primary sitelink">' . $localization->string('backup') . '</a>';
    }
    echo '<a href="serverbenchmark.php" class="btn btn-outline-primary sitelink">Server benchmark</a>';
    // update
    // echo '<a href="index.php?action=opttbl">Optimize tables</a>'; // update lang
    echo '</p>';
    
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
}

// check vavok cms version
if ($action == 'version') {
$version = $vavok_version;
$key = 'checkver'; // key to save cache with 
// get cached data from file cache, also check if cached data is not old

    echo '<div class="b">Vavok CMS ' . $localization->string('version') . ': <b>' . $vavok_version . '</b>';

    if ($version != $last_ver && !empty($last_ver)) {
        echo '<p><img src="../images/img/close.gif" alt="" /> ' . $localization->string('newver') . '!</p>';
        echo '<p><img src="../images/img/reload.gif" alt="" /> Latest version: ' . $last_ver . '</p>';
        echo '<a href="index.php?action=refver" class="btn btn-outline-primary sitelink">Refresh</a>';
    } else {
        echo '<p><img src="../images/img/reload.gif" alt=""> ' . $localization->string('hnewver') . '!</p>';
    } 

    echo '</div>';

}

if (!empty($action)) {
	echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a></p>';
}

echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>