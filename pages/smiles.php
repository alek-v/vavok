<?php
// (c) Aleksandar Vranešević - vavok.net
// updated 26.04.2020. 22:35:59

require_once"../include/strtup.php";

$page = isset($_GET['page']) ? check($_GET['page']) : 1;

$my_title = $lang_home['smile'];
include_once"../themes/$config_themes/index.php";


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

$limit_start = ($page - 1) * $smilesPerPage;

if ($total < $limit_start + $smilesPerPage) {
    $end = $total;
} else {
    $end = $limit_start + $smilesPerPage;
} 
for ($i = $limit_start; $i < $end; $i++) {
    $smkod = str_replace(".gif", "", $a[$i]);

    echo '<img src="' . BASEDIR . 'images/smiles/' . $a[$i] . '" alt="' . $a[$i] . '" />';
    if ($smkod != ';)') {
    echo '- :' . $smkod . '<br>';
  } else {
  	echo '- ' . $smkod . '<br>';
  }
} 


$navigation = new Navigation($smilesPerPage, $total, $page, 'smiles.php?');

echo '<p>';
echo $navigation->get_navigation();
echo '</p>';

echo '<p><br>' . $lang_page['totsmiles'] . ': <b>' . (int)$total . '</b><br><br>';
echo '<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';


include_once"../themes/$config_themes/foot.php";

?>