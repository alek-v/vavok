<?php 
// (c) vavok.net

require_once '../include/startup.php';

if (!$vavok->go('users')->is_moderator() && !$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?auth_error');

$ip = $vavok->check($vavok->post_and_get('ip'));

if (empty($ip)) exit('please set ip address');

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

$vavok->go('current_page')->page_title = 'IP Informations';
$vavok->require_header();

?>

<h1>Informations about IP</h1>

<div class="b">

<?php
echo 'IP Address: ' . $ip . '<br />';
if (!empty($ipData) && isset($ipData['country'])) {
	echo 'Country: ' . $ipData['country'] . '<br />';
	echo 'State/Region: ' . $ipData['regionName'] . '<br />';
	echo 'City/Town: ' . $ipData['city'] . '<br />';
} else {
    echo '<p>No data available</p>';
}
echo '</div>';

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>