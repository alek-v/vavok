<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$my_title = "Contact";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 
if (isset($_POST['name'])) {
    $name = check($_POST['name']);
} 
if (isset($_POST['body'])) {
    $body = check($_POST['body']);
} 
if (isset($_POST['umail'])) {
    $umail = check($_POST['umail']);
} 

if ($action == "go") {
    if (!empty($name)) {
        if (!empty($body)) {
            if (preg_match("/^[a-z0-9\._-]+@[a-z0-9\._-]+\.[a-z]{2,4}\$/", $umail)) {
            	require_once '../lang/' . $config['siteDefaultLang'] . '/index.php';
                require_once '../include/plugins/securimage/securimage.php';
                $securimage = new Securimage();

                if ($securimage->check($_POST['captcha_code']) == true) {

                	$mail = new Mailer();
                    $mail->send($config["adminEmail"], $lang_home['msgfrmst'] . " " . $config["title"], $body . " \n\n\n\n\n-----------------------------------------\nBrowser: " . $users->user_browser() . "\nIP: " . $ip . "\n" . $lang_home['datesent'] . ": " . date('d.m.Y. / H:i', $config["siteTime"]), $umail, $name);

                    header("Location: ./?isset=mail");
                    exit;
                } else {
                    header("Location: ./?isset=vrcode");
                    exit;
                } 
            } else {
                header("Location: ./?isset=noemail");
                exit;
            } 
        } else {
            header("Location: ./?isset=nobody");
            exit;
        } 
    } else {
        header ("Location: ./?isset=noname");
        exit;
    } 
} 

if (empty($action)) {
    include_once"../themes/$config_themes/index.php";
    if (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
        echo '<div align="center"><b><font color="#FF0000">';
        echo get_isset();
        echo '</font></b></div>';
    }

    // generate page
    $showPage = new PageGen("mail/mail_index.tpl");

    if (!$users->is_reg()) {
        $usernameAndMail = new PageGen("mail/usernameAndMail_guest.tpl");
        $showPage->set('usernameAndMail', $usernameAndMail->output());
    } else {
        $user_email = $db->select('vavok_about', "uid='" . $user_id . "'", '', 'email');

        $usernameAndMail = new PageGen("mail/usernameAndMail_registered.tpl");
        $usernameAndMail->set('log', $log);
        $usernameAndMail->set('user_email', $user_email['email']);

        $showPage->set('usernameAndMail', $usernameAndMail->output());
    }
} 

// show page
echo $showPage->output();

include_once "../themes/" . $config_themes . "/foot.php";

?>