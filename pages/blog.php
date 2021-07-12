<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

require_once '../include/startup.php';

$pg = $vavok->post_and_get('pg'); // blog page

$items_per_page = 5; // How many blog posts to show per page
$comments_per_page = 0; // How many comments to show per page

switch ($vavok->post_and_get('pg')) {
	case isset($pg):
		// Get page id
		// Redirect to blog main page if page does not exist
		$page_id = !empty($vavok->go('current_page')->page_id) or $vavok->redirect_to(HOMEDIR . 'blog/');

		// generate page
		$post = new PageGen('pages/blog/post.tpl');

		// Author
		// Author link
		$author_full_name = $vavok->go('users')->get_user_info($vavok->go('current_page')->page_author, 'full_name');
		$author_name = !empty($author_full_name) ? $author_full_name : $vavok->go('users')->getnickfromid($vavok->go('current_page')->page_author);

		$author_link = '<a href="' . HOMEDIR . 'pages/user.php?uz=' . $vavok->go('current_page')->page_author . '">' . $author_name . '</a>';
		$post->set('author_link', $author_link);

		/**
		 * Time created
		 */
		$post->set('created_date', $vavok->date_fixed($vavok->go('current_page')->page_created_date, 'd.m.Y.'));

		/**
		 * Time published
		 * If article is not published and page is viewed by administrator use current time
		 */
		if (!empty($vavok->go('current_page')->page_published_date)) {
			$post->set('published_date', $vavok->date_fixed($vavok->go('current_page')->page_published_date, 'd.m.Y.'));
		} else {
			$post->set('published_date', $vavok->date_fixed(time(), 'd.m.Y.'));
		}

		/**
		 * Content
		 */
		$post->set('content', $vavok->getbbcode($vavok->go('current_page')->page_content));

		/**
		 * Day and month when post is created
		 */
		$post->set('date-created-day', date('d', $vavok->go('current_page')->page_created_date));
		$post->set('date-created-month', mb_substr($vavok->go('localization')->show_all()['ln_all_month'][date('n', $vavok->go('current_page')->page_created_date) - 1], 0, 3));

		/**
		 * Day and month when post is published
		 * If article is not published and page is viewed by administrator use current time
		 */
		if (!empty($vavok->go('current_page')->page_published_date)) {
			$post->set('date-published-day', date('d', $vavok->go('current_page')->page_published_date));
			$post->set('date-published-month', mb_substr($vavok->go('localization')->show_all()['ln_all_month'][date('n', $vavok->go('current_page')->page_published_date) - 1], 0, 3));
		} else {
			$post->set('date-published-day', date('d', time()));
			$post->set('date-published-month', mb_substr($vavok->go('localization')->show_all()['ln_all_month'][date('n', time()) - 1], 0, 3));
		}

		/**
		 * Page URL
		 */
		$post->set('page-link', $vavok->go('current_page')->media_page_url());

		// comments
		$comments = new Comments();

		// Number of comments
		$total_comments = $comments->count_comments($vavok->go('current_page')->page_id);

		// Show all comments
		if ($comments_per_page == 0) { $comments_per_page = $total_comments; }

		// Start navigation
		$navi = new Navigation($items_per_page, $total_comments, $vavok->post_and_get('page'));

		$all_comments = $comments->load_comments($vavok->go('current_page')->page_id, $navi->start()['start'], $comments_per_page);

		// merge blog comments and output from object
		$merge_all = PageGen::merge($all_comments);

		$show_comments = new PageGen('pages/blog/all_comments.tpl');

		if ($vavok->go('users')->is_reg()) {
			$add_comment = new PageGen('pages/blog/add_comment.tpl');
			$add_comment->set('add_comment_page', HOMEDIR . 'pages/comments.php?action=save&amp;pid=' . $vavok->go('current_page')->page_id . '&amp;ptl=' . CLEAN_REQUEST_URI);

			$post->set('add_comment', $add_comment->output());
		}

		$show_comments->set('all_comments', $merge_all);

		$post->set('comments', $show_comments->output());

		/**
		 * Page tags
		 */
		$sql = $vavok->go('db')->query("SELECT * FROM " . DB_PREFIX . "tags WHERE page_id='{$vavok->go('current_page')->page_id}' ORDER BY id ASC");

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
		/**
		 * Add meta tags
		 */
		$vavok->go('current_page')->append_head_tags('<meta name="robots" content="noindex, follow" />' . "\r\n");

		// page title
		$vavok->go('current_page')->page_title = 'Blog';
		
		// page header
		$vavok->require_header();

		// load index template
		$show_page = new PageGen("pages/blog/index.tpl");

		/**
		 * Count total posts
		 */
		$total_posts = empty($vavok->post_and_get('category')) ? $vavok->go('db')->count_row('pages', "type='post' AND published = '2'") : $vavok->go('db')->count_row('tags', "tag_name='{$vavok->post_and_get('category')}'");

		// if there is no posts
		if ($total_posts < 1) {
			echo '<p><img src="' . HOMEDIR . 'images/img/reload.gif" alt="Nothing here" /> There is nothing here</p>';
			echo '<p><a href="' . HOMEDIR . 'blog/" class="btn btn-primary sitelink">' . $vavok->go('localization')->string('backtoblog') . '</a></p>';

			// page footer
			$vavok->require_footer();

			break;
		}

		// start navigation
		$navi = new Navigation($items_per_page, $total_posts, $vavok->post_and_get('page'));

		/**
		 * Get blog category names
		 */
		foreach ($vavok->go('db')->query("SELECT * FROM settings WHERE setting_group = 'blog_category'") as $category) {
			$page_category = new PageGen('pages/blog/blog_category.tpl');

			/**
			 * Set category name
			 */
			$page_category->set('category_name', $category['setting_name']);

			/**
			 * Set category link
			 */
			$page_category->set('category_link', HOMEDIR . 'blog/category/' . $category['value'] . '/');

			$all_categories[] = $page_category;
		}

		/**
		 * Don't show <div> with categories if there is no category
		 */
		if (empty($all_categories)) $show_page->set('bc_style_options', ' display-none');

		/**
		 * Merge categories and output from object
		 */
		if (isset($all_categories)) $show_page->set('category-list', PageGen::merge($all_categories));

		/**
		 * Get blog posts
		 */

		/**
		 * Query when category is not set and when it is set
		 */		
		$page_sql_query = empty($vavok->post_and_get('category')) ? "SELECT * FROM pages WHERE type = 'post' AND published = '2' ORDER BY pubdate DESC LIMIT {$navi->start()['start']}, {$items_per_page}" : 
		"SELECT pages.pubdate, pages.created, pages.tname, pages.pname, pages.content FROM pages INNER JOIN tags ON tags.tag_name='{$vavok->post_and_get('category')}' AND tags.page_id = pages.id  ORDER BY pubdate DESC LIMIT {$navi->start()['start']}, {$items_per_page}";

		foreach ($vavok->go('db')->query($page_sql_query) as $key) {
			// load template
			$page_posts = new PageGen('pages/blog/blog_post.tpl');

			$page_posts->set('post_name', '<a href="' . HOMEDIR . 'blog/' . $key['pname'] . '/">' . $key['tname'] . '</a>');

			$content = $key['content'];

			// length of blog text
			$content_length = mb_strlen($key['content']);

			// if there is more then 45 words
			if (count(explode(' ', $key['content'])) > 45) {
				// show first 45 words
				$content = $vavok->getbbcode(implode(' ', array_slice(explode(' ', str_replace('<br />', ' ', $key['content'])), 0, 45))) . '...';
			}

			// replace html headings
			$content = preg_replace('#<h([1-6])>(.*?)<\/h[1-6]>#si', '', $content);

			/**
			 * Day and month created
			 */
			$page_posts->set('date-created-day', date('d', $key['created']));
			$page_posts->set('date-created-month', mb_substr($vavok->go('localization')->show_all()['ln_all_month'][date('n', $key['created']) - 1], 0, 3));

			/**
			 * Day and month when article is published
			 */
			$page_posts->set('date-published-day', date('d', $key['pubdate']));
			$page_posts->set('date-published-month', mb_substr($vavok->go('localization')->show_all()['ln_all_month'][date('n', $key['pubdate']) - 1], 0, 3));

			/**
			 * Page URL
			 */
			$page_posts->set('page-link', $vavok->go('current_page')->media_page_url());

			$page_posts->set('post_text', $content);
			$page_posts->set('read_more_link', HOMEDIR . 'blog/' . $key['pname'] . '/');
			$page_posts->set('read_more_title', $vavok->go('localization')->string('readmore'));

			// blog post objects
			$all_posts[] = $page_posts;
		}

		/**
		 * Merge blog posts and output from object
		 */
		$show_page->set('posts', PageGen::merge($all_posts));

		// page navigation
		$navigation = new Navigation($items_per_page, $total_posts, $vavok->post_and_get('page'), './?');
		
		$show_page->set('navigation', $navigation->get_navigation());

		echo $show_page->output();

		// page footer
		$vavok->require_footer();

		break;
}

?>