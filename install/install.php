<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   28.07.2020. 11:25:21
*/

require_once "../include/startup.php";
include_once BASEDIR . "include/lang/" . get_configuration('language') . "/index.php";

$my_title = 'Install';
include_once "include/header.php";

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

$myconfig = new Config;

// first step
if ($step == 'first' || empty($step) && empty($act)) {
    ?>
    <p>By installing this software you agree to <a href="../LICENSE">license</a><br /><br /></p>

    <form method="post" action="install.php?step=first_end">
    <fieldset>
    <legend><?php echo $localization->string('firststepdatabase'); ?></legend>
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
    
    $values = array(
    77 => $_POST['dbhost'],
    78 => $_POST['dbusername'],
    79 => $_POST['dbpass'],
    80 => $_POST['database']
    );
    $myconfig->update($values);

    // prepare to import mysql data
    // MySQL host
    $mysql_host = $_POST['dbhost'];
    // MySQL username
    $mysql_username = $_POST['dbusername'];
    // MySQL password
    $mysql_password = $_POST['dbpass'];
    // database name
    $mysql_database = $_POST['database'];
     


    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $localization->string('firststepdatabase') . '<br></p>';

    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $localization->string('dataadded') . '!</p>';
    echo '<p><a href="finish.php?step=second&amp;host=' . $mysql_host . '&amp;user=' . $mysql_username . '&amp;pass=' . $mysql_password . '&amp;db=' . $mysql_database . '">' . $localization->string('secondstep') . '</a> - ' . strtolower($localization->string('inserttint')) . '</p>';

}

include_once"include/footer.php";
?>