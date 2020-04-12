<?php
// (c) Aleksandar Vranešević - https://vavok.net
// modified: 11.04.2020. 2:46:27
// load headers for page


// include custom (user made) <head> tags for all pages
echo file_get_contents(BASEDIR . 'used/headmeta.dat');

// include head tags specified for current page
if (!empty($head_tag)) { echo $head_tag; }

// cookie consent
if ($config['cookieConsent'] == 1) { include_once BASEDIR . "include/plugins/cookie-consent/cookie-consent.php"; }

echo "\r\n<!-- Vavok CMS http://www.vavok.net -->
<title>" . $my_title . "</title>\r\n";


?>