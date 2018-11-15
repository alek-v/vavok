<?php
// (c) vavok.net
header("Content-type: text/javascript"); 

include("../../../include/strtup.php");

include("../../english/isset.php");
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