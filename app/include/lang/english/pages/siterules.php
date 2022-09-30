<?php
// modified: 05.06.2020. 20:03:50
// (c) vavok.net
$sajtx = $_SERVER['HTTP_HOST'];

$lang_siterules['siterules'] = "Site rules";
$lang_siterules['mainrules'] = "
Rules for site users " . $sajtx . "<br><br>
1 <b>General rules</b><br>
1.1 You're not allowed to register more than one nickname.<br>
1.2 Vulgar and insulting nicknames are not allowed<br>
<br>2 <b>Rules for behavior</b><br>
2.1 You're not allowed to post any advertising links on this site.<br>
2.2 Racial and religious discrimination,insults or slight of other members are not allowed.<br>
2.3 Do not use capital letters only (Caps Lock)<br>
2.4 In case you notice topic in forum, message in forum, message in guesbook etc. that violates the rules, inform the site administration using personal messages.<br>
<br>3 <b>Forum</b><br>
3.1 You're not allowed to chat (<i>chat</i>) in forum.<br>
3.2 When starting a new topic in forum, you must not use these words in topic name: \"click\", \"urgent\" and similar<br>
3.3 Topic name should describe the topic subject in short line.<br>
3.4 Comments in topics must not consist of smilies and they have to be related to topic.<br>
<br>4 <b>Other</b><br>
4.1 Users that violate these rules can get their accounts banned (<i>ban</i>) from 5 min. to 30 days.<br>
4.2 Moderators have the rights to delete messages and topics without any prior notice.<br>
4.3 Administrators have the rights to change these rules without any prior notice.<br>
4.4 If a user ignores their bans, their account will be permanently deleted.<br>

";


$lang_home = array_merge($lang_home, $lang_siterules);