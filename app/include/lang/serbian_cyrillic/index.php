<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

$language = 'serbian_cyrillic';
$ln_loc = 'sr';
$ln_charset = 'UTF-8';
$ln_text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)

// shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$ln_byteUnits = array('бајтова', 'КБ', 'МБ', 'ГБ', 'ТБ', 'ПБ', 'ЕБ');

$ln_day_of_week = array('Понедељак', 'Уторак', 'Среда', 'Четвтак', 'Петак', 'Субота', 'Недеља');
$ln_all_month = array('јануар', 'фебруар', 'март', 'април', 'мај', 'јун', 'јул', 'август', 'септембар', 'октобар', 'новембар', 'децембар');

$ln_datefmt = 'd.m.Y.';
$ln_timefmt = 'H:M';

// главно
$lang_home['home'] = 'На главну';
$lang_home['login'] = 'Пријави се';
$lang_home['register'] = 'Регистрација';
$lang_home['lostpass'] = 'Заборављена шифра';
$lang_home['forum'] = 'Форум';
$lang_home['statistics'] = 'Статистика';
$lang_home['sitelife'] = 'Живот сајта';
$lang_home['guestbook'] = 'Књига гостију';
$lang_home['sitenews'] = 'Новости сајта';
$lang_home['qpool'] = 'Анкета';
$lang_home['mymenu'] = 'Мој мени';
$lang_home['adminpanel'] = 'Админ панел';
$lang_home['modpanel'] = 'Модер панел';
$lang_home['newmess'] = 'Нова порука!';
$lang_home['inbox'] = 'Сандуче';
$lang_home['refresh'] = 'Освежи';
$lang_home['message'] = 'Порука';
$lang_home['save'] = 'Сачувај';
$lang_home['no_messages'] = 'Нема порука, будите први!';
$lang_home['forw'] = 'Напред';
$lang_home['back'] = 'Назад';
$lang_home['smile'] = 'Смешци';
$lang_home['bbcode'] = 'BB кодови';
$lang_home['etoa'] = 'Пошаљите пошту администратору';
$lang_home['message_from_site'] = 'Порука са сајта';
$lang_home['datesent'] = 'Датум слања';
$lang_home['yname'] = 'Ваше име';
$lang_home['yemail'] = 'Адреса е-поште';
$lang_home['send'] = 'Пошаљите';
$lang_home['captcha'] = 'Код за проверу';
$lang_home['version'] = 'верзија';
$lang_home['confirm'] = 'Потврди';
$lang_home['user_does_not_exist'] = 'Корисник не постоји';
$lang_home['today'] = 'Данас';
$lang_home['delete'] = 'Обриши';
$lang_home['all'] = 'све';
$lang_home['comment'] = 'Коментар';
$lang_home['time'] = 'Време';
$lang_home['user'] = 'Корисник';
$lang_home['access101'] = 'Главни администратор';
$lang_home['access102'] = 'Администратор';
$lang_home['access103'] = 'Главни модератор';
$lang_home['access105'] = 'Супер модератор';
$lang_home['access106'] = 'Модератор';
$lang_home['access107'] = 'Члан';
$lang_home['username'] = 'Корисничко име';
$lang_home['pass'] = 'Шифра';
$lang_home['logout'] = 'Одјавите се';
$lang_home['rembme'] = 'Запамти ме';
$lang_home['page'] = 'Страница';
$lang_home['autopmreg'] = 'Здраво![br][br]Надамо се да ћете наставити да нас посећујете![br][br]С поштовањем, администрација сајта';
$lang_home['lang'] = 'Језик';
$lang_home['pggen'] = 'Страница генерисана за';
$lang_home['minutes'] = 'минута';
$lang_home['hours'] = 'сати';
$lang_home['days'] = 'дана';
$lang_home['secs'] = 'секунди';
$lang_home['guest'] = 'Гост';
$lang_home['search'] = 'Претрага';
$lang_home['notloged'] = "Нисте пријављени, да би приступили страници морате се <a href=\"" . HOMEDIR . "users/login\">пријавити</a><br>или најпре <a href=\"" . HOMEDIR. "users/register\">регистровати</a>";
$lang_home['next'] = 'Напред';
$lang_home['prev'] = 'Назад';
$lang_home['contact'] = 'Контакт';
$lang_home['read_more'] = 'Опширније';
$lang_home['resend'] = 'Пошаљи поново';
$lang_home['differentImage'] = 'Промени слику';
$lang_home['author'] = 'Аутор';
$lang_home['published'] = 'Објављено';
$lang_home['security'] = 'Безбедност';
$lang_home['backtoblog'] = 'Назад на блог';
$lang_home['category'] = 'Категорија';
$lang_home['updated'] = 'Ажурирано';
$lang_home['timezone'] = 'Временска зона';
$lang_home['settings'] = 'Подешавања';
$lang_home['language'] = 'Језик';

// cookie consent
$lang_home['cookies'] = 'Колачићи.'; // Title
$lang_home['purecookieDesc'] = 'Коришћењем овог сајта прихватате да користите колачиће.'; // Description
$lang_home['purecookieLink'] = '<a href="/pages/cookies_policy" target="_blank">Због чега?</a>'; // Cookiepolicy link
$lang_home['purecookieButton'] = 'Прихватам'; // Button text

// ob_start("lat_to_cyr_skiptags");

$language_data = array(
    'language' => $language,
    'ln_loc' => $ln_loc,
    'ln_charset' => $ln_charset,
    'ln_text_dir' => $ln_text_dir,
    'ln_byteUnits' => $ln_byteUnits,
    'ln_day_of_week' => $ln_day_of_week,
    'ln_all_month' => $ln_all_month,
    'ln_datefmt' => $ln_datefmt,
    'ln_timefmt' => $ln_timefmt
);