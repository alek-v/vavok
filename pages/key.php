<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   20.05.2020. 23:51:16
*/

require_once"../include/strtup.php";

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

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['confreg'];
include_once"../themes/$config_themes/index.php";


// enter registration key
if (empty($action)) {

    if ($users->is_reg()) {

        echo $lang_page['wellcome'] . ', <b>' . $log . '!</b><br>';
        echo $lang_page['confinfo'] . '<br>';

    }

    echo '<form method="post" action="key.php?action=inkey"><br>';
    echo $lang_page['key'] . ':<br>';
    echo '<input name="key" maxlength="20" /><br><br>';
    echo '<button class="btn btn-primary" type="submit">' . $lang_home['confirm'] . '</button>
    </form>';

    echo '
    <form method="post" action="key.php?action=resendkey&amp;uid=' . $recipient_id . '">
		<button type="submit" class="btn btn-primary sitelink">' . $lang_home['resend'] . '</button>
	</form>
	<hr>
    ';

    echo $lang_page['actinfodel'] . '<br />';

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

            echo '<p>' . $lang_home['keynotok'] . '!</p>';

            echo '<pr><a href="../pages/key.php"><img src="../images/img/back.gif" alt="Back"> ' . $lang_home['back'] . '</a></p>';


        } else {

            echo '<p>' . $lang_page['keyok'] . '!</p>';


            echo '<pr><a href="../pages/login.php"><img src="../images/img/reload.gif" alt="Login"> ' . $lang_home['login'] . '</a></p>';


        }


    } else {

        echo '<p>' . $lang_page['nokey'] . '!</p>';

        echo '<p><a href="key.php"><img src="../images/img/back.gif" alt="Back" /> ' . $lang_home['back'] . '</a></p>';

    } 

}

echo '<p><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt="Home page" /> ' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>