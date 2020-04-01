<?php 
// (c) vavok.net

// website configuration
function getConfiguration($data = '') {
    global $config;

    if (empty($data)) {
        return $config;
    } else {
        return $config[$data];
    }
}

// get user nick from user id number - deprecated 31.03.2020. 5:48:28
function getnickfromid($uid) {
    global $db;
    $unick = $db->select('vavok_users', "id='" . $uid . "'", '', 'name');
    return $unick['name'];
} 

// get vavok_users user id from nickname - deprecated 31.03.2020. 5:48:36
function getidfromnick($nick) {
    global $db;
    $uid = $db->select('vavok_users', "name='" . $nick . "'", '', 'id');
    return $uid['id'];
} 

// send automated private message by system (bot)
function autopm($msg, $who, $sys = '') {
    global $db, $users;
    if (!empty($sys)) {
        $sysid = $sys;
    } else {
        $sysid = $users->getidfromnick('System');
    }
 	$values = array(
 	'text' => $msg,
 	'byuid' => $sysid,
 	'touid' => $who,
 	'unread' => '1',
 	'timesent' => time()
	);
	$db->insert_data('inbox', $values);
} 

function maketime($string) {
    if ($string < 3600) {
        $string = sprintf("%02d:%02d", (int)($string / 60) % 60, $string % 60);
    } else {
        $string = sprintf("%02d:%02d:%02d", (int)($string / 3600) % 24, (int)($string / 60) % 60, $string % 60);
    } 
    return $string;
} 

// correct date
function date_fixed($timestamp = "", $format = "d.m.Y.", $myzone = "") {
    global $config; // time zone from config
    if (empty($timestamp)) {
        $timestamp = time();
    } 
    if (empty($format)) {
        $format = "d.m.y. / H:i";
    } 
    if (!empty($myzone)) {
        $config["timeZone"] = $myzone;
    } 

    if (stristr($config["timeZone"], '-')) {
        $clock = str_replace('-', '', $config["timeZone"]);
        $clock = floatval($clock);
        $seconds = $clock * 3600; // number of seconds
        $rdate = date($format, $timestamp - ($seconds)); // return date
    } else {
        $clock = str_replace('+', '', $config["timeZone"]);
        $clock = floatval($clock);
        $seconds = $clock * 3600; // number of seconds
        $rdate = date($format, $timestamp + ($seconds)); // return date
    } 

    return $rdate;
}

// delete user - deprecated 31.03.2020. 5:49:27
function delete_users($users) {
    global $db;

    // check is it user's id
    if (preg_match ("/^([0-9]+)$/", $users)) {
        $users_id = $users;
    } else {
        $users_id = $users->getidfromnick($users);
    }

    $db->delete("vavok_users", "id = '" . $users_id . "'");
    $db->delete("vavok_profil", "uid = '" . $users_id . "'");
    $db->delete("page_setting", "uid = '" . $users_id . "'");
    $db->delete("vavok_about", "uid = ''" . $users_id . "'");
    $db->delete("inbox", "byuid = " . $users_id . "' OR touid` = '" . $users_id . "'");
    $db->delete("ignore", "target = ''" . $users_id . " OR name = '" . $users_id . "'");
    $db->delete("buddy", "target = '" . $users_id . "' OR name = '" . $users_id . "'");
    $db->delete("subs", "user_id = '" . $users_id . "'");
    $db->delete("notif", "uid = '" . $users_id . "'");
    $db->delete("specperm", "uid = '" . $users_id . "'");

    return $users;
}

// clear file
function clear_files($files) {
    $file = file($files);
    $fp = fopen($files, "a+");
    flock ($fp, LOCK_EX);
    ftruncate ($fp, 0);
    fflush ($fp);
    flock ($fp, LOCK_UN);
    fclose($fp);

    return $files;
} 

function utf_to_win($str) {
    if (function_exists('mb_convert_encoding')) return mb_convert_encoding($str, 'windows-1251', 'utf-8');
    if (function_exists('iconv')) return iconv('utf-8', 'windows-1251', $str);

    $utf8win1251 = array("А" => "\xC0", "Б" => "\xC1", "В" => "\xC2", "Г" => "\xC3", "Д" => "\xC4", "Е" => "\xC5", "Ё" => "\xA8", "Ж" => "\xC6", "З" => "\xC7", "И" => "\xC8", "Й" => "\xC9", "К" => "\xCA", "Л" => "\xCB", "М" => "\xCC",
        "Н" => "\xCD", "О" => "\xCE", "П" => "\xCF", "Р" => "\xD0", "С" => "\xD1", "Т" => "\xD2", "У" => "\xD3", "Ф" => "\xD4", "Х" => "\xD5", "Ц" => "\xD6", "Ч" => "\xD7", "Ш" => "\xD8", "Щ" => "\xD9", "Ъ" => "\xDA",
        "Ы" => "\xDB", "Ь" => "\xDC", "Э" => "\xDD", "Ю" => "\xDE", "Я" => "\xDF", "а" => "\xE0", "б" => "\xE1", "в" => "\xE2", "г" => "\xE3", "д" => "\xE4", "е" => "\xE5", "ё" => "\xB8", "ж" => "\xE6", "з" => "\xE7",
        "и" => "\xE8", "й" => "\xE9", "к" => "\xEA", "л" => "\xEB", "м" => "\xEC", "н" => "\xED", "о" => "\xEE", "п" => "\xEF", "р" => "\xF0", "с" => "\xF1", "т" => "\xF2", "у" => "\xF3", "ф" => "\xF4", "х" => "\xF5",
        "ц" => "\xF6", "ч" => "\xF7", "ш" => "\xF8", "щ" => "\xF9", "ъ" => "\xFA", "ы" => "\xFB", "ь" => "\xFC", "э" => "\xFD", "ю" => "\xFE", "я" => "\xFF");

    return strtr($str, $utf8win1251);
} 

function win_to_utf($str) {
    if (function_exists('mb_convert_encoding')) return mb_convert_encoding($str, 'utf-8', 'windows-1251');
    if (function_exists('iconv')) return iconv('windows-1251', 'utf-8', $str);

    $win1251utf8 = array("\xC0" => "А", "\xC1" => "Б", "\xC2" => "В", "\xC3" => "Г", "\xC4" => "Д", "\xC5" => "Е", "\xA8" => "Ё", "\xC6" => "Ж", "\xC7" => "З", "\xC8" => "И", "\xC9" => "Й", "\xCA" => "К", "\xCB" => "Л", "\xCC" => "М",
        "\xCD" => "Н", "\xCE" => "О", "\xCF" => "П", "\xD0" => "Р", "\xD1" => "С", "\xD2" => "Т", "\xD3" => "У", "\xD4" => "Ф", "\xD5" => "Х", "\xD6" => "Ц", "\xD7" => "Ч", "\xD8" => "Ш", "\xD9" => "Щ", "\xDA" => "Ъ",
        "\xDB" => "Ы", "\xDC" => "Ь", "\xDD" => "Э", "\xDE" => "Ю", "\xDF" => "Я", "\xE0" => "а", "\xE1" => "б", "\xE2" => "в", "\xE3" => "г", "\xE4" => "д", "\xE5" => "е", "\xB8" => "ё", "\xE6" => "ж", "\xE7" => "з",
        "\xE8" => "и", "\xE9" => "й", "\xEA" => "к", "\xEB" => "л", "\xEC" => "м", "\xED" => "н", "\xEE" => "о", "\xEF" => "п", "\xF0" => "р", "\xF1" => "с", "\xF2" => "т", "\xF3" => "у", "\xF4" => "ф", "\xF5" => "х",
        "\xF6" => "ц", "\xF7" => "ч", "\xF8" => "ш", "\xF9" => "щ", "\xFA" => "ъ", "\xFB" => "ы", "\xFC" => "ь", "\xFD" => "э", "\xFE" => "ю", "\xFF" => "я");

    return strtr($str, $win1251utf8);
}

// make safe url for urlrewriting - link generating
// convert non-latin chars to latin and remove special
function trans($str) {
	  $sr_latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
    $sr_cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");

    $tr = array(
        // serbian latin
        "č" => "c", "Č" => "c", "ć" => "c", "Ć" => "c", "ž" => "z", "Ž" => "z", "Š" => "s", "š" => "s", "Đ" => "dj", "đ" => "dj", "Ð" => 'dj',
        // greece
        "Α" => "A", "α" => "a", "Β" => "V", "β" => "v", "Γ" => "G", "γ" => "g", "Δ" => "D", "δ" => "d", "Ε" => "e", "ε" => "e", 
        "Ζ" => "Z", "ζ" => "z", "Η" => "I", "η" => "i", "Θ" => "Th", "θ" => "th", "Ι" => "I", "ι" => "i", "Κ" => "K", "κ" => "k", 
        "Λ" => "L", "λ" => "l", "Μ" => "M", "μ" => "m", "Ν" => "N", "ν" => "n", "Ξ" => "X", "ξ" => "x", "Ο" => "O", "ο" => "o", 
        "Π" => "P", "π" => "p", "Ρ" => "R", "ρ" => "r", "Σ" => "S", "σ" => "s", "ς" => "s", "Τ" => "T", "τ" => "t", "Υ" => "I", 
        "υ" => "i", "Φ" => "Ph", "φ" => "ph", "Χ" => "Kh", "χ" => "kh", "Ψ" => "Ps", "ψ" => "ps", "Ω" => "O", "ω" => "o", 
        // Russian cyrillic
    "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g", "Д"=>"d",
    "Е"=>"e", "Ё"=>"yo", "Ж"=>"zh", "З"=>"z", "И"=>"i", 
    "Й"=>"j", "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", 
    "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s", "Т"=>"t", 
    "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"ts", "Ч"=>"ch", 
    "Ш"=>"sh", "Щ"=>"sch", "Ъ"=>"", "Ы"=>"y", "Ь"=>"", 
    "Э"=>"e", "Ю"=>"yu", "Я"=>"ya", "а"=>"a", "б"=>"b", 
    "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo", 
    "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k", 
    "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", 
    "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", 
    "х"=>"kh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch", 
    "ъ"=>"", "ы"=>"y", "ь"=>"", "э"=>"e", "ю"=>"yu", 
    "я"=>"ya",
        // other languages
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y',
        'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
        'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
        // special chars
        " " => "-", "." => "-", " / " => "-", "/" => "", "," => "", ":" => "", "'" => "", "\'" => "", "’" => "", "`" => "", "„" => "", "“" => "",
        ";" => "", "—"=>"", "<"=>"", ">"=>"",
        "”" => "", "´" => "", "~" => "", "&quot;" => "", "&#147;" => "", "&" => "and",
        // money
        "£" => "pounds", "$" => "dollars", "€" => "euros"

        );
    
    $text = str_replace($sr_cyrillic, $sr_latin, $str); // serbian cyrillic
    $text = strtr($text, $tr);// other languages 
    $text = preg_replace('/[^A-Za-z0-9_\-]/', '', $text); // replace special chars
    $text = str_replace("---", "-", $text);
    $text = strtolower(str_replace("--", "-", $text));
    return $text;
} 

function rus_utf_tolower($str) {
    if (function_exists('mb_strtolower')) return mb_strtolower($str, 'utf-8');

    $arraytolower = array("А" => "а", "Б" => "б", "В" => "в", "Г" => "г", "Д" => "д", "Е" => "е", "Ё" => "ё", "Ж" => "ж", "З" => "з", "И" => "и", "Й" => "й", "К" => "к", "Л" => "л", "М" => "м", "Н" => "н", "О" => "о", "П" => "п", "Р" => "р", "С" => "с", "Т" => "т", "У" => "у", "Ф" => "ф", "Х" => "х", "Ц" => "ц", "Ч" => "ч", "Ш" => "ш", "Щ" => "щ", "Ь" => "ь", "Ъ" => "ъ", "Ы" => "ы", "Э" => "э", "Ю" => "ю", "Я" => "я",
        "A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "I" => "i", "F" => "f", "G" => "g", "H" => "h", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z");

    return strtr($str, $arraytolower);
}

// check input fields
function check($str, $mysql = "") {
    if (get_magic_quotes_gpc()) {
        // strip all slashes
        $str = stripslashes($str);
    } 
    $str = str_replace("|", "&#124;", $str);
    $str = htmlspecialchars($str);
    $str = str_replace("'", "&#39;", $str);
    $str = str_replace("\"", "&#34;", $str);
    $str = str_replace("/\\\$/", "&#36;", $str);
    $str = str_replace('\\', "&#92;", $str);
    $str = str_replace("`", "", $str);
    $str = str_replace("^", "&#94;", $str);
    $str = str_replace("%", "&#37;", $str);
    $str = str_replace("№", "&#8470;", $str);
    $str = str_replace("™", "&#153;", $str);
    $str = str_replace("”", "&#8221;", $str);
    $str = str_replace("“", "&#8220;", $str);
    $str = str_replace("…", "&#8230;", $str);
    $str = str_replace("°", "&#176;", $str);
    $str = preg_replace("/&#58;/", ":", $str, 3);
    $str = str_replace("\\r\\n", "\r\n", $str); // we want new lines
    return $str;
} 
// no new line
function no_br($msg, $replace = "") { 
    // convert to unix new lines
    $msg = preg_replace("/\r\n/", "\n", $msg); 
    // remove extra new lines
    $msg = preg_replace("/\n/", $replace, $msg);
    return $msg;
}

// real IP
if (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_X_REAL_IP'])) {
    $ip = $_SERVER['HTTP_X_REAL_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} else {
    $ip = preg_replace("/[^0-9.]/", "", $_SERVER['REMOTE_ADDR']);
} 
$userip = htmlspecialchars(stripslashes($ip));
$ip = $userip; // deprecated! 14.7.2017. 8:41:36

// function for current time
$user_time = $config["timeZone"] * 3600;
$currHour = date("H", time() + $user_time);
$currHour = round($currHour);
$currDate = date("d F Y", time() + $user_time);
$curr = date("i:s", time() + $user_time);
$currTime = date("$currHour:i:s", time() + $user_time);
$currTime2 = date("$currHour:i", time());

// add smiles
function smiles($string) {
    $dir = opendir (BASEDIR . "images/smiles");
    while ($file = readdir ($dir)) {
        if (preg_match ("/.gif/", $file)) {
            $smfile[] = str_replace(".gif", "", $file);
        } 
    } 
    closedir ($dir);
    rsort($smfile);

    foreach($smfile as $smval) {
        $string = str_replace(":$smval", '<img src="' . HOMEDIR . 'images/smiles/' . $smval . '.gif" alt=":' . $smval . '" />', $string);
    } 
    $string = str_replace(" ;)", ' <img src="' . HOMEDIR . 'images/smiles/;).gif" alt=";)" />', $string);
    return $string;
} 

function nosmiles($string) {
    $string = preg_replace('#<img src="\.\./images/smiles/(.*?)\.gif" alt="(.*?)>#', ':$1', $string);
    return $string;
} 

function read_dir($dir) {
    if ($path = opendir($dir)) while ($file_name = readdir($path)) {
        if (($file_name !== '.') && ($file_name !== "..") && ($file_name !== ".htaccess")) {
            if (is_dir($dir . "/" . $file_name)) $size += read_dir($dir . "/" . $file_name);
            else $size += filesize($dir . "/" . $file_name);
        } 
    } 
    return $size;
}

// file size
function formatsize($file_size) {
    if ($file_size >= 1073741824) {
        $file_size = round($file_size / 1073741824 * 100) / 100 . " GB";
    } elseif ($file_size >= 1048576) {
        $file_size = round($file_size / 1048576 * 100) / 100 . " MB";
    } elseif ($file_size >= 1024) {
        $file_size = round($file_size / 1024 * 100) / 100 . " KB";
    } else {
        $file_size = $file_size . " b";
    } 
    return $file_size;
}

// badword / anti spam function
function antiword($string) {
    $words = file_get_contents(BASEDIR . "used/antiword.dat");
    $wordlist = explode("|", $words);

    foreach($wordlist as $value) {
        if (!empty($value)) {
            $string = preg_replace("/$value/i", "***", $string);
        } 
    } 
    return $string;
}

// CHMOD function
function permissions($filez) {
    $filez = decoct(fileperms("$filez")) % 1000;
    return $filez;
} 

function safe_encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('_', '-', ''), $data);
    return $data;
} 

function safe_decode($string) {
    $string = str_replace(array('_', '-'), array('+', '/'), $string);
    $data = base64_decode($string);
    return $data;
}

// encode using key
function xoft_encode($string, $key) {
    $result = "";
    for($i = 1; $i <= strlen($string); $i++) {
        $char = substr($string, $i-1, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    } 
    return safe_encode($result);
} 

// decode using key
function xoft_decode($string, $key) {
    $string = safe_decode($string);
    $result = "";
    for($i = 1; $i <= strlen($string); $i++) {
        $char = substr($string, $i - 1, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    } 
    return $result;
} 

// generate password
function generate_password() {
    $length = rand(10, 12);
    $salt = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789";
    $len = strlen($salt);
    $makepass = '';
    mt_srand(10000000 * (double)microtime());
    for ($i = 0; $i < $length; $i++)
    $makepass .= $salt[mt_rand(0, $len - 1)];
    return $makepass;
} 

// antiflood
$phpself = $_SERVER['PHP_SELF'];
function flooder($ip, $phpself) {
    global $config;

    $old_db = file(BASEDIR . "used/flood.dat");
    $new_db = fopen(BASEDIR . "used/flood.dat", "w");
    flock ($new_db, LOCK_EX);
    $result = false;

    foreach($old_db as $old_db_line) {
        $old_db_arr = explode("|", $old_db_line);

        if (($old_db_arr[0] + $config["floodTime"]) > time()) {
            fputs ($new_db, $old_db_line);

            if ($old_db_arr[1] == $ip && $old_db_arr[2] == $phpself) {
                $result = true;
            } 
        } 
    } 

    fflush($new_db);
    flock ($new_db, LOCK_UN);
    fclose($new_db);
    return $result;
}

// delete image links
function erase_img($image) {
    $image = preg_replace('#<img src="\.\./images/smiles/(.*?)\.gif" alt="(.*?)>#', '', $image);
    $image = preg_replace('#<img src="\.\./images/smiles2/(.*?)\.gif" alt="(.*?)>#', '', $image);
    $image = preg_replace('#<img src="(.*?)" alt="(.*?)>#', '', $image);

    return $image;
}

// user age
function getage($strdate) {
    $dob = explode(".", $strdate);
    if (count($dob) != 3) {
        return 0;
    } 
    $y = $dob[2];
    $m = $dob[1];
    $d = $dob[0];
    if (strlen($y) != 4) {
        return 0;
    } 
    if (strlen($m) != 2) {
        return 0;
    } 
    if (strlen($d) != 2) {
        return 0;
    } 

    $y += 0;
    $m += 0;
    $d += 0;

    if ($y == 0) return 0;
    $rage = date("Y") - $y;
    if (date("m") < $m) {
        $rage -= 1;
    } else {
        if ((date("m") == $m) && (date("d") < $d)) {
            $rage -= 1;
        } 
    } 
    return $rage;
} 

// parse bb code
function badlink($link, $prefix) {
    if ($prefix == "mailto:") {
        if (strpos($link, "@") === false || strpos($link, ".", (strpos($link, "@") + 2)) === false || substr_count($link, "@") > 1 || strpos($link, "@") == 0) {
            return 1;
        } 
    } 
    if (strpos($link, ".") == 0 || strpos($link, ".") == strlen($link) || (strpos($link, "/") < strpos($link, ".") && strpos($link, "/") !== false)) {
        return 1;
    } 
} ;
function setlinks($r, $prefix) {
    $target = "";
    if (substr($r, 0, strlen($prefix)) == $prefix) {
        $r = "\n" . $r;
    } 
    $r = str_replace("<br />" . $prefix, "<br />\n" . $prefix, $r);
    $r = str_replace(" " . $prefix, " \n" . $prefix, $r);
    while (strpos($r, "\n" . $prefix) !== false) {
        list($r1, $r2) = explode("\n" . $prefix, $r, 2);
        if (strpos($r2, " ") === false && strpos($r2, "<br />") === false) {
            if ($prefix != "mailto:") {
                $target = ' target="_blank"';
            } else {
                $target = "";
            } 
            if (strpos($r2, ".") > 1 && strpos($r2, ".") < strlen($r2) && badlink($r2, $prefix) != 1) {
                $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a>';
            } else {
                $r = $r1 . $prefix . $r2;
            } 
        } else {
            if (strpos($r2, " ") === false || (strpos($r2, " ") > strpos($r2, "<br />") && strpos($r2, "<br />") !== false)) {
                list($r2, $r3) = explode("<br />", $r2, 2);
                if (badlink($r2, $prefix) != 1) {
                    $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a><br>' . $r3;
                } else {
                    $r = $r1 . $prefix . $r2 . '<br />' . $r3;
                } 
            } else {
                list($r2, $r3) = explode(" ", $r2, 2);
                if (strpos($r2, ".") > 1 && strpos($r2, ".") < strlen($r2) && badlink($r2, $prefix) != 1) {
                    $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a> ' . $r3;
                } else {
                    $r = $r1 . $prefix . $r2 . ' ' . $r3;
                } 
            } 
        } 
    } 
    return $r;
}

function getbbcode($r) {
    $r = str_replace("\r\n", "<br />", $r);
    $r = str_replace("[br]", "<br />", $r);
    $r = preg_replace('#\[b\](.*?)\[/b\]#si', '<b>\1</b>', $r);
    $r = preg_replace('#\[i\](.*?)\[/i\]#si', '<i>\1</i>', $r);
    $r = preg_replace('#\[u\](.*?)\[/u\]#si', '<u>\1</u>', $r);
    $r = preg_replace("/\[big\](.*?)\[\/big\]/i", "<big>\\1</big>", $r);
    $r = preg_replace("/\[small\](.*?)\[\/small\]/i", "<small>\\1</small>", $r);
    $r = str_replace("[spoiler]", '[spoiler]<font color="#DDDDDD">', $r);
    $r = str_replace("[/spoiler]", "</font>[/spoiler]", $r);
    $r = preg_replace('#\[img\](.*?)\[/img\]#si', '<img src=\"\1\" alt=\"\" />', $r);

    $r = preg_replace('#\[red\](.*?)\[/red\]#si', '<span style="color:#FF0000">\1</span>', $r);
    $r = preg_replace('#\[green\](.*?)\[/green\]#si', '<span style="color:#00FF00">\1</span>', $r);
    $r = preg_replace('#\[blue\](.*?)\[/blue\]#si', '<span style="color:#0000FF">\1</span>', $r);

    $r = preg_replace('#\[code\](.*?)\[/code\]#si', '<div class="d"><code style="white-space:wrap">\1</code></div>', $r);
    $r = preg_replace('#\[quote\](.*?)\[/quote\]#si', '<q>\1</q>', $r); 
    // set [link]s
    while (strpos($r, "[url=") !== false) {
        list ($r1, $r2) = explode("[url=", $r, 2);
        if (strpos($r2, "]") !== false) {
            list ($r2, $r3) = explode("]", $r2, 2);
            if (strpos($r3, "[/url]") !== false) {
                list($r3, $r4) = explode("[/url]", $r3, 2);
                $target = ' target="_blank"';
                if (substr($r2, 0, 7) == "mailto:") {
                    $target = "";
                } 
                $r = $r1 . '<a href="' . $r2 . '"' . $target . '>' . $r3 . '</a>' . $r4;
            } else {
                $r = $r1 . "[url\n=" . $r2 . "]" . $r3;
            } 
        } else {
            $r = $r1 . "[url\n=" . $r2;
        } 
    } 
    $r = str_replace("[url\n=", "[url=", $r); 
    // //[url]
    // /default url link setting
    $r = setlinks($r, "http://");
    $r = setlinks($r, "https://");
    $r = setlinks($r, "ftp://");
    $r = setlinks($r, "mailto:"); 
    // //links
    $r = trim($r);
    return $r;
}

// are you moderator - deprecated! use is_moderator()
function ismod($id = '', $num = '') {
    global $user_id, $db;

    if (empty($id) && !empty($user_id)) {
        $id = $user_id;
    } else {
        return false;
    } 
    $chk_mod = $db->select('vavok_users', "id='" . $id . "'", '', 'perm');

    $perm = $chk_mod['perm'];

    if ($perm == "103" || $perm == "105" || $perm == "106" && empty($num)) {
        return true;
    } elseif ($num == $perm) {
        return true;
    } else {
        return false;
    } 
}

// deprecated 31.03.2020. 5:50:56
function is_moderator($num = '', $id = '') {
    global $db, $user_id;

    if (empty($id) && !empty($user_id)) {
        $id = $user_id;
    }

    $chk_adm = $db->select('vavok_users', "id='" . $id . "'", '', 'perm');
    $perm = trim($chk_adm['perm']);
    if ($perm == $num) {
        return true;
    } elseif (empty($num) && ($perm == 103 || $perm == 105 || $perm == 106)) {
        return true;
    } else {
        return false;
    } 
}

// are you admin? - deprecated! use is_administrator()
function isadmin($id = '', $num = '') {
    global $user_id, $db;
    if (empty($id) && !empty($user_id)) {
        $id = $user_id;
    } else {
        return false;
    } 
    $chk_adm = $db->select('vavok_users', "id='" . $id . "'", '', 'perm');
    $perm = trim($chk_adm['perm']);
    if ($perm == "101" || $perm == "102" && empty($num)) {
        return true;
    } elseif ($num == $perm) {
        return true;
    } else {
        return false;
    } 
}

// deprecated 31.03.2020. 5:52:58
function is_administrator($num = '', $id = '') {
    global $db, $user_id;

    if (empty($id) && !empty($user_id)) {
        $id = $user_id;
    }

    $chk_adm = $db->select('vavok_users', "id='" . $id . "'", '', 'perm');
    $perm = trim($chk_adm['perm']);
    if ($perm == $num) {
        return true;
    } elseif (empty($num) && ($perm == 101 || $perm == 102)) {
        return true;
    } else {
        return false;
    } 
} 

// is ignored - deprecated 31.03.2020. 5:53:28
function isignored($tid, $uid) {
    global $db;
    $ign = $db->count_row('`ignore`', "`target`='" . $tid . "' AND `name`='" . $uid . "'");
    if ($ign > 0) {
        return true;
    } 
    return false;
}

// ignore result - deprecated 31.03.2020. 5:53:35
function ignoreres($uid, $tid) { 
    // 0 user can't ignore the target
    // 1 yes can ignore
    // 2 already ignored
    if ($uid == $tid) {
        return 0;
    } 
    /*
  if (ismod($tid)) {
    //you cant ignore staff members
    return 0;
  }
  if (arebuds($tid, $uid)) {
    //why the hell would anyone ignore his bud? o.O
    return 0;
  }
  */
    if (isignored($tid, $uid)) {
        return 2; // the target is already ignored by the user
    } 
    return 1;
} 

// is buddy - deprecated 31.03.2020. 5:54:11
function isbuddy($tid, $uid) {
    global $db;
    $ign = $db->count_row('buddy', "target='" . $tid . "' AND name='" . $uid . "'");
    if ($ign > 0) {
        return true;
    } 
    return false;
}

// count number of lines in file
function counter_string($files) {
    $count_lines = 0;
    if (file_exists($files)) {
        $lines = file($files);
        $count_lines = count($lines);
    } 

    return $count_lines;
}

// private messages - deprecated 31.03.2020. 5:55:07
function getpmcount($uid, $view = "all") {
    global $db, $user_id;
    if ($view == "all") {
        $nopm = $db->count_row('inbox', "touid='" . $uid . "' AND (deleted <> '" . $user_id . "' OR deleted IS NULL)");
    } elseif ($view == "snt") {
        $nopm = $db->count_row('inbox', "byuid='" . $uid . "' AND (deleted <> '" . $user_id . "' OR deleted IS NULL)");
    } elseif ($view == "str") {
        $nopm = $db->count_row('inbox', "touid='" . $uid . "' AND starred='1'");
    } elseif ($view == "urd") {
        $nopm = $db->count_row('inbox', "touid='" . $uid . "' AND unread='1'");
    } 
    return $nopm;
} 

// get number of unread pms - deprecated 31.03.2020. 5:55:07
function getunreadpm($uid) {
    global $db;
    $nopm = $db->count_row('inbox', "touid='" . $uid . "' AND unread='1'");
    return $nopm[0];
}

// number of private msg's - deprecated 31.03.2020. 5:55:07
function user_mail($userid) {
    $fcheck_all = getpmcount($userid);
    $new_privat = getunreadpm($userid);

    $all_mail = $new_privat . '/' . $fcheck_all;

    return $all_mail;
} 

// user online status - deprecated 31.03.2020. 5:55:07
function user_online($login) {
    global $db;

    $xuser = $users->getidfromnick($login);
    $statwho = '<font color="#FF0000">[Off]</font>';

    $result = $db->count_row('online', 'user="' . $xuser . '"');

    if ($result > 0 && $xuser > 0) {
        $statwho = '<font color="#00FF00">[On]</font>';
    } 

    return $statwho;
}

// get title for page
function page_title($string) {
    global $db;

    $page_title = $db->select('pages', "pname='" . $string . "'", '', '*');

    if (!empty($page_title['tname'])) {
        $position = $page_title['tname'];
    } else {
        $position = '';
    }

    return $position;
} 

// number of registered members - deprecated 31.03.2020. 5:55:41
function regmemcount() {
    global $db;
    $rmc = $db->count_row('vavok_users');
    return $rmc;
}

// send email - deprecated 28.3.2020. 15:36:41
function sendmail($usermail, $subject, $msg, $mail = "", $name = "") {
    global $config, $config_srvhost;

    if (empty($mail)) {
        $mail = $config_srvhost;
        if (substr($mail, 0, 2) == 'm.') {
            $mail = substr($str, 2);
        } 
        if (substr($mail, 0, 4) == 'www.') {
            $mail = substr($str, 4);
        }
        $mail = 'no_reply@' . $mail;
        $name = $config["title"];
    } 
    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $adds = "From: " . $name . " <" . $mail . ">\n";
    $adds .= "X-sender: " . $name . " <" . $mail . ">\n";
    $adds .= "Content-Type: text/plain; charset=utf-8\n";
    $adds .= "MIME-Version: 1.0\n";
    $adds .= "Content-Transfer-Encoding: 8bit\n";
    $adds .= "X-Mailer: PHP v." . phpversion();

    return mail($usermail, $subject, $msg, $adds);
} 

function compress_output_gzip($output) {
    return gzcompress($output, 3);
} 

function compress_output_deflate($output) {
    return gzdeflate($output, 3);
}

// generate meta tags "description" and "keywords"
function create_metatags($story) {
    $keyword_count = 10;
    $newarr = array ();
    $headers = array ();
    $quotes = array("\x27", "\x22", "\x60", "\t", '\n', '\r', '\\', "'", ",", ".", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "-", "+", ")", "(", "*", "&", "^", "%", "$", "<", ">", "?", "!", '"');
    $fastquotes = array("\x27", "\x22", "\x60", "\t", "\n", "\r", '"', "'");

    $story = preg_replace("'\[hide\](.*?)\[/hide\]'si", "", $story);

    $story = str_replace('<br>', ' ', $story);
    $story = trim(strip_tags($story));
    $story = str_replace($fastquotes, '', $story);

    $headers['description'] = substr($story, 0, 190);

    $story = str_replace('<br>', ' ', $story);
    $story = trim(strip_tags($story));

    $story = str_replace($quotes, '', $story);

    $arr = explode(" ", $story);

    foreach ($arr as $word) {
        if (strlen($word) > 4) $newarr [] = $word;
    } 

    $arr = array_count_values ($newarr);
    arsort ($arr);

    $arr = array_keys($arr);

    $total = count ($arr);

    $offset = 0;

    $arr = array_slice ($arr, $offset, $keyword_count);

    $headers['keywords'] = implode(", ", $arr);

    return $headers;
} 

// deprecated 31.03.2020. 5:56:16
function isstarred($pmid) {
    global $db;
    $strd = $db->select('inbox', "id='" . $pmid . "'", '', 'starred');
    if ($strd['starred'] == "1") {
        return true;
    } else {
        return false;
    } 
} 

// deprecated 31.03.2020. 5:56:16
function parsepm($text) {
    $text = antiword($text);
    $text = smiles($text);
    $text = getbbcode($text);
    if (get_magic_quotes_gpc()) {
        $text = stripslashes($text);
    } 

    return $text;
} 

// deprecated 31.03.2020. 5:56:16
function is_reg() {
    global $db;
    if (!empty($_SESSION['log']) && !empty($_SESSION['pass'])) {
        $isuser_check = getidfromnick(check($_SESSION['log']));
        if (!empty($isuser_check)) {
            $show_user = $db->select('vavok_users', "id='" . $isuser_check . "'", '', 'name, pass');
            if (check($_SESSION['log']) == $show_user['name'] && md5($_SESSION['pass']) == $show_user['pass']) {
                return true;
            } else {
                session_destroy();
                return false;
            }
        } else {
            session_destroy();
            return false;
        }
    }
}

// check URL
function validateURL($URL) {
    $v = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
    return (bool)preg_match($v, $URL);
} 

function isValidEmail($email) {
    if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email) == true) {
        return true;
    } else {
        return false;
    } 
}

// is this really number - return integer
function clean_int($i) {
    if (is_numeric($i)) {
        return (int)$i;
    } 
    // return False if we don't get a number
    else {
        return false;
    } 
}

// get info about user - deprecated 31.03.2020. 5:56:51
function get_user_info($xuser_id, $info) {
    global $db;
    if ($info == 'email') {
        $uinfo = $db->select('vavok_about', "uid='" . $xuser_id . "'", '', 'email');
        return $uinfo['email'];
    } 
}

// deprecated 24.5.2017. 2:50:18 echo_isset is deprecated, use get_isset
function echo_isset($msg = '') {
    global $config, $isset, $error;

    if (!empty($msg)) {
        $isset = $msg;
    } 
    include_once BASEDIR . "lang/" . $config["language"] . "/isset.php";
    if (!empty($issetLang[$isset])) {
    return $issetLang[$isset];
    }
}

function get_isset($msg = '') {
    global $config, $isset;

    if (!empty($msg)) {
        $isset = $msg;
    } 
    include_once BASEDIR . "lang/" . $config["language"] . "/isset.php";

    if (!empty($issetLang[$isset])) {
    return $issetLang[$isset];
    }
}

// show online
function show_online() {
    global $config, $counter_reg, $counter_online;
    if ($config["showOnline"] == "1") {
        $online = '<a href="/pages/online.php">[Online: ' . $counter_reg . '/' . $counter_online . ']</a>';
        return $online;
    } 
}

// show counter
function show_counter() {
    global $config, $counter_host, $counter_all, $counter_hits, $counter_allhits;

    if (!empty($config["showCounter"]) && $config["showCounter"] != "6") {
        if ($config["showCounter"] == "1") {
            $counter = '<a href="/pages/counter.php">' . $counter_host . ' | ' . $counter_all . '</a>';
        } 
        if ($config["showCounter"] == "2") {
            $counter = '<a href="/pages/counter.php">' . $counter_hits . ' | ' . $counter_allhits . '</a>';
        } 
        if ($config["showCounter"] == "3") {
            $counter = '<a href="/pages/counter.php">' . $counter_host . ' | ' . $counter_hits . '</a>';
        } 
        if ($config["showCounter"] == "4") {
            $counter = '<a href="/pages/counter.php">' . $counter_all . ' | ' . $counter_allhits . '</a>';
        } 
        if ($config["showCounter"] == "5") {
            $counter = '<a href="/pages/counter.php"><img src="/gallery/count.php" alt=""></a>';
        } 
        return $counter;
    } 
}

// get prefered language
function getDefaultLanguage() {
    if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        return parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    else
        return parseDefaultLanguage(null);
}

function parseDefaultLanguage($http_accept, $deflang = "en") {
    if (isset($http_accept) && strlen($http_accept) > 1) {
        // Split possible languages into array
        $x = explode(",", $http_accept);
        foreach ($x as $val) {
            // check for q-value and create associative array. No q-value means 1 by rule
            if (preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i", $val, $matches))
                $lang[$matches[1]] = (float)$matches[2];
            else
                $lang[$val] = 1.0;
        } 
        // return default language (highest q-value)
        $qval = 0.0;
        foreach ($lang as $key => $value) {
            if ($value > $qval) {
                $qval = (float)$value;
                $deflang = $key;
            } 
        } 
    } 
    return strtolower($deflang);
}

// check special permissions - deprecated! 02.07.2017. 09:45:18 use checkPermissions
function chkcpecprm($permname, $needed) {
    global $user_id, $db;

    $check = $db->count_row('specperm', "uid='" . $user_id . "' AND permname='" . $permname . "'");

    if ($check > 0) {
        $check_data = $db->select('specperm', "uid='" . $user_id . "' AND permname='" . $permname . "'", '', 'permacc');
        $perms = explode(',', $check_data['permacc']);
        if ($needed == 'show' && $perms[0] == 1) {
            return true;
        } elseif ($needed == 'edit' && $perms[1] == 2) {
            return true;
        } elseif ($needed == 'del' && $perms[2] == 3) {
            return true;
        } elseif ($needed == 'insert' && $perms[3] == 4) {
            return true;
        } elseif ($needed == 'editunpub' && $perms[4] == 5) {
            return true;
        } else {
            return false;
        } 
    } else {
        return false;
    } 
}

// check permissions for admin panel
// check if user have permitions to see, edit, delete, etc selected part of the website
function checkPermissions($permname, $needed = 'show') {
    global $user_id, $db;

    $permname = str_replace('.php', '', $permname);

    if (is_administrator(101)) {
        return true;
    }

    $check = $db->count_row(getConfiguration('tablePrefix') . 'specperm', "uid='" . $user_id . "' AND permname='" . $permname . "'");

    if ($check > 0) {
        $check_data = $db->select(getConfiguration('tablePrefix') . 'specperm', "uid='" . $user_id . "' AND permname='" . $permname . "'", '', 'permacc');
        $perms = explode(',', $check_data['permacc']);
        if ($needed == 'show' && (in_array(1, $perms) || in_array('show', $perms))) {
            return true;
        } elseif ($needed == 'edit' && (in_array(2, $perms) || in_array('edit', $perms))) {
            return true;
        } elseif ($needed == 'del' && (in_array(3, $perms) || in_array('del', $perms))) {
            return true;
        } elseif ($needed == 'insert' && (in_array(4, $perms) || in_array('insert', $perms))) {
            return true;
        } elseif ($needed == 'editunpub' && (in_array(5, $perms) || in_array('editunpub', $perms))) {
            return true;
        } else {
            return false;
        } 
    } else {
        return false;
    } 
}

// java script bb codes for text input
function java_bbcode($inputName) {
	
?>
<script language="JavaScript">
<!--
  function tag(text1, text2) 
  { 
     if ((document.selection)) 
     { 
       document.form.<?php echo $inputName; ?>.focus(); 
       document.form.document.selection.createRange().text = text1+document.form.document.selection.createRange().text+text2; 
     } else if(document.forms['form'].elements[<?php echo '\'' . $inputName . '\''; ?>].selectionStart != undefined) { 
         var element    = document.forms['form'].elements[<?php echo '\'' . $inputName . '\''; ?>]; 
         var str     = element.value; 
         var start    = element.selectionStart; 
         var length    = element.selectionEnd - element.selectionStart; 
         element.value = str.substr(0, start) + text1 + str.substr(start, length) + text2 + str.substr(start + length); 
     } else document.form.<?php echo $inputName; ?>.value += text1+text2; 
  }	
//--> 
</script>

<a href=# onClick="javascript:tag('[url=', ']url name here[/url]'); return false;"><img src="<?php echo HOMEDIR; ?>images/editor/a.gif" alt="" /></a> 
<a href=# onClick="javascript:tag('[img]', '[/img]'); return false;"><img src="<?php echo HOMEDIR; ?>images/editor/img.gif" alt="" /></a> 
<a href=# onClick="javascript:tag('[b]', '[/b]'); return false;"><img src="<?php echo HOMEDIR; ?>images/editor/b.gif" alt="" /></a> 
<a href=# onClick="javascript:tag('[i]', '[/i]'); return false;"><img src="<?php echo HOMEDIR; ?>images/editor/i.gif" alt="" /></a> 
<a href=# onClick="javascript:tag('[u]', '[/u]'); return false;"><img src="<?php echo HOMEDIR; ?>images/editor/u.gif" alt="" /></a> 
<a href=# onClick="javascript:tag('[youtube]', '[/youtube]'); return false;"><img src="<?php echo HOMEDIR; ?>images/socialmedia/youtube.png" width="16" height="16" alt="" /></a> 
<?php
}

// redirect page
function redirect_to($url) {
    if (!headers_sent()) { // check headers - you can not send headers if they already sent
        header('Location: ' . $url);
        exit; // protects from code being executed after redirect request
    } else {
        throw new Exception('Cannot redirect, headers already sent');
    }
}
?>