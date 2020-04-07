<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$my_title = $lang_reguser['registration'];

$mediaLikeButton = 'off'; // dont show like buttons

$log = check($_POST['log']);
$par = check($_POST['par']);
$pars = check($_POST['pars']);
$meil = check($_POST['meil']);
$pagetoload = !empty($_POST['ptl']) ? check($_POST['ptl']) : '';

$str1 = strlen($log);
$str2 = strlen($par);
if ($str1 > 20 || $str2 > 20) {
    header ("Location: registration.php?isset=biginfo");
    exit;
} elseif ($str1 < 3 || $str2 < 3) {
    header ("Location: registration.php?isset=smallinfo");
    exit;
} elseif (preg_match("/^([0-9,._-]+)$/", $log)) {
    header("Location: registration.php?isset=useletter");
    exit;
} elseif (preg_match("/[^a-z0-9-]/", $par)) {
    header ("Location: registration.php?isset=noreg");
    exit;
} elseif ($par !== $pars) {
    header ("Location: registration.php?isset=nopassword");
    exit;
} elseif ($log != "" && $par != "" && $meil != "") {
    include_once"../themes/$config_themes/index.php"; 
    // check email
    $check_mail = $db->count_row('vavok_about', "email='" . $meil . "'");
    if ($check_mail > 0) {
        $check_mail = "no";
    } 
    // check nick
    $check_users = $db->count_row('vavok_users', "name='" . $log . "'");
    if ($check_users > 0) {
        $check_users = "no";
    } 
    // check for '-'
    $substr_log = substr_count($log, "-");

    if ($substr_log <= 2) {
        if ($check_mail != "no") {
            if ($check_users != "no") {
                    if (isValidEmail($meil)) {
                        require_once '../include/plugins/securimage/securimage.php';
                        $securimage = new Securimage();

                        if ($securimage->check($_POST['captcha_code']) == true) {
                            $log = check($log);
                            $par = check($par);
                            $passwords = md5($par);

                            $mail = htmlspecialchars(stripslashes(strtolower($meil)));
                            $brow = check($brow);
                            $config_themes = check($config_themes);
                            $config["regConfirm"] = (int)$config["regConfirm"];
                            if ($config["regConfirm"] == "1") {
                                $registration_key = time() + 24 * 60 * 60;
                            } else {
                                $registration_key = '';
                            }
                            // /////////////
                            $regdate = time();
                            register($log, $passwords, $regdate, $config["regConfirm"], $registration_key, $config_themes, $brow, $ip, $mail); // register user
                             
                            // send email with reg. data
                            if ($config["regConfirm"] == "1") {
                                $needkey = "\r\n\r\n" . $lang_reguser['emailpart5'] . "\r\n" . $lang_reguser['yourkey'] . ": " . $registration_key . "\r\n" . $lang_reguser['emailpart6'] . ":\r\n\r\n" . $website_home_addr . "/pages/key.php?action=inkey&key=" . $registration_key . "\r\n\r\n" . $lang_reguser['emailpart7'] . "\r\n\r\n";
                            } else {
                                $needkey = "\r\n\r\n";
                            } 

                            $subject = $lang_reguser['regonsite'] . ' ' . $config["title"];
                            $regmail = "" . $lang_reguser['hello'] . " " . $log . "\r\n" . $lang_reguser['emailpart1'] . " " . $config["homeUrl"] . " \r\n" . $lang_reguser['emailpart2'] . ":\r\n\r\n" . $lang_home['username'] . ": " . $log . "\r\n" . $lang_home['pass'] . ": " . $par . "" . $needkey . "" . $lang_reguser['emailpart3'] . "\r\n" . $lang_reguser['emailpart4'] . "";

                            // send confirmation email
                            $newMail = new Mailer;
                            $newMail->send($mail, $subject, $regmail);

                            // reg. sucessful, show info
                            echo $lang_reguser['regoknick'] . ': <b>' . $log . '</b> <br />' . $lang_home['pass'] . ': <b>' . $par . '</b><br />' . $lang_reguser['loginnow'] . '<br />';
                            echo '<br /><img src="../images/img/reload.gif" alt=""> ';
                            if (empty($pagetoload)) {
                            echo '<b><a href="' . $connectionProtocol . $config_srvhost . '/input.php?log=' . $log . '&amp;pass=' . $par . '&amp;cookietrue=1">' . $lang_reguser['entersite'] . '</a></b><br /><br />';
                          } else {
                          	echo '<b><a href="' . $pagetoload . '">Continue</a></b><br /><br />'; // update lang

                          }
                            if ($config["regConfirm"] == "1") {
                                echo '<b><font color="#FF0000">' . $lang_reguser['enterkeymessage'] . '</font></b><br /><br />';
                            } 

                        } else {
                            echo $lang_reguser['badcaptcha'] . '!<br />';
                        } 
                    } else {
                        echo $lang_reguser['badmail'] . "<br />";
                    }
            } else {
                echo $lang_reguser['userexists'] . "<br />";
            } 
        } else {
            echo $lang_reguser['emailexists'] . '<br />';
        } 
    } else {
        echo $lang_reguser['toomuchslashes'] . '<br />';
    } 
} else {
    include_once"../themes/" . $config_themes . "/index.php";

    echo $lang_reguser['missingdata'] . "!<br />";
} 

echo '<p><a href="registration.php" class="sitelink">' . $lang_home['back'] . '</a><br />';
echo '<a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";
?>