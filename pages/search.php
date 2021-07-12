<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

if (!function_exists('cyr_to_lat')) {
	function cyr_to_lat($str) {
	    $latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
	    $cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");
	    $str = str_replace($cyrillic, $latin, $str);
	    return $str;
	}
}
if (!function_exists('lat_to_cyr')) {
	function lat_to_cyr($str, $correct = '') {
	    $latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
	    $cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");

	    $str = str_replace($latin, $cyrillic, $str);

	    if ($correct == 'full') {
	    	$str = skip($str);
	    }

	    return $str;
	}
}

$itemCyr = lat_to_cyr(htmlspecialchars_decode($vavok->post_and_get('item'))); // translate to cyrillic to search for cyrillic post
$itemLat = cyr_to_lat(htmlspecialchars_decode($vavok->post_and_get('item'))); // translate to latin to search for latin post

// redirect if url is not rewriten
if (stristr(CLEAN_REQUEST_URI, 'search.php')) {
	$vavok->redirect_to(HOMEDIR . 'search/' . $vavok->post_and_get('item') . '/');
}

/**
 * Add meta tags
 */
$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex, follow" />' . "\r\n");

if (empty($vavok->post_and_get('item'))) {
	$vavok->go('current_page')->page_title = $vavok->go('localization')->string('search');

	$vavok->require_header();

	$indexPage = new PageGen("search/index.tpl");
	echo $indexPage->output();
} else {
	if (!empty($ln_loc)) {
		$orderBy = " order by lang='" . $ln_loc . "' desc, pname desc ";
	} else {
		$orderBy = ' ';
	}

	$clean_search_item = str_replace('_', ' ', $vavok->post_and_get('item'));

	$vavok->go('current_page')->page_title = $clean_search_item;
	$vavok->require_header();

	$resultsPage = new PageGen('search/results.tpl');
	$resultsPage->set('searchItem', $clean_search_item);

	$page_ids = array();

	/**
	 * Count pages by tags
	 */
	$prep = "SELECT id, page_id FROM " . DB_PREFIX . "tags WHERE tag_name = '{$vavok->post_and_get('item')}'";
	foreach ($vavok->go('db')->query($prep) as $resultItem) {
		if (!in_array($resultItem['page_id'], $page_ids)) array_push($page_ids, $resultItem['page_id']);
	}

	/**
	 * Count pages by content
	 */
	$prep = "SELECT id, pname, tname, content, type FROM " . DB_PREFIX . "pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'";
	foreach($vavok->go('db')->query($prep) as $resultItem) {
		/**
		 * Add item if it is not added
		 */
		if (!in_array($resultItem['id'], $page_ids)) array_push($page_ids, $resultItem['id']);

	}

	$items = count($page_ids);
	unset($page_ids);

	/**
	 * Count forum posts
	 */
	if ($vavok->go('db')->table_exists(DB_PREFIX . 'ftopics')) {
		$items += $vavok->go('db')->count_row(DB_PREFIX . "ftopics", "name LIKE '%" . $itemLat . "%' OR name LIKE '%" . $itemCyr . "%' AND (closed = '' OR closed = 0)");
	}

	$thisPageNav = new Navigation(20, $items, $vavok->post_and_get('page'));

	$searchItems = array();
	$page_ids = array();

	/**
	 * Search tags
	 */
	$prep = "SELECT id, page_id FROM " . DB_PREFIX . "tags WHERE tag_name = '{$vavok->post_and_get('item')}' LIMIT {$thisPageNav->start()['start']}, 20";
	foreach ($vavok->go('db')->query($prep) as $resultItem) {
		/**
		 * Page data
		 */
		$resultItem = $vavok->go('db')->get_data(DB_PREFIX . 'pages', "id = '" . $resultItem['page_id'] . "'");
		$dots = strlen($resultItem['content']) > 50 ? '...' : '';

		$itemText = mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots;

		$setItem = new PageGen('search/item.tpl');

		if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
		else $type = 'blog';

		$setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '/">' . $resultItem['tname'] . '</a>');
		$setItem->set('itemText', $itemText);

		/**
		 * Add item if it is not added
		 */
		if (!in_array($resultItem['id'], $page_ids)) {
			array_push($page_ids, $resultItem['id']);

			array_push($searchItems, $setItem);
		}
	}

	/**
	 * Search pages
	 */
	$prep = "SELECT id, pname, tname, content, type FROM " . DB_PREFIX . "pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'" . $orderBy . "LIMIT {$thisPageNav->start()['start']}, 20";
	foreach ($vavok->go('db')->query($prep) as $resultItem) {
		$dots = strlen($resultItem['content']) > 50 ? '...' : '';

		$itemText = mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots;

		$setItem = new PageGen('search/item.tpl');

		if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
		else $type = 'blog';

		$setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '/">' . $resultItem['tname'] . '</a>');
		$setItem->set('itemText', $itemText);

		/**
		 * Add item if it is not added
		 */
		if (!in_array($resultItem['id'], $page_ids)) {
			array_push($searchItems, $setItem);
		}
	}

	/**
	 * Search forum posts
	 */
	if ($vavok->go('db')->table_exists(DB_PREFIX . 'ftopics')) {
		$preps = "SELECT `name`, `text`, `id` FROM " . DB_PREFIX . "ftopics WHERE name LIKE '%" . $itemLat . "%' OR name LIKE '%" . $itemCyr . "%' AND (closed = '' OR closed = 0) LIMIT {$thisPageNav->start()['start']}, 20";

		foreach($vavok->go('db')->query($preps) as $resultItems) {
			$dots = strlen($resultItems['text']) > 50 ? '...' : '';

			$itemText = mb_substr(strip_tags($resultItems['text']), 0, 50, 'UTF-8') . $dots;

			$setItem = new PageGen('search/item.tpl');

			$setItem->set('itemLink', '<a href="' . HOMEDIR . 'forum/viewtpc/' . $resultItems['id'] . '-' . $vavok->trans($resultItems['name']) . '/">' . $resultItems['name'] . '</a>');
			$setItem->set('itemText', $itemText);

			$searchItems[] = $setItem;
		}
	}

	if ($items < 1) {
		echo $resultsPage->output();

		echo '<p>No search results, try another phrase</p>';

		$vavok->require_footer();
		exit;
	}

	$resultsPage->set('allResults', $resultsPage->merge($searchItems));
	echo $resultsPage->output();

	$navigation = new Navigation(20, $items, $vavok->post_and_get('page'), "./");
	echo $navigation->get_navigation();
}

$vavok->require_footer();
?>