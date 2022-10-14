<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;

class AdminchatModel extends BaseModel {
    public function index()
    {
        // Users data
        $data['user'] = $this->user_data;
        $data['tname'] = '{@localization[adminchat]}}';
        $data['content'] = '';

        if (!$this->user->checkPermissions(basename(__FILE__))) $this->container['core']->redirection('../?auth_error');

        // add to admin chat
        if ($this->container['core']->postAndGet('action') == 'acadd') {
            $brow = $this->container['core']->check($this->user->user_browser());
            $msg = $this->container['core']->check(wordwrap($this->container['core']->postAndGet('msg'), 150, ' ', 1));
            $msg = substr($msg, 0, 1200);
            $msg = $this->container['core']->check($msg);
        
            $msg = $this->container['core']->antiword($msg);
            $msg = $this->container['core']->smiles($msg);
            $msg = $this->container['core']->replaceNewLines($msg, '<br />');
        
            $text = $msg . '|' . $this->user->show_username() . '|' . $this->container['core']->correctDate(time(), "d.m.y") . '|' . $this->container['core']->correctDate(time(), "H:i") . '|' . $brow . '|' . $this->user->find_ip() . '|';
            $text = $this->container['core']->replaceNewLines($text);
        
            $this->container['core']->writeDataFile('adminchat.dat', $text . PHP_EOL, 1);
        
            $file = $this->container['core']->getDataFile('adminchat.dat');
            $i = count($file);
            if ($i >= 300) {
                $fp = fopen(STORAGEDIR . "adminchat.dat", "w");
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
        if ($this->container['core']->postAndGet('action') == "acdel") {
            if ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102) {
                $this->container['core']->clearFile(STORAGEDIR . "adminchat.dat");

                header ('Location: ' . HOMEDIR . 'adminpanel/adminchat/?isset=mp_admindelchat');
                exit;
            }
        }

        $data['content'] .= '<img src="../themes/images/img/menu.gif" alt=""> <b>{@localization[adminchat]}}</b><br><br>';
        
        if (empty($this->container['core']->postAndGet('action'))) {
            $data['content'] .= '<a href="#down"><img src="../themes/images/img/downs.gif" alt=""></a> ';
            $data['content'] .= $this->container['core']->sitelink(HOMEDIR . 'adminpanel/adminchat/?r=' . rand(100, 999), '{@localization[refresh]}}') . '<br>';
        
            $data['content'] .='<hr><form action="adminchat/?action=acadd" method="post"><b>{@localization[message]}}</b><br>';
            $data['content'] .='<textarea cols="80" rows="5" name="msg"></textarea><br>';
        
            $data['content'] .='<input type="submit" value="{@localization[save]}}" /></form><hr>';
        
            $file = $this->container['core']->getDataFile('adminchat.dat');
            $file = array_reverse($file);
            $total = count($file);
        
            if ($total < 1) {
                $data['content'] .='<br><img src="../themes/images/img/reload.gif" alt=""> <b>{@localization[nomsgs]}}</b><br>';
            }
        
            $navigation = new Navigation(10, $total, $this->container['core']->postAndGet('page'), HOMEDIR . 'adminpanel/adminchat/?'); // start navigation
        
            $limit_start = $navigation->start()['start']; // starting point
        
            if ($total < $limit_start + 10) {
                $end = $total;
            } else {
                $end = $limit_start + 10;
            }
        
            for ($i = $limit_start; $i < $end; $i++) {
                $chat_data = explode("|", $file[$i]); 
        
                $statwho = $this->user->userOnline($chat_data[1]); 
        
                $data_text = $this->container['core']->getbbcode($chat_data[0]);
        
                $data['content'] .= '<div class=b><b>' . $this->container['core']->sitelink(HOMEDIR . 'users/u/' . $chat_data[1], $chat_data[1]) . '</b> ' . $statwho;
        
                if (date('d.m.y') == $chat_data[2]) {
                    $chat_data[2] = '<font color="#FF0000">{@localization[today]}}</font>';
                }
        
                $data['content'] .='<small> (' . $chat_data[2] . ' / ' . $chat_data[3] . ')</small></div>' . $data_text . '<br><small><font color="#CC00CC">[' . $chat_data[4] . ', ' . $chat_data[5] . ']</font></small>';
                $data['content'] .='<br>';
            }
        
            $data['content'] .= '<hr>';
            $data['content'] .= $navigation->get_navigation();
        
            $data['content'] .= '<br><br>';
        
            $data['content'] .= $this->container['core']->sitelink('../pages/smiles.php', '{@localization[smile]}}');
        }

        if ($this->container['core']->postAndGet('action') == 'prodel') {
            $data['content'] .= '<br>{@localization[delacmsgs]}}?<br>';
            $data['content'] .= '<b>' . $this->container['core']->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=acdel', '{@localization[yessure]}}' . '!') . '</b><br>';
        
            $data['content'] .= '<br>' . $this->container['core']->sitelink(HOMEDIR . 'adminpanel/adminchat/', '{@localization[back]}}');
        } 
        
        if (isset($total) && $total > 0 && ($_SESSION['permissions'] == 101 || $_SESSION['permissions'] == 102)) {
            $data['content'] .= '<br>' . $this->container['core']->sitelink(HOMEDIR . 'adminpanel/adminchat/?action=prodel', '{@localization[cleanchat]}}');
        }
        
        $data['content'] .= '<p>' . $this->container['core']->sitelink('./', '{@localization[adminpanel]}}') . '<br>';
        $data['content'] .= $this->container['core']->homelink() . '</p>';

        return $data;
    }    
}