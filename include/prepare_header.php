<?php
// (c) Aleksandar Vranešević - https://vavok.net
// modified: 24.07.2020. 16:28:49
// prepare headers for page

// Update page title if page is not dynamic
if (isset($my_title)) { $current_page->page_title = $my_title; }

// load head tags for specific page
$head_tag = '';

// if we are not installing cms
if (!stristr($_SERVER['PHP_SELF'], 'install/install.php')) {

    // is this regular page
    if (!empty($pg)) {

        $vk_page = $db->get_data('pages', "pname='" . $pg . "' AND lang='" . $ln_loc . "'");
        $head_tag .= $vk_page['headt'];

        if (empty($head_tag)) {
            $vk_page = $db->get_data('pages', "pname='" . $pg . "'");
            $head_tag .= $vk_page['headt'];
        }

    } 

    // no data, try PHP_SELF
    if (empty($head_tag)) {
        $vk_page = $db->get_data('pages', "pname='" . $_SERVER['PHP_SELF'] . "'");
        if (!empty($vk_page['headt'])) {
            $head_tag .= $vk_page['headt'];
        } 
    } 


    // if it is main page
    if ($_SERVER['PHP_SELF'] == '/index.php' && empty($head_tag)) {

        // first we check is there a page with language we use
        $vk_page = $db->get_data('pages', "pname='index' AND lang='" . $ln_loc . "'");

        $head_tag .= $vk_page['headt'];

        if (empty($vk_page['headt'])) {
            // load default index page title
            $vk_page = $db->get_data('pages', "pname='index'");
            $head_tag .= $vk_page['headt'];

        }

    }

}

// append head tags for specific pages
if (!empty($genHeadTag)) { $head_tag .= $genHeadTag; }


// check for missing tags

// tell bots what is our preferred page
if (!stristr($head_tag, 'rel="canonical"') && isset($pg)) { $head_tag .= "\n" . '<link rel="canonical" href="' . transfer_protocol() . $_SERVER['HTTP_HOST'] . '/page/' . $pg . '/" />'; }

// add missing open graph tags
if (!strstr($head_tag, 'og:type')) { $head_tag .= "\n" . '<meta property="og:type" content="website" />'; }

if (!strstr($head_tag, 'og:title') && !empty($vk_page['tname'])) { $head_tag .= "\n" . '<meta property="og:title" content="' . $vk_page['tname'] . '" />'; }



?>