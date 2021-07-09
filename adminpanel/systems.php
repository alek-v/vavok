<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

function prev_dir($string) {
    $d1 = strrpos($string, "/");
    $d2 = substr($string, $d1, 999);
    $string = str_replace($d2, "", $string);

    return $string;
}

if ($vavok->go('users')->is_reg()) {
    if ($_SESSION['permissions'] == 101) {
        $vavok->require_header();

        switch ($vavok->post_and_get('action')) {
            default:

                echo '<img src="../images/img/menu.gif" alt=""> ' . $vavok->go('localization')->string('checksys') . '<hr>';

                if (isset($_GET['did'])) {
                    $did = $vavok->check($_GET['did']);
                } else {
                    $did = '';
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

                if ($did == '') {
                    if (file_exists("../used/.htaccess")) {
                        echo '<a href="systems.php?action=pod_chmod&amp;file=/.htaccess" class="btn btn-outline-primary sitelink">[Chmod - ' . $vavok->permissions("../used/.htaccess") . ']</a> - <font color="#00FF00">' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('exist') . '</font><br>';

                        if (is_writeable("../used/.htaccess")) {
                            echo'<font color="#FF0000">' . $vavok->go('localization')->string('wrhtacc') . '</font><br>';
                        }
                    } else {
                        echo '<font color="#FF0000">' . $vavok->go('localization')->string('warning') . '!!! ' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('noexist') . '!<br></font>';
                    }
                }

                if ((count($files) + count($dires)) > 0) {
                    if (count($files) > 0) {
                        if ($did != "") {
                            if (file_exists("../used" . "$did/.htaccess")) {
                                echo '<a href="systems.php?action=pod_chmod&amp;file=' . $did . '/.htaccess" class="btn btn-outline-primary sitelink">[CHMOD - ' . $vavok->permissions("../used" . "$did/.htaccess") . ']</a> - <font color="#00FF00">' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('exist') . '</font><br>';

                                if (is_writeable("../used" . "$did/.htaccess")) {
                                    echo '<font color="#FF0000">' . $vavok->go('localization')->string('wrhtacc') . '</font><br>';
                                }
                            }
                        }

                        echo $vavok->go('localization')->string('filecheck') . ': <br />';

                        $usedfiles = 0;
                        foreach ($files as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . $vavok->permissions("../used" . "$value") . ']</a> - used' . $value . ' (' . $vavok->formatsize(filesize("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $vavok->go('localization')->string('filewrit') . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $vavok->go('localization')->string('filenowrit') . '</font><br>';
                            }
                            $usedfiles += filesize("../used" . "$value");
                        }
                        echo '<hr>' . $vavok->go('localization')->string('filessize') . ': ' . $vavok->formatsize($usedfiles) . '<hr>';
                    }

                    if (count($dires) > 0) {
                        echo $vavok->go('localization')->string('checkdirs') . ': <br>';

                        foreach ($dires as $value) {
                            echo '<a href="systems.php?action=pod_chmod&amp;file=' . $value . '" class="btn btn-outline-primary sitelink">[CHMOD - ' . $vavok->permissions("../used" . "$value") . ']</a> - <a href="systems.php?did=' . $value . '" class="btn btn-outline-primary sitelink">used' . $value . '</a> (' . $vavok->formatsize($vavok->read_dir("../used" . "$value")) . ') - ';
                            if (is_writeable("../used" . "$value")) {
                                echo '<font color="#00FF00">' . $vavok->go('localization')->string('filewrit') . '</font><br>';
                            } else {
                                echo '<font color="#FF0000">' . $vavok->go('localization')->string('filenowrit') . '</font><br>';
                            }

                            $useddires = $vavok->read_dir("../used" . "$value");
                        }
                        echo '<hr>' . $vavok->go('localization')->string('dirsize') . ': ' . $vavok->formatsize($useddires) . '<hr>';
                    }
                } else {
                    echo '' . $vavok->go('localization')->string('dirempty') . '!<hr>';
                }

                if ($did != '') {
                    if (prev_dir($did) != '') {
                        echo '<img src="../images/img/reload.gif" alt=""> <a href="systems.php?did=' . prev_dir($did) . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br>';
                    }
                    echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('checksys') . '</a><br>';
                }

                break; 
            // CHMOD
            case ('pod_chmod'):
                echo '<img src="../images/img/menu.gif" alt=""> ' . $vavok->go('localization')->string('chchmod') . '<hr>';

                if ($_GET['file'] != "" && file_exists("../used/" . $_GET['file'] . "")) {
                        echo '<form action="systems.php?action=chmod" method=post>';
                        if (is_file("../used/" . $_GET['file'] . "")) {
                            echo $vavok->go('localization')->string('file') . ': ../used' . $_GET['file'] . '<br>';
                        } elseif (is_dir("../used/" . $_GET['file'] . "")) {
                            echo $vavok->go('localization')->string('folder') . ': ../used' . $_GET['file'] . '<br>';
                        } 
                        echo 'CHMOD: <br><input type="text" name="mode" value="' . $vavok->permissions("../used/" . $_GET['file'] . "") . '" maxlength="3" /><br>
<input name="file" type="hidden" value="' . $_GET['file'] . '" />
<input type=submit value="' . $vavok->go('localization')->string('save') . '"></form><hr>';

                } else {
                    echo 'No file name!<hr>';
                }

                if (prev_dir($_GET['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_GET['file']) . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br>';
                }
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('checksys') . '</a><br>';

                break;

            case ("chmod"):
                if ($_POST['file'] != "" && $_POST['mode'] != "") {
                    if (chmod("../used/" . $_POST['file'] . "", octdec($_POST['mode'])) != false) {
                        echo '' . $vavok->go('localization')->string('chmodok') . '!<hr>';
                    } else {
                        echo '' . $vavok->go('localization')->string('chmodnotok') . '!<hr>';
                    } 
                } else {
                    echo '' . $vavok->go('localization')->string('noneededdata') . '!<hr>';
                } 

                if (prev_dir($_POST['file']) != "") {
                    echo '<a href="systems.php?did=' . prev_dir($_POST['file']) . '" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('back') . '</a><br>';
                } 
                echo '<a href="systems.php" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('checksys') . '</a><br>';

                break;
        } 

        echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br>
		' . $vavok->homelink() . '</p>';
    } else {
        header("Location: ../index.php?error");
        exit;
    } 
} else {
    header("Location: ../index.php?error");
    exit;
} 

$vavok->require_footer();

?>