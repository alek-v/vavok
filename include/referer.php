<?php
// modified: 19.3.2016. 16:26:06
// (c) vavok.net
if (!empty($_SERVER['HTTP_REFERER'])) { $ref_from = $_SERVER['HTTP_REFERER']; }

if (!empty($ref_from) && preg_match("/http/", $ref_from)) {
    $ref_from = str_replace("http://", "", $ref_from);
    $ref_from = str_replace("https://", "", $ref_from);
    $ref_from = strtok($ref_from, '/');
    $ref_from = strtok($ref_from, '?');

    $ref_from = check($ref_from);
    // do not add my site to referer list
    if ($ref_from != 'm.' . $config["homeBase"] && $ref_from != 'www.' . $config["homeBase"] && $ref_from != $config["homeBase"]) {
        $lines = file(BASEDIR . "used/referer.dat");
        $count = count($lines);
        for ($rb = 0; $rb < $count; $rb++) {
            $dt = explode("|", $lines[$rb]);
            if ($dt[0] == $ref_from) {
                $rlinn = (int)$rb;
                $ref_count = $dt[3];
            } 
        } 

            if (isset($rlinn) && $rlinn > 0) {
                $ref_count = $ref_count + 1;
                $time = time();
                $t = $ref_from . '|' . $time . '|' . $ip . '|' . $ref_count . '|';

                $reffile = file(BASEDIR . "used/referer.dat");
                $fp = fopen(BASEDIR . "used/referer.dat", "a+");
                flock ($fp, LOCK_EX);
                ftruncate ($fp, 0);
                for ($ri = 0;$ri < sizeof($reffile);$ri++) {
                    if ($rlinn != $ri) {
                        fputs($fp, $reffile[$ri]);
                    } else {
                        fputs($fp, "$t\r\n");
                    } 
                } 
                fflush ($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);
            } else {
                $time = time();

                $t = $ref_from . '|' . $time . '|' . $ip . '|1|';
                $fp = fopen(BASEDIR . "used/referer.dat", "a+");
                flock ($fp, LOCK_EX);
                fputs($fp, "$t\r\n");
                fflush ($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);

                $reffile = file(BASEDIR . "used/referer.dat");
                $ri = count($reffile);
                if ($ri >= $config["refererLog"]) {
                    $fp = fopen(BASEDIR . "used/referer.dat", "w");
                    flock ($fp, LOCK_EX);
                    unset($reffile[0]);
                    unset($reffile[1]);
                    fputs($fp, implode("", $reffile));
                    flock ($fp, LOCK_UN);
                    fclose($fp);
                } 
            }  
    } 
} 

?>