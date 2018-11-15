<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!is_reg() || (!ismod() && !isadmin())) {
header("Location: ../input.php?action=exit");
exit;
}

$ip = check($_GET['ip']);

if (empty($ip)) { exit('please set ip address'); }

$my_title = 'IP Informations';
include'../themes/' . $config_themes . '/index.php';

// Get an array with geoip-infodata
function geoCheckIP($ip) { 
    // check, if the provided ip is valid
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        throw new InvalidArgumentException("IP is not valid");
    } 
    // contact ip-server
    $response = @file_get_contents('http://www.netip.de/search?query=' . $ip);
    if (empty($response)) {
        throw new InvalidArgumentException("Error contacting Geo-IP-Server");
    } 
    // Array containing all regex-patterns necessary to extract ip-geoinfo from page
    $patterns = array();
    $patterns["domain"] = '#Domain: (.*?)&nbsp;#i';
    $patterns["country"] = '#Country: (.*?)&nbsp;#i';
    $patterns["state"] = '#State/Region: (.*?)<br#i';
    $patterns["town"] = '#City: (.*?)<br#i'; 
    // Array where results will be stored
    $ipInfo = array(); 
    // check response from ipserver for above patterns
    foreach ($patterns as $key => $pattern) {
        // store the result in array
        $ipInfo[$key] = preg_match($pattern, $response, $value) && !empty($value[1]) ? $value[1] : 'not found';
    } 

    return $ipInfo;
} 


// Array ( [domain] => dslb-094-219-040-096.pools.arcor-ip.net [country] => DE - Germany [state] => Hessen [town] => Erzhausen )
// Array ( [domain] => not found [country] => DE - Germany [state] => Hessen [town] => Heppenheim )
$ipData = geoCheckIP($ip);

?>

<h2>Informations about IP</h2>
<div class="b">

<?php
echo 'IP Address: ' . $ip . '<br />';
echo 'Country: ' . $ipData['country'] . '<br />';
echo 'State/Region: ' . $ipData['state'] . '<br />';
echo 'City/Town: ' . $ipData['town'] . '<br />';
echo 'Domain: ' . $ipData['domain'] . '<br />';
echo '</div>';
?>
<div class="clear"></div>
<div class="break"></div>

<?php
echo '<a href="./" class="sitelink">' . $lang_home['admpanel'] . '</a><br>';
echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a><br>';


include'../themes/' . $config_themes . '/foot.php';

?>