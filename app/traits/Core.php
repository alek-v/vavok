<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Traits;

trait Core {
    /**
     * Return correct date
     *
     * @param int $timestamp
     * @param string $format
     * @param string $my_zone
     * @param bool $show_zone_info
     * @return string
     */
    public function correctDate(int $timestamp = 0, string $format = 'd.m.Y.', string $my_zone = '', bool $show_zone_info = false): string
    {
        $timezone = $this->configuration->getValue('timezone');

        if ($timestamp == 0) $timestamp = time();
        if (empty($format)) $format = 'd.m.y. / H:i';
        if (!empty($my_zone)) $timezone = $my_zone;

        if (stristr($timezone, '-')) {
            $clock = str_replace('-', '', $timezone);
            $clock = floatval($clock);
            $seconds = $clock * 3600; // number of seconds
            $return_date = date($format, $timestamp - ($seconds)); // return date
        } else {
            $clock = str_replace('+', '', $timezone);
            $clock = floatval($clock);
            $seconds = $clock * 3600; // number of seconds
            $return_date = date($format, $timestamp + ($seconds)); // return date
        }

        $zone_info = $show_zone_info ? ' UTC ' . $timezone : '';

        return $return_date . $zone_info;
    }

    /**
     * Format and show the time
     *
     * @param int $time
     * @return string
     */
    public function makeTime(int $time): string
    {
        if ($time < 3600) {
            return sprintf("%02d:%02d", (int)($time / 60) % 60, $time % 60);
        } else {
            return sprintf("%02d:%02d:%02d", (int)($time / 3600) % 24, (int)($time / 60) % 60, $time % 60);
        }
    }

    /**
     * Format time into days and minutes
     *
     * @param int $file_time
     * @return string
     */
    public function formatTime(int $file_time): string
    {
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

    /**
     * Make safe url for url rewriting - link generating
     * Convert non-latin chars to latin and remove special chars
     *
     * @param string $str
     * @return string
     */
    function trans(string $str): string
    {
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

        // Other languages
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y',
        'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
        'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',

        // Special chars
        " " => "-", "." => "-", " / " => "-", "/" => "", "," => "", ":" => "", "'" => "", "\'" => "", "’" => "", "`" => "", "„" => "", "“" => "",
        ";" => "", "—"=>"", "<"=>"", ">"=>"",
        "”" => "", "´" => "", "~" => "", "&quot;" => "", "&#147;" => "", "&" => "and",

        // Money
        "£" => "pounds", "$" => "dollars", "€" => "euros"
        );

        $str = str_replace($sr_cyrillic, $sr_latin, $str); // serbian cyrillic
        $str = strtr($str, $tr); // other languages 
        $str = preg_replace('/[^A-Za-z0-9_\-]/', '', $str); // replace special chars
        $str = str_replace("---", "-", $str);
        $str = strtolower(str_replace("--", "-", $str));

        return $str;
    }

    /**
     * Remove unwanted characters from Unicode URL-s
     *
     * @param string $text
     * @return string
     */
    function translateUnicode(string $text): string
    {
        // Special chars
        $tr = array(
        " " => "-", "." => "-", " / " => "-", "/" => "", "," => "", ":" => "", "'" => "", "\'" => "", "’" => "", "`" => "", "„" => "", "“" => "",
        ";" => "", "—"=>"", "<"=>"", ">"=>"",
        "”" => "", "´" => "", "~" => "", "&quot;" => "", "&#147;" => ""
        );

        return mb_strtolower(strtr($text, $tr));
    }

    /**
     * Replace lines in text
     * 
     * @param string $string
     * @param string $replace
     * @return string
     */
    function replaceNewLines(string $string, string $replace = ''): string
    {
        // convert to unix new lines
        $string = preg_replace("/\r\n/", "\n", $string); 
        // remove extra new lines
        $string = preg_replace("/\n/", $replace, $string);

        return $string;
    }

    /**
     * Format file size
     *
     * @param int $file_size
     * @return string
     */
    function formatSize(int $file_size): string
    {
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

    /**
     * Bad-word and anti-spam filter
     *
     * @param string $string
     * @return string
     */
    function antiword(string $string): string
    {
        $words = file_get_contents(STORAGEDIR . "bad_words.dat");
        $wordlist = explode("|", $words);

        foreach($wordlist as $value) {
            if (!empty($value)) {
                $string = preg_replace("/$value/i", "***", $string);
            }
        }
        return $string;
    }

    /**
     * Remove image links from the string
     *
     * @param string $image
     * @return string
     */
    function eraseImage(string $image): string
    {
        $image = preg_replace('#<img src="\.\./themes/images/smiles/(.*?)\.gif" alt="(.*?)>#', '', $image);
        $image = preg_replace('#<img src="\.\./themes/images/smiles2/(.*?)\.gif" alt="(.*?)>#', '', $image);
        $image = preg_replace('#<img src="(.*?)" alt="(.*?)>#', '', $image);
        $image = preg_replace('/<img src="(.*?)" width="(.*?)" height="(.*?)>/', '', $image);
        $image = preg_replace('/<img class="(.*?)" src="(.*?)" \/>/', '', $image);
        $image = preg_replace('/<img class="(.*?)" src="(.*?)" alt="(.*?)>/', '', $image);

        return $image;
    }

    /**
     * Method for getbbcode()
     *
     * @param string $link
     * @param string $prefix
     * @return int|null
     */
    private function badlink(string $link, string $prefix): ?int
    {
        if ($prefix == "mailto:") {
            if (strpos($link, "@") === false || strpos($link, ".", (strpos($link, "@") + 2)) === false || substr_count($link, "@") > 1 || strpos($link, "@") == 0) {
                return 1;
            } 
        }
        if (strpos($link, ".") == 0 || strpos($link, ".") == strlen($link) || (strpos($link, "/") < strpos($link, ".") && strpos($link, "/") !== false)) {
            return 1;
        }

        return null;
    }

    private function setlinks(string $r, string $prefix): string
    {
        $target = '';
        if (substr($r, 0, strlen($prefix)) == $prefix) {
            $r = "\n" . $r;
        }
        $r = str_replace("<br />" . $prefix, "<br />\n" . $prefix, $r);
        $r = str_replace(" " . $prefix, " \n" . $prefix, $r);

        // Add target to links
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
    function getbbcode(string $r): string
    {
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
        // [url]
        // default url link setting
        $r = $this->setlinks($r, "http://");
        $r = $this->setlinks($r, "https://");
        $r = $this->setlinks($r, "ftp://");
        $r = $this->setlinks($r, "mailto:"); 
        //links
        $r = trim($r);

        return $r;
    }

    /**
     * Current opened URL cleaned for Facebook share, Twitter share, etc, to prevent duplicated URL's
     * 
     * @return string
     */
    public function cleanPageUrl(): string
    {
        // Cleanup request
        $r = preg_replace('/&page=(\d+)/', '', CLEAN_REQUEST_URI);
        $r = preg_replace('/page=(\d+)/', '', $r);
        $r = str_replace('&page=last', '', $r);
        $r = str_replace('page=last', '', $r);

        // Show language directory without slash
        $r = str_replace('/en/', '/en', $r);
        $r = str_replace('/sr/', '/sr', $r);

        // Remove index.php from urls to remove double content
        $r = str_replace('/index.php', '', $r);

        if (empty($website)) $website = $this->websiteHomeAddress();

        // Return URL
        return $website . $r;
    }

    /**
     * Check if text is HTML or plain text
     * 
     * @param string $text
     * @return bool
     */
    public function isTextHtml(string $text): bool
    {
       $processed = htmlentities($text);
       if ($processed == $text) return false;
       return true; 
    }

    /**
     * Leave only latin letters and numbers
     * 
     * @param string $string
     * @return string
     */
    public function leaveLatinLettersNumbers(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * Check input fields
     *
     * @param string $str
     * @return string
     */
    public function securityCheck(string $str): string
    {
        return htmlspecialchars($str);
    }

    /**
     * Deprecated method 23.5.2023.
     * 
     * Check input fields
     *
     * @param string $str
     * @return string
     */
    public function check(string $str): string
    {
        return $this->securityCheck($str);
    }

    /**
     * Generate password
     * 
     * @return string
     */
    public function generatePassword(): string
    {
        $length = rand(10, 12);
        $salt = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789";
        $len = strlen($salt);
        $makepass = '';
        mt_srand(crc32(microtime())); 

        for ($i = 0; $i < $length; $i++)
        $makepass .= $salt[mt_rand(0, $len - 1)];

        return $makepass;
    }

    /**
     * Get reCAPTCHA validation response
     * 
     * @param string $captcha
     * @return array
     */
    public function recaptchaResponse(string $captcha): array
    {
        // Return success if there is no secret key or disabled reCAPTCHA
        if (empty($this->configuration->getValue('recaptcha_secret_key'))) return array('success' => true);

        // Post request to Google, check captcha code
        $url =  'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->configuration->getValue('recaptcha_secret_key')) .  '&response=' . urlencode($captcha);
        $response = file_get_contents($url);

        return $responseKeys = json_decode($response, true);
    }

    /**
     * Show smiles in message
     * 
     * @param string $string
     * @return string
     */
    function smiles(string $string): string
    {
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

        $string = str_replace(";)", ' <img src="' . HOMEDIR . 'themes/images/smiles/;).gif" alt=";)" />', $string);
        $string = str_replace(":)", ' <img src="' . HOMEDIR . 'themes/images/smiles/).gif" alt=":)" />', $string);
        $string = str_replace(":(", ' <img src="' . HOMEDIR . 'themes/images/smiles/(.gif" alt=":(" />', $string);
        $string = str_replace(":D", ' <img src="' . HOMEDIR . 'themes/images/smiles/D.gif" alt=":D" />', $string);
        $string = str_replace(":P", ' <img src="' . HOMEDIR . 'themes/images/smiles/P.gif" alt=":P" />', $string);

       return $string;
    }

    /**
     * Show number of visitors online
     *
     * @return string
     */
    public function showOnline(): string
    {
        $online = $this->db->countRow('online');
        $registered = $this->db->countRow('online', "user > 0");

        $online = '<p class="site-online-users"><a href="/pages/online">Online: ' . $registered . ' / ' . $online . '</a></p>';

        return $online;
    }

    /**
     * Show counter
     *
     * @return string
     */
    public function showCounter(): string
    {
        $counts = $this->db->selectData('counter');

        $clicks_today = $counts['clicks_today'];
        $total_clicks = $counts['clicks_total'];
        $visits_today = $counts['visits_today'];
        $total_visits = $counts['visits_total'];

        $counter_configuration = $this->configuration->getValue('show_counter');

        if (!empty($counter_configuration) && $counter_configuration != 6) {
            if ($counter_configuration == 1) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $visits_today . ' | ' . $total_visits . '</a>';

            if ($counter_configuration == 2) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $clicks_today . ' | ' . $total_clicks . '</a>';

            if ($counter_configuration == 3) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $visits_today . ' | ' . $clicks_today . '</a>';

            if ($counter_configuration == 4) $info = '<a href="' . HOMEDIR . 'pages/statistics">' . $total_visits . ' | ' . $total_clicks . '</a>';

            return '<p class="site-counter">' . $info . '</p>';
        }

        return '';
    }

    /**
     * Show page generation time
     * 
     * @return string
     */
    public function showPageGenTime(): string
    {
        if ($this->configuration->getValue('page_generation_time') == 1) {
            $end_time = microtime(true);
            $gen_time = $end_time - START_TIME;
            return '<p class="site-generate-time">{@localization[pggen]}}' . ' ' . round($gen_time, 4) . ' s.</p>';
        }

        return '';
    }

    /**
     * Redirection
     *
     * @param string $url
     * @return void
     * @throws \Exception;
     */
    public function redirection(string $url): void
    {
        // Cannot redirect if headers are already sent
        if (!headers_sent()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $url);
            // Protects from code being executed after redirect request
            exit;
        } else {
            throw new \Exception('Cannot redirect, headers already sent');
        }
    }

    /**
     * Return preferred transfer protocol from site settings (https or http)
     * 
     * @return string https://|http://
     */
    public function transferProtocol(): string
    {
        if (empty($this->configuration->getValue('transfer_protocol')) || $this->configuration->getValue('transfer_protocol') == 'auto') {
            if (!empty($_SERVER['HTTPS'])) {
                $connectionProtocol = 'https://';
            } else {
                $connectionProtocol = 'http://';
            }
        } elseif ($this->configuration->getValue('transfer_protocol') == 'HTTPS') {
            $connectionProtocol = 'https://';
        } else {
            $connectionProtocol = 'http://';
        }

        return $connectionProtocol;
    }

    /**
     * Current connection that we use to open site
     * 
     * @return string
     */
    public function currentConnection(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
    }

    /**
     * Explode url into array
     * 
     * @return array
     */
    public function paramsFromUrl(): array
    {
        if (isset($_GET['url'])) {
            $url = $this->check(rtrim($_GET['url'], '/'));
            $url = explode('/', $url);
            return $url;
        }

        return array();
    }

    /**
     * Clean request URI from unwanted data in url
     *
     * @param string $uri
     * @return string $clean_requri
     */
    public function cleanRequestUri(string $uri): string
    {
        $clean_requri = explode('&fb_action_ids', $uri)[0]; // facebook
        $clean_requri = explode('?fb_action_ids', $clean_requri)[0]; // facebook
        $clean_requri = explode('?isset', $clean_requri)[0];

        return $clean_requri;
    }

    /**
     * Site address with connection protocol that is used for current connection
     * 
     * @return string
     */
    public function websiteHomeAddress(): string
    {
        return $this->transferProtocol() . $_SERVER['HTTP_HOST'];
    }

    /**
     * Generate meta tags description and keywords
     *
     * @param string $story
     * @return array
     */
    function create_metatags(string $story): array
    {
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
            if (strlen($word) > 4) $newarr[] = $word;
        }

        $arr = array_count_values($newarr);
        arsort($arr);

        $arr = array_keys($arr);

        $total = count($arr);

        $offset = 0;

        $arr = array_slice($arr, $offset, $keyword_count);

        $headers['keywords'] = implode(", ", $arr);

        return $headers;
    }

    /**
     * Bot or spider name
     * 
     * @return string|bool
     */
    function detectBot(): string|bool
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        // List of the bot user agents and names
        $bot_list = array(
            'Yandex' => 'Yandex',
            'Slurp' => 'Yahoo! Slurp',
            'Yahoo' => 'Yahoo!',
            'mediapartners-google' => 'Mediapartners-Google',
            'Googlebot-Image' => 'Googlebot-Image',
            'google' => 'Googlebot',
            'lycos' => 'Lycos',
            'SurveyBot' => 'SurveyBot',
            'bingbot' => 'Bing',
            'msnbot' => 'msnbot',
            'Baiduspider' => 'Baidu Spider',
            'Sosospider' => 'Soso Spider',
            'ia_archiver' => 'ia_archiver',
            'facebookexternalhit' => 'Facebook Bot',
            'applebot' => 'Apple Bot',
            'SemrushBot' => 'SemrushBot',
            'SiteAuditBot' => 'Semrush SiteAuditBot'
        );

        // Bot user agents to search
        $bot_names = array_keys($bot_list);

        // Return name of the bot if user agent is found
        foreach($bot_names as $bot) {
            if (str_contains($user_agent, $bot) && !empty($user_agent)) return $bot_list[$bot];
        }

        return false;
    }

    /**
     * Home link
     *
     * @param string $before
     * @param string $after
     * @return string
     */
    public function homelink(string $before = '', string $after = ''): string
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
    public function sitelink(string $href, string $link_name, string $before = '', string $after = ''): string
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
    public function postAndGet(string $return_key = '', bool $unchainged = false): array|string
    {
        $arrays = array_merge($_POST, $_GET);

        // Handle page number
        if (!isset($arrays['page']) || empty($arrays['page']) || $arrays['page'] < 1) {
            $arrays['page'] = 1;
        }

        // Return unfiltered data when requested
        if (!empty($return_key) && $unchainged == true && isset($arrays[$return_key])) {
            return $arrays[$return_key];
        }

        // Return filtered (checked) requested key
        if (!empty($return_key) && isset($arrays[$return_key])) {
            return $arrays[$return_key];
        }

        // Check all fields with a check() method
        $return = array();
        foreach ($arrays as $key => $value) {
            $return[$key] = $value;
        }

        // Handle case when return key is not set
        if (!empty($return_key) && !isset($arrays[$return_key])) {
            return '';
        }

        return $return;
    }

    /**
     * Return website domain
     *
     * @return string
     */
    public function cleanDomain(): string
    {
        return str_replace('www.', '', $_SERVER['SERVER_NAME']);
    }

    /**
     * User's device, computer or a phone
     * 
     * @return string
     */
    public function userDevice(): string
    {
        return \App\Classes\BrowserDetection::userDevice();
    }

    /**
     * Clean unconfirmed registrations
     * 
     * @param object $user_model
     * @return void
     */
    public function cleanRegistrations($user_model): void
    {
        // Hours user have to confirm registration
        $confirmationHours = 24;
        $confirmationTime = $confirmationHours * 3600;

        foreach ($this->db->query("SELECT registration_date, uid FROM vavok_profile WHERE registration_activated = '1'") as $userCheck) {
            // Delete user if registration is not confirmed within $confirmationHours
            if (($userCheck['registration_date'] + $confirmationTime) < time()) $user_model->deleteUser($userCheck['uid']);
        }
    }

    /**
     * Head tags for all pages
     *
     * @param array $page_data
     * @return string
     */
    public function pageHeadMetatags(array $page_data): string
    {
        // Page title
        if (isset($page_data['page_title'])) {
            $title = $page_data['page_title'];
        }

        // Tags for all pages at the website
        $tags = file_get_contents(STORAGEDIR . 'header_meta_tags.dat');

        // Tags for this page only
        if (isset($page_data['head_tags'])) {
            $tags .= $page_data['head_tags'];
        }

        // Add missing open graph tags
        if (!strstr($tags, 'og:type')) {
            $tags .= "\n" . '<meta property="og:type" content="website" />';
        }
        if (!strstr($tags, 'og:title') && isset($title) && !empty($title) && $title != $this->configuration->getValue('title')) {
            $tags .= "\n" . '<meta property="og:title" content="' . $title . '" />';
        }

        return $tags;
    }

    /**
     * Return cyrillic and latin letters in array
     * 
     * @return array
     */
    private function cyrillicLatinLetters(): array
    {
        $latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
        $cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");

        return array('latin' => $latin, 'cyrillic' => $cyrillic);
    }

    /**
     * Translate cyrillic script to latin
     * 
     * @param string $str
     * @return string
     */
    public function cyrillicToLatin(string $str): string
    {
        return str_replace($this->cyrillicLatinLetters()['cyrillic'], $this->cyrillicLatinLetters()['latin'], $str);
    }

    /**
     * Translate latin script to cyrillic
     * 
     * @param string $str
     * @return string
     */
    public function latinToCyrillic(string $str): string
    {
        return str_replace($this->cyrillicLatinLetters()['latin'], $this->cyrillicLatinLetters()['cyrillic'], $str);
    }

    /**
     * Return error 404
     * 
     * @return array
     */
    public function handleNoPageError(): array
    {
        header("HTTP/1.1 404 Not Found");

        return [
            'page_title' => 'Error 404',
            'content' => '{@localization[page_or_file_not_found]}}'
        ];    
    }

    /**
     * Count nested arrays
     * @param array $array
     * @return int
     */
    public function countNestedArrays(array $array) 
    {
        $count = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $count++;
            }
        }
        return $count;
    }
}