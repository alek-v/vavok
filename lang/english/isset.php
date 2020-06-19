<?php
// (c) vavok.net

$issetLang = array(
"delfrombase" => 'Successfully deleted from database!',

// inbox
"mail" => 'Message successfully sent!',
"nouz" => 'Please complete all the fields!',
"alldelpriv" => 'Inbox cleared!',
"delpriv" => 'Message successfully deleted!',
"selectpriv" => 'Messages deleted!',
"nomess" => 'Message too short!',
"noprivlog" => 'You can not send message to yourself',
"fullmail" => 'You can not send message to the user(s), because they do not have space in their inbox',

// settings
"editprofil" => 'Profile successfully changed!',
"editpass" => 'Password successfully changed!',
"editsetting" => 'Settings successfully changed!',
"noemail" => 'Incorrect email address!',
"noname" => 'Please fill in the name!',
"lostpass" => 'Password generated!<br />New password has been sent to email set in your profile<br />',
"nolostpass" => 'Incorrect data, password can not be sent!',
"incorrect" => 'Please enter less then 50 letters<br />',
"nopost" =>  'Comment or theme too long!',
"inlogin" => 'Please use only letters and numbers',
"nopass" => 'Old password incorrect!',
"nonewpass" => 'Password does not match!',
"inmail" => 'Incorrect email format! It must be in format like name@name.name',
"inhappy" => 'Incorrect date of birth<br />It must be in format dd.mm.yyyy',
"biginfo" => 'Username and/or password too long!',
"smallinfo" => 'Username and/or password too short!',
"insite" => 'Incorrect site address<br />Address must be in format like http://my_site.com',
"nouzer" => 'Member does not exist!',
"names" =>  'You have not completed the name field!',
"posts" =>  'You have not entered the message.',
"noreg" => 'Please use only letters and numbers',
"antirega" => 'You are already registered!',
"nopassword" => 'Please enter your password!',

// admin panel isset
"mp_yesset" => 'Settings successfully saved!',
"mp_nosset" => 'Please complete all the fields!',
"mp_votesyes" => 'Poll successfully changed!',
"mp_votesno" => 'Please complete all the fields!',
"mp_addvotes" => 'Poll successfully created!',
"mp_delvotes" => 'Poll successfully deleted!',
"mp_editfiles" => 'File successfully edited!',
"mp_newfiles" => 'New file successfully created!',
"mp_pageexists" => 'Page already exists!',
"mp_nonewfiles" => 'Unsupported file name!',
"mp_nodelfiles" => 'Files are not deleted!',
"mp_delfiles" => 'File successfully deleted!',
"mp_noyesfiles" => 'File does not exist!',
"mp_delchat" => 'Chat successfully cleaned!',
"mp_delpostchat" => 'Message deleted!',
"mp_editpostchat" => 'Message edited!',
"mp_dellogs" => 'Log file has been cleared!',
"mp_editstatus" => 'Status has been changed successfully!',
"mp_noeditstatus" => 'Please complete all the fields!',
"mp_delsubmail" => 'Successfully deleted from database!',
"mp_nodelsubmail" => 'Error!',
"mp_delsuball" => 'All subscriptions to news have been deleted!',
"mp_ydelconf" => 'Registration successfully confirmed!',
"ap_noaccess" => 'You do not have permition to access this page!',

// user isset
"quarantine" => 'Quarantine is on! New users cannot write comments and messages ' . round(get_configuration("quarantine") / 3600) . ' hours after registration',
"addfoto" => 'Photography successfully added!',
"delfoto" => 'Photography successfully deleted!',
"editfoto" => 'Photography successfully edited!',
"addkomm" => 'Comment successfully added!',
"delkomm" => 'Comment successfully deleted!',

// contact and ignore list
"contactb_noadd" => 'Failed to add contact! Check if you have reached the limit.',
"contactb_add" => 'Contact successfully added!',
"noaddcontactb" => 'Please complete all the fields!',
"contactb_del" => 'Contact successfully deleted',
"contactb_nodel" => 'Contact not deleted',
"useletter" =>  'You must use letters for the username',
"ignor_add" => 'User added to ignore list!',
"ignor_noadd" => 'Error adding to ignore list!',
"ignor_del" => 'User successfully deleted from ignore list!',
"ignor_nodel" => 'Error deleting from ignore list!',
"ignoring" => 'You can\'t send message to this user',
"kontakt_add" => 'User successfully added to contact list!',
"kontakt_noadd" => 'Error adding to contact list!',
"kontakt_del" => 'Member successfully deleted from contact list!',
"kontakt_nodel" => 'Error deleting from contact list!'
);

// forms
$formsArray = array(
"antiflood" =>  'Antiflood! Please wait ' . (int)get_configuration("floodTime") . ' seconds!',
"nobody" =>  'You didn\'t enter message text!',
"addon" =>  'Message successfully saved!<br />',
"noadduzer" => 'Member does not exist!',
"nologin" => 'You are not logged in, please log in!',
"vrcode" => 'Wrong code!',
"savedok" => 'Successfully saved!',
"inputoff" => 'Incorrect username and/or password',
"exit" => 'You are now successfully logged out',
"fixerrors" => 'Please fix errors to submit.',
"valid" => 'Valid',
"namerequired" => 'Name is required',
"mailwrong" => 'Email Invalid',
"msgshort" => 'Message too short'
);

$issetLang = array_merge($issetLang, $formsArray);
?>