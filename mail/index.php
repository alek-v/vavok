<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 1:28:30
*/

require_once"../include/startup.php";

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

    require_once BASEDIR . 'lang/' . get_configuration('siteDefaultLang') . '/index.php';

    // Captcha code
    require_once BASEDIR . 'include/plugins/securimage/securimage.php';
    $securimage = new Securimage();

    // Check captcha code
    if ($securimage->check($_POST['captcha_code']) != true) { redirect_to("./?isset=vrcode"); }

    // Send email
    $mail = new Mailer();
    $mail->send(get_configuration("adminEmail"), $localization->string('msgfrmst') . " " . get_configuration("title"), $body . " \n\n\n\n\n-----------------------------------------\nBrowser: " . $users->user_browser() . "\nIP: " . $users->find_ip() . "\n" . $localization->string('datesent') . ": " . date('d.m.Y. / H:i', get_configuration("siteTime")), $umail, $name);

    // Email sent
    redirect_to("./?isset=mail");

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