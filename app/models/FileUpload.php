<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class FileUpload extends BaseModel {
    public function __construct()
    {
        parent::__construct();

        $this->file_upload = $this->model('Upload');
    }

    public function index()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'File Upload';
        $this_page['content'] = '';

        if (!$this->user->is_administrator() && !$this->user->is_moderator(103) && !$this->user->is_moderator(105)) $this->redirect_to('../pages/login.php');

        if (empty($this->post_and_get('action'))) {
            $this_page['headt'] = $this->file_upload->get_header_data();

            $this_page['content'] .= '<p>' . $this->sitelink('./', $this->localization->string('admpanel')) . '<br />';
            $this_page['content'] .= $this->homelink() . '</p>';

            return $this_page;
        }
    }

    public function finish_upload()
    {
        $this_page['content'] = '';

        $upload_data = $this->file_upload->upload('', $this->localization);

        if (!empty($upload_data['file_address'])) {
            $this_page['content'] .= '<p>' . $this->localization->string('filesadded') . '</p>';
            $this_page['content'] .= '<p>' . $this->localization->string('fileaddress') . ': ' . $upload_data['file_address'] . '</p>';
        } else {
            $this_page['content'] .= $upload_data['error'];
        }

        $this_page['content'] .= $this_page['content'];
    }

    public function uploaded_files()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Uploaded Files';
        $this_page['content'] = '';

        if (!$this->user->is_administrator() && !$this->user->is_moderator(103) && !$this->user->is_moderator(105)) $this->redirect_to('../pages/login.php');

        if ($this->post_and_get('action') == 'del') {
            $file_data = $this->db->get_data('uplfiles', "id='{$this->post_and_get('id')}'");

            // Location of file to delete
            $file_to_delete = PUBLICDIR . ltrim($file_data['fulldir'], '/');

            // Delete file or show error
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            } else {
                die('File does not exist!');
            }

            // Delete from database
            $this->db->delete("uplfiles", "id='{$this->post_and_get('id')}'");

            $this->redirect_to(HOMEDIR . 'adminpanel/uploaded_files?isset=mp_delfiles');
        }

        if (empty($this->post_and_get('action'))) {
            $this_page['content'] .= '<p><img src="../themes/images/img/partners.gif" alt="" /> List of uploaded files</p>'; 

            $num_items = $this->db->count_row('uplfiles');

            if ($num_items > 0) {
                $items_per_page = 10;

                // Start navigation
                $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'adminpanel/uploaded_files?'); 
                $limit_start = $navigation->start()['start']; // starting point
        
                if ($num_items > 0) {
                    foreach ($this->db->query("SELECT * FROM uplfiles ORDER BY id LIMIT $limit_start, $items_per_page") as $item) {
                        $lnk = '<div class="a"><a href="' . $item['fulldir'] . '">' . $item['name'] . '</a> | <a href="?action=del&amp;id=' . $item['id'] . '">[DEL]</a></div>';
                        $this_page['content'] .= $lnk . "<br />";
                    }
                }
        
                $this_page['content'] .= $navigation->get_navigation();
            } else {
                $this_page['content'] .= '<p><img src="../themes/images/img/reload.gif" alt="" /> No uploaded files!</p>';
            }
        }

        $this_page['content'] .= '<p>';
        $this_page['content'] .= $this->sitelink('./', $this->localization->string('admpanel')) . '<br />';
        $this_page['content'] .= $this->homelink();
        $this_page['content'] .= '</p>';

        return $this_page;
    }
}