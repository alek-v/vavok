<?php
// modified: 31.3.2013 3:30:30
if (!defined("BASEDIR")) { header("Location:../index.php"); exit; }
$config_skint = 2;

if ($config_skint == 1) {
echo'<form method="post" action="'.BASEDIR.'pages/chlang.php?'.SID.'">';
echo'<select name="lang"><option value="0">Choose language</option>';

$skindir = opendir(BASEDIR."lang"); 
while ($skinfile = readdir ($skindir)){
if (is_dir(BASEDIR."lang/$skinfile")) {
if($skinfile=="."||$skinfile=="..") continue;

echo'<option value="'.$skinfile.'">'.$skinfile.'</option>';
 }}
echo'</select>';
closedir ($skindir);


echo'<input value="Izaberite" type="submit" /></form>';

}

if ($config_skint == 2) {
echo'<form method="post" action="'.BASEDIR.'pages/chlang.php?'.SID.'">';
echo'<select name="lang" onchange="this.form.submit();"><option value="0">Choose language</option>';

$skindir = opendir(BASEDIR."lang"); 
while ($skinfile = readdir ($skindir)){
if (is_dir(BASEDIR."lang/$skinfile")) {
if($skinfile=="."||$skinfile=="..") continue;

echo'<option value="'.$skinfile.'">'.$skinfile.'</option>';
 }}
echo'</select>';
closedir ($skindir);


echo'</form>';

}


?>