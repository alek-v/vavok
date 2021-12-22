<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class BlogModel extends BaseModel {
    protected object $current_page;
	protected $page_name;             // Page name
	protected $page_language;         // Page language
	protected $page_title;            // Title
	protected $page_content;          // Content
	protected $published;             // Visitors can see page
	protected $page_author;           // Page author
	protected $page_created_date;     // Page created date
	protected $head_tags;             // Head tags
	protected $page_published_date;   // Date when post is published

	/**
	 * Blog
	 * 
	 * @param array $params
	 * @return array
	 */
	public function index($params = [])
	{
        // Instantiate model
        $this->current_page = $this->model('Pagemanager');

        // Users data
		$this_page['user'] = $this->user_data;

        // Content of the page
        $this_page['content'] = '';

        // Blog category
        $pg = isset($params[0]) && $params[0] !== 'category' ? $params[0] : ''; // blog page

        $items_per_page = 9; // How many blog posts to show per page
        $comments_per_page = 0; // How many comments to show per page

        switch ($pg) {
            case isset($pg):
                // Load page data and merge with existing data
                if (isset($params) && is_array($params)) $this_page = array_merge($this->blog_page_data($params), $this_page);

                // Redirect to blog main page if page does not exist
                $page_id = !empty($this->page_id) or $this->redirection(HOMEDIR . 'blog/');
        
                // generate page
                $post = $this->model('ParsePage');
                $post->load('blog/post');

                // Author
                // Author link
                $author_full_name = $this->user->user_info('full_name', $this->page_author);
                $author_name = !empty($author_full_name) ? $author_full_name : $this->user->getnickfromid($this->page_author);
        
                $author_link = '<a href="' . HOMEDIR . 'users/u/' . $this->page_author . '">' . $author_name . '</a>';
                $post->set('author_link', $author_link);
        
                /**
                 * Time created
                 */
                $post->set('created_date', $this->correctDate($this->page_created_date, 'd.m.Y.'));
        
                /**
                 * Time published
                 * If article is not published and page is viewed by administrator use current time
                 */
                if (!empty($this->page_published_date)) {
                    $post->set('published_date', $this->correctDate($this->page_published_date, 'd.m.Y.'));
                } else {
                    $post->set('published_date', $this->correctDate(time(), 'd.m.Y.'));
                }
        
                // Date updated
                $post->set('date_updated', $this->correctDate($this->page_updated_date, 'd.m.Y.'));
        
                /**
                 * Content
                 */
                $post->set('content', $this->getbbcode($this->page_content));
        
                /**
                 * Day and month when post is created
                 */
                $post->set('date-created-day', date('d', $this->page_created_date));
                $post->set('date-created-month', mb_substr($this->localization->show_all()['ln_all_month'][date('n', $this->page_created_date) - 1], 0, 3));

                /**
                 * Day and month when post is published
                 * If article is not published and page is viewed by administrator use current time
                 */
                if (!empty($this->page_published_date)) {
                    $post->set('date-published-day', date('d', $this->page_published_date));
                    $post->set('date-published-month', mb_substr($this->localization->show_all()['ln_all_month'][date('n', $this->page_published_date) - 1], 0, 3));
                } else {
                    $post->set('date-published-day', date('d', time()));
                    $post->set('date-published-month', mb_substr($this->localization->show_all()['ln_all_month'][date('n', time()) - 1], 0, 3));
                }
        
                /**
                 * Page URL
                 */
                $post->set('page-link', $this->current_page->media_page_url());

                // comments
                $comments = $this->model('Comments');

                // Number of comments
                $total_comments = $comments->count_comments($this->page_id);

                // Show all comments
                if ($comments_per_page == 0) $comments_per_page = $total_comments;

                // Start navigation
                $navi = new Navigation($items_per_page, $total_comments, $this->post_and_get('page'));
        
                $all_comments = $comments->load_comments($this->page_id, $navi->start()['start'], $comments_per_page, $this->user);

                // merge blog comments and output from object
                $merge_all = $post->merge($all_comments);
        
                $show_comments = $this->model('ParsePage');
                $show_comments->load('blog/all_comments');
        
                if ($this->user->userAuthenticated()) {
                    $add_comment = $this->model('ParsePage');
                    $add_comment->load('blog/add_comment');
                    $add_comment->set('add_comment_page', HOMEDIR . 'blog/save_comment/?pid=' . $this->page_id . '&amp;ptl=' . CLEAN_REQUEST_URI);
        
                    $post->set('add_comment', $add_comment->output());
                }
        
                $show_comments->set('all_comments', $merge_all);
        
                $post->set('comments', $show_comments->output());
        
                // Page tags
                $sql = $this->db->query("SELECT * FROM tags WHERE page_id='{$this->page_id}' ORDER BY id ASC");
        
                $tags = '';
        
                foreach ($sql as $key => $value) {
                    $tag_link = $this->model('ParsePage');
                    $tag_link->load('blog/tag_link');
                    $tag_link->set('tag_link', HOMEDIR . 'search/' . $value['tag_name'] . '/');
                    $tag_link->set('tag_link_name', str_replace('_', ' ', $value['tag_name']));
                    $tags .= $tag_link->output();
                }
        
                $post->set('tags', $tags);

                // Page content
                $this_page['content'] .= $post->output();

                return $this_page;

                break;
            
            default:
                $this_category = isset($params[0]) && isset($params[1]) && $params[0] == 'category' ? $params[1] : '';

                // Add meta tags
                $this_page['headt'] = '<meta name="robots" content="noindex, follow" />' . "\r\n";
        
                // Page title
                $this_page['tname'] = 'Blog';
        
                // Load index template
                $show_page = $this->model('ParsePage');
                $show_page->load("blog/index");
        
                // Count total posts
                $total_posts = empty($this_category) ? $this->db->count_row('pages', "type='post' AND published = '2'") : $this->db->count_row('tags', "tag_name='{$this_category}'");

                // When there is no posts
                if ($total_posts < 1) {
                    $this_page['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Nothing here" /> There is nothing here</p>';
                    $this_page['content'] .= $this->sitelink( HOMEDIR . 'blog/', $this->localization->string('backtoblog'), '<p>', '</p>');

                    return $this_page;
                    break;
                }

                // start navigation
                $navi = new Navigation($items_per_page, $total_posts, $this->post_and_get('page'));
        
                /**
                 * Get blog category names
                 */
                foreach ($this->db->query("SELECT * FROM settings WHERE setting_group = 'blog_category' ORDER BY options") as $category) {
                    $page_category = $this->model('ParsePage');
                    $page_category->load('blog/blog_category');
        
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
                if (isset($all_categories)) $show_page->set('category-list', $page_category->merge($all_categories));

                /**
                 * Get blog posts
                 */
        
                // Query when category is not set and case when it is set
                $page_sql_query = empty($this_category) ? "SELECT * FROM pages WHERE type = 'post' AND published = '2' ORDER BY pubdate DESC LIMIT {$navi->start()['start']}, {$items_per_page}" : 
                "SELECT pages.pubdate, pages.created, pages.tname, pages.pname, pages.content, pages.default_img FROM pages INNER JOIN tags ON tags.tag_name='{$this_category}' AND tags.page_id = pages.id AND pages.published = '2' ORDER BY pubdate DESC LIMIT {$navi->start()['start']}, {$items_per_page}";
        
                foreach ($this->db->query($page_sql_query) as $key) {
                    // load template
                    $page_posts = $this->model('ParsePage');
                    $page_posts->load('blog/blog_post');
        
                    // Linked title
                    $page_posts->set('post_name', '<a href="' . HOMEDIR . 'blog/' . $key['pname'] . '">' . $key['tname'] . '</a>');
        
                    // Replace html headings, images and other tags from content
                    $content = strip_tags($this->erase_img(preg_replace('#<h([1-6])>(.*?)<\/h[1-6]>#si', '', $key['content'])));
        
                    // When there is more then 45 words show only first 45 words
                    if (count(explode(' ', $content)) > 45) $content = implode(' ', array_slice(explode(' ', str_replace('<br />', ' ', $content)), 0, 45)) . '...';
        
                    // Day when article is created
                    $page_posts->set('date-created-day', date('d', $key['created']));
        
                    // Month when article is created
                    $page_posts->set('date-created-month', $this->localization->show_all()['ln_all_month'][date('n', $key['created']) - 1]);
        
                    // Day when article is published
                    $page_posts->set('date-published-day', date('d', $key['pubdate']));
        
                    // Month when article is published
                    $page_posts->set('date-published-month', $this->localization->show_all()['ln_all_month'][date('n', $key['pubdate']) - 1]);
        
                    // Year when article is published
                    $page_posts->set('date-published-year', date('Y', $key['pubdate']));
        
                    // Page URL
                    $page_posts->set('page-link', $this->current_page->media_page_url());
        
                    // Page text
                    $page_posts->set('post_text', $content);
        
                    // Read more link
                    $page_posts->set('read_more_link', HOMEDIR . 'blog/' . $key['pname']);
        
                    // Read more title
                    $page_posts->set('read_more_title', $this->localization->string('readmore'));
        
                    // Cover image
                    $page_posts->set('page_image', $key['default_img']);
        
                    // blog post objects
                    $all_posts[] = $page_posts;
                }
        
                // Merge blog posts and output from object
                $show_page->set('posts', $page_posts->merge($all_posts));
        
                // page navigation
                $navigation = new Navigation($items_per_page, $total_posts, $this->post_and_get('page'), './?');
                
                $show_page->set('navigation', $navigation->get_navigation());
        
                $this_page['content'] .= $show_page->output();
        
                return $this_page;

                break;
        }
    }

	/**
	 * Blog
	 * 
	 * @param array $params
	 * @return array
	 */
	public function save_comment()
	{
        // Users data
		$this_page['user'] = $this->user_data;

        // Content of the page
        $this_page['content'] = '';

        $ptl = ltrim($this->check($this->post_and_get('ptl')), '/'); // Return page

        // In case data is missing
        if ($this->user->userAuthenticated() && (empty($this->post_and_get('comment')) || empty($this->post_and_get('pid')))) { $this->redirection(HOMEDIR . $ptl . '?isset=msgshort'); }

        $comments = $this->model('Comments');

        // Insert data to database
        $comments->insert($this->post_and_get('comment'), $this->post_and_get('pid'));

        // Saved, return to page
        $this->redirection(HOMEDIR . $ptl . '?isset=savedok');
    }

	/**
	 * Load page
	 * 
	 * @param array $params
	 */
	protected function blog_page_data($params = [])
	{
		// Fetch page
		$page_data = $this->db->get_data('pages', "pname='" . $params[0] . "'");

		// return false if there is no data
		if (empty($page_data['tname']) && empty($page_data['content'])) {
			return false;
		} else {
			// Update page title
			$this->page_title = $page_data['tname'];

			// Update language
			if (!empty($page_data['lang']) && !defined('PAGE_LANGUAGE')) define('PAGE_LANGUAGE', ' lang="' . $page_data['lang'] . '"');

			// Page content
			$this->page_content = $page_data['content'];

			// Published status
			$this->published = $page_data['published'];

			// Page id
			$this->page_id = $page_data['id'];

			// Author
			$this->page_author = $page_data['crtdby'];

			// Date created
			$this->page_created_date = $page_data['created'];

			// Head tags
			$this->head_tags = $page_data['headt'];

			// Published date
			$this->page_published_date = $page_data['pubdate'];

			// Published date
			$this->page_updated_date = $page_data['lastupd'];

			// Page views
			$this->views = !empty($page_data['views']) ? $page_data['views'] : 0;

			return $page_data;
		}
	}
}