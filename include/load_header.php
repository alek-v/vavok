<?php
// (c) Aleksandar Vranešević - https://vavok.net
// modified: 02.08.2020. 16:42:28
// load headers for page

// include custom (user made) <head> tags for all pages
echo file_get_contents(BASEDIR . 'used/headmeta.dat');

// include head tags specified for current page
if (!empty($current_page->head_tags)) { echo $current_page->head_tags; }

// cookie consent
if ($vavok->get_configuration('cookieConsent') == 1) { include_once BASEDIR . "include/plugins/cookie-consent/cookie-consent.php"; }

echo "\r\n<!-- Vavok CMS http://www.vavok.net -->
<title>" . $current_page->page_title . "</title>\r\n";

?>