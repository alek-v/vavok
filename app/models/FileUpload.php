<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\Upload;

class FileUpload extends BaseModel {
    public function index()
    {
        $this->page_data['page_title'] = 'File Upload';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) $this->redirection(HOMEDIR . 'users/login');

        return $this->page_data;
    }

    /**
     * Upload file and return file location or error
     * 
     * @return string
     */
    public function finish_upload()
    {
        // Instantiate Upload class
        $file_upload = new Upload($this->container, $this->localization);

        // Upload file and retrieve upload results
        $upload_data = $file_upload->upload();

        if (!empty($upload_data['file_address'])) {
            $this->page_data['content'] .= '<p>' . $this->localization->string('filesadded') . '</p>';
            $this->page_data['content'] .= '<p>' . $this->localization->string('fileaddress') . ': ' . $upload_data['file_address'] . '</p>';
        } else {
            $this->page_data['content'] .= $upload_data['error'];
        }

        // Return data with file location or error
        echo $this->page_data['content'];
    }

    public function uploaded_files()
    {
        $this->page_data['page_title'] = 'Uploaded Files';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) {
            $this->redirection(HOMEDIR . 'users/login');
        }

        if ($this->postAndGet('action') == 'del') {
            $file_data = $this->db->selectData('uplfiles', 'id = :id', [':id' => $this->postAndGet('id')]);

            // Location of file to delete
            $file_to_delete = PUBLICDIR . ltrim($file_data['fulldir'], '/');

            // Delete file or show error
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            } else {
                die('File does not exist!');
            }

            // Delete from database
            $this->db->delete("uplfiles", "id='{$this->postAndGet('id')}'");

            $this->redirection(HOMEDIR . 'adminpanel/uploaded_files?isset=mp_delfiles');
        }

        if (empty($this->postAndGet('action'))) {
            $this->page_data['content'] .= '<p><img src="../themes/images/img/partners.gif" alt="" /> List of uploaded files</p>'; 

            $num_items = $this->db->countRow('uplfiles');

            if ($num_items > 0) {
                $items_per_page = 10;

                // Start navigation
                $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'adminpanel/uploaded_files?'); 
                $limit_start = $navigation->start()['start']; // starting point
        
                if ($num_items > 0) {
                    foreach ($this->db->query("SELECT * FROM uplfiles ORDER BY id LIMIT $limit_start, $items_per_page") as $item) {
                        $lnk = '<div class="a"><a href="' . $item['fulldir'] . '">' . $item['name'] . '</a> | <a href="?action=del&id=' . $item['id'] . '">[DEL]</a></div>';
                        $this->page_data['content'] .= $lnk . '<br />';
                    }
                }

                $this->page_data['content'] .= $navigation->getNavigation();
            } else {
                $this->page_data['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="" /> No uploaded files!</p>';
            }
        }

        return $this->page_data;
    }

    /**
     * Search uploaded files
     */
    public function search_uploads()
    {
        $this->page_data['page_title'] = 'Search';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) $this->redirection(HOMEDIR . '?auth_error');
        
        if (empty($this->postAndGet('action'))) {
            $this->page_data['content'] .= '<form action="' . HOMEDIR . 'adminpanel/search_uploads?action=stpc" method="POST">';
            $this->page_data['content'] .= 'Text:<br><input name="stext" maxlength="30" /><br>';
            $this->page_data['content'] .= '<br>';
            $this->page_data['content'] .= '<input type="submit" value="Search"></form><br><br>';
        } else if ($this->postAndGet('action') == 'stpc') {
            $stext = $this->check($_POST["stext"]);

            if (empty($stext)) {
                $this->page_data['content'] .= "<br />Please fill all fields<br />";
            } else {
                $this->page_data['content'] .= 'Searching for: ' . $stext . '<br />';
                $this->page_data['content'] .= '<div class="break"></div>'; 
                // begin search

                $where_table = "uplfiles";
                $cond = "name";
                $select_fields = "*";
                $ord_fields = "id DESC";

                $noi = $this->db->countRow($where_table, "{$cond} LIKE '%{$stext}%'");
                $num_items = $noi;
                $items_per_page = 10;

                $navigation = new Navigation($items_per_page, $num_items, 'search_uploads.php?'); // start navigation

                $limit_start = $navigation->start()['start']; // starting point

                $sql = "SELECT " . $select_fields . " FROM " . $where_table . " WHERE name LIKE '%" . $stext . "%' ORDER BY " . $ord_fields . " LIMIT $limit_start, $items_per_page";

                foreach ($this->db->query($sql) as $item) {
                    $this->page_data['content'] .= '<div class="a"><a href="' . $item['fulldir'] . '">' . $item['name'] . '</a> | <a href="' . HOMEDIR . 'adminpanel/uploaded_files?action=del&id=' . $item['id'] . '">[DEL]</a></div>';
                    $this->page_data['content'] .= '<div class="break"></div>';
                }

                $this->page_data['content'] .= '<p>';
                $this->page_data['content'] .= 'Items: ' . $noi;
                $this->page_data['content'] .= '</p>';

                $this->page_data['content'] .= $navigation->getNavigation();
            }
        }

        $this->page_data['content'] .= '<p>';
        $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/search_uploads', $this->localization->string('back'));
        $this->page_data['content'] .= '</p>';

        return $this->page_data;
    }
}