<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Exceptions\FileException;
use App\Traits\Files;

class AdminchatModel extends BaseModel {
    use Files;

    /**
     * @return array
     * @throws Exception
     */
    public function index(): array
    {
        $this->page_data['page_title'] = '{@localization[admin_chat]}}';

        if (!$this->user->administrator() && !$this->user->moderator()) {
            $this->redirection('../?auth_error');
        }

        // Add message to the administrator chat
        if ($this->postAndGet('action') == 'add_message') {
            $brow = $this->check($this->user->userBrowser());
            $msg = $this->check(wordwrap($this->postAndGet('msg'), 150, ' ', 1));
            $msg = substr($msg, 0, 1200);
            $msg = $this->check($msg);
            $msg = $this->replaceNewLines($msg, '<br />');

            $text = $msg . '|' . $this->user->showUsername() . '|' . $this->correctDate(time(), "d.m.y") . '|' . $this->correctDate(time(), "H:i") . '|' . $brow . '|' . $this->user->findIpAddress() . '|';
            $text = $this->replaceNewLines($text);

            try {
                $this->writeDataFile('admin_chat.dat', $text . PHP_EOL, 1);
            } catch (FileException) {
                // Try to handle the exception, make file writeable
                try {
                    $this->makeFileWriteable('admin_chat.dat');
                } catch (FileException $e) {
                    // Stop the script and show error message
                    echo $e->getMessage();
                    exit;
                }
            }

            // Limit number of messages in the file
            $this->limitFileLines('admin_chat.dat', 10);

            header('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=addon');
            exit;
        }

        // empty admin chat
        if ($this->postAndGet('action') == 'clear_admin_chat') {
            if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
                $this->clearFile(STORAGEDIR . 'admin_chat.dat');

                header('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=mp_admindelchat');
                exit;
            }
        }

        $this->page_data['content'] .= '<h1>{@localization[admin_chat]}}</h1>';

        if (empty($this->postAndGet('action'))) {
            $this->page_data['content'] .= '<a href="#down"><img src="../themes/images/img/downs.gif" alt=""></a> ';
            $this->page_data['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?r=' . rand(100, 999), '{@localization[refresh]}}') . '<br>';
        
            $this->page_data['content'] .='<hr><form action="adminchat/?action=add_message" method="post"><b>{@localization[message]}}</b><br>';
            $this->page_data['content'] .='<textarea cols="80" rows="5" name="msg"></textarea><br>';
        
            $this->page_data['content'] .='<input type="submit" value="{@localization[save]}}" /></form><hr>';
        
            $file = $this->getDataFile('admin_chat.dat');
            $file = array_reverse($file);
            $total = count($file);

            if ($total < 1) {
                $this->page_data['content'] .='<p><img src="../themes/images/img/reload.gif" alt=""> <b>{@localization[no_messages]}}</b></p>';
            }

            $navigation = new Navigation(10, $total, HOMEDIR . 'adminpanel/adminchat/?'); // start navigation
        
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
        
                $this->page_data['content'] .= '<div class=b><b>' . $this->sitelink(HOMEDIR . 'users/u/' . $chat_data[1], $chat_data[1]) . '</b> ' . $online_status;
        
                if (date('d.m.y') == $chat_data[2]) {
                    $chat_data[2] = '<font color="#FF0000">{@localization[today]}}</font>';
                }

                $this->page_data['content'] .='<small> (' . $chat_data[2] . ' / ' . $chat_data[3] . ')</small></div>';
                $this->page_data['content'] .='<div><p>' . $data_text . '<br><small><font color="#CC00CC">[' . $chat_data[4] . ', ' . $chat_data[5] . ']</font></small></p></div>';
            }

            $this->page_data['content'] .= '<hr>';
            $this->page_data['content'] .= $navigation->getNavigation();
        }

        if ($this->postAndGet('action') == 'confirm_to_clear_chat') {
            $this->page_data['content'] .= '<br>{@localization[delacmsgs]}}?<br>';
            $this->page_data['content'] .= '<b>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=clear_admin_chat', '{@localization[yessure]}}' . '!') . '</b><br>';

            $this->page_data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/', '{@localization[back]}}');
        } 

        if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
            $this->page_data['content'] .= '<br>' . $this->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=confirm_to_clear_chat', '{@localization[cleanchat]}}');
        }

        return $this->page_data;
    }    
}