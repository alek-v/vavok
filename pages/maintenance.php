 <?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 1:31:07
 */

require_once"../include/startup.php";

if ($vavok->get_configuration('siteOff') != 1) { $vavok->redirect_to("../"); }

$current_page->page_title = $localization->string('maintenance');
$vavok->require_header();

echo '<p>' . $localization->string('maintenance_msg') . '!</p>';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();
?>