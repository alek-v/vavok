<?php 
// vavok.net
if (!defined('BASEDIR')) {
    $folder_level = "";
    while (!file_exists($folder_level . "robots.txt")) {
        $folder_level .= "../";
    } 
    define("BASEDIR", $folder_level);
} 

require_once BASEDIR . "include/startup.php";

$ip = $users->find_ip();

if (isset($_GET['error'])) {
    $error = check($_GET['error']);
} 

if ($error == '404') {
    header("HTTP/1.0 404 Not Found");
} 

include_once BASEDIR . "themes/$config_themes/index.php";

$http_referer = !empty($_SERVER['HTTP_REFERER']) ? check($_SERVER['HTTP_REFERER']) : 'No referer';

$http_referer = str_replace(":|:", "|", $http_referer);
$request_uri = check(urldecode($_SERVER['REQUEST_URI']));
$request_uri = str_replace(":|:", "|", $request_uri);
$phpself = $_SERVER['PHP_SELF'];
$phpself = str_replace("/pages/error.php", "", $phpself);
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
    echo $lang_error['err401'] . '.<br>';
    $logdat = BASEDIR . "used/datalog/error401.dat";
    $write = ':|:Error 401:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
} elseif ($error == '402') {
    echo $lang_error['err402'] . '.<br>';
    $logdat = BASEDIR . "used/datalog/error402.dat";
    $write = ':|:Error 402:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
} elseif ($error == '403') {
    echo $lang_error['err403'] . '.<br>';

    $write = ':|:Error 403:|:' . $phpself . '' . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
    $logdat = BASEDIR . "used/datalog/error403.dat";
} elseif ($error == '404') {
    echo $lang_error['err404youtrytoop'] . ' ' . $config_srvhost . '' . $phpself . $request_uri . '<br>' . $lang_error['filenotfound'] . '.<br>';

    $write = ':|:Error 404:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
    $logdat = BASEDIR . "used/datalog/error404.dat";
} elseif ($error == '406') {
    echo $lang_error['err406descr'] . ' ' . $config_srvhost . '' . $phpself . $request_uri . ' ' . $lang_error['notfonserver'] . '.<br>';

    $write = ':|:406 - Not acceptable:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
    $logdat = BASEDIR . "used/datalog/error406.dat";
} elseif ($error == '500') {
    echo $lang_error['err500'] . '.<br>';
    $logdat = BASEDIR . "used/datalog/error500.dat";
    $write = ':|:500 - Internal server error:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
} elseif ($error == '502') {
    echo $lang_error['err502'] . '.<br>';
    $logdat = BASEDIR . "used/datalog/error502.dat";
    $write = ':|:Error 502:|:' . $phpself . $request_uri . ':|:' . $datetime . ':|:' . $ip . ':|:' . $hostname . ':|:' . $users->user_browser() . ':|:' . $http_referer . ':|:' . $users->show_username() . ':|:';
} elseif ($error == "db") {
    $line = 0;
    $file = file(BASEDIR . "used/datalog/dberror.dat");
    $file = explode(":|:", $file[0]); // dberror:|:time error start:|:
    $dberrorstart = $file[1];
    $nowtime = time();
    $timeresult = $nowtime - $dberrorstart;

    if ($timeresult > 300) { // 300 = 5 minutes
        $dberdate = date_fixed($dberrorstart, 'd-M-Y', '');
        $dbertime = date_fixed($dberrorstart, 'H-i-s', '');
        $subject = 'Database down';
        $mailtext = $lang_error['dbdownmail'] . " " . $dberdate . " " . $dbertime . "\n";
        sendmail($config["adminEmail"], $subject, $mailtext); // email to me
    } 

    if ($timeresult > 28800) { // 28800 = 8 hours
        $text = 'dberror:|:' . $nowtime . ':|:';

        if (isset($line)) {
            $file = file(BASEDIR . "used/datalog/dberror.dat");
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

    echo '<b>' . $lang_error['dberrmsg'] . '</b><br>';
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
    if ($i >= $config["maxLogData"]) {
        $fp = fopen($logdat, "w");
        flock ($fp, LOCK_EX);
        unset($file[0]);
        unset($file[1]);
        fputs($fp, implode("", $file));
        flock ($fp, LOCK_UN);
        fclose($fp);
    } 
} 

echo '<div class="break"></div>';
echo '<p><img src="' . HOMEDIR . 'images/img/homepage.gif" alt="" /> <a href="/" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><p>';

include_once BASEDIR . "themes/" . $config_themes . "/foot.php";

?>