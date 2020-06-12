<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$my_title = $lang_home['contact'];

$action = isset($_GET['action']) ? check($_GET['action']) : '';
$name = isset($_POST['name']) ? check($_POST['name']) : '';
$body = isset($_POST['body']) ? check($_POST['body']) : '';
$umail = isset($_POST['umail']) ? check($_POST['umail']) : '';

if ($action == "go") {

    // Check name
    if (empty($name)) { redirect_to("./?isset=noname"); }

    // Check email body
    if (empty($body)) { redirect_to("./?isset=nobody"); }

    // Validate email address
    if (!$users->validate_email($umail)) { redirect_to("./?isset=noemail"); }

    require_once '../lang/' . $config['siteDefaultLang'] . '/index.php';

    // Captcha code
    require_once '../include/plugins/securimage/securimage.php';
    $securimage = new Securimage();

    // Check captcha code
    if ($securimage->check($_POST['captcha_code']) != true) { redirect_to("./?isset=vrcode"); }

    // Send email
    $mail = new Mailer();
    $mail->send($config["adminEmail"], $lang_home['msgfrmst'] . " " . $config["title"], $body . " \n\n\n\n\n-----------------------------------------\nBrowser: " . $users->user_browser() . "\nIP: " . $ip . "\n" . $lang_home['datesent'] . ": " . date('d.m.Y. / H:i', $config["siteTime"]), $umail, $name);

    // Email sent
    redirect_to("./?isset=mail");

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

        $user_email = $db->get_data('vavok_about', "uid='" . $user_id . "'", 'email');

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