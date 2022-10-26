<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\FileException;
use App\Traits\Files;

class AdminchatModel extends BaseModel {
    use Files;

    /**
     * @return array
     */
    public function index(): array
    {
        $data['tname'] = '{@localization[admin_chat]}}';
        $data['content'] = '';

        if (!$this->user->checkPermissions(basename(__FILE__))) $this->redirection('../?auth_error');

        // Add message to the administrator chat
        if ($this->postAndGet('action') == 'add_message') {
            $brow = $this->check($this->user->user_browser());
            $msg = $this->check(wordwrap($this->postAndGet('msg'), 150, ' ', 1));
            $msg = substr($msg, 0, 1200);
            $msg = $this->check($msg);
            $msg = $this->replaceNewLines($msg, '<br />');

            $text = $msg . '|' . $this->user->show_username() . '|' . $this->correctDate(time(), "d.m.y") . '|' . $this->correctDate(time(), "H:i") . '|' . $brow . '|' . $this->user->find_ip() . '|';
            $text = $this->replaceNewLines($text);

            try {
                $this->writeDataFile('adminchat.dat', $text . PHP_EOL, 1);
            } catch (FileException) {
                // Try to handle the exception, make file writeable
                try {
                    $this->makeFileWriteable('adminchat.dat');
                } catch (FileException $e) {
                    // Stop the script and show error message
                    echo $e->getMessage();
                    exit;
                }
            }

            // Limit number of messages in the file
            $this->limitFileLines('adminchat.dat', 10);

            header('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=addon');
            exit;
        }

        // empty admin chat
        if ($this->postAndGet('action') == 'clear_admin_chat') {
            if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
                $this->clearFile(STORAGEDIR . 'adminchat.dat');

                header('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=mp_admindelchat');
                exit;
            }
        }

        $data['content'] .= '<h1>{@localization[admin_chat]}}</h1>';

        if (empty($this->postAndGet('action'))) {
            $data['content'] .= '<a href="#down"><img src="../themes/images/img/downs.gif" alt=""></a> ';
            $data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?r=' . rand(100, 999), '{@localization[refresh]}}') . '<br>';
        
            $data['content'] .='<hr><form action="adminchat/?action=add_message" method="post"><b>{@localization[message]}}</b><br>';
            $data['content'] .='<textarea cols="80" rows="5" name="msg"></textarea><br>';
        
            $data['content'] .='<input type="submit" value="{@localization[save]}}" /></form><hr>';
        
            $file = $this->getDataFile('adminchat.dat');
            $file = array_reverse($file);
            $total = count($file);

            if ($total < 1) {
                $data['content'] .='<p><img src="../themes/images/img/reload.gif" alt=""> <b>{@localization[no_messages]}}</b></p>';
            }

            $navigation = new Navigation(10, $total, $this->postAndGet('page'), HOMEDIR . 'adminpanel/adminchat/?'); // start navigation
        
            $limit_start = $navigation->start()['start']; // starting point
        
            if ($total < $limit_start + 10) {
                $end = $total;
            } else {
                $end = $limit_start + 10;
            }
        
            for ($i = $limit_start; $i < $end; $i++) {
                $chat_data = explode("|", $file[$i]); 

                // Online status
                $online_status = $this->user->userOnline($chat_data[1]);

                // Message
                $data_text = $this->antiword($this->smiles($this->getbbcode($chat_data[0])));
        
                $data['content'] .= '<div class=b><b>' . $this->sitelink(HOMEDIR . 'users/u/' . $chat_data[1], $chat_data[1]) . '</b> ' . $online_status;
        
                if (date('d.m.y') == $chat_data[2]) {
                    $chat_data[2] = '<font color="#FF0000">{@localization[today]}}</font>';
                }

                $data['content'] .='<small> (' . $chat_data[2] . ' / ' . $chat_data[3] . ')</small></div>';
                $data['content'] .='<div><p>' . $data_text . '<br><small><font color="#CC00CC">[' . $chat_data[4] . ', ' . $chat_data[5] . ']</font></small></p></div>';
            }

            $data['content'] .= '<hr>';
            $data['content'] .= $navigation->get_navigation();
        }

        if ($this->postAndGet('action') == 'confirm_to_clear_chat') {
            $data['content'] .= '<br>{@localization[delacmsgs]}}?<br>';
            $data['content'] .= '<b>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=clear_admin_chat', '{@localization[yessure]}}' . '!') . '</b><br>';

            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/', '{@localization[back]}}');
        } 

        if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
            $data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=confirm_to_clear_chat', '{@localization[cleanchat]}}');
        }

        $data['content'] .= '<p>' . $this->sitelink('./', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->homelink() . '</p>';

        return $data;
    }    
}