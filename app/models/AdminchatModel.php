<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class AdminchatModel extends BaseModel {
    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[adminchat]}}';
        $data['content'] = '';

        if (!$this->user->check_permissions(basename(__FILE__))) $this->redirection('../?auth_error');

        // add to admin chat
        if ($this->post_and_get('action') == 'acadd') {
            $brow = $this->check($this->user->user_browser());
            $msg = $this->check(wordwrap($this->post_and_get('msg'), 150, ' ', 1));
            $msg = substr($msg, 0, 1200);
            $msg = $this->check($msg);
        
            $msg = $this->antiword($msg);
            $msg = $this->smiles($msg);
            $msg = $this->no_br($msg, '<br />');
        
            $text = $msg . '|' . $this->user->show_username() . '|' . $this->correctDate(time(), "d.m.y") . '|' . $this->correctDate(time(), "H:i") . '|' . $brow . '|' . $this->user->find_ip() . '|';
            $text = $this->no_br($text);
        
            $this->writeDataFile('adminchat.dat', $text . PHP_EOL, 1);
        
            $file = $this->getDataFile('adminchat.dat');
            $i = count($file);
            if ($i >= 300) {
                $fp = fopen(APPDIR . "used/adminchat.dat", "w");
                flock ($fp, LOCK_EX);
                unset($file[0]);
                unset($file[1]);
                fputs($fp, implode("", $file));
                flock ($fp, LOCK_UN);
                fclose($fp);
            }

            header('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=addon');
            exit;
        }

        // empty admin chat
        if ($this->post_and_get('action') == "acdel") {
            if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
                $this->clearFile(APPDIR . "used/adminchat.dat");

                header ('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=mp_admindelchat');
                exit;
            }
        }

        $data['content'] .= '<img src="../themes/images/img/menu.gif" alt=""> <b>' . $this->localization->string('adminchat') . '</b><br><br>';
        
        if (empty($this->post_and_get('action'))) {
            $data['content'] .= '<a href="#down"><img src="../themes/images/img/downs.gif" alt=""></a> ';
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?r=' . rand(100, 999), $this->localization->string('refresh')) . '<br>';
        
            $data['content'] .='<hr><form action="adminchat/?action=acadd" method="post"><b>' . $this->localization->string('message') . '</b><br>';
            $data['content'] .='<textarea cols="80" rows="5" name="msg"></textarea><br>';
        
            $data['content'] .='<input type="submit" value="' . $this->localization->string('save') . '" /></form><hr>';
        
            $file = $this->getDataFile('adminchat.dat');
            $file = array_reverse($file);
            $total = count($file);
        
            if ($total < 1) {
                $data['content'] .='<br><img src="../themes/images/img/reload.gif" alt=""> <b>' . $this->localization->string('nomsgs') . '</b><br>';
            }
        
            $navigation = new Navigation(10, $total, $this->post_and_get('page'), HOMEDIR . 'adminpanel/adminchat/?'); // start navigation
        
            $limit_start = $navigation->start()['start']; // starting point
        
            if ($total < $limit_start + 10) {
                $end = $total;
            } else {
                $end = $limit_start + 10;
            }
        
            for ($i = $limit_start; $i < $end; $i++) {
                $chat_data = explode("|", $file[$i]); 
        
                $statwho = $this->user->user_online($chat_data[1]); 
        
                $data_text = $this->getbbcode($chat_data[0]);
        
                $data['content'] .= '<div class=b><b>' . $this->sitelink(HOMEDIR . 'users/u/' . $chat_data[1], $chat_data[1]) . '</b> ' . $statwho;
        
                if (date('d.m.y') == $chat_data[2]) {
                    $chat_data[2] = '<font color="#FF0000">' . $this->localization->string('today') . '</font>';
                }
        
                $data['content'] .='<small> (' . $chat_data[2] . ' / ' . $chat_data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $chat_data[4] . ', ' . $chat_data[5] . ']</font></small>';
                $data['content'] .='<br>';
            }
        
            $data['content'] .= '<hr>';
            $data['content'] .= $navigation->get_navigation();
        
            $data['content'] .= '<br><br>';
        
            $data['content'] .= $this->sitelink('../pages/smiles.php', $this->localization->string('smile'));
        }

        if ($this->post_and_get('action') == 'prodel') {
            $data['content'] .= '<br>' . $this->localization->string('delacmsgs') . '?<br>';
            $data['content'] .= '<b>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=acdel', $this->localization->string('yessure') . '!') . '</b><br>';
        
            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/', $this->localization->string('back'));
        } 
        
        if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=prodel', $this->localization->string('cleanchat'));
        }
        
        $data['content'] .= '<p>' . $this->sitelink('./', $this->localization->string('admpanel')) . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }    
}