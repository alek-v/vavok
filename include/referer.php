<?php
// (c) vavok.net
// modified: 01.08.2020. 0:20:12

if (!empty($_SERVER['HTTP_REFERER'])) { $ref_from = $_SERVER['HTTP_REFERER']; }

if (!empty($ref_from) && preg_match("/http/", $ref_from)) {
    $ref_from = str_replace("http://", "", $ref_from);
    $ref_from = str_replace("https://", "", $ref_from);
    $ref_from = strtok($ref_from, '/');
    $ref_from = strtok($ref_from, '?');

    $ref_from = $vavok->check($ref_from);
    // do not add my site to referer list
    if ($ref_from != 'm.' . $vavok->get_configuration('homeBase') && $ref_from != 'www.' . $vavok->get_configuration('homeBase') && $ref_from != $vavok->get_configuration('homeBase')) {
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

                $t = $ref_from . '|' . time() . '|' . $users->find_ip() . '|' . $ref_count . '|';

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
                $t = $ref_from . '|' . time() . '|' . $users->find_ip() . '|1|';
                $fp = fopen(BASEDIR . "used/referer.dat", "a+");
                flock ($fp, LOCK_EX);
                fputs($fp, "$t\r\n");
                fflush ($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);

                $reffile = file(BASEDIR . "used/referer.dat");
                $ri = count($reffile);
                if ($ri >= $vavok->get_configuration('refererLog')) {
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