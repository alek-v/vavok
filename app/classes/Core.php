<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Core {
	protected object $db;

	public function __construct()
	{
		$this->db = new Database;
	}

	/**
	 * Get website configuration
	 *
	 * @param string $data
	 * @param bool $full_configuration
	 * @return mixed
	 */
	public function configuration($data = '', $full_configuration = false)
	{
		$config = array();

		foreach ($this->db->query("SELECT * FROM settings WHERE setting_group = 'system'") as $item) {
			$config[$item['setting_name']] = $item['value'];
		}

	    // Additional settings
	    $config['rssIcon'] = 0; // RSS icon
		$config['timeZone'] = empty($config['timeZone']) ? $config['timeZone'] = 0 : $config['timeZone']; // check is there timezone number set
		$config['siteTime'] = time() + ($config['timeZone'] * 3600); 
		$config['homeBase'] = isset($config['homeUrl']) ? str_replace('http://', '', $config['homeUrl']) : '';
		$config['homeBase'] = str_replace('https://', '', $config['homeBase']);

	    // Get complete configuration
	    if ($full_configuration == true) {
	        return $config;
	    }

	    if (!empty($data) && isset($config[$data])) {
	        return $config[$data];
	    } else {
	        return false;
	    }
	}

	/**
	 * Return correct date
	 *
	 * @param bool $timestamp
	 * @param string $format
	 * @param string $myzone
	 * @return string
	 */
	public function correctDate($timestamp = '', $format = 'd.m.Y.', $myzone = '', $show_zone_info = '')
	{
	    $timezone = $this->configuration('timeZone');

	    if (empty($timestamp)) $timestamp = time();

	    if (empty($format)) $format = "d.m.y. / H:i";

	    if (!empty($myzone)) $timezone = $myzone;

	    if (stristr($timezone, '-')) {
	        $clock = str_replace('-', '', $timezone);
	        $clock = floatval($clock);
	        $seconds = $clock * 3600; // number of seconds
	        $rdate = date($format, $timestamp - ($seconds)); // return date
	    } else {
	        $clock = str_replace('+', '', $timezone);
	        $clock = floatval($clock);
	        $seconds = $clock * 3600; // number of seconds
	        $rdate = date($format, $timestamp + ($seconds)); // return date
	    }

	    $zone_info = $show_zone_info == true ? ' UTC ' . $timezone : '';

	    return $rdate . $zone_info;
	}

	// Make time
	function makeTime($string) {
	    if ($string < 3600) {
	        $string = sprintf("%02d:%02d", (int)($string / 60) % 60, $string % 60);
	    } else {
	        $string = sprintf("%02d:%02d:%02d", (int)($string / 3600) % 24, (int)($string / 60) % 60, $string % 60);
	    }
	    return $string;
	}

	// Format time into days and minutes
	function formatTime($file_time) {
	    if ($file_time >= 86400) {
	        $file_time = round((($file_time / 60) / 60) / 24, 1) . ' {@localization[days]}}';
	    } elseif ($file_time >= 3600) {
	        $file_time = round(($file_time / 60) / 60, 1) . ' {@localization[hours]}}';
	    } elseif ($file_time >= 60) {
	        $file_time = round($file_time / 60) . ' {@localization[minutes]}}';
	    } else {
	        $file_time = round($file_time) . ' {@localization[secs]}}';
	    }

	    return $file_time;
	}

	// clear file
	function clearFile($files) {
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
	function clearDirectory($directory) {
	    $di = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
	    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

	    foreach($ri as $file) {
	        $file->isDir() ?  rmdir($file) : unlink($file);
	    }
	}

	// Read directory size
	function readDirectory($dir) {
	    if ($path = opendir($dir)) while ($file_name = readdir($path)) {
	        $size = 0;
	        if (($file_name !== '.') && ($file_name !== "..") && ($file_name !== ".htaccess")) {
	            if (is_dir($dir . "/" . $file_name)) $size = $this->readDirectory($dir . "/" . $file_name);
	            else $size += filesize($dir . "/" . $file_name);
	        }
	    }

	    return $size;
	}

	// count number of lines in file
	function linesInFile($files) {
	    $count_lines = 0;
	    if (file_exists($files)) {
	        $lines = file($files);
	        $count_lines = count($lines);
	    }

	    return $count_lines;
	}

	/**
	 * Limit number of lines in file
	 * 
	 * @param string $file_name
	 * @param integer $max
	 * @return void
	 */
	function limitFileLines($file_name, $max = 100)
	{
	    $file = file($file_name);
	    $i = count($file);
	    if ($i >= $max) {
	        $fp = fopen($file_name, "w");
	        flock ($fp, LOCK_EX);
	        unset($file[0]);
	        unset($file[1]);
	        fputs($fp, implode('', $file));
	        flock ($fp, LOCK_UN);
	        fclose($fp);
	    }
	}

	/**
	 * Read file from data directory
	 *
	 * @param string $filename
	 * @return array
	 */
	public function getDataFile($filename)
	{
		return file(APPDIR . 'used/' . $filename);
	}

	/**
	 * Write to file in data directory
	 *
	 * @param string $filename
	 * @param string $data
	 * @param integer $append_data, 1 is to append new data
	 * @return void
	 */
	public function writeDataFile($filename, $data, $append_data = '')
	{
		if ($append_data == 1) {
			file_put_contents(APPDIR . 'used/' . $filename, $data, FILE_APPEND);
			return;
		}

		file_put_contents(APPDIR . 'used/' . $filename, $data, LOCK_EX);
	}

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

	/**
	 * Make safe url for urlrewriting - link generating
	 * convert non-latin chars to latin and remove special
	 *
	 * @param string $str
	 * @return string
	 */
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

	    $str = str_replace($sr_cyrillic, $sr_latin, $str); // serbian cyrillic
	    $str = strtr($str, $tr); // other languages 
	    $str = preg_replace('/[^A-Za-z0-9_\-]/', '', $str); // replace special chars
	    $str = str_replace("---", "-", $str);
	    $str = strtolower(str_replace("--", "-", $str));

	    return $str;
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

	/**
	 * Replace lines in text
	 * 
	 * @param string $string
	 * @param string $replace
	 * @return string $string
	 */
	function replaceNewLines($string, $replace = '') {
	    // convert to unix new lines
	    $string = preg_replace("/\r\n/", "\n", $string); 
	    // remove extra new lines
	    $string = preg_replace("/\n/", $replace, $string);

	    return $string;
	}

	// file size
	function formatSize($file_size) {
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
	    $words = file_get_contents(APPDIR . "used/antiword.dat");
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
	    $image = preg_replace('#<img src="\.\./themes/images/smiles/(.*?)\.gif" alt="(.*?)>#', '', $image);
	    $image = preg_replace('#<img src="\.\./themes/images/smiles2/(.*?)\.gif" alt="(.*?)>#', '', $image);
	    $image = preg_replace('#<img src="(.*?)" alt="(.*?)>#', '', $image);
	    $image = preg_replace('/<img src="(.*?)" width="(.*?)" height="(.*?)>/', '', $image);
	    $image = preg_replace('/<img class="(.*?)" src="(.*?)" \/>/', '', $image);
	    $image = preg_replace('/<img class="(.*?)" src="(.*?)" alt="(.*?)>/', '', $image);

	    return $image;
	}

	function no_smiles($string) {
	    $string = preg_replace('#<img src="' . HOMEDIR . '/themes/images/smiles/(.*?)\.gif" alt="(.*?)>#', ':$1', $string);
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

	    /**
	     * Add target to links
	     */
        if ($prefix != 'mailto:') {
            $target = ' target="_blank"';
        } else {
            $target = '';
        }

	    while (strpos($r, "\n" . $prefix) !== false) {
	        list($r1, $r2) = explode("\n" . $prefix, $r, 2);
	        if (strpos($r2, " ") === false && strpos($r2, "<br />") === false) {
	            if (strpos($r2, ".") > 0 && strpos($r2, ".") < strlen($r2) && $this->badlink($r2, $prefix) != 1) {
	                $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a>';
	            } else {
	                $r = $r1 . $prefix . $r2;
	            } 
	        } else {
	            if (strpos($r2, " ") === false || (strpos($r2, " ") > strpos($r2, "<br />") && strpos($r2, "<br />") !== false)) {
	                list($r2, $r3) = explode("<br />", $r2, 2);
	                if ($this->badlink($r2, $prefix) != 1) {
	                    $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a><br>' . $r3;
	                } else {
	                    $r = $r1 . $prefix . $r2 . '<br />' . $r3;
	                } 
	            } else {
	                list($r2, $r3) = explode(" ", $r2, 2);
	                if (strpos($r2, ".") > 0 && strpos($r2, ".") < strlen($r2) && $this->badlink($r2, $prefix) != 1) {
	                    $r = $r1 . '<a href="' . $prefix . $r2 . '"' . $target . '>' . $prefix . $r2 . '</a> ' . $r3;
	                } else {
	                    $r = $r1 . $prefix . $r2 . ' ' . $r3;
	                } 
	            } 
	        } 
	    } 
	    return $r;
	}

	/**
	 * Parse bb code
	 *
	 * @param string $r
	 * @return string
	 */
	function getbbcode($r) {
		$r = str_replace("\r\n", '<br />', $r);
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
	    $r = $this->setlinks($r, "http://");
	    $r = $this->setlinks($r, "https://");
	    $r = $this->setlinks($r, "ftp://");
	    $r = $this->setlinks($r, "mailto:"); 
	    // //links
	    $r = trim($r);

	    return $r;
	}

	/**
	 * Check if text is HTML or plain text
	 * 
	 * @param string $text
	 * @return bool
	 */
	public function isTextHtml($text)
	{
	   $processed = htmlentities($text);
	   if($processed == $text) return false;
	   return true; 
	}

	/**
	 * Leave only latin letters and numbers
	 * 
	 * @param $string
	 * @return $string
	 */
	public function leaveLatinLettersNumbers($string)
	{
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	}

	/**
	 * Check input fields
	 *
	 * @param string $str
	 * @return string
	 */
	public function check($str)
	{
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

	/**
	 * Read file permissions CHMOD
	 * 
	 * @param string $file
	 * @return $file
	 */
	public function permissions($file)
	{
	    $file = decoct(fileperms("$file")) % 1000;
	    return $file;
	}

	// generate password
	public function generatePassword() {
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
	    $old_db = $this->getDataFile('flood.dat');
	    $new_db = fopen(APPDIR . "used/flood.dat", "w");
	    flock ($new_db, LOCK_EX);
	    $result = false;

	    foreach($old_db as $old_db_line) {
	        $old_db_arr = explode("|", $old_db_line);

	        if (($old_db_arr[0] + $this->configuration('floodTime')) > time()) {
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

	/**
	 * Get reCAPTCHA validation response
	 * 
	 * @param string $captcha
	 * @return bool
	 */
	public function recaptchaResponse($captcha)
	{
		// Return success if there is no secret key or disabled reCAPTCHA
		if (empty($this->configuration('recaptcha_secretkey'))) return array('success' => true);

	    // Post request to Google, check captcha code
	    $url =  'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->configuration('recaptcha_secretkey')) .  '&response=' . urlencode($captcha);
	    $response = file_get_contents($url);
	    return $responseKeys = json_decode($response, true);
	}

	// check URL
	public function validateUrl($URL) {
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

	// Show fatal error and stop script execution
	public function fatalError($error) {
	    echo '<div style="text-align: center;margin-top: 40px;">Error: ' . $error . '</div>';
	    exit;
	}

	/**
	 * Show success notification
	 * 
	 * @param string $error
	 * @return str
	 */
	public function showSuccess($success)
	{
	    return '<div class="alert alert-success" role="alert">' . $success . '</div>';
	}

	/**
	 * Show error notification to user
	 * 
	 * @param string $error
	 * @return str
	 */
	public function showDanger($error)
	{
	    return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
	}

	/**
	 * Show notification to user
	 * 
	 * @param string $notification
	 * @return str
	 */
	public function showNotification($notification)
	{
	    return '<div class="alert alert-info" role="alert">' . $notification . '</div>';
	}

	// add smiles
	function smiles($string) {
	    $dir = opendir(PUBLICDIR . "themes/images/smiles");
	    while ($file = readdir($dir)) {
	        if (preg_match ("/.gif/", $file)) {
	            $smfile[] = str_replace(".gif", "", $file);
	        }
	    }
	    closedir ($dir);
	    rsort($smfile);

	    foreach($smfile as $smval) {
	        $string = str_replace(":$smval:", '<img src="' . HOMEDIR . 'themes/images/smiles/' . $smval . '.gif" alt=":' . $smval . ':" />', $string);
	    } 

	    $string = str_replace(" ;)", ' <img src="' . HOMEDIR . 'themes/images/smiles/).gif" alt=";)" />', $string);
	    $string = str_replace(" :)", ' <img src="' . HOMEDIR . 'themes/images/smiles/).gif" alt=":)" />', $string);
	    $string = str_replace(" :(", ' <img src="' . HOMEDIR . 'themes/images/smiles/(.gif" alt=":(" />', $string);
	    $string = str_replace(" :D", ' <img src="' . HOMEDIR . 'themes/images/smiles/D.gif" alt=":D" />', $string);
	    $string = str_replace(" :E", ' <img src="' . HOMEDIR . 'themes/images/smiles/E.gif" alt=":E" />', $string);
	    $string = str_replace(" :P", ' <img src="' . HOMEDIR . 'themes/images/smiles/P.gif" alt=":P" />', $string);

	   return $string;
	}

	/**
	 * Show number of visitors online
	 *
	 * @return string
	 */
	public function showOnline()
	{
        $online = $this->db->count_row('online');
        $registered = $this->db->count_row('online', "user > 0");

		$online = '<p class="site_online_users"><a href="/pages/online">Online: ' . $registered . ' / ' . $online . '</a></p>';

		return $online;
	}

	/**
	 * Show counter
	 *
	 * @return string
	 */
	public function showCounter()
	{
        $counts = $this->db->getData('counter');

        $current_day = $counts['day'];
        $clicks_today = $counts['clicks_today'];
        $total_clicks = $counts['clicks_total'];
        $visits_today = $counts['visits_today']; // visits today
    	$total_visits = $counts['visits_total']; // total visits

		$counter_configuration = $this->configuration('showCounter');

	    if (!empty($counter_configuration) && $counter_configuration != 6) {
	        if ($counter_configuration == 1) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $visits_today . ' | ' . $total_visits . '</a>';

	        if ($counter_configuration == 2) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $clicks_today . ' | ' . $total_clicks . '</a>';

	        if ($counter_configuration == 3) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $visits_today . ' | ' . $clicks_today . '</a>';

	        if ($counter_configuration == 4) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $total_visits . ' | ' . $total_clicks . '</a>';

	        if ($counter_configuration == 5) $info = '<a href="' . HOMEDIR . 'pages/statistics"><img src="' . HOMEDIR . 'gallery/count.php" alt=""></a>';

	        return '<p class="site_counter">' . $info . '</p>';
	    }
	}

	// show page generation time
	public function showPageGenTime()
	{
	    if ($this->configuration('pageGenTime') == 1) {
	        $end_time = microtime(true);
	        $gen_time = $end_time - START_TIME;
	        $pagegen = '<p class="site_pagegen_time">{@localization[pggen]}}' . ' ' . round($gen_time, 4) . ' s.</p>';

	        return $pagegen;
	    }
	}

	/**
	 * Get group members number
	 *
	 * @param string $group_name
	 * @return int
	 */
	public function count_group_members($group_name)
	{
		return $this->go('db')->count_row('group_members', "group_name = '{$group_name}'");
	}

	// get prefered language
	function getDefaultLanguage() {
	    if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
	        return $this->parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	    else
	        return $this->parseDefaultLanguage(null);
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

	<a href=# onClick="javascript:tag('[url=', ']url name here[/url]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/editor/a.gif" alt="" /></a> 
	<a href=# onClick="javascript:tag('[img]', '[/img]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/editor/img.gif" alt="" /></a> 
	<a href=# onClick="javascript:tag('[b]', '[/b]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/editor/b.gif" alt="" /></a> 
	<a href=# onClick="javascript:tag('[i]', '[/i]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/editor/i.gif" alt="" /></a> 
	<a href=# onClick="javascript:tag('[u]', '[/u]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/editor/u.gif" alt="" /></a> 
	<a href=# onClick="javascript:tag('[youtube]', '[/youtube]'); return false;"><img src="<?php echo HOMEDIR; ?>themes/images/socialmedia/youtube.png" width="16" height="16" alt="" /></a> 
	<?php
	}

	/**
	 * Redirection
	 *
	 * @param string $url
	 * @return mixed
	 */
	public function redirection($url) {
		// Cannot redirect if headers are already sent
	    if (!headers_sent()) {
			header('HTTP/1.1 301 Moved Permanently');
	        header('Location: ' . $url);
			// Protects from code being executed after redirect request
	        exit;
	    } else {
	        throw new Exception('Cannot redirect, headers already sent');
	    }
	}

	// get transfer protocol https or http
	public function transferProtocol() {
	    if (empty($this->configuration('transferProtocol')) || $this->configuration('transferProtocol') == 'auto') {
	        if (!empty($_SERVER['HTTPS'])) {
	            $connectionProtocol = 'https://';
	        } else {
	            $connectionProtocol = 'http://';
	        }
	    } elseif ($this->configuration('transferProtocol') == 'HTTPS') {
	        $connectionProtocol = 'https://';
	    } else {
	        $connectionProtocol = 'http://';
	    }

	    return $connectionProtocol;
	}

	/**
	 * Check if we use SSL
	 * 
	 * @return bool
	 */
	public function secureConection()
	{
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') return true;

		return false;
	}

	/**
	 * Explode url into array
	 * 
	 * @return array $url
	 */
    public function paramsFromUrl()
    {
        if (isset($_GET['url'])) {
            $url = $this->check(rtrim($_GET['url'], '/'));
            $url = explode('/', $url);
            return $url;
        }
    }

	/**
	 * Clean request URI from unwanted data in url
	 *
	 * @param string $uri
	 * @return string $clean_requri
	 */
	public function cleanRequestUri($uri)
	{
		$clean_requri = explode('&fb_action_ids', $uri)[0]; // facebook
		$clean_requri = explode('?fb_action_ids', $clean_requri)[0]; // facebook
		$clean_requri = explode('?isset', $clean_requri)[0];

		return $clean_requri;
	}

	/**
	 * Current connection that we use to open site
	 * 
	 * @return string
	 */
	public function currentConnection()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
	}

	// complete dynamic website address
	public function websiteHomeAddress()
	{
	    return $this->transferProtocol() . $_SERVER['HTTP_HOST'];
	}

	function compress_output_gzip($output)
	{
	    return gzcompress($output, 3);
	} 

	function compress_output_deflate($output)
	{
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

	/**
	 * Home link
	 *
	 * @param string $before
	 * @param string $after
	 * @return string
	 */
	public function homelink($before = '', $after = '')
	{
		return $before . '<a href="' . HOMEDIR . '" class="btn btn-primary homepage">{@localization[home]}}</a>' . $after;
	}

	/**
	 * Sitelink
	 *
	 * @param string $href
	 * @param string $link_name
	 * @param string $before
	 * @param string $after
	 * @return string
	 */
	public function sitelink($href, $link_name, $before = '', $after = '')
	{
		return $before . '<a href="' . $href . '" class="btn btn-primary sitelink">' . $link_name . '</a>' . $after;
	}

	/**
	 * Return POST and GET variables or single variable
	 *
	 * @param string $return_key
	 * @param bool $unchainged
	 * @return array|string
	 */
	public function postAndGet($return_key = '', $unchainged = '')
	{
		$arrays = array_merge($_POST, $_GET);

		// Handle page number
		if (!isset($arrays['page']) || empty($arrays['page']) || $arrays['page'] < 1) $arrays['page'] = 1;

		// Return unfiltered data when requested
		if (!empty($return_key) && $unchainged == true && isset($arrays[$return_key])) return $arrays[$return_key];

		// Return filtered (checked) requested key
		if (!empty($return_key) && isset($arrays[$return_key])) return $this->check($arrays[$return_key]);

		// Check all fields with Vavok:check()
		$return = array();
		foreach ($arrays as $key => $value) {
			$return[$key] = $this->check($value);
		}

		// Handle case when return key is not set
		if (!empty($return_key) && !isset($arrays[$return_key])) return '';

		return $return;
	}

	/**
	 * Return website domain
	 *
	 * @return string
	 */
	public function cleanDomain()
	{
		return str_replace('www.', '', $_SERVER['SERVER_NAME']);
	}

	/**
	 * Users device, computer or phone
	 */
	public function userDevice() {
    	return BrowserDetection::userDevice();
	}

	/**
	 * Clean unconfirmed registrations
	 * 
	 * @param object $user_model
	 * @return void
	 */
	public function cleanRegistrations($user_model)
	{
		// Hours user have to confirm registration
		$confirmationHours = 24;
		$confirmationTime = $confirmationHours * 3600;

		foreach ($this->db->query("SELECT regdate, uid FROM vavok_profil WHERE regche = '1'") as $userCheck) {
			// Delete user if registration is not confirmed within $confirmationHours
			if (($userCheck['regdate'] + $confirmationTime) < time()) $user_model->delete_user($userCheck['uid']);
		}
	}

	/**
	 * Head tags for all pages
	 *
	 * @param string $page_data
	 * @return string
	 */
	public function pageHeadMetatags($page_data)
	{
		// Page title
		if (isset($page_data['tname'])) $title = $page_data['tname'];

		// Tags for all pages at the website
		$tags = file_get_contents(APPDIR . 'used/headmeta.dat');

		// Tags for this page only
		if (isset($page_data['headt'])) $tags .= $page_data['headt'];

		// Add missing open graph tags
		if (!strstr($tags, 'og:type')) $tags .= "\n" . '<meta property="og:type" content="website" />';
		if (!strstr($tags, 'og:title') && isset($title) && !empty($title) && $title != $this->configuration('title')) $tags .= "\n" . '<meta property="og:title" content="' . $title . '" />';

		return $tags;
	}
}