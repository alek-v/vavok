<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   22.07.2020. 0:44:27
*/

/*

Date and time

*/

// Correct date
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

// Function for current time
$user_time = $config["timeZone"] * 3600;
$currHour = date("H", time() + $user_time);
$currHour = round($currHour);
$currDate = date("d F Y", time() + $user_time);
$curr = date("i:s", time() + $user_time);
$currTime = date("$currHour:i:s", time() + $user_time);
$currTime2 = date("$currHour:i", time());

function maketime($string) {
    if ($string < 3600) {
        $string = sprintf("%02d:%02d", (int)($string / 60) % 60, $string % 60);
    } else {
        $string = sprintf("%02d:%02d:%02d", (int)($string / 3600) % 24, (int)($string / 60) % 60, $string % 60);
    } 
    return $string;
}

/*

File manipulation

*/

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

// Clear directory
function clear_directory($directory) {
    $di = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

    foreach($ri as $file) {
        $file->isDir() ?  rmdir($file) : unlink($file);
    }
}

// Read directory size
function read_dir($dir) {
    if ($path = opendir($dir)) while ($file_name = readdir($path)) {
        $size = 0;
        if (($file_name !== '.') && ($file_name !== "..") && ($file_name !== ".htaccess")) {
            if (is_dir($dir . "/" . $file_name)) $size = read_dir($dir . "/" . $file_name);
            else $size += filesize($dir . "/" . $file_name);
        } 
    } 
    return $size;
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

/*

String and text manipulation

*/

// Multibyte ucfirst by plemieux
function my_mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);
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
    // Serbian latin
    "č" => "c", "Č" => "c", "ć" => "c", "Ć" => "c", "ž" => "z", "Ž" => "z", "Š" => "s", "š" => "s", "Đ" => "dj", "đ" => "dj", "Ð" => 'dj',
    
    // Greece
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
    $text = strtr($text, $tr); // other languages 
    $text = preg_replace('/[^A-Za-z0-9_\-]/', '', $text); // replace special chars
    $text = str_replace("---", "-", $text);
    $text = strtolower(str_replace("--", "-", $text));

    return $text;
}

// remove unwanted characters from Unicode URL-s
function trans_unicode($text) {

    $tr = array(

    // special chars
    " " => "-", "." => "-", " / " => "-", "/" => "", "," => "", ":" => "", "'" => "", "\'" => "", "’" => "", "`" => "", "„" => "", "“" => "",
    ";" => "", "—"=>"", "<"=>"", ">"=>"",
    "”" => "", "´" => "", "~" => "", "&quot;" => "", "&#147;" => ""

    );

    $text = mb_strtolower(strtr($text, $tr)); // other languages 

    return $text;
}

// no new line
function no_br($msg, $replace = "") { 
    // convert to unix new lines
    $msg = preg_replace("/\r\n/", "\n", $msg); 
    // remove extra new lines
    $msg = preg_replace("/\n/", $replace, $msg);
    return $msg;
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

// delete image links
function erase_img($image) {
    $image = preg_replace('#<img src="\.\./images/smiles/(.*?)\.gif" alt="(.*?)>#', '', $image);
    $image = preg_replace('#<img src="\.\./images/smiles2/(.*?)\.gif" alt="(.*?)>#', '', $image);
    $image = preg_replace('#<img src="(.*?)" alt="(.*?)>#', '', $image);

    return $image;
}

function no_smiles($string) {
    $string = preg_replace('#<img src="' . HOMEDIR . '/images/smiles/(.*?)\.gif" alt="(.*?)>#', ':$1', $string);
    return $string;
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
}

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

/* 

Security

*/

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
function flooder($ip, $phpself = '') {
    global $config;

    $old_db = file(BASEDIR . "used/flood.dat");
    $new_db = fopen(BASEDIR . "used/flood.dat", "w");
    flock ($new_db, LOCK_EX);
    $result = false;

    foreach($old_db as $old_db_line) {
        $old_db_arr = explode("|", $old_db_line);

        if (($old_db_arr[0] + $config["floodTime"]) > time()) {
            fputs ($new_db, $old_db_line);

            if ($old_db_arr[1] == $ip && $old_db_arr[2] == $_SERVER['PHP_SELF']) {
                $result = true;
            } 
        } 
    } 

    fflush($new_db);
    flock ($new_db, LOCK_UN);
    fclose($new_db);
    return $result;
}

/*

Validations

*/

// check URL
function validateURL($URL) {
    $v = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
    return (bool)preg_match($v, $URL);
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

// check if sting contain unicode characters
function is_unicode($data) {

    if (strlen($data) !== strlen(utf8_decode($data))) {
        return true;
    } else {
        return false;
    }

}

/*

Show informations

*/

// show fatal error
function fatal_error($error) {
    echo '<div style="text-align: center;margin-top: 40px;">Error: ' . $error . '</div>';
    exit;
}

// show error
function show_error($error) {
    echo '<span style="text-align: center;margin-top: 40px;">Error: ' . $error . '</span>';
}

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
        $string = str_replace(":$smval:", '<img src="' . HOMEDIR . 'images/smiles/' . $smval . '.gif" alt=":' . $smval . ':" />', $string);
    } 

    $string = str_replace(" ;)", ' <img src="' . HOMEDIR . 'images/smiles/).gif" alt=";)" />', $string);
    $string = str_replace(" :)", ' <img src="' . HOMEDIR . 'images/smiles/).gif" alt=":)" />', $string);
    $string = str_replace(" :(", ' <img src="' . HOMEDIR . 'images/smiles/(.gif" alt=":(" />', $string);
    $string = str_replace(" :D", ' <img src="' . HOMEDIR . 'images/smiles/D.gif" alt=":D" />', $string);
    $string = str_replace(" :E", ' <img src="' . HOMEDIR . 'images/smiles/E.gif" alt=":E" />', $string);
    $string = str_replace(" :P", ' <img src="' . HOMEDIR . 'images/smiles/P.gif" alt=":P" />', $string);

   return $string;
}

// Get message from url
function get_isset($msg = '') {

    if (!empty($msg)) {
        $isset = $msg;
    } elseif (isset($_GET['isset'])) {
        $isset = check($_GET['isset']);
    }

    include_once BASEDIR . "lang/" . get_configuration("language") . "/isset.php";

    if (isset($isset) && !empty($issetLang[$isset])) {

        $isset_msg = new PageGen('pages/isset.tpl');
        $isset_msg->set('message', $issetLang[$isset]);
        
        return $isset_msg->output();
        
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

/*

Other

*/

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

// get transfer protocol https or http
function transfer_protocol() {

    if (empty(get_configuration('transferProtocol')) || get_configuration('transferProtocol') == 'auto') {
        if (!empty($_SERVER['HTTPS'])) {
            $connectionProtocol = 'https://';
        } else {
            $connectionProtocol = 'http://';
        }
    } elseif (get_configuration('transferProtocol') == 'HTTPS') {
        $connectionProtocol = 'https://';
    } else {
        $connectionProtocol = 'http://';
    }

    return $connectionProtocol;
}

// complete dynamic website address
function website_home_address() {
    return transfer_protocol() . $_SERVER['HTTP_HOST'];
}

// website configuration
function get_configuration($data = '') {
    global $config;

    if (empty($data)) {
        return $config;
    } else {
        return $config[$data];
    }
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

// Detect bots and spiders
function detect_bot() {
    $user_agents = '';
    $searchbot = '';

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agents = $_SERVER['HTTP_USER_AGENT'];
    }
    if (stristr($user_agents, 'Yandex')) {
        $searchbot = 'Yandex';
    } elseif (stristr($user_agents, 'Slurp')) {
        $searchbot = 'Yahoo! Slurp';
    } elseif (stristr($user_agents, 'yahoo')) {
        $searchbot = 'Yahoo!';
    } elseif (stristr($user_agents, 'mediapartners-google')) {
        $searchbot = 'Mediapartners-Google';
    } elseif (stristr($user_agents, 'Googlebot-Image')) {
        $searchbot = 'Googlebot-Image';
    } elseif (stristr($user_agents, 'google')) {
        $searchbot = 'Googlebot';
    } elseif (stristr($user_agents, 'StackRambler')) {
        $searchbot = 'Rambler';
    } elseif (stristr($user_agents, 'lycos')) {
        $searchbot = 'Lycos';
    } elseif (stristr($user_agents, 'SurveyBot')) {
        $searchbot = 'Survey';
    } elseif (stristr($user_agents, 'bingbot')) {
        $searchbot = 'Bing';
    } elseif (stristr($user_agents, 'msnbot')) {
        $searchbot = 'msnbot';
    } elseif (stristr($user_agents, 'Baiduspider')) {
        $searchbot = 'Baidu Spider';
    } elseif (stristr($user_agents, 'Sosospider')) {
        $searchbot = 'Soso Spider';
    } elseif (stristr($user_agents, 'ia_archiver')) {
        $searchbot = 'ia_archiver';
    } elseif (stristr($user_agents, 'facebookexternalhit')) {
        $searchbot = 'Facebook External Hit';
    }

    return $searchbot;

}

?>