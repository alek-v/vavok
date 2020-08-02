<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   26.07.2020. 14:47:13
*/

require_once"../include/startup.php";

$action = isset($_GET['action']) ? $vavok->check($_GET['action']) : '';

function prev_dir($string) {
    $d1 = strrpos($string, "/");
    $d2 = substr($string, $d1, 999);
    $string = str_replace($d2, "", $string);

    return $string;
} 

if ($users->is_reg()) {
    if ($_SESSION['permissions'] == 101) {
        
        require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

        switch ($action) {
            default:

                echo '<img src="../images/img/menu.gif" alt=""> ' . $localization->string('checksys') . '<hr>';

                if (isset($_GET['did'])) {
                    $did = $vavok->check($_GET['did']);
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
                        echo '<a href="systems.php?action=pod_chmod&amp;file=/.htaccess" class="btn btn-outline-primary sitelink">[Chmod - ' . permissions("../used/.htaccess") . ']</a> - <font color="#00FF00">' . $localization->string('file') . ' .htaccess ' . $localization->string('exist') . '</font><br>';

                        if (is_writeable("../used/.htaccess")) {
                            echo'<font color="#FF0000">' . $localization->string('wrhtacc') . '</font><br>';
                        } 
                    } else {
                        echo '<font color="#FF0000">' . $localization->string('warning') . '!!! ' . $localization->string('file') . ' .htaccess ' . $localization->string('noexist') . '!<br></font>';
                    } 
                } 

                if ((count($files) + count($dires)) > 0) {
                    if (count($files) > 0) {
                        if ($did != "") {
                            if (file_exists("../used" . "$did/.htaccess")) {
                                echo '<a href="systems.php?action=pod_chmod&amp;file=' . $did . '/.htaccess" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$did/.htaccess") . ']</a> - <font color="#00FF00">' . $localization->string('file') . ' .htaccess ' . $localization->string('exist') . '</font><br>';

                                if (is_writeable("../used" . "$did/.htaccess")) {
                                    echo '<font color="#FF0000">' . $localization->string('wrhtacc') . '</font><br>';
                                } 
                            } 
                        } 

                        echo '' . $localization->string('filecheck') . ': <br>';

                        $usedfiles = '';
                        foreach ($files as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$value") . ']</a> - used' . $value . ' (' . formatsize(filesize("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $localization->string('filewrit') . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $localization->string('filenowrit') . '</font><br>';
                            } 

                            $usedfiles += filesize("../used" . "$value");
                        } 
                        echo '<hr>' . $localization->string('filessize') . ': ' . formatsize($usedfiles) . '<hr>';
                    } 

                    if (count($dires) > 0) {
                        echo '' . $localization->string('checkdirs') . ': <br>';

                        foreach ($dires as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . permissions("../used" . "$value") . ']</a> - <a href="systems.php?did=' . $value . '" class="btn btn-outline-primary sitelink">used' . $value . '</a> (' . formatsize(read_dir("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $localization->string('filewrit') . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $localization->string('filenowrit') . '</font><br>';
                            } 

                            $useddires = read_dir("../used" . "$value");
                        } 
                        echo '<hr>' . $localization->string('dirsize') . ': ' . formatsize($useddires) . '<hr>';
                    } 
                } else {
                    echo '' . $localization->string('dirempty') . '!<hr>';
                } 

                if ($did != "") {
                    if (prev_dir($did) != "") {
                        echo '<img src="../images/img/reload.gif" alt=""> <a href="systems.php?did=' . prev_dir($did) . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a><br>';
                    } 
                    echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $localization->string('checksys') . '</a><br>';
                } 

                break; 
            // CHMOD
            case ("pod_chmod"):

                echo '<img src="../images/img/menu.gif" alt=""> ' . $localization->string('chchmod') . '<hr>';

                if ($_GET['file'] != "" && file_exists("../used/" . $_GET['file'] . "")) {
                        echo '<form action="systems.php?action=chmod" method=post>';
                        if (is_file("../used/" . $_GET['file'] . "")) {
                            echo $localization->string('file') . ': ../used' . $_GET['file'] . '<br>';
                        } elseif (is_dir("../used/" . $_GET['file'] . "")) {
                            echo $localization->string('folder') . ': ../used' . $_GET['file'] . '<br>';
                        } 
                        echo 'CHMOD: <br><input type="text" name="mode" value="' . permissions("../used/" . $_GET['file'] . "") . '" maxlength="3" /><br>
<input name="file" type="hidden" value="' . $_GET['file'] . '" />
<input type=submit value="' . $localization->string('save') . '"></form><hr>';

                } else {
                    echo 'No file name!<hr>';
                } 

                if (prev_dir($_GET['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_GET['file']) . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a><br>';
                } 
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $localization->string('checksys') . '</a><br>';

                break;

            case ("chmod"):


                if ($_POST['file'] != "" && $_POST['mode'] != "") {
                    if (chmod("../used/" . $_POST['file'] . "", octdec($_POST['mode'])) != false) {
                        echo '' . $localization->string('chmodok') . '!<hr>';
                    } else {
                        echo '' . $localization->string('chmodnotok') . '!<hr>';
                    } 
                } else {
                    echo '' . $localization->string('noneededdata') . '!<hr>';
                } 

                if (prev_dir($_POST['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_POST['file']) . '" class="btn btn-outline-primary sitelink">' . $localization->string('back') . '</a><br>';
                } 
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $localization->string('checksys') . '</a><br>';

                break;
        } 

        echo '<a href="./" class="btn btn-outline-primary sitelink">' . $localization->string('admpanel') . '</a><br>
		<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a><br>';
    } else {
        header("Location: ../index.php?error");
        exit;
    } 
} else {
    header("Location: ../index.php?error");
    exit;
} 

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>