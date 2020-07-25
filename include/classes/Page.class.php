<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Package:   Class for managing pages
* Updated:   24.07.2020. 13:30:11
*/

class Page {

	public $page_name; // Page name
	public $page_language; // Page language
	public $page_title; // Title
	public $page_content; // Content
	public $published; // Visitors can see page
	public $page_author; // Page author
	public $page_created_date; // Page created date

	// class constructor
	function __construct($page_name = '', $page_language = '') {
		global $db, $users;

		$this->table_prefix = get_configuration('tablePrefix'); // Table prefix
		$this->transfer_protocol = transfer_protocol(); // Transfer protocol
		$this->db = $db; // database
		$this->user_id = $users->current_user_id(); // User id with active login
		$this->page_name = $page_name;
		$this->page_language = $page_language;

		if (empty($page_title)) { $this->page_title = get_configuration('title'); /* Page title */ }
	}

	/*
	Update, insert and delete informations
	*/

	// insert new page
	function insert($values) {
		$this->db->insert_data($this->table_prefix . 'pages', $values);
	}

	// delete page
	function delete($id) {
		$this->db->delete($this->table_prefix . 'pages', "id='{$id}'");
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
		$file = $this->db->get_data($this->table_prefix . 'pages', "id = '{$id}'", 'file')['file'];
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

	    $this->db->update($this->table_prefix . 'pages', $fields, $values, "`id`='{$id}'");
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
	    $this->db->query("UPDATE " . $this->table_prefix . "pages SET lang='" . $lang . "', file='" . $pageData['pname'] . "!." . $lang . "!.php' WHERE `id`='" . $id . "'");
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

	/*
	Read data
	*/

	// Show page content
	public function show_page() {

		echo $this->page_content;

	}

	// return total number of pages
	function total_pages($creator = '') {
		$where = '';

		if (!empty($creator)) {
			$where = " WHERE crtdby = '{$creator}'";
		}

		return $this->db->count_row($this->table_prefix . "pages" . $where);
	}

	// select page by id - get page data
	function select_page($id , $fields = '*') {

		// update visitor counter if page is vewed by visitor
		if (stristr($_SERVER['PHP_SELF'], '/pages/pages.php') || stristr($_SERVER['PHP_SELF'], '/pages/blog.php')) {

			$this->db->query("UPDATE {$this->table_prefix}pages SET views = views + 1 WHERE id = '{$id}'");
		}

		// return page data
		return $this->db->get_data($this->table_prefix . 'pages', "id='{$id}'", $fields);

	}

	// Load page
	public function load_page() {
		// Load page with requested language
		$language = !empty($this->page_language) ? " AND lang='" . $this->page_language . "'" : '';

		// Load main page only from main page
		if (isset($_GET['pg']) && $_GET['pg'] == 'index') return false;

		// Update visitor counter if page is vewed by visitor
		if (stristr($_SERVER['PHP_SELF'], '/pages/pages.php') || stristr($_SERVER['PHP_SELF'], '/pages/blog.php')) {

			$this->db->query("UPDATE {$this->table_prefix}pages SET views = views + 1 WHERE pname = '" . $this->page_name . "'{$language}");

		}

		// Get data
		$page_data = $this->db->get_data(get_configuration('tablePrefix') . 'pages', "pname='" . $this->page_name . "'{$language}");

		// When language is set and page does not exsist try to find page without language
		if (empty($page_data) && !empty($this->page_language)) {
			$page_data = $this->db->get_data(get_configuration('tablePrefix') . 'pages', "pname='" . $this->page_name . "'");
		}

		// return false if there is no data
		if (empty($page_data['tname']) && empty($page_data['content'])) {
			return false;
		} else {
			// Update page title
			$this->page_title = $page_data['tname'];

			// Update language
			if (!empty($page_data['lang']) && !defined('PAGE_LANGUAGE')) define('PAGE_LANGUAGE', ' lang="' . $page_data['lang'] . '"');

			// Page content
			$this->page_content = $page_data['content'];

			// Published
			$this->published = $page_data['published'];

			// Published
			$this->page_id = $page_data['id'];

			// Published
			$this->page_author = $page_data['crtdby'];

			// Created date
			$this->page_created_date = $page_data['created'];

			return $page_data;

		}
	}

	// check if page exists
	function page_exists($file = '', $where = '') {
		if (!empty($file) && $this->db->count_row($this->table_prefix . 'pages', "file='{$file}'") > 0) {
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

	// load page editor program
	function loadPageEditor () {
		// load page editor
		$pageEditor = @file_get_contents(BASEDIR . "/include/plugins/tinymce/tinymce.php");

		// set base dir
		$pageEditor = str_replace('{@BASEDIR}', BASEDIR, $pageEditor);

		return $pageEditor;
	}

	// url for facebook share, twitter etc to prevent duplicated url's
	public function media_page_url() {

		// Clean up request
		$r = preg_replace('/&page=(\d+)/', '', $_SERVER['HTTP_HOST']);
		$r = preg_replace('/page=(\d+)/', '', $r);
		$r = str_replace('&page=last', '', $r);
		$r = str_replace('page=last', '', $r);

		// remove language dir from main page
		$r = str_replace('/en/', '', $r);
		$r = str_replace('/sr/', '', $r);

		// remove index.php from urls to remove double content
		$r = str_replace('/index.php', '/', $r);

		if (empty($website)) { $website = website_home_address(); }

		// return url
		return $website . $r;
	}

	// get title for page
	public function page_title() {
	    $page_title = $this->db->get_data('pages', "pname='" . $_SERVER['PHP_SELF'] . "'", 'tname')['tname'];

	    if (!empty($page_title)) {
	        $title = $page_title;
	    } else {
	        $title = '';
	    }

	    return $title;
	}


}
?>