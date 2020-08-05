<?php 
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   06.08.2020. 0:02:30
*/

require_once"../include/startup.php";

if (!empty($_GET['action'])) {
    $action = $vavok->check($_GET["action"]);
} else {
    $action = '';
}

$current_page->append_head_tags('<style type="text/css">
.photo img {
    max-width: 320px;
    max-height: 320px;
    overflow: hidden;
}
</style>');

$current_page->page_title = 'Change Photo'; // update lang
require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

if ($users->is_reg()) {
    if (empty($action)) {
        $chk_photo = $db->get_data('vavok_about', "uid='{$users->user_id}'", 'photo');

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
                        if (file_exists("../used/dataphoto/" . $users->user_id . ".jpg")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".jpg");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".png")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".png");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".gif")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".gif");
                        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".jpeg")) {
                            unlink("../used/dataphoto/" . $users->user_id . ".jpeg");
                        }
                        // add new photo
                        copy($_FILES['file']['tmp_name'], "../used/dataphoto/" . $users->user_id . "." . $av_string . "");
                        $ch = $_FILES['file']['tmp_name'];
                        chmod($ch, 0777);
                        chmod("../used/dataphoto/" . $users->user_id . "." . $av_string . "", 0777);

                        $db->update('vavok_about', 'photo', "gallery/photo.php?uz=" . $users->user_id, "uid='{$users->user_id}'");
                        echo '<div class="photo">';
                        echo '<br>Photo saved!<br>';
                        echo 'Current photo: <img src="../gallery/photo.php?uz=' . $users->user_id . '" alt="" /><br>';
                        echo '</div>';
                    } else {
                        echo 'Error uploading photography<br>';
                    }
                } else {
                    echo $localization->string('badfileext') . '<br>';
                }
            } else {
                echo 'Photography must be under 1024 px<br>';
            }
        } else {
            echo $localization->string('filemustb') . ' under 5 MB<br>';
        }

        echo '<p><a href="photo.php" class="btn btn-primary sitelink">' . $localization->string('back') . '</a></p>';
    }
    if ($action == 'remove') {
        if (file_exists("../used/dataphoto/" . $users->user_id . ".jpg")) {
            unlink("../used/dataphoto/" . $users->user_id . ".jpg");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".png")) {
            unlink("../used/dataphoto/" . $users->user_id . ".png");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".gif")) {
            unlink("../used/dataphoto/" . $users->user_id . ".gif");
        } elseif (file_exists("../used/dataphoto/" . $users->user_id . ".jpeg")) {
            unlink("../used/dataphoto/" . $users->user_id . ".jpeg");
        }

        $db->update('vavok_about', 'photo', "", "uid='{$users->user_id}'");

        echo '<p>Your photography successfully deleted!</p>'; // update lang

        echo '<p><a href="profile.php" class="btn btn-primary sitelink">' . $localization->string('back') . '</a></p>';
    }
} else {
    echo '<p>' . $localization->string('notloged') . '</p>';
}

echo '<p>';
if (empty($action)) {
    echo '<a href="../pages/profile.php" class="btn btn-primary sitelink">' . $localization->string('back') . '</a><br />';
} 
echo '<a href="../" class="btn btn-primary homepage">' . $localization->string('home') . '</a>';
echo '</p>';

require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

?>