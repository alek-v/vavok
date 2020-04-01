<?php
// (c) vavok.net
// class for managing pages

class Page {

	// class constructor
	function __construct() {
		global $db, $user_id;

		$this->table_prefix = getConfiguration('tablePrefix'); // table prefix
		$this->db = $db;
		$this->user_id = $user_id; // user id with active login
	}

	// update page
	function update($id, $content) {
	    $fields[] = 'content';
	    $fields[] = 'lastupd';
	    $fields[] = 'lstupdby';

	    $values[] = $content;
	    $values[] = time();
	    $values[] = $this->user_id;

	    $this->db->update($this->table_prefix . 'pages', $fields, $values, "`id`='" . $id . "'");

		// update cached index and menu pages
		// this pages must be cached other pages are not cached
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
	function rename($newName, $id) {
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

	    $this->db->update($this->table_prefix . 'pages', $fields, $values, "`id`='" . $id . "'");
	}

	// return total number of pages
	function total_pages($creator = '') {
		$where = '';

		if (!empty($creator)) {
			$where = " WHERE crtdby = '" . $creator . "'";
		}

		return $this->db->count_row($this->table_prefix . "pages" . $where);
	}

	// select page - get page data
	function select_page($id , $fields = '*') {
		return $this->db->get_data($this->table_prefix . 'pages', "id='" . $id . "'", $fields);
	}

	// check if page exists
	function page_exists($file = '', $where = '') {
		if (!empty($file) && $this->db->count_row($this->table_prefix . 'pages', "file='" . $file . "'") > 0) {
			return true;
		} elseif (!empty($where) && ($this->db->count_row($this->table_prefix . 'pages', $where) > 0)) {
			return true;
		} else {
			return false;
		}
	}

	// get page id
	function get_page_id($where) {
		return $this->db->get_data($this->table_prefix . 'pages', $where, 'id')['id'];
	}

	// insert new page
	function insert($values) {
		$this->db->insert_data($this->table_prefix . 'pages', $values);
	}

	// delete page
	function delete($id) {
		$this->db->delete('pages', "id='" . $id . "'");
	}

	// page visibility. publish or unpubilsh for visitors
	function visibility($id, $visibility) {
        $values = array($visibility, time());


        $fields = array('published', 'pubdate');

        $this->db->update($this->table_prefix . 'pages', $fields, $values, "id='" . $id . "'");
	}

	// update page language
	function language($id, $lang) {
		$pageData = $this->select_page($id);
	    // update database data
	    $this->db->query("UPDATE " . $this->table_prefix . "`pages` SET lang='" . $lang . "', file='" . $pageData['pname'] . "!." . $lang . "!.php' WHERE `id`='" . $id . "'");
	}

	// update head tags
	function head_data($id, $data) {
		$sql = "UPDATE " . $this->table_prefix . "`pages` SET `headt`= :data WHERE `id`='" . $id . "'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":data", trim($data), PDO::PARAM_INT);
        $stmt->execute();
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

	// load page editor program
	function loadPageEditor () {
		// load page editor
		$pageEditor = @file_get_contents(BASEDIR . "/include/plugins/tinymce/tinymce.php");

		// set base dir
		$pageEditor = str_replace('{@BASEDIR}', BASEDIR, $pageEditor);

		return $pageEditor;
	}

}
?>