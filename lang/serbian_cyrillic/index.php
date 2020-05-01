<?php
// (c) vavok.net

$language = 'serbian_cyrillic';
$ln_loc = 'sr';
$charset = 'UTF-8';
$allow_recoding = true;
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$number_thousands_separator = ',';
$number_decimal_separator = '.';
// current date
$currDate = str_replace("January", "Јануар", $currDate);
$currDate = str_replace("February", "Фебруар", $currDate);
$currDate = str_replace("March", "Март", $currDate);
$currDate = str_replace("April", "Април", $currDate);
$currDate = str_replace("May", "Мај", $currDate);
$currDate = str_replace("June", "Јун", $currDate);
$currDate = str_replace("July", "Јул", $currDate);
$currDate = str_replace("August", "Август", $currDate);
$currDate = str_replace("September", "Септембар", $currDate);
$currDate = str_replace("October", "Октобар", $currDate);
$currDate = str_replace("November", "Новембар", $currDate);
$currDate = str_replace("December", "Децембар", $currDate); 
// shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$byteUnits = array('бајтова', 'КБ', 'МБ', 'ГБ', 'ТБ', 'ПБ', 'ЕБ');

$ln_day_of_week = array('Понедељак', 'Уторак', 'Среда', 'Четвтак', 'Петак', 'Субота','Недеља');
$ln_all_month = array('јануар', 'фебруар', 'март', 'април', 'мај', 'јун', 'јул', 'август', 'септембар', 'октобар', 'новембар', 'децембар');

// See http://www.php.net/manual/en/function.strftime.php to define the
// variable below
$ln_datefmt = '%d. %B %Y.';
$ln_timefmt = '%H:%M';
$timespanfmt = '%s дана, %s сати, %s минута и %s секунди';

// главно
$lang_home['home'] = "На главну";
$lang_home['login'] = "Пријави се";
$lang_home['register'] = "Регистрација";
$lang_home['lostpass'] = "Заборављена шифра";
$lang_home['forum'] = "Форум";
$lang_home['statistic'] = "Статистика";
$lang_home['sitelife'] = "Живот сајта";
$lang_home['guestbook'] = "Књига гостију";
$lang_home['sitenews'] = "Новости сајта";
$lang_home['qpool'] = "Анкета";
$lang_home['mymenu'] = "Мој мени";
$lang_home['admpanel'] = "Админ панел";
$lang_home['modpanel'] = "Модер панел";
$lang_home['newmess'] = "Нова порука!";
$lang_home['inbox'] = "Сандуче";
$lang_home['refresh'] = "Освежи";
$lang_home['message'] = "Порука";
$lang_home['save'] = "Сачувај";
$lang_home['nomsgs'] = "Нема порука, будите први!";
$lang_home['forw'] = "Напред";
$lang_home['back'] = "Назад";
$lang_home['smile'] = "Смешци";
$lang_home['bbcode'] = "BB кодови";
$lang_home['etoa'] = "Пошаљите пошту администратору";
$lang_home['msgfrmst'] = "Писмо са сајта";
$lang_home['datesent'] = "Датум слања";
$lang_home['yname'] = "Ваше име";
$lang_home['yemail'] = "Адреса е-поште";
$lang_home['send'] = "Пошаљите";
$lang_home['captcha'] = "Код за проверу";
$lang_home['version'] = "верзија";
$lang_home['confirm'] = "Потврди";
$lang_home['usrnoexist'] = "Корисник не постоји";
$lang_home['today'] = "Данас";
$lang_home['delete'] = "Обриши";
$lang_home['all'] = "све";
$lang_home['comment'] = "Коментар";
$lang_home['time'] = "Време";
$lang_home['user'] = "Члан";
$lang_home['access101'] = "Главни администратор";
$lang_home['access102'] = "Администратор";
$lang_home['access103'] = "Главни модератор";
$lang_home['access105'] = "Виши модератор";
$lang_home['access106'] = "Модератор";
$lang_home['access107'] = "Члан";
$lang_home['username'] = "Корисничко име";
$lang_home['pass'] = "Шифра";
$lang_home['logout'] = "Одјавите се";
$lang_home['rembme'] = "Запамти ме";
$lang_home['page'] = "Страница";
$lang_home['autopmreg'] = "Здраво![br][br]Надамо се да ћете наставити да нас посећујете![br][br]С поштовањем, администрација сајта";
$lang_home['lang'] = "Језик";
$lang_home['pggen'] = "Страница генерисана за";
$lang_home['minutes'] = "минута";
$lang_home['hours'] = "сати";
$lang_home['days'] = "дана";
$lang_home['secs'] = "секунди";
$lang_home['guest'] = "Гост";
$lang_home['search'] = "Претрага";
$lang_home['notloged'] = "Нисте пријављени, да би приступили страници морате се <a href=\"" . BASEDIR. "pages/login.php\">пријавити</a><br>или најпре <a href=\"" . BASEDIR. "pages/registration.php\">регистровати</a>";
$lang_home['next'] = "Напред";
$lang_home['prev'] = "Назад";
$lang_home['contact'] = "Контакт";
$lang_home['readmore'] = "Опширније";

// cookie consent
$lang_home['cookies'] = "Колачићи."; // Title
$lang_home['purecookieDesc'] = "Коришћењем овог сајта прихватате да користите колачиће."; // Description
$lang_home['purecookieLink'] = '<a href="/pages/cookies-policy.php" target="_blank">Због чега?</a>'; // Cookiepolicy link
$lang_home['purecookieButton'] = "Прихватам"; // Button text

$langdir = explode("/", $config_requri);
$langdir = $langdir[1];

$phpselflang = str_replace(".php", '', $subself);
$phpselflang = str_replace("/", '', $phpselflang);

if(file_exists(BASEDIR."lang/serbian_cyrillic/" . $langdir . ".php")) {
include_once BASEDIR."lang/serbian_cyrillic/" . $langdir . ".php"; }
if(file_exists(BASEDIR."lang/serbian_cyrillic/" . $phpselflang . ".php")) {
include_once BASEDIR."lang/serbian_cyrillic/" . $phpselflang . ".php"; }

// ob_start("lat_to_cyr_skiptags");
?>