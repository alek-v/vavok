<?php
// (c) vavok.net
class Page {

// update page
function update($file, $content) {
	global $db, $user_id;

	// update page
    $fields[] = 'content';
    $fields[] = 'lastupd';
    $fields[] = 'lstupdby';

    $values[] = $content;
    $values[] = time();
    $values[] = $user_id;

    $db->update('pages', $fields, $values, "`file`='" . $file . "'");

	// update cached index page
	if (preg_match('/^index(?:!\.[a-z]{2}!)?\.php$/', $file) || preg_match('/^menu_slider(?:!\.[a-z]{2}!)?\.php$/', $file) || preg_match('/^site-menu(?:!\.[a-z]{2}!)?\.php$/', $file)) {
		$this->updateCached($file, $content);
	}

	return true;
}

// update cache file
function updateCached($file, $content) {
	$fileLocation = BASEDIR . 'used/datamain/' . $file;

	file_put_contents($fileLocation, $content, LOCK_EX);

	return true;
}

// rename page
function rename($newName, $file) {
	global $db;

	// set page name
	$pageName = str_replace('.php', '', $newName); // page name (without extension (.php))
    // remove language data from page name
    if (stristr($pageName, '!.')) {
        $pageName = preg_replace("/(.*)!.(.*)!/", "$1", $pageName);
    } 

    // update database
    $fields[] = 'pname';
    $fields[] = 'file';

    $values[] = $pageName;
    $values[] = $newName;

    $db->update('pages', $fields, $values, "`file`='" . $file . "'");
}

function currentPage($total_pages = 1) {
	$page = 1;
	if (isset($_GET['page'])) {
	if ($_GET['page'] == 'end') $page = intval($total_pages);
	else if (is_numeric($_GET['page'])) $page = intval($_GET['page']);
	}
	if ($page < 1) $page = 1;
	if ($page > $total_pages) $page = $total_pages;
	return $page;
}

function totalPages($total = 0, $limit = 10) {
	if ($total != 0) {
	$v_pages = ceil($total / $limit);
	return $v_pages;
	}
	else return 1;
}

function navStart($total, $limit) {
	global $total_pages, $page, $limit_start;

	$total_pages = $this->totalPages($total, $limit);
	$page = $this->currentPage($total_pages);
	$limit_start = $limit * $page - $limit;
}

}
?>