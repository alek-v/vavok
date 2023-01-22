<?php
/**
 * Manage (create, update, delete) pages on the site
 */

namespace App\Classes;
use App\Exceptions\FileException;
use App\Traits\Core;
use App\Traits\Files;
use Pimple\Container;

class PageManager {
    use Core, Files;

    protected object $db;

    public function __construct(Container $container)
    {
        $this->db = $container['db'];
    }

    /**
     * Insert a new page
     * 
     * @param array $values
     * @return int last inserted id
     */
    public function insert(array $values): int
    {
        $this->db->insert('pages', $values);

        return $this->db->lastInsertId();
    }

    /**
     * Delete page
     * 
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->db->delete('pages', "id='{$id}'");
    }

    /**
     * Update page tags
     *
     * @param integer $id
     * @param string $tags
     * @return void
     */
    public function updateTags(int $id, string $tags): void
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

    /**
     * Update page content
     *
     * @param int $id
     * @param string $content
     * @return bool
     * @throws FileException
     */
    public function update(int $id, string $content): bool
    {
        $fields[] = 'content';
        $fields[] = 'lastupd';
        $fields[] = 'lstupdby';

        $values[] = $this->pageContentToSave($content);
        $values[] = time();
        $values[] = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

        $this->db->update('pages', $fields, $values, "id='{$id}'");

        // update cached index and menu pages
        // this pages must be cached other pages are not cached
        $file = $this->db->selectData('pages', "id = :id", [':id' => $id], 'file')['file'];

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
     * @throws FileException
     */
    public function updateCached(string $file, string $content): bool
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
    public function rename(string $newName, int $id): void
    {
        // Set page name
        $pageName = str_replace('.php', '', $newName); // page name (without extension (.php))

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

    /**
     * Page visibility. Publish or unpublish the page
     * 
     * @param integer $id
     * @param integer $visibility
     * @return void
     */
    public function visibility(int $id, int $visibility): void
    {
        $values = array($visibility, time());

        $fields = array('published', 'pubdate');

        $this->db->update('pages', $fields, $values, "id='{$id}'");
    }

    /**
     * Update page language
     * 
     * @param integer $id
     * @param string $lang
     * @return void
     */
    function language(int $id, string $lang): void
    {
        // Data of the page
        $pageData = $this->selectPage($id);

        // Update data in database
        $this->db->update('pages', array('lang', 'file'), array($lang, $pageData['pname'] . '_' . $lang . '.php'), "id='{$id}'");
    }

    /**
     * Process content of the page and display correctly in the page editor
     * 
     * @param ?string $content
     * @return string
     */
    public function processPageContent(?string $content = ''): string
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
     * @param string $content
     * @return string
     */
    private function pageContentToSave(string $content = ''): string
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
    function headData(int $id, array $data): void
    {
        // Get database fields
        $fields = array_keys($data);

        // Get database values
        $values = array_values($data);

        $this->db->update('pages', $fields, $values, "id='{$id}'");
    }

    /**
     * Return number of pages
     * 
     * @param ?integer $creator
     * @return integer
     */
    public function totalPages(?int $creator = null): int
    {
        $where = '';

        if (!empty($creator)) $where = " WHERE crtdby = '{$creator}'";

        return $this->db->countRow('pages' . $where);
    }

    /**
     * Return all page data
     * 
     * @param int $id
     * @param string $fields
     * @return array|bool
     */
    public function selectPage(int $id , string $fields = '*'): array|bool
    {
        return $this->db->selectData('pages', "id = :id", ['id' => $id], $fields);
    }

    /**
     * Check if page exists
     *
     * @param string $search
     * @param string $type
     * @param array $bind
     * @return int|bool
     */
    public function pageExists(string $search, string $type = 'file', array $bind = []): int|bool
    {
        if ($type == 'file' && $this->db->countRow('pages', "file='{$search}'") > 0) {
            return $this->getPageId('file = :file', [':file' => $search]);
        } elseif ($type == 'where') {
            return $this->getPageId($search, $bind);
        }

        return false;
    }

    /**
     * Return page id
     *
     * @param string $where
     * @param array $bind
     * @return bool
     */
    public function getPageId(string $where, array $bind): bool
    {
        $page_id = $this->db->selectData('pages', $where, $bind, 'id');
        return !empty($page_id['id']) ? $page_id['id'] : 0;
    }

    /**
     * Load page editor program
     * 
     * @return string
     */
    public function loadPageEditor(): string
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
    public function pageTags(int $id): string
    {
        $tags = '';

        $sql = $this->db->query("SELECT * FROM tags WHERE page_id = '{$id}' ORDER BY id ASC");
        foreach ($sql as $key => $value) {
            $tags .= ' ' . $value['tag_name'];
        }
        return trim($tags);
    }

    /**
     * Editing mode we are currently using
     * 
     * @return string $edmode
     */
    public function editMode(): string
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