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
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'File Upload';
        $this_page['content'] = '';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) $this->container['core']->redirection('../pages/login.php');

        if (empty($this->container['core']->postAndGet('action'))) {
            $this_page['content'] .= '<p>' . $this->container['core']->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
            $this_page['content'] .= $this->container['core']->homelink() . '</p>';

            return $this_page;
        }
    }

    /**
     * Upload file and return file location or error
     * 
     * @return string
     */
    public function finish_upload()
    {
        $this_page['content'] = '';

        // Instantiate Upload class
        $file_upload = new Upload($this->container, $this->localization);

        // Upload file and retrieve upload results
        $upload_data = $file_upload->upload();

        if (!empty($upload_data['file_address'])) {
            $this_page['content'] .= '<p>' . $this->localization->string('filesadded') . '</p>';
            $this_page['content'] .= '<p>' . $this->localization->string('fileaddress') . ': ' . $upload_data['file_address'] . '</p>';
        } else {
            $this_page['content'] .= $upload_data['error'];
        }

        // Return data with file location or error
        echo $this_page['content'];
    }

    public function uploaded_files()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Uploaded Files';
        $this_page['content'] = '';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) $this->container['core']->redirection('../pages/login.php');

        if ($this->container['core']->postAndGet('action') == 'del') {
            $file_data = $this->db->selectData('uplfiles', 'id = :id', [':id' => $this->container['core']->postAndGet('id')]);

            // Location of file to delete
            $file_to_delete = PUBLICDIR . ltrim($file_data['fulldir'], '/');

            // Delete file or show error
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            } else {
                die('File does not exist!');
            }

            // Delete from database
            $this->db->delete("uplfiles", "id='{$this->container['core']->postAndGet('id')}'");

            $this->container['core']->redirection(HOMEDIR . 'adminpanel/uploaded_files?isset=mp_delfiles');
        }

        if (empty($this->container['core']->postAndGet('action'))) {
            $this_page['content'] .= '<p><img src="../themes/images/img/partners.gif" alt="" /> List of uploaded files</p>'; 

            $num_items = $this->db->countRow('uplfiles');

            if ($num_items > 0) {
                $items_per_page = 10;

                // Start navigation
                $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'adminpanel/uploaded_files?'); 
                $limit_start = $navigation->start()['start']; // starting point
        
                if ($num_items > 0) {
                    foreach ($this->db->query("SELECT * FROM uplfiles ORDER BY id LIMIT $limit_start, $items_per_page") as $item) {
                        $lnk = '<div class="a"><a href="' . $item['fulldir'] . '">' . $item['name'] . '</a> | <a href="?action=del&id=' . $item['id'] . '">[DEL]</a></div>';
                        $this_page['content'] .= $lnk . "<br />";
                    }
                }
        
                $this_page['content'] .= $navigation->get_navigation();
            } else {
                $this_page['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="" /> No uploaded files!</p>';
            }
        }

        $this_page['content'] .= '<p>';
        $this_page['content'] .= $this->container['core']->sitelink(HOMEDIR . 'adminpanel', $this->localization->string('adminpanel')) . '<br />';
        $this_page['content'] .= $this->container['core']->homelink();
        $this_page['content'] .= '</p>';

        return $this_page;
    }

    /**
     * Search uploaded files
     */
    public function search_uploads()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Search';
        $this_page['content'] = '';

        if (!$this->user->administrator() && !$this->user->moderator(103) && !$this->user->moderator(105)) $this->container['core']->redirection(HOMEDIR . '?auth_error');
        
        if (empty($this->container['core']->postAndGet('action'))) {
            $this_page['content'] .= '<form action="' . HOMEDIR . 'adminpanel/search_uploads?action=stpc" method="POST">';
            $this_page['content'] .= 'Text:<br><input name="stext" maxlength="30" /><br>';
            $this_page['content'] .= '<br>';
            $this_page['content'] .= '<input type="submit" value="Search"></form><br><br>';
        } else if ($this->container['core']->postAndGet('action') == 'stpc') {
            $stext = $this->container['core']->check($_POST["stext"]);

            if (empty($stext)) {
                $this_page['content'] .= "<br />Please fill all fields<br />";
            } else {
                $this_page['content'] .= 'Searching for: ' . $stext . '<br />';
                $this_page['content'] .= '<div class="break"></div>'; 
                // begin search

                $where_table = "uplfiles";
                $cond = "name";
                $select_fields = "*";
                $ord_fields = "id DESC";

                $noi = $this->db->countRow($where_table, "{$cond} LIKE '%{$stext}%'");
                $num_items = $noi;
                $items_per_page = 10;

                $navigation = new Navigation($items_per_page, $num_items, $this->container['core']->postAndGet('page'), 'search_uploads.php?'); // start navigation

                $limit_start = $navigation->start()['start']; // starting point

                $sql = "SELECT " . $select_fields . " FROM " . $where_table . " WHERE name LIKE '%" . $stext . "%' ORDER BY " . $ord_fields . " LIMIT $limit_start, $items_per_page";

                foreach ($this->db->query($sql) as $item) {
                    $this_page['content'] .= '<div class="a"><a href="' . $item['fulldir'] . '">' . $item['name'] . '</a> | <a href="' . HOMEDIR . 'adminpanel/uploaded_files?action=del&id=' . $item['id'] . '">[DEL]</a></div>';
                    $this_page['content'] .= '<div class="break"></div>';
                }

                $this_page['content'] .= '<p>';
                $this_page['content'] .= 'Items: ' . $noi;
                $this_page['content'] .= '</p>';

                $this_page['content'] .= $navigation->get_navigation();
            }
        }

        $this_page['content'] .= '<p>';
        $this_page['content'] .= $this->container['core']->sitelink(HOMEDIR . 'adminpanel/search_uploads', $this->localization->string('back')) . '<br />';
        $this_page['content'] .= $this->container['core']->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $this_page['content'] .= $this->container['core']->homelink();
        $this_page['content'] .= '</p>';

        return $this_page;
    }
}