<?php 
// (c) vavok.net
require_once"../include/strtup.php";

$action = isset($_GET['action']) ? check($_GET['action']) : '';

function prev_dir($string) {
    $d1 = strrpos($string, "/");
    $d2 = substr($string, $d1, 999);
    $string = str_replace($d2, "", $string);

    return $string;
} 

if ($users->is_reg()) {
    if ($accessr == 101) {
        include_once"../themes/$config_themes/index.php";
        if (isset($_GET['isset'])) {
				$isset = check($_GET['isset']);
				echo '<div align="center"><b><font color="#FF0000">';
				echo get_isset();
				echo '</font></b></div>';
				}

        switch ($action) {
            default:

                echo '<img src="../images/img/menu.gif" alt=""> ' . $lang_admin['checksys'] . '<hr>';

                if (isset($_GET['did'])) {
                    $did = check($_GET['did']);
                } else {
                    $did = "";
                } 

                if (!is_dir("../used" . "$did") || !file_exists("../used" . "$did")) {
                    header("Location: systems.php");
                    exit;
                } 

                foreach (scandir("../used" . "$did") as $value) {
                    if ($value != "." && $value != ".." && $value != ".htaccess") {
                        if (is_file("../used" . "$did/$value")) {
                            $files[] = "$did/$value";
                        } elseif (is_dir("../used" . "$did/$value")) {
                            $dires[] = "$did/$value";
                        } 
                    } 
                } 

                if ($did == "") {
                    if (file_exists("../used/.htaccess")) {
                        echo '<a href="systems.php?action=pod_chmod&amp;file=/.htaccess" class="btn btn-outline-primary sitelink">[Chmod - ' . permissions("../used/.htaccess") . ']</a> - <font color="#00FF00">' . $lang_admin['file'] . ' .htaccess ' . $lang_admin['exist'] . '</font><br>';

                        if (is_writeable("../used/.htaccess")) {
                            echo'<font color="#FF0000">' . $lang_admin['wrhtacc'] . '</font><br>';
                        } 
                    } else {
                        echo '<font color="#FF0000">' . $lang_admin['warning'] . '!!! ' . $lang_admin['file'] . ' .htaccess ' . $lang_admin['noexist'] . '!<br></font>';
                    } 
                } 

                if ((count($files) + count($dires)) > 0) {
                    if (count($files) > 0) {
                        if ($did != "") {
                            if (file_exists("../used" . "$did/.htaccess")) {
                                echo '<a href="systems.php?action=pod_chmod&amp;file=' . $did . '/.htaccess" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$did/.htaccess") . ']</a> - <font color="#00FF00">' . $lang_admin['file'] . ' .htaccess ' . $lang_admin['exist'] . '</font><br>';

                                if (is_writeable("../used" . "$did/.htaccess")) {
                                    echo '<font color="#FF0000">' . $lang_admin['wrhtacc'] . '</font><br>';
                                } 
                            } 
                        } 

                        echo '' . $lang_admin['filecheck'] . ': <br>';

                        $usedfiles = '';
                        foreach ($files as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$value") . ']</a> - used' . $value . ' (' . formatsize(filesize("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $lang_admin['filewrit'] . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $lang_admin['filenowrit'] . '</font><br>';
                            } 

                            $usedfiles += filesize("../used" . "$value");
                        } 
                        echo '<hr>' . $lang_admin['filessize'] . ': ' . formatsize($usedfiles) . '<hr>';
                    } 

                    if (count($dires) > 0) {
                        echo '' . $lang_admin['checkdirs'] . ': <br>';

                        foreach ($dires as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$value") . ']</a> - <a href="systems.php?did=' . $value . '" class="btn btn-outline-primary sitelink">used' . $value . '</a> (' . formatsize(read_dir("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $lang_admin['filewrit'] . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $lang_admin['filenowrit'] . '</font><br>';
                            } 

                            $useddires = read_dir("../used" . "$value");
                        } 
                        echo '<hr>' . $lang_admin['dirsize'] . ': ' . formatsize($useddires) . '<hr>';
                    } 
                } else {
                    echo '' . $lang_admin['dirempty'] . '!<hr>';
                } 

                if ($did != "") {
                    if (prev_dir($did) != "") {
                        echo '<img src="../images/img/reload.gif" alt=""> <a href="systems.php?did=' . prev_dir($did) . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';
                    } 
                    echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $lang_admin['checksys'] . '</a><br>';
                } 

                break; 
            // CHMOD
            case ("pod_chmod"):

                echo '<img src="../images/img/menu.gif" alt=""> ' . $lang_admin['chchmod'] . '<hr>';

                if ($_GET['file'] != "" && file_exists("../used/" . $_GET['file'] . "")) {
                        echo '<form action="systems.php?action=chmod" method=post>';
                        if (is_file("../used/" . $_GET['file'] . "")) {
                            echo '' . $lang_admin['file'] . ': ../used' . $_GET['file'] . '<br>';
                        } elseif (is_dir("../used/" . $_GET['file'] . "")) {
                            echo '' . $lang_admin['folder'] . ': ../used' . $_GET['file'] . '<br>';
                        } 
                        echo 'CHMOD: <br><input type="text" name="mode" value="' . permissions("../used/" . $_GET['file'] . "") . '" maxlength="3" /><br>
<input name="file" type="hidden" value="' . $_GET['file'] . '" />
<input type=submit value="' . $lang_home['save'] . '"></form><hr>';

                } else {
                    echo 'No file name!<hr>';
                } 

                if (prev_dir($_GET['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_GET['file']) . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';
                } 
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $lang_admin['checksys'] . '</a><br>';

                break;

            case ("chmod"):


                if ($_POST['file'] != "" && $_POST['mode'] != "") {
                    if (chmod("../used/" . $_POST['file'] . "", octdec($_POST['mode'])) != false) {
                        echo '' . $lang_admin['chmodok'] . '!<hr>';
                    } else {
                        echo '' . $lang_admin['chmodnotok'] . '!<hr>';
                    } 
                } else {
                    echo '' . $lang_admin['noneededdata'] . '!<hr>';
                } 

                if (prev_dir($_POST['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_POST['file']) . '" class="btn btn-outline-primary sitelink">' . $lang_home['back'] . '</a><br>';
                } 
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $lang_admin['checksys'] . '</a><br>';

                break;
        } 

        echo '<a href="index.php" class="btn btn-outline-primary sitelink">' . $lang_home['admpanel'] . '</a><br>
		<a href="../" class="btn btn-primary homepage">' . $lang_home['home'] . '</a><br>';
    } else {
        header("Location: ../index.php?error");
        exit;
    } 
} else {
    header("Location: ../index.php?error");
    exit;
} 

include_once"../themes/$config_themes/foot.php";

?>