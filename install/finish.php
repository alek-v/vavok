<?php 
// (c) vavok.net
// /install/install.php have disabled connection to db in strtup.php, so I manipulate with db in this page
require_once"../include/strtup.php";
require_once "../lang/" . $config["language"] . "/installinstall.php";

$my_title = 'Install';
require_once"header.php";

if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

if (isset($_GET['step'])) {
    $step = $_GET['step'];
} else {
    $step = $_POST['step'];
} 

$sitetime = time();
$datex = date("d.m.y");
$timex = date("H:i");

if ($step == 'second') {
    // import mysql data
    // Name of the file
    $filename = 'mysql/mysql_main.sql';
    // MySQL host
    $mysql_host = $_GET['host'];
    // MySQL username
    $mysql_username = $_GET['user'];
    // MySQL password
    $mysql_password = $_GET['pass'];
    // Database name
    $mysql_database = $_GET['db']; 
    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue; 
        // Add this line to the current segment
        $templine .= $line; 
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            $db->query($templine); 
            // Reset temp variable to empty
            $templine = '';
        } 
    } 

    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['secondstep'] . ' - ' . $lang_install['inserttint'] . '<br></p>';

    echo '<p><img src="../images/img/reload.gif" alt="" /> ' . $lang_install['dataadded'] . '!<br /></p>';
    echo '<p><a href="finish.php?step=third" class="sitelink">' . $lang_install['thirdstep'] . '</a></p>';
} 

if ($step == 'third') {
    echo '<p><img src="../images/img/partners.gif" alt="" /> ' . $lang_install['installinfo'] . '<br></p><hr />'; 
    // echo 'Please use only alphanumeric characters<br>';
    ?>

    <form method="post" action="finish.php?step=regadmin">
    <fieldset>
    <legend><?php echo $lang_install['thirdstep'];
    ?></legend>
    <label for="name">Username (max20):</label><br />
    <input name="name" id="name" maxlength="20" /><br />
    <label for="password">Password (max20):</label><br />
    <input name="password" id="password" type="password" maxlength="20" /><br />
    <label for="password2">Password again:</label><br />
    <input name="password2" id="password2" type="password" maxlength="20" /><br>
    <label for="email">Email:</label><br />
    <input name="email" id="email" maxlength="100" /><br />
    <label for="osite">Site address:</label><br />
    <input name="osite" id="osite" value="<?php echo $connectionProtocol . $config_srvhost; ?>" maxlength="100" /><br />
    <input value="Continue..." type="submit" />
    </fieldset>
    </form>
    <hr />

<?php
} 
// instalation results
if ($step == "regadmin") {
    echo '<p><img src="../images/img/partners.gif" alt=""> ' . $lang_install['installresults'] . '<br></p>';

    $str1 = strlen($_POST['name']);
    $str2 = strlen($_POST['password']);

    $name = $_POST['name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $osite = $_POST['osite'];
    $password2 = $_POST['password2'];

    if ($name != "" && $password != "" && $email != "" && $osite != "") {
        if ($str1 < 21 && $str1 > 2 && $str2 < 21 && $str2 > 2) {
            if (!preg_match('/[^0-9A-Za-z.\_\-]/', $name) && !preg_match('/[^0-9A-Za-z.\_\-]/', $password)) {
                if ($password == $password2) {
                    if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email)) {
                        if (preg_match('#^http://([a-z0-9_\-\.])+(\.([a-z0-9\/])+)+$#', $osite)) {
                            $osite_name = ucfirst(str_replace("http://", "", $osite));
                            $osite_name = str_replace("https://", "", $osite_name);

                            $passwords = md5($password);

                            $ufile = file_get_contents(BASEDIR . "used/config.dat");
                            $udata = explode("|", $ufile);

                            $udata[0] = 0;
                            $udata[1] = generate_password();
                            $udata[2] = 'default';
                            $udata[3] = 0;
                            $udata[4] = 0;
                            $udata[5] = 0;
                            $udata[6] = 0;
                            $udata[7] = 0;
                            $udata[8] = $name;
                            $udata[9] = $email;
                            $udata[10] = 0; // time zone
                            $udata[11] = $osite_name;
                            $udata[12] = 'default';
                            $udata[14] = $osite;
                            $udata[17] = 5;
                            $udata[18] = 0;
                            $udata[19] = 5;
                            $udata[20] = 0;
                            $udata[22] = 200;
                            $udata[23] = 5;
                            $udata[24] = 100;
                            $udata[25] = 1000;
                            $udata[26] = 5;
                            $udata[27] = 5;
                            $udata[28] = 100;
                            $udata[29] = 10;
                            $udata[30] = 0;
                            $udata[31] = 5;
                            $udata[33] = 4;
                            $udata[34] = 100;
                            $udata[35] = 0;
                            $udata[36] = 0;
                            $udata[37] = 5;
                            $udata[38] = 40000;
                            $udata[39] = 320;
                            $udata[40] = 5;
                            $udata[41] = 600;
                            $udata[42] = 5;
                            $udata[44] = 5;
                            $udata[45] = 15;
                            $udata[46] = 5;
                            $udata[47] = 'english';
                            $udata[48] = 'adminpanel';
                            $udata[49] = 0;
                            $udata[50] = 1;
                            $udata[51] = 100;
                            $udata[52] = 1;
                            $udata[53] = 0;
                            $udata[54] = 5;
                            $udata[55] = 6;
                            $udata[56] = 50;
                            $udata[57] = 400;
                            $udata[58] = 200;
                            $udata[59] = 0;
                            $udata[60] = 5;
                            $udata[61] = 1;
                            $udata[62] = 0;
                            $udata[63] = 0;
                            $udata[64] = 0;
                            $udata[65] = 0;
                            $udata[66] = 15;
                            $udata[67] = 0;
                            $udata[68] = 0;
                            $udata[69] = 1;
                            $udata[70] = 0;
                            $udata[71] = 0;
                            $udata[72] = 0;
                            $udata[74] = 6;
                            $udata[76] = 43200;

                            $utext = '';
                            for ($u = 0; $u < $config["configKeys"]; $u++) {
                                $utext .= $udata[$u] . '|';
                            } 

                            if (!empty($udata[8]) && !empty($udata[9])) {
                                $fp = fopen(BASEDIR . "used/config.dat", "a+");
                                flock($fp, LOCK_EX);
                                ftruncate($fp, 0);
                                fputs($fp, $utext);
                                fflush($fp);
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                unset($utext);
                            } 
                            // write to database
                            $registration_key = '';
                            $config["regConfirm"] = '0';
                            register($name, $passwords, $sitetime, $config["regConfirm"], $registration_key, 'default', $brow, $ip, $email); // register user
                            $user_id = getidfromnick($name);
                            $db->update('vavok_users', 'perm', 101, "id='" . $user_id . "'");

                            echo '<p>' . $lang_install['installok'] . '.<br></p>';

                            echo '<p><img src="../images/img/reload.gif" alt="" /> <b><a href="../input.php?log=' . $name . '&amp;pass=' . $password . '&amp;cookietrue=1">' . $lang_install['logintosite'] . '</a></b></p>';
                        } else {
                            echo '<p><b>' . $lang_install['siteaddressbad'] . '</b></p>';
                            echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
                        } 
                    } else {
                        echo '<p><b>' . $lang_install['bademail'] . '</b></p>';
                        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
                    } 
                } else {
                    echo '<p><b>' . $lang_install['badagainbass'] . '.</b></p>';
                    echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
                } 
            } else {
                echo '<p><b>' . $lang_install['onlylatin'] . '!</b></p>';
                echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
            } 
        } else {
            echo '<p><b>' . $lang_install['shortuserpass'] . '</b></p>';
            echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
        } 
    } else {
        echo '<p><b>' . $lang_install['fillfields'] . '.</b></p>';
        echo '<p><img src="' . BASEDIR . 'images/img/back.gif" alt="" /> <a href="finish.php?step=third">' . $lang_home['back'] . '</a></p>';
    } 
}

require_once "footer.php";
?>
