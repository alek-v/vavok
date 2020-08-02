<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   02.08.2020. 2:21:29
*/

include"../include/startup.php";

$pg = isset($_GET['pg']) ? $vavok->check($_GET['pg']) : ''; // blog page

$page = isset($_GET['page']) ? $vavok->check($_GET['page']) : 1; // page number

$items_per_page = 5; // How many blog posts to show per page
$comments_per_page = 0; // How many comments to show per page

switch ($pg) {

	case isset($pg):

		// Get page id
		// Redirect to blog main page if page does not exist
		$page_id = !empty($current_page->page_id) or $vavok->redirect_to(HOMEDIR . 'blog/');

		// generate page
		$post = new PageGen('pages/blog/post.tpl');

		// Author
		// Author link
		$author_full_name = $users->get_user_info($current_page->page_author, 'full_name');
		$author_name = !empty($author_full_name) ? $author_full_name : $users->getnickfromid($current_page->page_author);

		$author_link = '<a href="' . HOMEDIR . 'pages/user.php?uz=' . $current_page->page_author . '">' . $author_name . '</a>';
		$post->set('author_link', $author_link);

		// Time created
		$post->set('created_date', $vavok->date_fixed($current_page->page_created_date, 'd.m.Y.'));

		// content
		$post->set('content', $vavok->getbbcode($current_page->page_content));

		// day created
		$post->set('date-created-day', date('d', $current_page->page_created_date));

		// month created
		$post->set('date-created-month', mb_substr($localization->show_all()['ln_all_month'][date('n', $current_page->page_created_date) - 1], 0, 3));

		// comments
		$comments = new Comments();

		// Number of comments
		$total_comments = $comments->count_comments($page_id);

		// Show all comments
		if ($comments_per_page == 0) { $comments_per_page = $total_comments; }

		// Start navigation
		$navi = new Navigation($items_per_page, $total_comments, $page);

		$all_comments = $comments->load_comments($page_id, $navi->start()['start'], $comments_per_page);

		// merge blog comments and output from object
		$merge_all = PageGen::merge($all_comments);

		$show_comments = new PageGen('pages/blog/all_comments.tpl');

		if ($users->is_reg()) {

			$add_comment = new PageGen('pages/blog/add_comment.tpl');
			$add_comment->set('add_comment_page', HOMEDIR . 'pages/comments.php?action=save&amp;pid=' . $page_id . '&amp;ptl=' . CLEAN_REQUEST_URI);

			$post->set('add_comment', $add_comment->output());

		}

		$show_comments->set('all_comments', $merge_all);

		$post->set('comments', $show_comments->output());

		// page header
		require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

		// show page content
		echo $post->output();

		// page footer
		require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

		break;
	
	default:

		// page title
		$current_page->page_title = 'Blog';
		
		// page header
		require_once BASEDIR . "themes/" . MY_THEME . "/index.php";

		// load index template
		$showPage = new PageGen("pages/blog/index.tpl");

		// page navigation
		$total_posts = $db->count_row('pages', "type='post'");


		// if there is no posts
		if ($total_posts < 1) {

			echo '<p><img src="../images/img/reload.gif" alt="" /> There is nothing here</p>';

			// page footer
			require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

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
				$content = $vavok->getbbcode(implode(' ', array_slice(explode(' ', str_replace('<br />', ' ', $key['content'])), 0, 120))) . '...';
			}

			// replace html headings
			$content = preg_replace('#<h([1-6])>(.*?)<\/h[1-6]>#si', '<h3>${2}</h3>', $content);

			// day created
			$page_posts->set('date-created-day', date('d', $key['created']));

			// month created
			$page_posts->set('date-created-month', mb_substr($ln_all_month[date('n', $key['created']) - 1], 0, 3));

			$page_posts->set('post_text', $content);
			$page_posts->set('read_more_link', HOMEDIR . 'blog/' . $key['pname'] . '/');
			$page_posts->set('read_more_title', $localization->string('readmore'));


			// blog post objects
			$all_posts[] = $page_posts;

		}

		// merge blog posts and output from object
		$merge_all = PageGen::merge($all_posts);

		// show page
		$showPage->set('posts', $merge_all);

		// page navigation
		$navigation = new Navigation($items_per_page, $total_posts, $page, './?');
		
		$showPage->set('navigation', $navigation->get_navigation());


		echo $showPage->output();


		// page footer
		require_once BASEDIR . "themes/" . MY_THEME . "/foot.php";

		break;

}




?>