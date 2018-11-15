<?php
// (c) vavok.net

require_once "../include/strtup.php";
$my_title = 'Install';
include_once "header.php";
if (isset($_GET['isset'])) {
	$isset = check($_GET['isset']);
	echo '<div align="center"><b><font color="#FF0000">';
	echo get_isset();
	echo '</font></b></div>';
}

if (isset($_GET['act'])) {
	$act = $_GET['act'];
} elseif (isset($_POST['act'])) {
	$act = $_POST['act'];
} else {
	$act = '';
}

if (isset($_GET['step'])) {
	$step = $_GET['step'];
} elseif (isset($_POST['step'])) {
	$step = $_POST['step'];
} else {
	$step = '';
}

$sitetime = time();
$datex = date("d.m.y");
$timex = date("H:i");

// first step
if ($step == 'first' || empty($step) && empty($act)) {
?>
<p>By installing this software you agree to <a href="../eula.html">EULA</a><br /><br /></p>

<form method="post" action="install.php?step=first_end">
<fieldset>
<legend><?php echo $lang_install['firststepdatabase']; ?></legend>
<label for="dbhost">Database host (mostly localhost):</label><br />
<input name="dbhost" id="dbhost" maxlength="30" /><br />
<label for="database">Database name:</label><br />
<input name="database" id="database" maxlength="30" /><br />
<label for="dbusername">Database username:</label><br />
<input name="dbusername" id="dbusername" maxlength="30" /><br />
<label for="dbpass">Database password:</label><br />
<input name="dbpass" id="dbpass" maxlength="30" /><br />
<input value="Continue..." type="submit" />
</fieldset>
</form>
<hr />

<?php
} 

if ($step == 'first_end') {
    $ufile = file_get_contents("../used/config.dat");
    $udata = explode("|", $ufile);

    $udata[77] = $_POST['dbhost'];
    $udata[78] = $_POST['dbusername'];
    $udata[79] = $_POST['dbpass'];
    $udata[80] = $_POST['database'];

    $utext = '';
    for ($u = 0; $u < 100; $u++) {
        $utext .= $udata[$u] . '|';
    } 

    $fp = fopen(BASEDIR . "used/config.dat", "a+");
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fputs($fp, $utext);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    unset($utext);
    

// prepare to import mysql data
// MySQL host
$mysql_host = $_POST['dbhost'];
// MySQL username
$mysql_username = $_POST['dbusername'];
// MySQL password
$mysql_password = $_POST['dbpass'];
// database name
$mysql_database = $_POST['database'];
 


echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['firststepdatabase'] . '<br></p>';

echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $lang_install['dataadded'] . '!</p>';
echo '<p><a href="finish.php?step=second&amp;host=' . $mysql_host . '&amp;user=' . $mysql_username . '&amp;pass=' . $mysql_password . '&amp;db=' . $mysql_database . '">' . $lang_install['secondstep'] . '</a> - ' . strtolower($lang_install['inserttint']) . '</p>';

}

include_once"footer.php";
?>