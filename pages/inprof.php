<?php 
// (c) vavok.net
require_once"../include/startup.php";

if (!$users->is_reg()) {
    header ("Location: ../?isset=inputoff");
    exit;
}

if (!empty($_POST['site']) && !validateURL($_POST['site'])) {
    header ("Location: profile.php?isset=insite");
    exit;
} 
// check email
if (!empty($_POST['email']) && !$users->validate_email($_POST['email'])) {
    header ("Location: profile.php?isset=noemail");
    exit;
} 
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

?>