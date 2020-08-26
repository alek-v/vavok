<?php
/*
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Package:   Class for managing pages
* Updated:   12.08.2020. 18:40:37
*/

class Page {
	public $page_name;         // Page name
	public $page_language;     // Page language
	public $page_title;        // Title
	public $page_content;      // Content
	public $published;         // Visitors can see page
	public $page_author;       // Page author
	public $page_created_date; // Page created date
	public $head_tags;         // Head tags
	private $vavok;
	private $users;

	/**
	 * @param string $page_name
	 * @param string $page_language
	 */
	function __construct($page_name = '', $page_language = '') {
		global $db, $users, $vavok;

		$this->table_prefix = DB_PREFIX; // Table prefix
		$this->transfer_protocol = $vavok->transfer_protocol(); // Transfer protocol
		$this->db = $db; // database
		$this->vavok = $vavok;
		$this->users = $users;
		$this->user_id = $users->current_user_id(); // User id with active login
		if (isset($_GET['pg'])) $this->page_name = $this->vavok->check($_GET['pg']); // Requested page name
		if (isset($_GET['ln']) || $_SERVER['PHP_SELF'] == '/index.php') { $this->page_name = 'index'; }
		if (isset($_GET['ln'])) $this->page_language = $this->vavok->check($_GET['ln']);
		if (isset($_GET['pg'])) $this->page_language = $this->get_page_language($this->vavok->check($_GET['pg']));

		// Update user's language
		if (!empty($this->page_language) && strtolower($this->page_language) != $users->get_prefered_language($_SESSION['lang'], 'short')) { $users->change_language(strtolower($this->page_language)); }

		// Load main page
		if ($this->page_name == 'index') {

			// Redirect to root dir if visitor is using site default language and this language is requested in url
			// Example: default website language is english and user is opening www.example.com/en/
			if (isset($_GET['ln']) && $this->vavok->get_configuration('siteDefaultLang') == $this->users->get_prefered_language($this->page_language) && !empty($this->users->get_prefered_language($this->page_language)) && file_exists(BASEDIR . "include/lang/" . $this->users->get_prefered_language($this->page_language) . "/index.php")) {
				$this->vavok->redirect_to(HOMEDIR);
			}

			/*
			  Redirect if user's language is not website default language,
			  language is not in URL (www.example.com/en/)
			  and page with users's language exists
			*/
			if ($vavok->get_configuration('siteDefaultLang') != $this->users->get_user_language() && empty($_GET['ln'])) {
				$vavok->redirect_to("/" .  $this->users->get_prefered_language($users->get_user_language(), 'short') . "/");
			}

		}

	    /**
	     * Show website maintenance page
	     */
	    if ($this->vavok->get_configuration('siteOff') == 1 && !strstr($_SERVER['PHP_SELF'], 'pages/maintenance.php') && !strstr($_SERVER['PHP_SELF'], 'pages/input.php') && !$this->users->is_administrator() && !strstr($_SERVER['PHP_SELF'], 'pages/login.php')) {
	        $this->vavok->redirect_to($this->vavok->website_home_address() . "/pages/maintenance.php");
	    }

		$this->head_tags = $this->get_head_tags();
		$this->load_page();
		$this->page_title = $this->page_title();

	}

	/**
	 * Update, insert and delete informations
	 */

	// insert new page
	function insert($values) {
		$this->db->insert_data($this->table_prefix . 'pages', $values);
	}

	// delete page
	function delete($id) {
		$this->db->delete($this->table_prefix . 'pages', "id='{$id}'");
	}

	/**
	 * Update page tags
	 *
	 * @param integer $id
	 * @param string $tags
	 * @return void
	 */
	public function update_tags($id, $tags)
	{
		/**
		 * Delete current tags
		 */
		$this->db->delete($this->table_prefix . 'tags', "page_id = '{$id}'");

		/**
		 * Insert new tags
		 */
		if (substr_count($tags, ' ') == 0) $tags = array($tags); 
		else { $tags = explode(' ', $tags); }

		foreach ($tags as $key => $value) {
			$values = array(
				'page_id' => $id,
				'tag_name' => $value
			);
			$this->db->insert_data($this->table_prefix . 'tags', $values);
		}
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

	/**
	 * Update cached file
	 *
	 * @param string $file
	 * @param string $content
	 * @return bool
	 */
	function updateCached($file, $content)
	{
		$this->vavok->write_data_file('datamain/' . $file, $content);
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
	 * Read data
	 */

	// Show page content
	public function show_page()
	{
		echo $this->page_content;
	}

	// return total number of pages
	public function total_pages($creator = '') {
		$where = '';

		if (!empty($creator)) {
			$where = " WHERE crtdby = '{$creator}'";
		}

		return $this->db->count_row($this->table_prefix . "pages" . $where);
	}

	// select page by id - get page data
	public function select_page($id , $fields = '*')
	{
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
		$page_data = $this->db->get_data(DB_PREFIX . 'pages', "pname='" . $this->page_name . "'{$language}");

		// When language is set and page does not exsist try to find page without language
		if (empty($page_data) && !empty($this->page_language)) {
			$page_data = $this->db->get_data(DB_PREFIX . 'pages', "pname='" . $this->page_name . "'");
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

			// Head tags
			$this->head_tags = $this->append_head_tags($page_data['headt']);

			return $page_data;

		}
	}

	/**
	 * Return page header tags
	 */
	public function load_head_tags() {

		// include head tags specified for current page
		echo $this->head_tags;

		echo "\r\n<!-- Vavok CMS http://www.vavok.net -->
		<title>{$this->page_title}</title>\r\n";

	}

	/**
	 * Return page language
	 *
	 * @param string $page
	 * @return bool|string
	 */
	private function get_page_language($page)
	{
		$lang = $this->db->get_data(DB_PREFIX . 'pages', "pname = '{$page}'", 'lang')['lang'];
		if (empty($lang)) return false;
		return $lang;
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
		$r = preg_replace('/&page=(\d+)/', '', CLEAN_REQUEST_URI);
		$r = preg_replace('/page=(\d+)/', '', $r);
		$r = str_replace('&page=last', '', $r);
		$r = str_replace('page=last', '', $r);

		// remove language dir from main page
		$r = str_replace('/en/', '', $r);
		$r = str_replace('/sr/', '', $r);

		// remove index.php from urls to remove double content
		$r = str_replace('/index.php', '/', $r);

		if (empty($website)) { $website = $this->vavok->website_home_address(); }

		// return url
		return $website . $r;
	}

	/**
	 * Get title for page
	 *
	 * @return string
	 */
	public function page_title() {
		if (!empty($this->page_title)) { return $this->page_title; }

	    $page_title = $this->db->get_data(DB_PREFIX . 'pages', "pname='" . trim($_SERVER['PHP_SELF'], '/') . "'", 'tname')['tname'];

	    if (!empty($page_title)) {
	        return $page_title;
	    } else {
	        return $this->vavok->get_configuration('title');
	    }
	}

	/**
	 * Page tags (keywords)
	 *
	 * @param integer $id
	 * @return string
	 */
	public function page_tags($id)
	{
		$tags = '';

		$sql = $this->db->query("SELECT * FROM tags WHERE page_id = '{$id}' ORDER BY id ASC");
		foreach ($sql as $key => $value) {
			$tags .= ' ' . $value['tag_name'];
		}
		return trim($tags);
	}

	/**
	 * Head tags for all pages
	 *
	 * @param string $tags
	 * @return string
	 */
	private function get_head_tags()
	{
		$tags = file_get_contents(BASEDIR . 'used/headmeta.dat');

        $vk_page = $this->db->get_data(DB_PREFIX . 'pages', "pname='" . trim($_SERVER['PHP_SELF'], '/') . "'");
        if (!empty($vk_page['headt'])) { $tags .= $vk_page['headt']; }

		/**
		 * Tell bots what is our preferred page
		 */
		if (!stristr($tags, 'rel="canonical"') && isset($_GET['pg'])) { $tags .= "\n" . '<link rel="canonical" href="' . $this->vavok->transfer_protocol() . $_SERVER['HTTP_HOST'] . '/page/' . $_GET['pg'] . '/" />'; }

		/**
		 * Add missing open graph tags
		 */
		if (!strstr($tags, 'og:type')) { $tags .= "\n" . '<meta property="og:type" content="website" />'; }

		if (!strstr($tags, 'og:title') && !empty($this->page_title) && $this->page_title != $this->vavok->get_configuration('title')) { $tags .= "\n" . '<meta property="og:title" content="' . $this->page_title . '" />'; }

		return $tags;
	}

	/**
	 * Append head tags
	 *
	 * @param string $tags
	 * @return void
	 */
	public function append_head_tags($tags) {
		return $this->head_tags .= $tags;
	}

}

?>