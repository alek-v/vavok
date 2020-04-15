<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!is_reg() || !isadmin()) {
    header("Location: ./");
    exit;
} 

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (empty($action)) {
    $action = 'tpc';
} 
if (!empty($_GET['page'])) {
    $page = check($_GET["page"]);
} else {
    $page = '';
} 

if ($action == "tpc") {
    $my_title = 'Search';
    include"../themes/$config_themes/index.php";

    echo'<form action="filesearch.php?action=stpc" method="POST">';
    echo 'Page name:<br><input name="stext" maxlength="30" /><br>';
    echo "<br>";
    echo '<input type="submit" value="Search"></form><br><br>';

    echo '<a href="files.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
    echo '<a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br />';
    echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br />';

    include"../themes/$config_themes/foot.php";
} else if ($action == "stpc") {
    $stext = check($_POST["stext"]);
    $my_title = 'Search';
    include"../themes/$config_themes/index.php";

    if (empty($stext)) {
        echo "<br>Please fill all fields";
    } else {
        // begin search
        if (empty($page) || $page < 1) {
            $page = 1;
        } 

        $where_table = "pages";
        $cond = "pname";
        $select_fields = "*";
        $ord_fields = "pubdate DESC";

        $noi = $db->count_row($where_table, "" . $cond . " LIKE '%" . $stext . "%'");
        $num_items = $noi;
        $items_per_page = 10;
        $num_pages = ceil($num_items / $items_per_page);
        if (($page > $num_pages) && $page != 1)$page = $num_pages;
        $limit_start = ($page-1) * $items_per_page;
        if ($limit_start < 0) {
            $limit_start = 0;
        } 

        $sql = "SELECT " . $select_fields . " FROM " . $where_table . " WHERE pname LIKE '%" . $stext . "%' OR tname LIKE '%" . $stext . "%' ORDER BY " . $ord_fields . " LIMIT $limit_start, $items_per_page";

        foreach ($db->query($sql) as $item) {
            $tname = $item['tname'];
            if (empty($tname)) {
                $tname = $item['pname'];
            } 
            if (empty($item['file'])) {
            	$item['file'] = $item['pname'] . '.php';
            }
            if ($tname == "") {
                $tlink = "Unreachable<br>";
            } else {
            	if (!empty($item['lang'])) {
            		$itemLang = '(' . $item['lang'] . ')'; } else {
            			$itemLang = ''; }
                $tlink = '<a href="files.php?action=show&amp;file=' . $item['file'] . '" class="btn btn-outline-primary sitelink">' . $tname . '</a> ' . $itemLang . '<br />';
            } 
            echo $tlink;
        } 

        page_navigation('filesearch.php?action=stpc&amp;', $items_per_page, $page, $num_items);
        page_numbnavig('filesearch.php?action=stpc&amp;', $items_per_page, $page, $num_items);
    } 

    echo '<a href="filesearch.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
    echo '<a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br />';
    echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br />';

    include"../themes/$config_themes/foot.php";
    exit;
} 

?>