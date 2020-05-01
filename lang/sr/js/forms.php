<?php
// (c) vavok.net
header("Content-type: text/javascript"); 

include("../../../include/strtup.php");

if (file_exists("../../serbian_cyrillic/isset.php")) {
	include("../../serbian_cyrillic/isset.php");
} else {
	include("../../serbian_latin/isset.php");
}

?>
var formsLocalized = {
<?php
$noItems = count($formsArray);
$i = 0;
foreach ($formsArray as $key => $value) {
	echo $key . ' : "' . $value . '"';

	if ($i < $noItems - 1) {
		echo ',' . "\n";
	} else {
		echo "\n";
	}

	$i++;
}

?>
}