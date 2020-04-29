<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URL:       http://vavok.net
* Updated:   29.04.2020. 6:39:02
*/

require_once"../include/strtup.php";


$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_page['confreg'];
include_once"../themes/$config_themes/index.php";

if (!empty($_GET['action'])) {
    $action = check($_GET["action"]);
} else {
    $action = '';
} 

// enter registration key
if (empty($action)) {

    if ($users->is_reg()) {

        echo $lang_page['wellcome'] . ', <b>' . $log . '!</b><br>';
        echo $lang_page['confinfo'] . '<br>';

    }

    echo '<form method="post" action="key.php?action=inkey"><br>';
    echo $lang_page['key'] . ':<br>';
    echo '<input name="key" maxlength="20" /><br><br>';
    echo '<input value="' . $lang_home['confirm'] . '" type="submit" /></form><hr>';

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