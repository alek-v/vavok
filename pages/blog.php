<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   29.08.2020. 2:58:50
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
		$total_comments = $comments->count_comments($current_page->page_id);

		// Show all comments
		if ($comments_per_page == 0) { $comments_per_page = $total_comments; }

		// Start navigation
		$navi = new Navigation($items_per_page, $total_comments, $page);

		$all_comments = $comments->load_comments($current_page->page_id, $navi->start()['start'], $comments_per_page);

		// merge blog comments and output from object
		$merge_all = PageGen::merge($all_comments);

		$show_comments = new PageGen('pages/blog/all_comments.tpl');

		if ($users->is_reg()) {
			$add_comment = new PageGen('pages/blog/add_comment.tpl');
			$add_comment->set('add_comment_page', HOMEDIR . 'pages/comments.php?action=save&amp;pid=' . $current_page->page_id . '&amp;ptl=' . CLEAN_REQUEST_URI);

			$post->set('add_comment', $add_comment->output());
		}

		$show_comments->set('all_comments', $merge_all);

		$post->set('comments', $show_comments->output());

		/**
		 * Page tags
		 */
		$sql = $db->query("SELECT * FROM " . DB_PREFIX . "tags WHERE page_id='{$current_page->page_id}' ORDER BY id ASC");
		$tags = '';

		foreach ($sql as $key => $value) {
			$tag_link = new PageGen('pages/blog/tag_link.tpl');
			$tag_link->set('tag_link', HOMEDIR . 'search/' . $value['tag_name'] . '/');
			$tag_link->set('tag_link_name', str_replace('_', ' ', $value['tag_name']));
			$tags .= $tag_link->output();
		}

		$post->set('tags', $tags);

		// page header
		$vavok->require_header();

		// show page content
		echo $post->output();

		// page footer
		$vavok->require_footer();

		break;
	
	default:
		// page title
		$current_page->page_title = 'Blog';
		
		// page header
		$vavok->require_header();

		// load index template
		$showPage = new PageGen("pages/blog/index.tpl");

		/**
		 * Count total posts
		 */
		$total_posts = $db->count_row('pages', "type='post' AND published = '2'");

		// if there is no posts
		if ($total_posts < 1) {
			echo '<p><img src="../images/img/reload.gif" alt="" /> There is nothing here</p>';

			// page footer
			$vavok->require_footer();

			break;
		}

		// start navigation
		$navi = new Navigation($items_per_page, $total_posts, $page);

		// get blog posts
		foreach ($db->query("SELECT * FROM pages WHERE type = 'post' AND published = '2' ORDER BY id DESC LIMIT {$navi->start()['start']}, {$items_per_page}") as $key) {
			// load template
			$page_posts = new PageGen('pages/blog/blog_post.tpl');
			$page_posts->set('post_name', '<a href="' . HOMEDIR . 'blog/' . $key['pname'] . '/">' . $key['tname'] . '</a>');

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
			$page_posts->set('date-created-month', mb_substr($localization->show_all()['ln_all_month'][date('n', $key['created']) - 1], 0, 3));

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
		$vavok->require_footer();

		break;
}

?>