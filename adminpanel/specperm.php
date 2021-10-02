<?php 
require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?auth_error');

$edit_user = $vavok->post_and_get('users');
$permission_name = $vavok->post_and_get('permission_name');

if ($vavok->post_and_get('action') == 'update') {
    $acc_data = '';

    if (!empty($vavok->post_and_get('pageedit', true))) {
        $optionArray = $vavok->post_and_get('pageedit', true);
        for ($i = 0; $i < count($optionArray); $i++) {
            if ($optionArray[$i] == 'show') {
                $show = 'show,';
                break;
            } else {
                $show = '';
            } 
        }

        for ($i = 0; $i < count($optionArray); $i++) {
            if ($optionArray[$i] == 'edit') {
                $edit = 'edit,';
                break;
            } else {
                $edit = '';
            } 
        }

        for ($i = 0; $i < count($optionArray); $i++) {
            if ($optionArray[$i] == 'del') {
                $del = 'del,';
                break;
            } else {
                $del = '';
            } 
        }

        for ($i = 0; $i < count($optionArray); $i++) {
            if ($optionArray[$i] == 'insert') {
                $insert = 'insert,';
                break;
            } else {
                $insert = '';
            } 
        }

        for ($i = 0; $i < count($optionArray); $i++) {
            if ($optionArray[$i] == 'editunpub') {
                $editunpub = 'editunpub,';
                break;
            } else {
                $editunpub = '';
            } 
        }

        $acc_data = $show . $edit . $del . $insert . $editunpub;
        $acc_data = rtrim($acc_data, ',');

        // Show - Add access
		// Edit - Edit all data
		// Insert - User will have ability to create new data and edit data he created
		// Delete - User can delete any of data
		// Edit Unpublished - Ability do edit all unpublished data
    }

    $check_data = $vavok->go('db')->count_row('specperm', "uid='{$edit_user}' AND permname='{$permission_name}'");
    if ($check_data < 1) {
        $values = array(
            'uid' => $edit_user,
            'permname' => $permname,
            'permacc' => $acc_data
        );
        $vavok->go('db')->insert(DB_PREFIX . 'specperm', $values);
    } else {
        $vavok->go('db')->update(DB_PREFIX . 'specperm', 'permacc', $acc_data, "uid='{$edit_user}' AND permname='{$permission_name}'");
    }

    $vavok->redirect_to('users.php?action=edit&users=' . $vavok->go('users')->getnickfromid($edit_user) . '&isset=savedok');
}

if ($vavok->post_and_get('action') == 'delete_permission') {
    $vavok->go('db')->delete('specperm', "permname='{$permission_name}' AND uid='{$edit_user}'");
    $vavok->redirect_to('specperm.php?users=' . $edit_user);
}

if ($vavok->post_and_get('action') == 'forum') $vavok->redirect_to('forum-moders.php?users=' . $edit_user);

$vavok->go('current_page')->page_title = 'Special Permissions'; // update lang
$vavok->require_header();

echo '<p>Updating permissions for user <strong>' . $vavok->go('users')->getnickfromid($edit_user) . '</strong></p>';

if (empty($vavok->post_and_get('action')) && !empty($edit_user)) {
    $permissionData = $vavok->go('db')->count_row('specperm', "uid='{$edit_user}'");

    if ($permissionData > 0) {
        foreach($vavok->go('db')->query("SELECT * FROM specperm WHERE uid='{$edit_user}'") as $permission) {
            echo '<p><span class="btn btn-outline-primary"><strong>' . $permission['permname'] . '</strong></span> - 
            <a href="specperm.php?action=changepermissions&permission_name=' . $permission['permname'] . '&users=' . $permission['uid'] . '" class="btn btn-primary sitelink">[EDIT]</a>
            <a href="specperm.php?action=delete_permission&permission_name=' . $permission['permname'] . '&users=' . $permission['uid'] . '" class="btn btn-primary sitelink">[DEL]</a></p>';
        }
    }
    ?>
    <form method="post" action="specperm.php?action=changepermissions&users=<?php echo $edit_user; ?>">
        <div class="form-group">
            <label for="permission_list">Add or edit users permissions</label>
            <select class="form-control" id="permission_list" name="permission_name">
                <option value="adminchat">Admin Chat</option>
                <option value="adminlist">Admin List</option>
                <option value="reglist">List of uncofirmed registrations</option>
                <option value="pageedit">Page Editor</option>
                <option value="news">News</option>
                <?php if (file_exists("forum-moders.php")) echo '<option value="forum">Forum</option>'; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Confirm</button>
    </form>

    <form method="post" action="specperm.php?action=changepermissions&users=<?php echo $edit_user; ?>">
        <div class="form-group">
            <label for="change_permissions_input">Add or edit permissions that are not listed above</label>
            <input class="form-control" id="change_permissions_input" type="text" name="permission_name" value="" />
        </div>
        <button type="submit" class="btn btn-primary">Confirm</button>
    </form>
    <?php
}

if ($vavok->post_and_get('action') == 'changepermissions' && !empty($edit_user) && !empty($permission_name)) {
    if ($vavok->go('db')->count_row("specperm", "uid='{$edit_user}' AND permname='{$permission_name}'") > 0) {
        $check_data = $vavok->go('db')->get_data('specperm', "uid='{$edit_user}' AND permname='{$permission_name}'");
        $acc_data = explode(',', $check_data['permacc']);
    } else {
        $acc_data = array();
    }

    if (in_array('show', $acc_data) || in_array(1, $acc_data)) {
        $show_checked = 'checked';
    } else {
        $show_checked = '';
    } 
    if (in_array('edit', $acc_data) || in_array(2, $acc_data)) {
        $edit_checked = 'checked';
    } else {
        $edit_checked = '';
    } 
    if (in_array('del', $acc_data) || in_array(3, $acc_data)) {
        $del_checked = 'checked';
    } else {
        $del_checked = '';
    } 
    if (in_array('insert', $acc_data) || in_array(4, $acc_data)) {
        $insert_checked = 'checked';
    } else {
        $insert_checked = '';
    } 
    if (in_array('editunpub', $acc_data) || in_array(5, $acc_data)) {
        $editunpub_checked = 'checked';
    } else {
        $editunpub_checked = '';
    }
    ?>

    <p>Updating permission <strong><?php echo $permission_name; ?></strong></p>

    <form action="specperm.php?action=update&amp;users=<?php echo $edit_user; ?>" method="post">
        <div class="form-check form-check">
            <input type="checkbox" name="pageedit[]" value="show" <?php echo $show_checked; ?> class="form-check-input" id="show" />
            <label class="form-check-label" for="show">
                Show
            </label>
        </div>
        <div class="form-check form-check">
            <input type="checkbox" name="pageedit[]" value="edit" <?php echo $edit_checked; ?> class="form-check-input" id="edit" />
            <label class="form-check-label" for="edit">
                Edit
            </label>
        </div>
        <div class="form-check form-check">
            <input type="checkbox" name="pageedit[]" value="insert" <?php echo $insert_checked; ?> class="form-check-input" id="insert" />
            <label class="form-check-label" for="insert">
                Insert
            </label>
        </div>
        <div class="form-check form-check">
            <input type="checkbox" name="pageedit[]" value="del" <?php echo $del_checked; ?> class="form-check-input" id="delete" />
            <label class="form-check-label" for="delete">
                Delete
            </label>
        </div>
        <div class="form-check form-check">
            <input type="checkbox" name="pageedit[]" value="editunpub" <?php echo $editunpub_checked; ?> class="form-check-input" id="edit_unpublished" />
            <label class="form-check-label" for="edit_unpublished">
                Edit Unpublished
            </label>
        </div>
        <input type="hidden" name="permission_name" value="<?php echo $permission_name; ?>" />

        <button type="submit" class="btn btn-primary mt-3">Update</button>
    </form>

    <p>
        Show - Access to page<br />
        Edit - Edit all<br />
        Insert - Insert new data<br />
        Delete - Delete data<br />
        Edit Unpublished - Ability do edit all unpublished content
    </p>
    <?php
}

echo '<p><a href="./" class="btn btn-outline-primary sitelink">' . $vavok->go('localization')->string('admpanel') . '</a><br />';
echo $vavok->homelink() . '</p>';

$vavok->require_footer();

?>