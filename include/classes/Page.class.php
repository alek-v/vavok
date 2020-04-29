<?php
// (c) Aleksandar Vranešević - vavok.net
// class for managing pages
// updated 25.04.2020. 20:08:30


class Page {

	// class constructor
	function __construct() {
		global $db, $user_id;

		$this->table_prefix = get_configuration('tablePrefix'); // table prefix
		$this->transfer_protocol = transfer_protocol(); // transfer protocol
		$this->db = $db; // database
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
		$file = $this->db->get_data('pages', "id = '" . $id . "'", 'file')['file'];
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

		// update visitor counter if page is vewed by visitor
		if (stristr($_SERVER['PHP_SELF'], '/pages/pages.php') || stristr($_SERVER['PHP_SELF'], '/pages/blog.php')) {

			$this->db->query("UPDATE pages SET views = views + 1 WHERE id = '{$id}'");
		}

		// return page data
		return $this->db->get_data($this->table_prefix . 'pages', "id='{$id}'", $fields);


	}

	// check if page exists
	function page_exists($file = '', $where = '') {
		if (!empty($file) && $this->db->count_row($this->table_prefix . 'pages', "file='" . $file . "'") > 0) {
			return $this->get_page_id("file='" . $file . "'");
		} elseif (!empty($where) && ($this->db->count_row($this->table_prefix . 'pages', $where) > 0)) {
			return $this->get_page_id($where);
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

		// get database fields
        $fields = array_keys($data);

        // get data for fields
        $values = array_values($data);

        // update page data
        $this->db->update($this->table_prefix . "pages", $fields, $values, "id='{$id}'");
	}


	// load page editor program
	function loadPageEditor () {
		// load page editor
		$pageEditor = @file_get_contents(BASEDIR . "/include/plugins/tinymce/tinymce.php");

		// set base dir
		$pageEditor = str_replace('{@BASEDIR}', BASEDIR, $pageEditor);

		return $pageEditor;
	}

	// return url for facebook share, twitter etc
	function media_page_url($host, $request) {
		$r = preg_replace('/&page=(\d+)/', '', $request);
		$r = preg_replace('/page=(\d+)/', '', $r);
		$r = str_replace('&page=last', '', $r);
		$r = str_replace('page=last', '', $r);
		// remove language dir from main page
		$r = str_replace('/en/', '', $r);
		$r = str_replace('/sr/', '', $r);
		// remove index.php from urls to remove double content
		$r = str_replace('/index.php', '/', $r);

		// return url
		return $this->transfer_protocol . $host . $r;
	}

}
?>