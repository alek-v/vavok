<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

$language = 'serbian_latin';
$ln_loc = 'sr';
$ln_charset = 'UTF-8';
$ln_text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)

// shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$ln_byteUnits = array('bajtova', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

$ln_day_of_week = array('Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota','Nedelja');
$ln_all_month = array('januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar');

// See http://www.php.net/manual/en/function.strftime.php to define the
// variable below
$ln_datefmt = '%d. %B %Y.';
$ln_timefmt = '%H:%M';
$ln_timespanfmt = '%s dana, %s sati, %s minuta i %s sekundi';

// glavno
$lang_home['home'] = 'Na glavnu';
$lang_home['login'] = 'Prijavite se';
$lang_home['register'] = 'Registracija';
$lang_home['lostpass'] = 'Zaboravljena šifra';
$lang_home['forum'] = 'Forum';
$lang_home['statistics'] = 'Statistika';
$lang_home['sitelife'] = 'Život sajta';
$lang_home['guestbook'] = 'Knjiga gostiju';
$lang_home['sitenews'] = 'Novosti sajta';
$lang_home['qpool'] = 'Anketa';
$lang_home['mymenu'] = 'Moj meni';
$lang_home['admpanel'] = 'Admin panel';
$lang_home['modpanel'] = 'Moder panel';
$lang_home['newmess'] = 'Nova poruka!';
$lang_home['inbox'] = 'Sanduče';
$lang_home['refresh'] = 'Osveži';
$lang_home['message'] = 'Poruka';
$lang_home['save'] = 'Sačuvaj';
$lang_home['nomsgs'] = 'Nema poruka, budite prvi!';
$lang_home['forw'] = 'Napred';
$lang_home['back'] = 'Nazad';
$lang_home['smile'] = 'Smešci';
$lang_home['bbcode'] = 'BB kodovi';
$lang_home['etoa'] = 'Pošaljite email administratoru';
$lang_home['msgfrmst'] = 'Pismo sa sajta';
$lang_home['datesent'] = 'Datum slanja';
$lang_home['yname'] = 'Vaše ime';
$lang_home['yemail'] = 'Vaš email';
$lang_home['send'] = 'Pošaljite';
$lang_home['captcha'] = 'Kod za proveru';
$lang_home['version'] = 'verzija';
$lang_home['confirm'] = 'Potvrdi';
$lang_home['usrnoexist'] = 'Korisnik ne postoji';
$lang_home['today'] = 'Danas';
$lang_home['delete'] = 'Obriši';
$lang_home['all'] = 'sve';
$lang_home['comment'] = 'Komentar';
$lang_home['time'] = 'Vreme';
$lang_home['user'] = 'Korisnik';
$lang_home['access101'] = 'Glavni administrator';
$lang_home['access102'] = 'Administrator';
$lang_home['access103'] = 'Glavni moderator';
$lang_home['access105'] = 'Super moderator';
$lang_home['access106'] = 'Moderator';
$lang_home['access107'] = 'Član';
$lang_home['username'] = 'Korisničko ime';
$lang_home['pass'] = 'Šifra';
$lang_home['logout'] = 'Odjavite se';
$lang_home['rembme'] = 'Zapamti me';
$lang_home['page'] = 'Stranica';
$lang_home['autopmreg'] = 'Zdravo![br][br]Nadamo se da ćete nastaviti da nas posećujete![br][br]S poštovanjem, administracija sajta';
$lang_home['lang'] = 'Jezik';
$lang_home['pggen'] = 'Stranica generisana za';
$lang_home['minutes'] = 'minuta';
$lang_home['hours'] = 'sati';
$lang_home['days'] = 'dana';
$lang_home['secs'] = 'sekundi';
$lang_home['guest'] = 'Gost';
$lang_home['search'] = 'Pretraga';
$lang_home['notloged'] = "Niste prijavljeni, da bi videli stranicu morate se <a href=\"" . BASEDIR. "pages/login.php\">prijaviti</a><br>ili najpre <a href=\"" . BASEDIR. "pages/registration.php\">registrovati</a>";
$lang_home['next'] = 'Napred';
$lang_home['prev'] = 'Nazad';
$lang_home['contact'] = 'Kontakt';
$lang_home['readmore'] = 'Opširnije';
$lang_home['resend'] = 'Pošalji ponovo';
$lang_home['differentImage'] = 'Promeni sliku';
$lang_home['author'] = 'Autor';
$lang_home['published'] = 'Objavljeno';
$lang_home['security'] = 'Bezbednost';
$lang_home['backtoblog'] = 'Nazad na blog';
$lang_home['category'] = 'Kategorija';
$lang_home['updated'] = 'Ažurirano';
$lang_home['timezone'] = 'Vremenska zona';
$lang_home['settings'] = 'Podešavanja';

// cookie consent
$lang_home['cookies'] = 'Kolačići.'; // Title
$lang_home['purecookieDesc'] = 'Korišćenjem ovog sajta prihvatate da koristite kolačiće.'; // Description
$lang_home['purecookieLink'] = '<a href="/pages/cookies-policy.php" target="_blank">Zbog čega?</a>'; // Cookiepolicy link
$lang_home['purecookieButton'] = 'Prihvatam'; // Button text

// ob_start("cyr_to_lat_skiptags");

$language_data = array(
	'language' => $language,
	'ln_loc' => $ln_loc,
	'ln_charset' => $ln_charset,
	'ln_text_dir' => $ln_text_dir,
	'ln_byteUnits' => $ln_byteUnits,
	'ln_day_of_week' => $ln_day_of_week,
	'ln_all_month' => $ln_all_month,
	'ln_datefmt' => $ln_datefmt,
	'ln_timefmt' => $ln_timefmt,
	'ln_timespanfmt' => $ln_timespanfmt
);

?>
