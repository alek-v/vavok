<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   26.07.2020. 17:28:10
*/

require_once"../include/startup.php";

$action = isset($_GET['action']) ? check($_GET['action']) : '';
$recipient_mail = isset($_POST['recipient']) ? check($_POST['recipient']) : '';
$recipient_id = isset($_GET['uid']) ? check($_GET['uid']) : '';

// resend registration email with key
if ($action == 'resendkey') {

	// if user id is not in url, get it from submited email
	if (empty($recipient_id)) {
    	$recipient_id = $users->get_id_from_mail($recipient_mail);
	}

    // check if user really need to confirm registration
    $check = $db->count_row('vavok_profil', "uid = '{$recipient_id}' AND regche = 1");

    if ($check > 0) {

    	// Get user's email if it is not submited
    	if (empty($recipient_mail)) {
    		$recipient_mail = $users->get_user_info($recipient_id, 'email');
    	}

        $email = $db->get_data('email_queue', "recipient='{$recipient_mail}'");

        // resend confirmation email
        $sendMail = new Mailer();

        // send mail
        $result = $sendMail->send($email['recipient'], $email['subject'], $email['content']);

        // update sent date
        $fields = array('timesent');
        $values = array(date("Y-m-d H:i:s"));
        
        // update data if email is sent
        if ($result == true) {
            $db->update('email_queue', $fields, $values, 'id = ' . $email['id']);
        }

    }

    redirect_to('key.php?uid=' . $recipient_id);

}

$my_title = $localization->string('confreg');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

// enter registration key
if (empty($action)) {

    if ($users->is_reg()) {

        echo $localization->string('wellcome') . ', <b>' . $users->show_username() . '!</b><br>';
        echo $localization->string('confinfo') . '<br>';

    }

    echo '<form method="post" action="key.php?action=inkey"><br>';
    echo $localization->string('key') . ':<br>';
    echo '<input name="key" maxlength="20" /><br><br>';
    echo '<button class="btn btn-primary" type="submit">' . $localization->string('confirm') . '</button>
    </form>';

    echo '
    <form method="post" action="key.php?action=resendkey&amp;uid=' . $recipient_id . '">
		<button type="submit" class="btn btn-primary sitelink">' . $localization->string('resend') . '</button>
	</form>
	<hr>
    ';

    echo $localization->string('actinfodel') . '<br />';

}

// check comfirmation code
if ($action == "inkey") {

    if (isset($_GET['key'])) {
        $key = check(trim($_GET['key']));
    } else {
        $key = check(trim($_POST['key']));
    } 

    if (!empty($key)) {

        if (!$db->update('vavok_profil', array('regche', 'regkey'), array('', ''), "regkey='{$key}'")) {

            echo '<p>' . $localization->string('keynotok') . '!</p>';

            echo '<p><a href="../pages/key.php"><img src="../images/img/back.gif" alt="Back"> ' . $localization->string('back') . '</a></p>';


        } else {

            echo '<p>' . $localization->string('keyok') . '!</p>';


            echo '<pr><a href="../pages/login.php"><img src="../images/img/reload.gif" alt="Login"> ' . $localization->string('login') . '</a></p>';


        }


    } else {

        echo '<p>' . $localization->string('nokey') . '!</p>';

        echo '<p><a href="key.php"><img src="../images/img/back.gif" alt="Back" /> ' . $localization->string('back') . '</a></p>';

    } 

}

echo '<p><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt="Home page" /> ' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>