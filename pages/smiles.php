<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$vavok->go('current_page')->page_title = $vavok->go('localization')->string('smile');
$vavok->require_header();

$dir = opendir (BASEDIR . "images/smiles");
while ($file = readdir ($dir)) {
    if (preg_match("/.gif/", $file)) {
        $a[] = $file;
    }
}
closedir ($dir);
sort($a);

$smilesPerPage = 15;
$total = count($a);

$navigation = new Navigation($smilesPerPage, $total, $vavok->post_and_get('page'), 'smiles.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point
$end = $navigation->start()['end']; // ending point

echo '<p>';

for ($i = $limit_start; $i < $end; $i++) {
    $smkod = str_replace(".gif", "", $a[$i]);

    echo '<img src="' . HOMEDIR . 'images/smiles/' . $a[$i] . '" alt="' . $a[$i] . '" /> ';

    if ($smkod == ')' || $smkod == '(' || $smkod == 'D' || $smkod == 'E' || $smkod == 'P') {
      echo '- :' . $smkod . '<br>';
    }
    elseif ($smkod == ';)') {
      echo '- ' . $smkod . '<br>';
    } else {
      echo '- :' . $smkod . ':<br>';
    }
}

echo '</p>';

echo $navigation->get_navigation();

echo '<p>' . $vavok->go('localization')->string('totsmiles') . ': <b>' . (int)$total . '</b></p>';
echo $vavok->homelink('<p>', '</p>');

$vavok->require_footer();

?>