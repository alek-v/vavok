<?php
// (c) Aleksandar Vranešević - vavok.net
// updated: 18.04.2020. 19:54:14

include"../include/strtup.php";


$pg = isset($_GET['pg']) ? check($_GET['pg']) : ''; // blog page

$page = isset($_GET['page']) ? check($_GET['page']) : 1; // page number

$items_per_page = 5; // how many blog posts to show per page



switch ($pg) {
	case isset($pg):
		
		// page data management
		$blog = new Page;

		// get page id
		$page_id = $blog->get_page_id("pname = '{$pg}'");

		// select page from id
		$post_data = $blog->select_page($page_id);

		// generate page
		$post = new PageGen('pages/blog/post.tpl');

		// content
		$post->set('content', getbbcode($post_data['content']));

		// back link
		$post->set('back', getbbcode($lang_home['back']));

		// day created
		$post->set('date-created-day', date('d', $post_data['created']));

		// month created
		$post->set('date-created-month', mb_substr($ln_all_month[date('n', $post_data['created']) - 1], 0, 3));

		// page title
		$my_title = $post_data['tname'];

		// page header
		include"../themes/" . $config_themes . "/index.php";

		// show page content
		echo $post->output();

		// page footer
		include"../themes/" . $config_themes . "/foot.php";

		break;
	
	default:

		// page title
		$my_title = 'Blog';
		
		// page header
		include"../themes/" . $config_themes . "/index.php";

		// load index template
		$showPage = new PageGen("pages/blog/index.tpl");

		// page navigation
		$total_posts = $db->count_row('pages', "type='post'");


		// if there is no posts
		if ($total_posts < 1) {

			echo '<p><img src="../images/img/reload.gif" alt="" /> There is nothing here</p>';

			// page footer
			include"../themes/" . $config_themes . "/foot.php";

			break;

		}

		// start navigation
		$navi = new Navigation($items_per_page, $total_posts, $page);

		// get blog posts
		foreach ($db->query("SELECT * FROM pages WHERE type = 'post' ORDER BY id DESC LIMIT {$navi->start()['start']}, {$items_per_page}") as $key) {

			// load template
			$page_posts = new PageGen('pages/blog/blog_post.tpl');
			$page_posts->set('post_name', '<a href="' . $key['pname'] . '/">' . $key['tname'] . '</a>');


			$content = $key['content'];

			// length of blog text
			$content_length = mb_strlen($key['content']);

			// if there is more then 120 words
			if (count(explode(' ', $key['content'])) > 120) {
				// show first 120 words
				$content = getbbcode(implode(' ', array_slice(explode(' ', $key['content']), 0, 120))) . '...';
			}

			// replace html headings
			$content = preg_replace("/<h[1-2]>(.*)<\/h[1-2]>/i", '<h3>$1</h3>', $content);

			// day created
			$page_posts->set('date-created-day', date('d', $key['created']));

			// month created
			$page_posts->set('date-created-month', mb_substr($ln_all_month[date('n', $key['created']) - 1], 0, 3));

			$page_posts->set('post_text', $content);
			$page_posts->set('read_more_link', HOMEDIR . 'blog/' . $key['pname'] . '/');
			$page_posts->set('read_more_title', $lang_home['readmore']);


			// blog post objects
			$all_posts[] = $page_posts;

		}

		// merge blog posts and output from object
		$merge_all = PageGen::merge($all_posts);

		// show page
		$showPage->set('posts', $merge_all);

		// page navigation
		$showPage->set('navigation', Navigation::numbNavigation('./?', $items_per_page, $page, $total_posts));


		echo $showPage->output();


		// page footer
		include"../themes/" . $config_themes . "/foot.php";

		break;
}




?>