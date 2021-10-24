<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

class Manageip {
    private $vavok;

    public function __construct()
    {
        global $vavok;

        $this->vavok = $vavok;

        if (empty(REQUEST_URI)) {
            $config_requri = "index.php";
        } else { $config_requri = REQUEST_URI; }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $http_referer = urldecode($_SERVER['HTTP_REFERER']);
        } else {
            $http_referer = "No referer";
        }

        if (isset($this->vavok->go('users')->user_id)) {
            $username = $this->vavok->go('users')->user_id;
        } else {
            $username = 'Guest';
        }

        $ip = $this->vavok->go('users')->find_ip();
        $hostname = gethostbyaddr($ip);

        if ($opendir = opendir(BASEDIR . "used/datados")) {
            while (false !== ($doslog = readdir($opendir))) {
                if ($doslog != "." and $doslog != "..") {
                    $file_array_filemtime = @filemtime(BASEDIR . "used/datados/$doslog");

                    if ($file_array_filemtime < (time() - 60)) {
                        if (file_exists(BASEDIR . "used/datados/$doslog")) {
                            @unlink(BASEDIR . "used/datados/$doslog");
                        }
                    }
                }
            }

            $logfiles = BASEDIR . "used/datados/" . $ip . ".dat";

            if (file_exists($logfiles) && !empty($logfiles)) {
                $file_dos_time = file($logfiles);
                $file_dos_str = explode("|", $file_dos_time[0]);

                if ($file_dos_str[1] < ($this->vavok->get_configuration('siteTime')-60)) {
                    @unlink($logfiles);
                }
            }

            $write = '|' . $this->vavok->get_configuration('siteTime') . '|Time: ' . date("Y-m-d / H:i:s", $this->vavok->get_configuration('siteTime')) . '|Browser: ' . $this->vavok->go('users')->user_browser() . '|Referer: ' . $http_referer . '|URL: ' . $config_requri . '|User: ' . $username . '|';
            $fp = fopen($logfiles, "a+");
            flock ($fp, LOCK_EX);
            fputs($fp, "$write\r\n");
            flock ($fp, LOCK_UN);
            fclose($fp);

            if (count(file($logfiles)) > $this->vavok->get_configuration('dosLimit') && $this->vavok->get_configuration('dosLimit') > 0) {
                unlink($logfiles);

                $banlines = $this->vavok->get_data_file('ban.dat');
                $banarray = '';

                foreach($banlines as $banvalue) {
                    $bancell = explode("|", $banvalue);
                    $banarray[] = $bancell[1];
                } 

                if (!in_array($ip, $banarray)) {
                    $this->vavok->write_data_file('ban.dat', "|$ip|" . PHP_EOL, 1);

                    $logdat = BASEDIR . "used/datalog/ban.dat";
                    $hostname = gethostbyaddr($ip);

                    $write = ':|:Blocked access for IP:|:' . $_SERVER['PHP_SELF'] . REQUEST_URI . ':|:' . time() . ':|:' . $ip . ':|:' . $hostname . ':|:' . $this->vavok->go('users')->user_browser() . ':|:' . $http_referer . ':|:' . $username . ':|:';

                    $this->vavok->write_data_file('datalog/ban.dat', $write . PHP_EOL, 1);

                    $file = file($logdat);
                    $i = count($file);
                    if ($i >= $this->vavok->get_configuration('maxLogData')) {
                        $fp = fopen($logdat, "w");
                        flock ($fp, LOCK_EX);
                        unset($file[0], $file[1]);
                        fputs($fp, implode("", $file));
                        flock ($fp, LOCK_UN);
                        fclose($fp);
                    }
                }
        	}

        	// ban
        	$old_ips = $vavok->get_data_file('ban.dat');
        	foreach($old_ips as $old_ip_line) {
        	    $ip_arr = explode("|", $old_ip_line);

        	    $ip_check_matches = 0;
        	    $db_ip_split = explode(".", $ip_arr[1]);
        	    $this_ip_split = explode(".", $ip);

        	    for($i_i = 0;$i_i < 4;$i_i++) {
        	        if ($this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*') {
        	            $ip_check_matches += 1;
        	        } 
        	    } 

        	    if ($ip_check_matches == 4) {
        	        if ($this->vavok->go('users')->is_administrator() == false) {
        	            header ("Location: " . BASEDIR . "pages/banip.php");
        	            exit;
        	        }
        	    }
        	}
        }
    }
}
?>