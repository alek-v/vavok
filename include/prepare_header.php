<?php
// (c) Aleksandar Vranešević - https://vavok.net
// modified: 10.04.2020. 19:53:17
// prepare headers for page

// get page title
if (!isset($my_title)) { $my_title = page_title($phpself); }

// if we still dont have a page title use website main title
if (empty($my_title)) { $my_title = $config["title"]; }

// load head tags for specific page
$head_tag = '';

// if we are not installing cms
if (!stristr($phpself, 'install/install.php')) {
    // is this regular page
    if (!empty($pg)) {
        $vk_page = $db->select('pages', "pname='" . $pg . "'", '', 'headt');
        $head_tag .= $vk_page['headt'];
    } 
    // if data not found, contnue...
    // this is used if this is custom page
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $clean_requri . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag .= $vk_page['headt'];
        } 
    } 
    // no data using $clean_requri, try PHP_SELF :)
    if (empty($head_tag)) {
        $vk_page = $db->select('pages', "pname='" . $phpself . "'", '', 'headt');
        if (!empty($vk_page['headt'])) {
            $head_tag .= $vk_page['headt'];
        } 
    } 
}

// append head tags for specific pages
if (!empty($genHeadTag)) { $head_tag .= $genHeadTag; }

// tell bots what is our preferred page
if (stristr($head_tag, 'rel="canonical"') === false && isset($pg)) { $head_tag .= "\n" . '<link rel="canonical" href="' . $connectionProtocol . $config_srvhost . '/page/' . $pg . '/" />'; }




?>