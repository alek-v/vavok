<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 19:24:24
*/

require_once"../include/startup.php";

$act = '';
if (isset($_GET['act'])) {
    $act = $vavok->check($_GET['act']);
} elseif (isset($_POST['act'])) {
	$act = $vavok->check($_POST['act']);
}

if (!$users->is_administrator() || !$users->is_reg()) {
    $vavok->redirect_to("../?error");
} 

if ($act == "addedit") {

    $tfile = $vavok->check($_POST['tfile']);
    $msg = $vavok->no_br($_POST['msg']);

    // get page data
    $pageData = $db->get_data(DB_PREFIX . 'pages', "file='{$tfile}'", 'file, headt');

    $headData = $pageData['headt'];

    // remove old open graph title title and set new
    if (stripos($headData, 'property="og:title" content="')) {
    $start = stripos($headData, '<meta property="og:title"');
    for ($i = $start;$i < strlen($headData);$i++) {
        $currentChar = $headData[$i];
        $headData[$i] = '~';

        if ($currentChar == '>')
        break;
        }
    }

    $inputPosition = $start;
    $headData = str_replace('~', '', $headData);
    $headData = substr_replace($headData, '<meta property="og:title" content="' . $msg . '" />', $inputPosition, 0);

    $fields = array('tname', 'headt');
    $values = array($msg, $headData);
    $db->update(DB_PREFIX . 'pages', $fields, $values, "file='{$tfile}'");


    $vavok->redirect_to("files.php?action=edit&file=" . $pageData['file'] . "&isset=savedok");

} 

if ($act == "savenew") {
    $tpage = $vavok->check($_POST['tpage']);
    $tpage = strtolower($tpage);
    $tpage = str_replace(' ', '-', $tpage);

    $msg = $vavok->no_br($_POST['msg']);

    $last_notif = $db->get_data(DB_PREFIX . 'pages', "pname='" . $tpage . "'", '`tname`, `pname`, `file`, `headt`');

    $headData = $last_notif['headt'];

    // remove old open graph title title and set new
    if (stripos($headData, 'property="og:title" content="')) {
    $start = stripos($headData, '<meta property="og:title"');
    for ($i = $start;$i < strlen($headData);$i++) {
        $currentChar = $headData[$i];
        $headData[$i] = '~';

        if ($currentChar == '>')
        break;
        }
    }

    $inputPosition = $start;
    $headData = str_replace('~', '', $headData);
    $headData = trim(substr_replace($headData, '<meta property="og:title" content="' . $msg . '" />', $inputPosition, 0));

    // no data in database, insert data
    if (empty($last_notif['tname'] && $last_notif['pname'] && $last_notif['file'])) {
        $values = array(
            'pname' => $tpage,
            'tname' => $msg,
            'file' => $tpage
        );
        $db->insert_data(DB_PREFIX . 'pages', $values);

        $PBPage = false;
    } else {
        $fields = array('tname', 'headt');
        $values = array($msg, $headData);
        $db->insert_data(DB_PREFIX . 'pages', $fields, $values, "pname='" . $tpage . "'");

        $PBPage = true;
    } 

    $vavok->redirect_to("pgtitle.php?isset=savedok");

} 

if ($act == "del") {
    $tid = $vavok->check($_GET['tid']);

    $db->delete(DB_PREFIX . 'pages', "pname = '" . $tid . "'");

    $vavok->redirect_to("pgtitle.php");
} 

require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

 

if (!isset($act) || empty($act)) {

    $nitems = $db->count_row(DB_PREFIX . 'pages');
    $total = $nitems;

    if ($total < 1) {
        echo '<br /><img src="../images/img/reload.gif" alt=""> <b>Page titles not found!</b><br />';
    } 

    if (isset($_GET['page'])) {
        $page = $vavok->check($_GET['page']);
    } else { $page = ''; }

    $nitems = $db->count_row(DB_PREFIX . 'pages', 'tname is not null');
    $num_items = $nitems;

    $items_per_page = 30;


    $navigation = new Navigation($items_per_page, $num_items, $page, 'pgtitle.php?'); // start navigation

    $limit_start = $navigation->start()['start']; // starting point

    $sql = "SELECT id, pname, tname, file FROM " . DB_PREFIX . "pages WHERE tname is not null ORDER BY pname LIMIT $limit_start, $items_per_page";

    if ($num_items > 0) {
        foreach ($db->query($sql) as $item) {
            $lnk = $item['pname'] . " <img src=\"../images/img/edit.gif\" alt=\"\" /> <a href=\"pgtitle.php?act=edit&amp;pgfile=" . $item['file'] . "\">" . $item['tname'] . "</a> | <img src=\"../images/img/edit.gif\" alt=\"\" /> <a href=\"files.php?action=headtag&amp;file=" . $item['file'] . "\">[Edit Meta]</a> | <img src=\"../images/img/close.gif\" alt=\"\" /> <a href=\"pgtitle.php?act=del&amp;tid=" . $item['pname'] . "\">[DEL]</a>"; 
            // echo " <small>joined: $jdt</small>";
            echo "$lnk<br />";
        } 
    } 

    echo $navigation->get_navigation();

    echo '<br /><br /><a href="pgtitle.php?act=addnew" class="btn btn-outline-primary sitelink">Add new title</a><br /><br />'; // update lang
} 

if ($act == "edit") {
    $pgfile = $vavok->check($_GET['pgfile']);

    $page_title = $db->get_data(DB_PREFIX . 'pages', "file='" . $pgfile . "'", 'tname, pname');

    echo '<form action="pgtitle.php?act=addedit" method="POST">';
    echo '<input type="hidden" name="tfile" value="' . $pgfile . '"><br />';
    echo 'Page title:<br />'; // update lang
    echo '<textarea cols="50" rows="3" name="msg">' . $page_title['tname'] . '</textarea><br />';

    echo '<br /><input type="submit" value="' . $localization->string('save') . '"></form><hr>';

    echo '<br /><a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a><br />';
} 

if ($act == "addnew") {
    echo '<form action="pgtitle.php?act=savenew" method="POST">';
    echo 'Page: <input type="text" name="tpage" value=""><br />'; // update lang
    echo 'Page title: <input type="text" name="msg" value=""><br />';

    echo '<br /><input type="submit" value="' . $localization->string('save') . '"></form><hr>';

    echo '<br /><a href="pgtitle.php" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a><br />';
}

echo '<a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br />';
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>
