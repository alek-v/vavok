<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!$users->is_reg() || (!$users->is_moderator() && !$users->is_administrator())) {
    redirect_to("../pages/input.php?action=exit");
}

$ip = check($_GET['ip']);

if (empty($ip)) { exit('please set ip address'); }

// Get an array with geoip-infodata
function geo_check_ip($ip) {

    // check, if the provided ip is valid
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        throw new InvalidArgumentException("IP is not valid");
    } 

    // contact ip-server
    $response = @file_get_contents('http://ip-api.com/json/' . $ip);

    if (empty($response)) {
        throw new InvalidArgumentException("Error contacting Geo-IP-Server");
    } 

    // Return result as array
    return json_decode($response, true);

} 

$ipData = geo_check_ip($ip);

$my_title = 'IP Informations';
include'../themes/' . $config_themes . '/index.php';

?>

<h1>Informations about IP</h1>

<div class="b">

<?php

echo 'IP Address: ' . $ip . '<br />';
echo 'Country: ' . $ipData['country'] . '<br />';
echo 'State/Region: ' . $ipData['regionName'] . '<br />';
echo 'City/Town: ' . $ipData['city'] . '<br />';
echo '</div>';

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include'../themes/' . $config_themes . '/foot.php';

?>