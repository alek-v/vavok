<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Traits\Files;

class Logfiles extends BaseModel {
    use Files;

    /**
     * Read data from log files
     */
    public function index()
    {
        $this_page['tname'] = 'Log data';
        $this_page['content'] = '';

        if (!$this->user->userAuthenticated() || !$this->user->administrator(101)) $this->redirection("./?isset=ap_noaccess");

        if ($this->postAndGet('action') == "delerlog" && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
            $this->clearFile(STORAGEDIR . "datalog/error401.dat");
            $this->clearFile(STORAGEDIR . "datalog/error402.dat");
            $this->clearFile(STORAGEDIR . "datalog/error403.dat");
            $this->clearFile(STORAGEDIR . "datalog/error404.dat");
            $this->clearFile(STORAGEDIR . "datalog/error406.dat");
            $this->clearFile(STORAGEDIR . "datalog/error500.dat");
            $this->clearFile(STORAGEDIR . "datalog/error502.dat");
            $this->clearFile(STORAGEDIR . "datalog/dberror.dat");
            $this->clearFile(STORAGEDIR . "datalog/error.dat");
            $this->clearFile(STORAGEDIR . "datalog/ban.dat");
        
            $this->redirection(HOMEDIR . 'adminpanel/logfiles?isset=mp_dellogs');
        }

        if ($this->postAndGet('action') == 'delerid' && !empty($this->postAndGet('err')) && ($_SESSION['permissions'] == 101 or $_SESSION['permissions'] == 102)) {
            $err = $this->postAndGet('err');
            $this->clearFile(STORAGEDIR . 'datalog/' . $err . '.dat');

            $this->redirection(HOMEDIR . 'adminpanel/logfiles?isset=mp_dellogs');
        }

        $list = isset($_GET['list']) ? $list = $this->check($_GET['list']) : $list = '404';
        
        $config_loglist = 10;

        $errorFile = array(
            '401' => STORAGEDIR . 'datalog/error401.dat',
            '402' => STORAGEDIR . 'datalog/error402.dat',
            '403' => STORAGEDIR . 'datalog/error403.dat',
            '404' => STORAGEDIR . 'datalog/error404.dat',
            '406' => STORAGEDIR . 'datalog/error406.dat',
            '500' => STORAGEDIR . 'datalog/error500.dat',
            '502' => STORAGEDIR . 'datalog/error502.dat',
            'other' => STORAGEDIR . 'datalog/error.dat'
        );

        if (!empty(file_get_contents($errorFile['401']))) {
            $time401 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['401']));
        } else {
            $time401 = '';
        }

        if (!empty(file_get_contents($errorFile['402']))) {
            $time402 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['402']));
        } else {
            $time402 = '';
        }

        if (!empty(file_get_contents($errorFile['403']))) {
            $time403 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['403']));
        } else {
            $time403 = '';
        }

        if (!empty(file_get_contents($errorFile['404']))) {
            $time404 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['404']));
        } else {
            $time404 = '';
        }

        if (!empty(file_get_contents($errorFile['406']))) {
            $time406 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['406']));
        } else {
            $time406 = '';
        }

        if (!empty(file_get_contents($errorFile['500']))) {
            $time500 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['500']));
        } else {
            $time500 = '';
        }

        if (!empty(file_get_contents($errorFile['502']))) {
            $time502 =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['502']));
        } else {
            $time502 = '';
        }

        if (!empty(file_get_contents($errorFile['other']))) {
            $timeother =  ' - ' . date($this->localization->showAll()['ln_datefmt'], filemtime($errorFile['other']));
        } else {
            $timeother = '';
        }

        $listNames = array(
            '401' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=401', '<strong><u>401</u>' . $time401 . '</strong>'), 
            '402' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=402', '<strong><u>402</u>' . $time402 . '</strong>'),
            '403' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=403', '<strong><u>403</u>' . $time403 . '</strong>'),
            '404' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=404', '<strong><u>404</u>' . $time404 . '</strong>'),
            '406' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=406', '<strong><u>406</u>' . $time406 . '</strong>'),
            '500' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=500', '<strong><u>500</u>' . $time500 . '</strong>'),
            '502' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=502', '<strong><u>502</u>' . $time502 . '</strong>'),
            'other' => $this->sitelink(HOMEDIR . 'adminpanel/logfiles?list=other', '<strong><u>{@localization[other]}}</u>' . $timeother . '</strong>')
        );

        function getLogNavigation($listNames) {
            $last = count($listNames) - 1;
            $i = 0;

            $nav = "\n<p>";

            foreach ($listNames as $item) {
                if ($last != $i) {
                $nav .= $item . ' ';
                } else {
                    $nav .= $item;
                }

                $i++;
            }

            $nav .= "</p>\n";

            return $nav;
        }

        if ($list == 401) {
            $this_page['content'] .= '<p>{@localization[error_401]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error401.dat');
            $err = 'error401';
        }
        if ($list == 402) {
            $this_page['content'] .= '<p>{@localization[error_402]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error402.dat');
            $err = 'error402';
        }
        if ($list == 403) {
            $this_page['content'] .= '<p>{@localization[error_403]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error403.dat');
            $err = 'error403';
        }
        if ($list == 404) {
            $this_page['content'] .= '<p>{@localization[error_404]}}</p>';

            $this_page['content'] .= getLogNavigation($listNames);

            $opis = $this->getDataFile('datalog/error404.dat');
            $err = 'error404';
        }
        if ($list == 406) {
            $this_page['content'] .= '<p>{@localization[error_406]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error406.dat');
            $err = 'error406';
        }
        if ($list == 500) {
            $this_page['content'] .= '<p>{@localization[error_500]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error500.dat');
            $err = 'error500';
        }
        if ($list == 502) {
            $this_page['content'] .= '<p>{@localization[error_502]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error502.dat');
            $err = 'error502';
        }
        if ($list == 'other') {
            $this_page['content'] .= '<p>{@localization[other]}}</p>';
        
            $this_page['content'] .= getLogNavigation($listNames);
        
            $opis = $this->getDataFile('datalog/error.dat');
            $err = 'error';
        }

        $opis = array_reverse($opis);
        $total = count($opis);

        if ($total < 1) {
            $this_page['content'] .= '<p><img src="{@HOMEDIR}}themes/images/img/reload.gif" alt=""> <b>{@localization[noentry]}}</b></p>';
        }

        $navigation = new Navigation($config_loglist, $total, HOMEDIR . 'adminpanel/logfiles?list=' . $list. '&'); // start navigation

        $start = $navigation->start()['start']; // starting point
        $end = $navigation->start()['end']; // ending point

        for ($i = $start; $i < $end; $i++) {
            $dtlog = explode(":|:", $opis[$i]);
        
            $this_page['content'] .= '<img src="{@HOMEDIR}}themes/images/img/files.gif" alt=""> <b><font color="#FF0000">Fajl: ' . $dtlog[2] . '</font></b><br>';
            $this_page['content'] .= $this->localization->string('time') . ': ' . $this->correctDate((int)$dtlog[3], 'd.m.Y. / H:i:s') . '<br>';
            $this_page['content'] .= 'Referer: ' . $dtlog[7] . '<br>';
            $this_page['content'] .= 'Host: ' . $dtlog[5] . '<br>';
            $this_page['content'] .= $this->localization->string('user') . ': ' . $dtlog[8] . ' (IP: <a href="{@HOMEDIR}}adminpanel/ip_information?ip=' . $dtlog[4] . '" target="_blank">' . $dtlog[4] . '</a> / Browser: ' . $dtlog[6] . ')<hr>';
        }

        $this_page['content'] .= $navigation->getNavigation();

        if ($this->user->administrator()) {
            if (isset($err)) {
                $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/logfiles?action=delerid&err=' . $err, $this->localization->string('delaboerr') . ' ' . $err) . '<br>';
            }

            $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/logfiles?action=delerlog', $this->localization->string('delallerdata')) . '<br>';
        }

        $this_page['content'] .= '<p>' . $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $this_page['content'] .= $this->homelink() . '</p>';

        return $this_page;
    }
}