<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->check_permissions('adminpanel', 'show')) $vavok->redirect_to('../?auth_error');

if ($vavok->post_and_get('action') == 'refver') {
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

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('admpanel');
$vavok->require_header();
 
if (empty($vavok->post_and_get('action'))) {

	/*
	Moderator access level or bigger
	*/

    echo $vavok->sitelink('adminchat.php', $vavok->go('localization')->string('admchat'));
    echo $vavok->sitelink('adminlist.php', $vavok->go('localization')->string('modlist'));
    echo $vavok->sitelink('reglist.php', $vavok->go('localization')->string('notconf'));
    echo $vavok->sitelink('../pages/userlist.php', $vavok->go('localization')->string('userlist') . ' (' . $vavok->go('users')->regmemcount() . ')');

    /*
    Super moderator access level or bigger
    */

    if ($vavok->go('users')->is_moderator(103) || $vavok->go('users')->is_moderator(105) || $vavok->go('users')->is_administrator()) {

    	if (file_exists('reports.php')) echo $vavok->sitelink('reports.php', $vavok->go('localization')->string('usrcomp'));

        if (file_exists('upload.php')) {
            echo $vavok->sitelink('upload.php', $vavok->go('localization')->string('upload'));
            echo $vavok->sitelink('uploaded_files.php', $vavok->go('localization')->string('uplFiles'));
            echo $vavok->sitelink('search_uploads.php', 'Search uploaded files');
        }

    }

    /*
    Head moderator access level or bigger
    */

    if ($vavok->go('users')->is_administrator() || $vavok->go('users')->is_moderator(103)) {
        echo '<hr>';
        echo $vavok->sitelink('addban.php', $vavok->go('localization')->string('banunban'));
        echo $vavok->sitelink('banlist.php', $vavok->go('localization')->string('banlist'));
    } 

    /*
    Administrator access level or bigger
    */

    if ($vavok->go('users')->is_administrator()) {
        echo '<hr>';

        if (file_exists('forumadmin.php')) {
            echo $vavok->sitelink('forumadmin.php?action=fcats', $vavok->go('localization')->string('forumcat'));
            echo $vavok->sitelink('forumadmin.php?action=forums', $vavok->go('localization')->string('forums'));
        }

        if (file_exists('gallery/manage_gallery.php')) echo $vavok->sitelink('gallery/manage_gallery.php', $vavok->go('localization')->string('gallery'));

        if (file_exists('votes.php')) echo $vavok->sitelink('votes.php', $vavok->go('localization')->string('pools'));

        if (file_exists('antiword.php')) echo $vavok->sitelink('antiword.php', $vavok->go('localization')->string('badword'));

        echo $vavok->sitelink('statistics.php', $vavok->go('localization')->string('statistics'));
        echo $vavok->sitelink('users.php', $vavok->go('localization')->string('mngprof'));
    }

    if (file_exists('news.php') && ($vavok->go('users')->is_administrator() || $vavok->go('users')->check_permissions('news', 'show'))) {
        echo $vavok->sitelink('news.php', $vavok->go('localization')->string('sitenews'));
    } 

    if (file_exists('files.php') && ($vavok->go('users')->is_administrator() || $vavok->go('users')->check_permissions('pageedit'))) {
        echo $vavok->sitelink('files.php', $vavok->go('localization')->string('mngpage'));
    }

    /*
    Head administrator access level
    */

    if ($vavok->go('users')->is_administrator(101)) {
        echo '<hr>';

        echo $vavok->sitelink('settings.php', $vavok->go('localization')->string('syssets'));
        echo $vavok->sitelink('ban.php', $vavok->go('localization')->string('ipbanp') . ' (' . $vavok->counter_string(BASEDIR . 'used/ban.dat') . ')');
        
        if (file_exists('subscribe.php')) echo $vavok->sitelink('subscribe.php', $vavok->go('localization')->string('subscriptions'));
        
        echo $vavok->sitelink('index.php?action=sysmng', $vavok->go('localization')->string('sysmng'));
        
        if (file_exists('logfiles.php')) echo $vavok->sitelink('logfiles.php?action=sysmng', $vavok->go('localization')->string('logcheck'));
        
        if (file_exists('email-queue.php')) echo $vavok->sitelink('email-queue.php', 'Add to email queue');
    }

}

if ($vavok->post_and_get('action') == 'clear' && $vavok->go('users')->is_administrator(101)) {
	echo '<p>';
	if (file_exists('delusers.php')) echo $vavok->sitelink('delusers.php', $vavok->go('localization')->string('cleanusers'));
    echo $vavok->sitelink('./?action=clrmlog', $vavok->go('localization')->string('cleanmodlog'));
	echo '</p>';
}

if ($vavok->post_and_get('action') == 'clrmlog' && $vavok->go('users')->is_administrator(101)) {
    $vavok->go('db')->query("DELETE FROM mlog");

    echo '<p><img src="../themes/images/img/open.gif" alt="" /> ' . $vavok->go('localization')->string('mlogcleaned') . '</p>';
} 

if ($vavok->post_and_get('action') == "sysmng" && $vavok->go('users')->is_administrator(101)) {
    echo '<p>';
    echo $vavok->sitelink('systems.php', $vavok->go('localization')->string('chksystem'));
    echo $vavok->sitelink('./?action=clear', $vavok->go('localization')->string('cleansys'));

    if (file_exists('backup.php')) echo $vavok->sitelink('backup.php', $vavok->go('localization')->string('backup'));

    echo $vavok->sitelink('serverbenchmark.php', 'Server benchmark'); // update lang
    echo '</p>';
}

if ($vavok->post_and_get('action') == "opttbl" && $vavok->go('users')->is_administrator(101)) {
    $alltables = mysqli_query("SHOW TABLES");

    while ($table = mysqli_fetch_assoc($alltables)) {
        foreach ($table as $db => $tablename) {
            $sql = "OPTIMIZE TABLE `" . $tablename . "`";
            $vavok->go('db')->query($sql);
        } 
    } 

    echo '<p><img src="../themes/images/img/reload.gif" alt="" /> Optimized successfully!</p>'; // update lang
}

// check vavok cms version
if ($vavok->post_and_get('action') == 'version') {
$version = $vavok_version;
$key = 'checkver'; // key to save cache with 
// get cached data from file cache, also check if cached data is not old

    echo '<div class="b">Vavok CMS ' . $vavok->go('localization')->string('version') . ': <b>' . $vavok_version . '</b>';

    if ($version != $last_ver && !empty($last_ver)) {
        echo '<p><img src="../themes/images/img/close.gif" alt="" /> ' . $vavok->go('localization')->string('newver') . '!</p>';
        echo '<p><img src="../themes/images/img/reload.gif" alt="" /> Latest version: ' . $last_ver . '</p>';
        echo $vavok->sitelink('index.php?action=refver', 'Refresh');
    } else {
        echo '<p><img src="../themes/images/img/reload.gif" alt=""> ' . $vavok->go('localization')->string('hnewver') . '!</p>';
    } 

    echo '</div>';
}

if (!empty($vavok->post_and_get('action'))) echo $vavok->sitelink('./', $vavok->go('localization')->string('admpanel'), '<p>', '</p>');

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>