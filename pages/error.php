<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

if (!defined('BASEDIR')) {
    $folder_level = '';
    while (!file_exists($folder_level . 'robots.txt')) {
        $folder_level .= '../';
    }
    define("BASEDIR", $folder_level);
}

require_once BASEDIR . 'include/startup.php';

if ($vavok->post_and_get('error') == '404') { header("HTTP/1.0 404 Not Found"); }

$vavok->go('current_page')->page_title = 'Error - Ooops';

$vavok->require_header();

$http_referer = !empty($_SERVER['HTTP_REFERER']) ? $vavok->check($_SERVER['HTTP_REFERER']) : 'No referer';
$http_referer = str_replace(':|:', '|', $http_referer);
$request_uri = str_replace(':|:', '|', REQUEST_URI);
$phpself = str_replace('/pages/error.php', '', $_SERVER['PHP_SELF']);
$phpself = str_replace(':|:', '|', $phpself);
$hostname = gethostbyaddr($vavok->go('users')->find_ip());
$hostname = str_replace(':|:', '|', $hostname);

$log = !empty($vavok->go('users')->username) ? $vavok->go('users')->username : 'Guest';

$write_data = $phpself . $request_uri . ':|:' . time() . ':|:' . $vavok->go('users')->find_ip() . ':|:' . $hostname . ':|:' . $vavok->go('users')->user_browser() . ':|:' . $http_referer . ':|:' . $log . ':|:';

$error_number_info = '';
$additional_error_info = '';

if ($vavok->post_and_get('error') == '401') {
    $error_number_info = $vavok->go('localization')->string('err401');
    $logdat = "datalog/error401.dat";
    $write = ':|:Error 401:|:' . $write_data;
} elseif ($vavok->post_and_get('error') == '402') {
    $error_number_info =  $vavok->go('localization')->string('err402');
    $logdat = "datalog/error402.dat";
    $write = ':|:Error 402:|:' . $write_data;
} elseif ($vavok->post_and_get('error') == '403') {
    $error_number_info = $vavok->go('localization')->string('err403');
    $write = ':|:Error 403:|:' . $write_data;
    $logdat = "datalog/error403.dat";
} elseif ($vavok->post_and_get('error') == '404') {
    $error_number_info = $vavok->go('localization')->string('err404youtrytoop') . ' ' . $_SERVER['HTTP_HOST'] . $phpself . $request_uri;
    $additional_error_info = $vavok->go('localization')->string('filenotfound');
    $write = ':|:Error 404:|:' . $write_data;
    $logdat = 'datalog/error404.dat';
} elseif ($vavok->post_and_get('error') == '406') {
    $error_number_info = $vavok->go('localization')->string('err406descr') . ' ' . $_SERVER['HTTP_HOST'] . $phpself . $request_uri . ' ' . $vavok->go('localization')->string('notfonserver');
    $write = ':|:406 - Not acceptable:|:' . $write_data;
    $logdat = "datalog/error406.dat";
} elseif ($vavok->post_and_get('error') == '500') {
    $error_number_info = $vavok->go('localization')->string('err500');
    $logdat = "datalog/error500.dat";
    $write = ':|:500 - Internal server error:|:' . $write_data;
} elseif ($vavok->post_and_get('error') == '502') {
    $error_number_info = $vavok->go('localization')->string('err502');
    $logdat = "datalog/error502.dat";
    $write = ':|:Error 502:|:' . $write_data;
} else {
    $logdat = "datalog/error.dat";
    $write = ':|:Unknown error:|:' . $write_data;
}

echo '<p>' . $error_number_info . '</p>';
echo '<p>' . $additional_error_info . '</p>';

if (isset($write) && !empty($logdat)) {
    // Write new data to log file
    $vavok->write_data_file($logdat, $write . PHP_EOL, 1);

    // Remove lines from file
    $vavok->limit_file_lines(BASEDIR . 'used/' . $logdat, $vavok->get_configuration('maxLogData'));
}

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>