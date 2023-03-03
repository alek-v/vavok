<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

$language = 'english';
$ln_loc = 'en';
$ln_charset = 'UTF-8';
$ln_text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)

// Shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$ln_byteUnits = array('bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

$ln_day_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$ln_all_month = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');

// Date formats
$ln_date_format = 'm-d-Y';
$ln_time_format = 'g:i a';

$lang_home['home'] = 'Main page';
$lang_home['login'] = 'Login';
$lang_home['register'] = 'Registration';
$lang_home['lostpass'] = 'Lost password?';
$lang_home['forum'] = 'Forum';
$lang_home['statistics'] = 'Statistics';
$lang_home['sitelife'] = 'Sitelife';
$lang_home['guestbook'] = 'Guestbook';
$lang_home['sitenews'] = 'Site news';
$lang_home['qpool'] = 'Poll';
$lang_home['mymenu'] = 'My Menu';
$lang_home['adminpanel'] = 'Admin panel';
$lang_home['modpanel'] = 'Moderator panel';
$lang_home['newmess'] = 'New message!';
$lang_home['inbox'] = 'Inbox';
$lang_home['refresh'] = 'Refresh';
$lang_home['message'] = 'Message';
$lang_home['save'] = 'Save';
$lang_home['no_messages'] = 'No messages, be first!';
$lang_home['forw'] = 'Forward';
$lang_home['back'] = 'Back';
$lang_home['smile'] = 'Smiles';
$lang_home['bbcode'] = 'BB codes';
$lang_home['etoa'] = 'Send email to an administrator';
$lang_home['message_from_site'] = 'Message from website';
$lang_home['datesent'] = 'Date of sending';
$lang_home['yname'] = 'Your name';
$lang_home['yemail'] = 'Your email';
$lang_home['send'] = 'Send';
$lang_home['captcha'] = 'Captcha';
$lang_home['version'] = 'Version';
$lang_home['confirm'] = 'Confirm';
$lang_home['user_does_not_exist'] = 'Username does not exist';
$lang_home['today'] = 'Today';
$lang_home['delete'] = 'Delete';
$lang_home['all'] = 'All';
$lang_home['comment'] = 'Comment';
$lang_home['time'] = 'Time';
$lang_home['user'] = 'User';
$lang_home['access101'] = 'Head admin';
$lang_home['access102'] = 'Administrator';
$lang_home['access103'] = 'Head moderator';
$lang_home['access105'] = 'Super moderator';
$lang_home['access106'] = 'Moderator';
$lang_home['access107'] = 'User';
$lang_home['username'] = 'Username';
$lang_home['pass'] = 'Password';
$lang_home['logout'] = 'Logout';
$lang_home['rembme'] = 'Remember me';
$lang_home['page'] = 'Page';
$lang_home['autopmreg'] = 'Hello![br][br]We hope you will continue visiting us![br][br]Respectfully, site administration';
$lang_home['lang'] = 'Language';
$lang_home['pggen'] = 'Page generated in';
$lang_home['minutes'] = 'minutes';
$lang_home['hours'] = 'hours';
$lang_home['days'] = 'days';
$lang_home['secs'] = 'seconds';
$lang_home['guest'] = 'Guest';
$lang_home['search'] = 'Search';
$lang_home['notloged'] = "You are not logged in, in order to see this page you must <a href=\"" . HOMEDIR. "users/login\">log in</a><br>or <a href=\"" . HOMEDIR. "users/register\">register</a>";
$lang_home['next'] = 'Next';
$lang_home['prev'] = 'Prev';
$lang_home['contact'] = 'Contact';
$lang_home['read_more'] = 'Read more';
$lang_home['resend'] = 'Resend';
$lang_home['differentImage'] = 'Different image';
$lang_home['author'] = 'Author';
$lang_home['published'] = 'Published';
$lang_home['security'] = 'Security';
$lang_home['backtoblog'] = 'Back to blog';
$lang_home['category'] = 'Category';
$lang_home['updated'] = 'Updated';
$lang_home['timezone'] = 'Time zone';
$lang_home['settings'] = 'Settings';
$lang_home['language'] = 'Language';
$lang_home['page_or_file_not_found'] = "Page or file not found.";
$lang_home['localization'] = 'Localization';

// Cookie consent
$lang_home['cookies'] = 'Cookies.'; // Title
$lang_home['purecookieDesc'] = 'By using this website, you automatically accept that we use cookies.'; // Description
$lang_home['purecookieLink'] = '<a href="/pages/cookies_policy" target="_blank">What for?</a>'; // Cookie policy link
$lang_home['purecookieButton'] = 'Understood'; // Button text

$language_data = array(
    'language' => $language,
    'ln_loc' => $ln_loc,
    'ln_charset' => $ln_charset,
    'ln_text_dir' => $ln_text_dir,
    'ln_byteUnits' => $ln_byteUnits,
    'ln_day_of_week' => $ln_day_of_week,
    'ln_all_month' => $ln_all_month,
    'date_format' => $ln_date_format,
    'time_format' => $ln_time_format
);