<?php 
// (c) vavok.net

require_once"../include/startup.php";

if (!$users->is_reg()) redirect_to("../?isset=inputoff");

$action = isset($_GET['action']) ? check($_GET['action']) : '';

// Save data
if ($action == 'save') {

	if (!empty($_POST['site']) && !validateURL($_POST['site'])) redirect_to("profile.php?isset=insite");

	// check email
	if (!empty($_POST['email']) && !$users->validate_email($_POST['email'])) redirect_to("profile.php?isset=noemail");

	// check birthday
	// if (!empty($happy) && !preg_match("/^[0-9]{2}+\.[0-9]{2}+\.([0-9]{2}|[0-9]{4})$/",$happy)){header ("Location: profile.php?isset=inhappy"); exit;}


	$my_name = no_br(check($_POST['my_name']));
	$surname = no_br(check($_POST['surname']));
	$city = no_br(check($_POST['otkel']));
	$street = no_br(check($_POST['street']));
	$zip = no_br(check($_POST['zip']));
	$infa = no_br(check($_POST['infa']));
	$email = htmlspecialchars(strtolower($_POST['email']));
	$site = no_br(check($_POST['site']));
	$browser = no_br(check($users->user_browser()));
	$ip = no_br(check($users->find_ip()));
	$sex = no_br(check($_POST['pol']));
	$happy = no_br(check($_POST['happy']));

	$fields = array();
	$fields[] = 'city';
	$fields[] = 'about';
	$fields[] = 'email';
	$fields[] = 'site';
	$fields[] = 'sex';
	$fields[] = 'birthday';
	$fields[] = 'rname';
	$fields[] = 'surname';
	$fields[] = 'address';
	$fields[] = 'zip';

	$values = array();
	$values[] = $city;
	$values[] = $infa;
	$values[] = $email;
	$values[] = $site;
	$values[] = $sex;
	$values[] = $happy;
	$values[] = $my_name;
	$values[] = $surname;
	$values[] = $street;
	$values[] = $zip;

	$db->update('vavok_about', $fields, $values, "uid='{$users->user_id}'");

	redirect_to("./profile.php?isset=editprofil");

}

$genHeadTag = '
<style type="text/css">
    .photo img {
        max-width: 100px;
        max-height: 100px;
        overflow: hidden;
    }
</style>
';

$my_title = $localization->string('profsettings');
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

echo '<div class="row">';

$about_user = $db->get_data('vavok_about', "uid='{$users->user_id}'");
$user_profil = $db->get_data('vavok_profil', "uid='{$users->user_id}'", 'subscri');
$show_user = $db->get_data('vavok_users', "id='{$users->user_id}'", 'skin, banned, browsers');

echo '<div class="col-sm">';

    echo '<form method="post" action="profile.php?action=save">';
    echo $localization->string('name') . ':<br /><input name="my_name" value="' . $about_user['rname'] . '" /><br />';
    echo $localization->string('surname') . ':<br /><input name="surname" value="' . $about_user['surname'] . '" /><br />';
    echo $localization->string('city') . ':<br /><input name="otkel" value="' . $about_user['city'] . '" /><br />';
    echo $localization->string('street') . ':<br /><input name="street" value="' . $about_user['address'] . '" /><br />';
    echo $localization->string('postal') . ':<br /><input name="zip" value="' . $about_user['zip'] . '" /><br />';
    echo $localization->string('aboutyou') . ':<br /><input name="infa" value="' . $about_user['about'] . '" /><br />';
    echo $localization->string('yemail') . ':<br /><input name="email" value="' . $about_user['email'] . '" /><br />';
    echo $localization->string('site') . ':<br /><input name="site" value="' . $about_user['site'] . '" /><br />'; 
    echo $localization->string('birthday') . ' (dd.mm.yyyy):<br /><input name="happy" value="' . $about_user['birthday'] . '" /><br />';

    echo $localization->string('sex') . ':<br />';

    echo $localization->string('male') . ' ';

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
    echo ' ' . $localization->string('female') . '<br /><br />';


    echo'<input value="' . $localization->string('save') . '" type="submit" />

    </form>';

    // change password
    echo '<hr>';
    echo '<form method="post" action="newpass.php">';
    echo $localization->string('newpass') . ':<br /><input name="newpar" /><br />';
    echo $localization->string('passagain') . ':<br /><input name="newpar2" /><br />';
    echo $localization->string('oldpass') . ':<br /><input name="oldpar" /><br />';
    echo '<br /><input value="' . $localization->string('save') . '" type="submit" />
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

echo '</div>';

echo '<p><a href="../" class="btn btn-primary homepage"><img src="../images/img/homepage.gif" alt=""> ' . $localization->string('home') . '</a></p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>