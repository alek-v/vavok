 <?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if ($vavok->get_configuration('siteOff') != 1) { $vavok->redirect_to("../"); }

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('maintenance');
$vavok->require_header();

echo '<p>' . $vavok->go('localization')->string('maintenance_msg') . '!</p>';

echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();
?>