<?php 
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
}

$vavok->go('current_page')->append_head_tags('<style type="text/css">
.photo img {
    max-width: 320px;
    max-height: 320px;
    overflow: hidden;
}
</style>');

$vavok->go('current_page')->page_title = 'Change Photo'; // update lang
$vavok->require_header();

if ($vavok->go('users')->is_reg()) {
    if (empty($action)) {
        $chk_photo = $vavok->go('db')->get_data(DB_PREFIX . 'vavok_about', "uid='{$vavok->go('users')->user_id}'", 'photo');

        if (!empty($chk_photo['photo'])) {
            echo '<div class="photo">';
            echo '<h1>Your photo</h1><br /><img src="../' . $chk_photo['photo'] . '" alt="" />';
            echo '</div>';
        }

        $form = new PageGen('forms/form_upload.tpl');
        $form->set('form_action', 'photo.php?action=photo');
        $form->set('form_method', 'post');
        $form->set('form_name', 'form');

        $input = new PageGen('forms/input.tpl');
        $input->set('label_for', 'file');
        $input->set('label_value', 'Change your profile photography:');
        $input->set('input_type', 'file');
        $input->set('input_name', 'file');

        $form->set('fields', $input->output());

        echo $form->output();
    }
    // choose photo
    if ($action == "photo") {
        $avat_size = $_FILES['file']['size'];
        $avat_name = $_FILES['file']['name'];
        $size = GetImageSize($_FILES['file']['tmp_name']);
        $width = $size[0];
        $height = $size[1];
        $av_file = file($_FILES['file']['tmp_name']);
        $av_string = substr($avat_name, strrpos($avat_name, '.') + 1);
        $av_string = strtolower($av_string);

        if ($avat_size < 5242880) {
            if ($width < 1024 && $height < 1024) {
                if ($av_string == "gif" || $av_string == "jpg" || $av_string == "jpeg" || $av_string == "png") {
                    if ($av_file) {
                        // remove old photo
                        if (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpg")) {
                            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpg");
                        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".png")) {
                            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".png");
                        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".gif")) {
                            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".gif");
                        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpeg")) {
                            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpeg");
                        }
                        // add new photo
                        copy($_FILES['file']['tmp_name'], "../used/dataphoto/" . $vavok->go('users')->user_id . "." . $av_string . "");
                        $ch = $_FILES['file']['tmp_name'];
                        chmod($ch, 0777);
                        chmod("../used/dataphoto/" . $vavok->go('users')->user_id . "." . $av_string . "", 0777);

                        $vavok->go('db')->update('vavok_about', 'photo', "gallery/photo.php?uz=" . $vavok->go('users')->user_id, "uid='{$vavok->go('users')->user_id}'");
                        echo '<div class="photo">';
                        echo '<br>Photo saved!<br>';
                        echo 'Current photo: <img src="../gallery/photo.php?uz=' . $vavok->go('users')->user_id . '" alt="" /><br>';
                        echo '</div>';
                    } else {
                        echo 'Error uploading photography<br>';
                    }
                } else {
                    echo $vavok->go('localization')->string('badfileext') . '<br>';
                }
            } else {
                echo 'Photography must be under 1024 px<br>';
            }
        } else {
            echo $vavok->go('localization')->string('filemustb') . ' under 5 MB<br>';
        }

        echo $vavok->sitelink('photo.php', $vavok->go('localization')->string('back'), '<p>', '</p>');
    }
    if ($action == 'remove') {
        if (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpg")) {
            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpg");
        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".png")) {
            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".png");
        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".gif")) {
            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".gif");
        } elseif (file_exists("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpeg")) {
            unlink("../used/dataphoto/" . $vavok->go('users')->user_id . ".jpeg");
        }

        $vavok->go('db')->update(DB_PREFIX . 'vavok_about', 'photo', '', "uid='{$vavok->go('users')->user_id}'");

        echo '<p>Your photography successfully deleted!</p>'; // update lang

        echo $vavok->sitelink('profile.php', $vavok->go('localization')->string('back'), '<p>', '</p>');
    }
} else {
    echo '<p>' . $vavok->go('localization')->string('notloged') . '</p>';
}

echo '<p>';
if (empty($action)) {
    echo $vavok->sitelink(HOMEDIR . 'pages/profile.php', $vavok->go('localization')->string('back')) . '<br />';
}
echo $vavok->homelink();
echo '</p>';

$vavok->require_footer();

?>