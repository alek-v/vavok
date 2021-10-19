<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator(101)) $vavok->redirect_to('./');

function prev_dir($string) {
    $d1 = strrpos($string, "/");
    $d2 = substr($string, $d1, 999);
    $string = str_replace($d2, "", $string);

    return $string;
}

$vavok->require_header();

switch ($vavok->post_and_get('action')) {
    default:
        echo '<img src="../themes/images/img/menu.gif" alt=""> ' . $vavok->go('localization')->string('checksys') . '<hr>';

        $did = $vavok->check($vavok->post_and_get('did'));

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
                echo $vavok->sitelink('systems.php?action=pod_chmod&amp;file=/.htaccess', '[Chmod - ' . $vavok->permissions("../used/.htaccess") . ']') . ' - <font color="#00FF00">' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('exist') . '</font><br>';

                if (is_writeable("../used/.htaccess")) {
                    echo'<font color="#FF0000">' . $vavok->go('localization')->string('wrhtacc') . '</font><br>';
                }
            } else {
                echo '<font color="#FF0000">' . $vavok->go('localization')->string('warning') . '!!! ' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('noexist') . '!<br></font>';
            }
        }

        if ((count($files) + count($dires)) > 0) {
            if (count($files) > 0) {
                if (!empty($did)) {
                    if (file_exists("../used" . "$did/.htaccess")) {
                        echo $vavok->sitelink('systems.php?action=pod_chmod&amp;file=' . $did . '/.htaccess', '[CHMOD - ' . $vavok->permissions("../used" . "$did/.htaccess") . ']') . ' - <font color="#00FF00">' . $vavok->go('localization')->string('file') . ' .htaccess ' . $vavok->go('localization')->string('exist') . '</font><br>';

                        if (is_writeable("../used" . "$did/.htaccess")) {
                            echo '<font color="#FF0000">' . $vavok->go('localization')->string('wrhtacc') . '</font><br>';
                        }
                    }
                }

                echo $vavok->go('localization')->string('filecheck') . ': <br />';

                $usedfiles = 0;
                foreach ($files as $value) {
                    echo $vavok->sitelink('systems.php?action=pod_chmod&amp;file=' . $value, '[CHMOD - ' . $vavok->permissions("../used" . "$value") . ']') . ' - used' . $value . ' (' . $vavok->formatsize(filesize("../used" . "$value")) . ') - ';

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
                    echo $vavok->sitelink('systems.php?action=pod_chmod&amp;file=' . $value, '[CHMOD - ' . $vavok->permissions("../used" . "$value") . ']') . ' - ' . $vavok->sitelink('systems.php?did=' . $value, 'used' . $value) . ' (' . $vavok->formatsize($vavok->read_dir("../used" . "$value")) . ') - ';

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
                echo $vavok->sitelink('systems.php?did=' . prev_dir($did), '<img src="../themes/images/img/reload.gif" alt=""> ' . $vavok->go('localization')->string('back')) . '<br>';
            }
            echo $vavok->sitelink('systems.php', $vavok->go('localization')->string('checksys')) . '<br>';
        }

        break; 
        // CHMOD
    case ('pod_chmod'):
        echo '<img src="../themes/images/img/menu.gif" alt=""> ' . $vavok->go('localization')->string('chchmod') . '<hr>';

        if ($vavok->post_and_get('file') && file_exists("../used/" . $vavok->post_and_get('file'))) {
            echo '<form action="systems.php?action=chmod" method=post>';
            if (is_file("../used/" . $vavok->post_and_get('file'))) {
                echo $vavok->go('localization')->string('file') . ': ../used' . $vavok->post_and_get('file') . '<br>';
            } elseif (is_dir("../used/" . $vavok->post_and_get('file'))) {
                echo $vavok->go('localization')->string('folder') . ': ../used' . $vavok->post_and_get('file') . '<br>';
            } 
            echo 'CHMOD: <br><input type="text" name="mode" value="' . $vavok->permissions("../used/" . $vavok->post_and_get('file')) . '" maxlength="3" /><br>
            <input name="file" type="hidden" value="' . $vavok->post_and_get('file') . '" />
            <input type=submit value="' . $vavok->go('localization')->string('save') . '"></form><hr>';
        } else {
            echo 'No file name!<hr>';
        }

        if (!empty(prev_dir($vavok->post_and_get('file')))) {
            echo $vavok->sitelink('systems.php?did=' . prev_dir($vavok->post_and_get('file')), $vavok->go('localization')->string('back')) . '<br>';
        }

        echo $vavok->sitelink('systems.php', $vavok->go('localization')->string('checksys')) . '<br>';
    break;

    case ('chmod'):
        if (!empty($vavok->post_and_get('file')) && !empty($vavok->post_and_get('mode'))) {
            if (chmod("../used/" . $vavok->post_and_get('file'), octdec($vavok->post_and_get('mode'))) != false) {
                echo $vavok->go('localization')->string('chmodok') . '!<hr>';
            } else {
                echo $vavok->go('localization')->string('chmodnotok') . '!<hr>';
            }
        } else {
            echo $vavok->go('localization')->string('noneededdata') . '!<hr>';
        } 

        if (!empty(prev_dir($vavok->post_and_get('file')))) {
            echo $vavok->sitelink('systems.php?did=' . prev_dir($vavok->post_and_get('file')), $vavok->go('localization')->string('back')) . '<br>';
        }

        echo $vavok->sitelink('systems.php', $vavok->go('localization')->string('checksys')) . '<br>';
    break;
}

echo '<p>'
echo $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br />';
echo $vavok->homelink();
echo '</p>';

$vavok->require_footer();

?>