<?php

require_once '../include/startup.php';

if (!$vavok->go('users')->is_administrator()) $vavok->redirect_to('../?auth_error');

$vavok->go('current_page')->page_title = 'Blog';
$vavok->require_header();

$action = $vavok->post_and_get('action');
switch ($action) {
	case 'add-category':
		// Save category if name is sent
		if (!empty($vavok->post_and_get('category')) && !empty($vavok->post_and_get('value'))) {
			// Calculate category position
			$position = $vavok->go('db')->count_row(DB_PREFIX . 'settings', "setting_group = 'blog_category'");

			// Add category
			$data = array('setting_group' => 'blog_category', 'setting_name' => $vavok->post_and_get('category'), 'value' => $vavok->post_and_get('value'), 'options' => $position);
			$vavok->go('db')->insert(DB_PREFIX . 'settings', $data);

			// Show message if category is saved
			echo $vavok->show_notification('<img src="../themes/images/img/reload.gif" alt="Saved" /> Category saved</p>');
		}

	    // Category input
	    $category = new PageGen('forms/input.tpl');
	    $category->set('label_value', 'Category name');
	    $category->set('label_for', 'category');
	    $category->set('input_name', 'category');
	    $category->set('input_id', 'category');
	    $category->set('input_type', 'text');
	    $category->set('input_value', '');

	    // Value input
	    $value = new PageGen('forms/input.tpl');
	    $value->set('label_value', 'Category value (page tag)');
	    $value->set('label_for', 'value');
	    $value->set('input_name', 'value');
	    $value->set('input_id', 'value');
	    $value->set('input_type', 'text');
	    $value->set('input_value', '');

	    $fields = array($category, $value);

		// Create form
	    $form = new PageGen('forms/form.tpl');
	    $form->set('form_action', 'blog.php?action=add-category');
	    $form->set('form_method', 'post');
	    $form->set('fields', $form->merge($fields));

	    echo $form->output();
		break;

	case 'edit-category':
		// Update category if data are sent
		if (!empty($vavok->post_and_get('id')) && !empty($vavok->post_and_get('category')) && !empty($vavok->post_and_get('value'))) {
			// Update category
			$vavok->go('db')->update(DB_PREFIX . 'settings', array('setting_name', 'value'), array($vavok->post_and_get('category'), $vavok->post_and_get('value')), "id = '{$vavok->post_and_get('id')}'");

			// Show message if category is updated
			echo $vavok->show_notification('<img src="../themes/images/img/reload.gif" alt="Saved" /> Category updated</p>');
		}

		// Category data
		$cat_info = $vavok->go('db')->get_data(DB_PREFIX . 'settings', "id='{$vavok->post_and_get('id')}'");

	    // Category input
	    $category = new PageGen('forms/input.tpl');
	    $category->set('label_value', 'Category name');
	    $category->set('label_for', 'category');
	    $category->set('input_name', 'category');
	    $category->set('input_id', 'category');
	    $category->set('input_type', 'text');
	    $category->set('input_value', $cat_info['setting_name']);

	    // Value input
	    $value = new PageGen('forms/input.tpl');
	    $value->set('label_value', 'Category value (page tag)');
	    $value->set('label_for', 'value');
	    $value->set('input_name', 'value');
	    $value->set('input_id', 'value');
	    $value->set('input_type', 'text');
	    $value->set('input_value', $cat_info['value']);

	    // Category id
	    $category_id = new PageGen('forms/input.tpl');
	    $category_id->set('input_name', 'id');
	    $category_id->set('input_type', 'hidden');
	    $category_id->set('input_value', $vavok->post_and_get('id'));

	    $fields = array($category, $value, $category_id);

		// Create form
	    $form = new PageGen('forms/form.tpl');
	    $form->set('form_action', 'blog.php?action=edit-category');
	    $form->set('form_method', 'post');
	    $form->set('fields', $form->merge($fields));

	    echo $form->output();
	break;
	
	case 'delete':
		if ($vavok->go('db')->count_row(DB_PREFIX . 'settings', "id = {$vavok->post_and_get('id')}") > 0) {
			$vavok->go('db')->delete(DB_PREFIX . 'settings', "id = {$vavok->post_and_get('id')}");

			echo $vavok->show_notification('<img src="../themes/images/img/error.gif" alt="Deleted" /> Category deleted');
		} else {
			echo $vavok->show_danger('<img src="../themes/images/img/error.gif" alt="Error" /> This category does not exist');
		}
	break;

	case 'move-up':
		// cat we want to update
		$cat_info = $vavok->go('db')->get_data(DB_PREFIX . 'settings', "id='{$vavok->post_and_get('id')}'");

		$cat_position = $cat_info['options'];
		$new_position = $cat_position - 1;
			
		if ($cat_position != 0 && !empty($cat_position)) {
			// Update cat with position we want to take
			$cat_to_down = $vavok->go('db')->get_data('settings', "setting_group = 'blog_category' AND options='{$new_position}'");
			$cat_to_down_position = $cat_to_down['options'] + 1;
			$vavok->go('db')->exec("UPDATE settings SET options='{$cat_to_down_position}' WHERE id='{$cat_to_down['id']}'");
			
			// Now, update our cat
			$vavok->go('db')->exec("UPDATE settings SET options='{$new_position}' WHERE id='{$vavok->post_and_get('id')}'");

			echo $vavok->show_notification('<img src="../themes/images/img/reload.gif" alt="Updated" /> Category position updated');
		} else {
			echo $vavok->show_danger('<img src="../themes/images/img/error.gif" alt="Error" /> Category position not updated');
		}
	break;

	case 'move-down':
		$total = $vavok->go('db')->count_row(DB_PREFIX . 'settings', "setting_group = 'blog_category'");
		
		// cat we want to update
		$cat_info = $vavok->go('db')->get_data(DB_PREFIX . 'settings', "id='{$vavok->post_and_get('id')}'");

		$cat_position = $cat_info['options'];
		$new_position = $cat_position + 1;
			
		if ($new_position < $total && (!empty($cat_position) || $cat_position == '0')) {
			// Update cat with position we want to take
			$cat_to_down = $vavok->go('db')->get_data(DB_PREFIX . 'settings', "setting_group = 'blog_category' AND options='{$new_position}'");
			$cat_to_down_position = $cat_to_down['options'] - 1;
			$vavok->go('db')->exec("UPDATE " . DB_PREFIX . "settings SET options='{$cat_to_down_position}' WHERE id='{$cat_to_down['id']}'");
			
			// Now, update our cat
			$vavok->go('db')->exec("UPDATE " . DB_PREFIX . "settings SET options='{$new_position}' WHERE id='{$vavok->post_and_get('id')}'");

			echo $vavok->show_notification('<img src="../themes/images/img/reload.gif" alt="Updated" /> Category position updated');
		} else {
			echo $vavok->show_danger('<img src="../themes/images/img/error.gif" alt="Error" /> Category position not updated');
		}
	break;

	default:
		if ($vavok->go('db')->count_row(DB_PREFIX . 'settings', "setting_group = 'blog_category'") == 0) echo '<p><img src="../themes/images/img/reload.gif" alt=""/> There is no any category</p>';

		// Blog categories
		foreach ($vavok->go('db')->query("SELECT * FROM " . DB_PREFIX . "settings WHERE setting_group = 'blog_category' ORDER BY options") as $category) {
			echo '<div class="a">';
			echo $vavok->sitelink(HOMEDIR . 'blog/category/' . $category['value'] . '/', $category['setting_name']) . ' ';
			echo $vavok->sitelink('blog.php?action=edit-category&id=' . $category['id'], '<img src="../themes/images/img/edit.gif" alt="Edit" /> Edit') . ' ';
			echo $vavok->sitelink('blog.php?action=delete&id=' . $category['id'], '<img src="../themes/images/img/error.gif" alt="Delete" /> Delete') . ' ';
			echo $vavok->sitelink('blog.php?action=move-up&id=' . $category['id'], '<img src="../themes/images/img/ups.gif" alt="Up" /> Move up') . ' ';
			echo $vavok->sitelink('blog.php?action=move-down&id=' . $category['id'], '<img src="../themes/images/img/downs.gif" alt="Down" /> Move down');
			echo '</div>';
		}
		break;
}

echo '<p class="mt-5">';
if ($vavok->post_and_get('action') !== 'add-category') echo $vavok->sitelink('blog.php?action=add-category', 'Add category') . '<br />';
if (!empty($vavok->post_and_get('action'))) echo $vavok->sitelink('blog.php', 'Blog') . '<br />';
echo $vavok->sitelink('./', $vavok->go('localization')->string('admpanel')) . '<br />';
echo $vavok->homelink();
echo '</p>';

$vavok->require_footer();
?>