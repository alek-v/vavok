<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$genHeadTag = '
<style type="text/css">
    .photo img {
        max-width: 100px;
        max-height: 100px;
        overflow: hidden;
    }
</style>
';

$my_title = $lang_profil['profsettings'];
include_once"../themes/$config_themes/index.php";

echo '<div class="row">';

if (isset($_GET['isset'])) {
    $isset = check($_GET['isset']);
    echo '<div align="center"><b><font color="#FF0000">';
    echo get_isset();
    echo '</font></b></div>';
} 

if ($users->is_reg()) {

    $about_user = $db->select('vavok_about', "uid='" . $user_id . "'", '', '*');
    $user_profil = $db->select('vavok_profil', "uid='" . $user_id . "'", '', 'subscri');
    $show_user = $db->select('vavok_users', "id='" . $user_id . "'", '', 'skin, banned, browsers');

    echo '<div class="col-sm">';

        echo '<form method="post" action="inprof.php">';
        echo $lang_profil['name'] . ':<br /><input name="my_name" value="' . $about_user['rname'] . '" /><br />';
        echo $lang_profil['surname'] . ':<br /><input name="surname" value="' . $about_user['surname'] . '" /><br />';
        echo $lang_profil['city'] . ':<br /><input name="otkel" value="' . $about_user['city'] . '" /><br />';
        echo $lang_profil['street'] . ':<br /><input name="street" value="' . $about_user['address'] . '" /><br />';
        echo $lang_profil['postal'] . ':<br /><input name="zip" value="' . $about_user['zip'] . '" /><br />';
        echo $lang_profil['aboutyou'] . ':<br /><input name="infa" value="' . $about_user['about'] . '" /><br />';
        echo 'Email:<br /><input name="email" value="' . $about_user['email'] . '" /><br />';
        echo $lang_profil['site'] . ':<br /><input name="site" value="' . $about_user['site'] . '" /><br />'; 
        echo $lang_profil['birthday'] . ' (dd.mm.yyyy):<br /><input name="happy" value="' . $about_user['birthday'] . '" /><br />';
        echo $lang_profil['sex'] . ':<br />';


        echo $lang_profil['male'] . ' ';

        if ($about_user['sex'] == "M") {
            echo '<input name="pol" type="radio" value="M" checked>';
        } else {
            echo '<input name="pol" type="radio" value="M" />';
        } 
        echo ' &nbsp; &nbsp; ';
        if ($about_user['sex'] == "Z") {
            echo'<input name="pol" type="radio" value="Z" checked>';
        } else {
            echo'<input name="pol" type="radio" value="Z" />';
        } 
        echo ' ' . $lang_profil['female'] . '<br /><br />';


        echo'<input value="' . $lang_home['save'] . '" type="submit" />

        </form>';

        // change password
        echo '<hr>';
        echo '<form method="post" action="newpass.php">';
        echo $lang_profil['newpass'] . ':<br /><input name="newpar" /><br />';
        echo $lang_profil['passagain'] . ':<br /><input name="newpar2" /><br />';
        echo $lang_profil['oldpass'] . ':<br /><input name="oldpar" /><br />';
        echo '<br /><input value="' . $lang_home['save'] . '" type="submit" />
        </form>
        <hr>';

    echo '</div>';

    echo '<div class="col-sm">';
        echo '<div class="photo">';
        if (!empty($about_user['photo'])) {
            echo '<img src="../' . $about_user['photo'] . '" alt=""><br /> ';
            echo '<img src="../images/img/edit.gif" alt="" /> <a href="photo.php">Change photo</a><br />';
            echo '<img src="../images/img/close.gif" alt="" /> <a href="photo.php?action=remove">Remove photo</a>'; // update lang
        } else {
            echo '<img src="../images/img/no_picture.jpg" alt="" /><br /> ';
            echo '<a href="photo.php">Change photo</a>'; // update lang
        } 
        echo '</div>';

    echo '</div>';


} else {

    echo '<p>' . $lang_home['notloged'] . '</p>';

}

echo '</div>';

echo '<p><img src="../images/img/homepage.gif" alt=""> <a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>