<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!$users->is_reg() || !$users->is_administrator()) {
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

        $where_table = "pages";
        $cond = "pname";
        $select_fields = "*";
        $ord_fields = "pubdate DESC";

        $noi = $db->count_row($where_table, "" . $cond . " LIKE '%" . $stext . "%'");
        $items_per_page = 10;

        $navigation = new Navigation($items_per_page, $noi, $page, 'filesearch.php?'); // start navigation

        $limit_start = $navigation->start()['start']; // starting point

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

        echo $navigation->get_navigation();

    } 

    echo '<a href="filesearch.php" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br />';
    echo '<a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br />';
    echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br />';

    include"../themes/$config_themes/foot.php";
    exit;
} 

?>