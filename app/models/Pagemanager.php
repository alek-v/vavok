<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for managing pages
 */

use App\Classes\Controller;
use App\Classes\Database;

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
		// Instantiate database
		parent::__construct();

 		// User id with active login
		$this->user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
	}

	/**
	 * Update, insert and delete information
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
	public function updateTags($id, $tags)
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
	    $header_data = $this->selectPage($id, 'headt, pname');

	    $updated_links = str_replace($header_data['pname'], $pageName, $header_data['headt']);

        $new_data = array(
            'headt' => $updated_links
        );
        $this->headData($id, $new_data);

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
		$pageData = $this->selectPage($id);
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
	function headData($id, $data)
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

	// return total number of pages
	public function totalPages($creator = '') {
		$where = '';

		if (!empty($creator)) $where = " WHERE crtdby = '{$creator}'";

		return $this->db->countRow('pages' . $where);
	}

	/**
	 * Return all page data
	 * 
	 * @param int $id
	 * @param str $fields
	 * @return array
	 */
	public function selectPage($id , $fields = '*')
	{
		return $this->db->getData('pages', "id='{$id}'", $fields);
	}

	/**
	 * Check if page exists
	 *
	 * @param $search string
	 * @param $type string
	 * @return mix int|bool
	 */
	function pageExists($search, $type = 'file') {
		if ($type == 'file' && $this->db->countRow('pages', "file='{$search}'") > 0) {
			return $this->getPageId("file='{$search}'");
		} elseif ($type == 'where' && ($this->db->countRow('pages', $search) > 0)) {
			return $this->getPageId($search);
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
	function getPageId($where)
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

	/**
	 * Page tags (keywords)
	 *
	 * @param integer $id
	 * @return string
	 */
	public function pageTags($id)
	{
		$tags = '';

		$sql = $this->db->query("SELECT * FROM tags WHERE page_id = '{$id}' ORDER BY id ASC");
		foreach ($sql as $key => $value) {
			$tags .= ' ' . $value['tag_name'];
		}
		return trim($tags);
	}

	/**
	 * Append head tags
	 *
	 * @param string $tags
	 * @return void
	 */
	public function appendHeadTags($tags) {
		return $this->head_tags .= $tags;
	}

	/**
	 * Editing mode we are currently using
	 * 
	 * @return str $edmode
	 */
	public function editMode()
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