<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 2:59:39
*/

require_once"../include/startup.php";

$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : 1;

$current_page->page_title = $localization->string('smile');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";


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

$navigation = new Navigation($smilesPerPage, $total, $page, 'smiles.php?'); // start navigation

$limit_start = $navigation->start()['start']; // starting point
$end = $navigation->start()['end']; // ending point

echo '<p>';

for ($i = $limit_start; $i < $end; $i++) {

    $smkod = str_replace(".gif", "", $a[$i]);

    echo '<img src="' . BASEDIR . 'images/smiles/' . $a[$i] . '" alt="' . $a[$i] . '" /> ';

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

echo '<p>' . $localization->string('totsmiles') . ': <b>' . (int)$total . '</b></p>';
echo '<p><a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a></p>';


require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>