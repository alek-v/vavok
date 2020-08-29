<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 15:23:50
 */

if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
}

require_once BASEDIR . "include/startup.php";

$ip = $users->find_ip();

if (isset($_GET['error'])) { $error = $vavok->check($_GET['error']); } 

if ($error == '404') { header("HTTP/1.0 404 Not Found"); } 

$vavok->require_header();

$http_referer = !empty($_SERVER['HTTP_REFERER']) ? $vavok->check($_SERVER['HTTP_REFERER']) : 'No referer';

$http_referer = str_replace(":|:", "|", $http_referer);
$request_uri = str_replace(":|:", "|", REQUEST_URI);
$phpself = str_replace("/pages/error.php", "", $_SERVER['PHP_SELF']);
$phpself = str_replace(":|:", "|", $phpself);
$hostname = gethostbyaddr($ip);
$hostname = str_replace(":|:", "|", $hostname);

$datetime = time();

if (empty($_SESSION['log'])) {
    $log = 'Guest';
} else {
    $log = $_SESSION['log'];
}

if ($error == '401') {
    echo $localization->string('err401') . '.<br>';
    $logdat = BASEDIR . "used/datalog/error401.dat";
    $write = ':|:Error 401:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
} elseif ($error == '402') {
    echo $localization->string('err402') . '.<br>';
    $logdat = BASEDIR . "used/datalog/error402.dat";
    $write = ':|:Error 402:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
} elseif ($error == '403') {
    echo $localization->string('err403') . '.<br>';

    $write = ':|:Error 403:|:' . $phpself . '' . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
    $logdat = BASEDIR . "used/datalog/error403.dat";
} elseif ($error == '404') {
    echo $localization->string('err404youtrytoop') . ' ' . $_SERVER['HTTP_HOST'] . $phpself . $request_uri . '<br>' . $localization->string('filenotfound') . '.<br>';

    $write = ':|:Error 404:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
    $logdat = BASEDIR . "used/datalog/error404.dat";
} elseif ($error == '406') {
    echo $localization->string('err406descr') . ' ' . $_SERVER['HTTP_HOST'] . $phpself . $request_uri . ' ' . $localization->string('notfonserver') . '.<br>';

    $write = ':|:406 - Not acceptable:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
    $logdat = BASEDIR . "used/datalog/error406.dat";
} elseif ($error == '500') {
    echo $localization->string('err500') . '.<br>';
    $logdat = BASEDIR . "used/datalog/error500.dat";
    $write = ':|:500 - Internal server error:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
} elseif ($error == '502') {
    echo $localization->string('err502') . '.<br>';
    $logdat = BASEDIR . "used/datalog/error502.dat";
    $write = ':|:Error 502:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';
} elseif ($error == "db") {
    $line = 0;
    $file = $vavok->get_data_file('datalog/dberror.dat');
    $file = explode(":|:", $file[0]); // dberror:|:time error start:|:
    $dberrorstart = $file[1];
    $nowtime = time();
    $timeresult = $nowtime - $dberrorstart;

    if ($timeresult > 300) { // 300 = 5 minutes
        $dberdate = $vavok->date_fixed($dberrorstart, 'd-M-Y', '');
        $dbertime = $vavok->date_fixed($dberrorstart, 'H-i-s', '');
        $subject = 'Database down';
        $mailtext = $localization->string('dbdownmail') . " " . $dberdate . " " . $dbertime . "\n";
        sendmail($vavok->get_configuration('adminEmail'), $subject, $mailtext); // email to me
    }

    if ($timeresult > 28800) { // 28800 = 8 hours
        $text = 'dberror:|:' . $nowtime . ':|:';

        if (isset($line)) {
            $file = $vavok->get_data_file('datalog/dberror.dat');
            $fp = fopen(BASEDIR . "used/datalog/dberror.dat", "a+");
            flock ($fp, LOCK_EX);
            ftruncate ($fp, 0);
            for ($i = 0;$i < sizeof($file);$i++) {
                if ($line != $i) {
                    fputs($fp, $file[$i]);
                } else {
                    fputs($fp, "$text\r\n");
                } 
            } 
            fflush ($fp);
            flock ($fp, LOCK_UN);
            fclose($fp);
        }
    }

    echo '<b>' . $localization->string('dberrmsg') . '</b><br>';
} else {
    $logdat = BASEDIR . "used/datalog/error.dat";
    $write = ':|:Unknown error:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
}

if (isset($write) && !empty($logdat)) {
    $fp = fopen($logdat, "a+");
    flock ($fp, LOCK_EX);
    fputs($fp, "$write\r\n");
    flock ($fp, LOCK_UN);
    fclose($fp);
    chmod ($logdat, 0666);

    $file = file($logdat);
    $i = count($file);
    if ($i >= $vavok->get_configuration('maxLogData')) {
        $fp = fopen($logdat, "w");
        flock ($fp, LOCK_EX);
        unset($file[0]);
        unset($file[1]);
        fputs($fp, implode("", $file));
        flock ($fp, LOCK_UN);
        fclose($fp);
    }
}

echo $vavok->homelink('<p>', '</p>');

include_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>