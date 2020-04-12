<?php
// (c) v a v o k . n e t

require_once"../include/strtup.php";

$my_title = $lang_home['smile'];
include_once"../themes/$config_themes/index.php";

if (isset($_GET['isset'])) {
	$isset = check($_GET['isset']);
	echo '<div align="center"><b><font color="#FF0000">';
	echo get_isset();
	echo '</font></b></div>';
}


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

new Navigation($smilesPerPage, $total);

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



echo Navigation::siteNavigation('smiles.php?', $smilesPerPage, $page, $total);

echo '<br><br>' . $lang_page['totsmiles'] . ': <b>' . (int)$total . '</b><br><br>';
echo '<img src="../images/img/homepage.gif" alt=""> <a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a>';


include_once"../themes/$config_themes/foot.php";

?>