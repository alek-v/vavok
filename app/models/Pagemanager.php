<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for managing pages
 */

class Pagemanager extends Controller {
	public $page_name;             // Page name
	public $page_language;         // Page language
	public $page_title;            // Title
	public $page_content;          // Content
	public $published;             // Visitors can see page
	public $page_author;           // Page author
	public $page_created_date;     // Page created date
	public $head_tags;             // Head tags
	public $page_published_date;   // Date when post is published
	protected object $db;

	/**
	 * @param string $page_name
	 * @param string $page_language
	 */
	function __construct($page_name = '', $page_language = '')
	{
		$this->db = new Database;

 		// User id with active login
		$this->user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
	}

	/**
	 * Update, insert and delete informations
	 */

	// insert new page
	function insert($values) {
		$this->db->insert('pages', $values);
	}

	// delete page
	function delete($id) {
		$this->db->delete('pages', "id='{$id}'");
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
		// Delete current tags
		$this->db->delete('tags', "page_id = '{$id}'");

		// Insert new tags
		if (substr_count($tags, ' ') == 0) $tags = array($tags); 
		else { $tags = explode(' ', $tags); }

		foreach ($tags as $key => $value) {
			$values = array(
				'page_id' => $id,
				'tag_name' => $value
			);
			$this->db->insert('tags', $values);
		}
	}

	// update page
	function update($id, $content) {
	    $fields[] = 'content';
	    $fields[] = 'lastupd';
	    $fields[] = 'lstupdby';

	    $values[] = $this->pageContentToSave($content);
	    $values[] = time();
	    $values[] = $this->user_id;

	    $this->db->update('pages', $fields, $values, "`id`='" . $id . "'");

		// update cached index and menu pages
		// this pages must be cached other pages are not cached
		$file = $this->db->getData('pages', "id = '{$id}'", 'file')['file'];
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
		$this->writeDataFile('datamain/' . $file, $content);
		return true;
	}

	/**
 	 * Rename page
 	 *
 	 * @param string $newName
 	 * @param integer $id
 	 * @return void
 	 */
	function rename($newName, $id)
	{
		// Set page name
		$pageName = str_replace('.php', '', $newName); // page name (without extension (.php))

	    // Remove language data from page name
	    if (stristr($pageName, '!.')) {
	        $pageName = preg_replace("/(.*)!.(.*)!/", "$1", $pageName);
	    }

	    // Update URL tags in header data
	    $header_data = $this->select_page($id, 'headt, pname');

	    $updated_links = str_replace($header_data['pname'], $pageName, $header_data['headt']);

        $new_data = array(
            'headt' => $updated_links
        );
        $this->head_data($id, $new_data);

	    // Update other data in database
	    $fields[] = 'pname';
	    $fields[] = 'file';

	    $values[] = $pageName;
	    $values[] = $newName;

	    $this->db->update('pages', $fields, $values, "`id`='{$id}'");
	}

	// page visibility. publish or unpubilsh for visitors
	function visibility($id, $visibility) {
        $values = array($visibility, time());

        $fields = array('published', 'pubdate');

        $this->db->update('pages', $fields, $values, "id='" . $id . "'");
	}

	/**
	 * Update page language
	 * 
	 * @param int $id
	 * @param str $lang
	 * @return void
	 */
	function language($id, $lang) {
		$pageData = $this->select_page($id);
	    // Update data in database
        $this->db->update('pages', array('lang', 'file'), array($lang, $pageData['pname'] . '!.' . $lang . '!.php'), "id='{$id}'");
	}

	/**
	 * Process content of the page and display correctly in page editor
	 * 
	 * @param str @content
	 * @return string
	 */
	public function processPageContent($content = '')
	{
		$content = !empty($content) ? htmlspecialchars($content) : '';

		// Replace {@code}} with {{code}}
		while (preg_match('/{@(.*)}}/si', $content)) {
			$content = preg_replace('/{@(.*)}}/si', '{{$1}}', $content);
		}

		return $content;
	}

	/**
	 * Process content of the page to store in database
	 * 
	 * @param str @content
	 * @return string
	 */
	private function pageContentToSave($content = '')
	{
		// Replace {{code}} with {@code}}
		while (preg_match('/{{(.*)}}/si', $content)) {
			$content = preg_replace('/{{(.*)}}/si', '{@$1}}', $content);
		}

		return $content;
	}

	/**
	 * Update head tags
	 *
	 * @param integer $id
	 * @param array $data
	 * @return void
	 */
	function head_data($id, $data)
	{
		/**
		 * Get database fields
		 */
        $fields = array_keys($data);

        /**
         * Get database values
         */
        $values = array_values($data);

        $this->db->update('pages', $fields, $values, "id='{$id}'");
	}

	/**
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

		if (!empty($creator)) $where = " WHERE crtdby = '{$creator}'";

		return $this->db->count_row('pages' . $where);
	}

	/**
	 * Return all page data
	 * 
	 * @param int $id
	 * @param str $fields
	 * @return array
	 */
	public function select_page($id , $fields = '*')
	{
		return $this->db->getData('pages', "id='{$id}'", $fields);
	}

	/**
	 * Load page
	 * 
	 * @return array $page_data
	 */
	public function load_page()
	{
		// Load page with requested language
		$language = is_string($this->page_language) && !empty($this->page_language) ? " AND lang='" . $this->page_language . "'" : '';

		// Load main page only from main page
		if (isset($_GET['pg']) && $_GET['pg'] == 'index') return false;

		// Get data
		$page_data = $this->db->getData('pages', "pname='" . $this->page_name . "'{$language}");

		// When language is set and page does not exsist try to find page without language
		if (empty($page_data) && !empty($this->page_language)) {
			$page_data = $this->db->getData('pages', "pname='" . $this->page_name . "'");
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

			// Published status
			$this->published = $page_data['published'];

			// Page id
			$this->page_id = $page_data['id'];

			// Author
			$this->page_author = $page_data['crtdby'];

			// Date created
			$this->page_created_date = $page_data['created'];

			// Head tags
			$this->head_tags = $this->append_head_tags($page_data['headt']);

			// Published date
			$this->page_published_date = $page_data['pubdate'];

			// Published date
			$this->page_updated_date = $page_data['lastupd'];

			// Page views
			$this->views = !empty($page_data['views']) ? $page_data['views'] : 0;

			// Update page views
			if (stristr($_SERVER['PHP_SELF'], '/pages/pages.php') || stristr($_SERVER['PHP_SELF'], '/pages/blog.php')) {
				$this->db->update('pages', 'views', $this->views + 1, "pname = '" . $this->page_name . "'{$language}");
			}

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
		$lang = $this->db->getData('pages', "pname = '{$page}'", 'lang');

		if (!isset($lang['lang']) || empty($lang['lang'])) return false;

		return $lang['lang'];
	}

	/**
	 * Check if page exists
	 *
	 * @param $file string
	 * @param $where string
	 * @return mix int|bool
	 */
	function page_exists($file = '', $where = '') {
		if (!empty($file) && $this->db->count_row('pages', "file='{$file}'") > 0) {
			return $this->get_page_id("file='{$file}'");
		} elseif (!empty($where) && ($this->db->count_row('pages', $where) > 0)) {
			return $this->get_page_id($where);
		} else {
			return false;
		}
	}

	/**
	 * Return page id
	 *
	 * @param string
	 * @return bool
	 */
	function get_page_id($where)
	{
		$page_id = $this->db->getData('pages', $where, 'id');
		return $page_id = !empty($page_id['id']) ? $page_id['id'] : 0;
	}

	/**
	 * Load page editor program
	 */
	function loadPageEditor()
	{
		// load page editor
		$pageEditor = file_get_contents(APPDIR . 'include/plugins/tinymce.vavok.php');

		// set base dir
		$pageEditor = str_replace('{@BASEDIR}}', BASEDIR, $pageEditor);
		$pageEditor = str_replace('{@HOMEDIR}}', HOMEDIR, $pageEditor);

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

		if (empty($website)) { $website = $this->websiteHomeAddress(); }

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

	    $page_title = $this->db->getData('pages', "pname='" . trim($_SERVER['PHP_SELF'], '/') . "'", 'tname');
	    $page_title = !empty($page_title) ? $page_title['tname'] : '';

	    if (!empty($page_title)) {
	        return $page_title;
	    } else {
	        return $this->configuration('title');
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
		$tags = file_get_contents(APPDIR . 'used/headmeta.dat');

        $vk_page = $this->db->getData('pages', "pname='" . trim($_SERVER['PHP_SELF'], '/') . "'");
        if (!empty($vk_page['headt'])) { $tags .= $vk_page['headt']; }

		// Add missing open graph tags
		if (!strstr($tags, 'og:type')) { $tags .= "\n" . '<meta property="og:type" content="website" />'; }

		if (!strstr($tags, 'og:title') && !empty($this->page_title) && $this->page_title != $this->configuration('title')) { $tags .= "\n" . '<meta property="og:title" content="' . $this->page_title . '" />'; }

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

	/**
	 * Editing mode we are currently using
	 * 
	 * @return str $edmode
	 */
	public function edit_mode()
	{
		if (!empty($this->postAndGet('edmode'))) {
		    $edmode = $this->postAndGet('edmode');
		    $_SESSION['edmode'] = $edmode;
		} elseif (!empty($_SESSION['edmode'])) {
			// Use edit mode from session
		    $edmode = $_SESSION['edmode'];
		} else {
			// Use visual mode as default
		    $edmode = 'visual';
		    $_SESSION['edmode'] = $edmode;
		}

	    return $edmode;
	}
}

?>