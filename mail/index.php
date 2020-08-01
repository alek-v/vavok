<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   01.08.2020. 0:20:52
*/

require_once"../include/startup.php";

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';
$name = isset($_POST['name']) ? $vavok->check($_POST['name']) : '';
$body = isset($_POST['body']) ? $vavok->check($_POST['body']) : '';
$umail = isset($_POST['umail']) ? $vavok->check($_POST['umail']) : '';

if ($action == "go") {

    // Check name
    if (empty($name)) { $vavok->redirect_to("./?isset=noname"); }

    // Check email body
    if (empty($body)) { $vavok->redirect_to("./?isset=nobody"); }

    // Validate email address
    if (!$users->validate_email($umail)) { $vavok->redirect_to("./?isset=noemail"); }

    // Captcha code
    require_once BASEDIR . 'include/plugins/securimage/securimage.php';
    $securimage = new Securimage();

    // Check captcha code
    if ($securimage->check($_POST['captcha_code']) != true) { $vavok->redirect_to("./?isset=vrcode"); }

    // Send email
    $mail = new Mailer();
    $mail->send($vavok->get_configuration("adminEmail"), $localization->string('msgfrmst') . " " . $vavok->get_configuration("title"), $body . " \n\n\n\n\n-----------------------------------------\nBrowser: " . $users->user_browser() . "\nIP: " . $users->find_ip() . "\n" . $localization->string('datesent') . ": " . date('d.m.Y. / H:i', $vavok->get_configuration("siteTime")), $umail, $name);

    // Email sent
    $vavok->redirect_to("./?isset=mail");

}

if (empty($action)) {

    $my_title = $localization->string('contact');

    require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

    // generate page
    $showPage = new PageGen("mail/mail_index.tpl");

    if (!$users->is_reg()) {

        $usernameAndMail = new PageGen("mail/usernameAndMail_guest.tpl");
        $showPage->set('usernameAndMail', $usernameAndMail->output());

    } else {

        $user_email = $db->get_data('vavok_about', "uid='{$users->user_id}'", 'email');

        $usernameAndMail = new PageGen("mail/usernameAndMail_registered.tpl");
        $usernameAndMail->set('log', $users->show_username());
        $usernameAndMail->set('user_email', $user_email['email']);

        $showPage->set('usernameAndMail', $usernameAndMail->output());
    }

} 

// show page
echo $showPage->output();

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>