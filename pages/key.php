<?php 
// (c) vavok.net
require_once"../include/strtup.php";

if (!is_reg()) {
    header ("Location: ../");
    exit;
}

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['confreg'];
include_once"../themes/$config_themes/index.php";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 

$log = isset($log) ? check($log) : '';
$time = time();

        $user_profil = $db->select('vavok_profil', "uid='" . $user_id . "'", '', 'regche, regkey'); 
        // enter registration key
        if (empty($action)) {
            if ($user_profil['regche'] > 0) {
                echo $lang_page['wellcome'] . ', <b>' . $log . '!</b><br>';
                echo $lang_page['confinfo'] . '<br>';

                echo '<form method="post" action="key.php?action=inkey"><br>';
                echo $lang_page['key'] . ':<br>';
                echo '<input name="key" maxlength="20" /><br><br>';
                echo '<input value="' . $lang_home['confirm'] . '" type="submit" /></form><hr>';

                echo $lang_page['actinfodel'] . '<br />';
            } else {
                echo '<p>Your registration is already confirmed!</p>';
            } 
        } 
        // check comfirmation code
        if ($action == "inkey") {
            if (isset($_GET['key'])) {
                $key = check(trim($_GET['key']));
            } else {
                $key = check(trim($_POST['key']));
            } 
            if (!empty($key)) {
                if ($key == $user_profil['regkey']) {
                    $db->update('vavok_profil', array('regche', 'regkey'), array('', ''), "uid='" . $user_id . "'");

                    echo $lang_page['keyok'] . '!<br>';
                    echo '<br><img src="../images/img/reload.gif" alt=""> <a href="../index.php">' . $lang_home['login'] . '!</a><br>';
                } else {
                    echo $lang_page['keynotok'] . '!<br>';
                } 
            } else {
                echo $lang_page['nokey'] . '!<br>';
            } 

            echo '<br><img src="../images/img/back.gif" alt="" /> <a href="key.php">' . $lang_home['back'] . '</a>';
        }

echo '<br><img src="../images/img/homepage.gif" alt="" /> <a href="../" class="homepage">' . $lang_home['home'] . '</a>';

include_once"../themes/" . $config_themes . "/foot.php";

?>