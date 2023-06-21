<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\BlogComments;

class BlogModel extends BaseModel {
    private int $page_id;
    private int $views;
    private int $published;
    private string $page_title;
    private string $page_author;
    private string $page_created_date;
    private string $page_updated_date;
    private string $head_tags;
    private string $page_published_date;
    private ?string $page_content;
    private ?string $thumbnail;

    /**
     * Blog
     * 
     * @param array $params
     * @return array
     */
    public function index(array $params = []): array
    {
        // Blog page
        $pg = isset($params[0]) && $params[0] !== 'category' && strlen($params[0]) != 2 ? $params[0] : '';

        // Blog posts to show per page
        $items_per_page = 9;

        // Comments to show per page, 0 = unlimited
        $comments_per_page = 0;

        switch ($pg) {
            case isset($pg):
                // Page data
                $blog_page_data = $this->getBlogPageData($params);

                // Merge data with existing data
                if (!empty($blog_page_data) && is_array($blog_page_data)) {
                    $this->page_data = array_merge($blog_page_data, $this->page_data);
                }

                // Redirect to blog main page if page does not exist
                if (empty($this->page_id)) {
                    return $this->handleNoPageError();
                }

                // Update user's localization when page's language is different from current localization
                $this->user->updatePageLocalization($this->page_data['localization']);

                // Generate page
                $post = $this->container['parse_page'];
                $post->load('blog/post');

                // Author link
                $author_full_name = $this->user->userInfo('full_name', $this->page_author);
                $author_name = !empty($author_full_name) ? $author_full_name : $this->user->getNickFromId($this->page_author);

                $author_link = '<a href="' . HOMEDIR . 'users/u/' . $this->page_author . '">' . $author_name . '</a>';
                $post->set('author_link', $author_link);

                // Time created
                $post->set('created_date', $this->correctDate($this->page_created_date, $this->localization->showAll()['date_format']));

                // Time published
                // Use current time if article is not published, and page is viewed by administrator
                if (!empty($this->page_published_date)) {
                    $post->set('published_date', $this->correctDate($this->page_published_date, $this->localization->showAll()['date_format']));
                } else {
                    $post->set('published_date', $this->correctDate(time(), $this->localization->showAll()['date_format']));
                }

                // Date updated
                $post->set('date_updated', $this->correctDate($this->page_updated_date, $this->localization->showAll()['date_format']));

                // Content
                $post->set('content', $this->page_content);

                // Thumbnail
                $post->set('thumbnail', $this->thumbnail);

                // Day and month when post is created
                $post->set('date-created-day', date('d', $this->page_created_date));
                $post->set('date-created-month', mb_substr($this->localization->showAll()['ln_all_month'][date('n', $this->page_created_date) - 1], 0, 3));

                // Day and month when post is published
                // If article is not published and page is viewed by administrator use current time
                if (!empty($this->page_published_date)) {
                    $post->set('date-published-day', date('d', $this->page_published_date));
                    $post->set('date-published-month', mb_substr($this->localization->showAll()['ln_all_month'][date('n', $this->page_published_date) - 1], 0, 3));
                } else {
                    $post->set('date-published-day', date('d', time()));
                    $post->set('date-published-month', mb_substr($this->localization->showAll()['ln_all_month'][date('n', time()) - 1], 0, 3));
                }

                // Page URL
                $post->set('page-link', $this->cleanPageUrl());

                // comments
                $comments = new BlogComments($this->container);

                // Number of comments
                $total_comments = $comments->countComments($this->page_id);

                // Show all comments
                if ($comments_per_page == 0) {
                    $comments_per_page = $total_comments;
                }

                // Start navigation
                $navi = new Navigation($items_per_page, $total_comments);

                $all_comments = $comments->loadComments($this->page_id, $navi->start()['start'], $comments_per_page);

                // merge blog comments and output from object
                $merge_all = $post->merge($all_comments);

                $show_comments = $this->container['parse_page'];
                $show_comments->load('blog/all_comments');

                if ($this->user->userAuthenticated()) {
                    $add_comment = $this->container['parse_page'];
                    $add_comment->load('blog/add_comment');
                    $add_comment->set('add_comment_page', HOMEDIR . 'blog/save_comment/?pid=' . $this->page_id . '&ptl=' . CLEAN_REQUEST_URI);

                    $post->set('add_comment', $add_comment->output());
                }

                $show_comments->set('all_comments', $merge_all);

                $post->set('comments', $show_comments->output());

                // Page tags
                $sql = $this->db->query("SELECT * FROM tags WHERE page_id='{$this->page_id}' ORDER BY id ASC");

                $tags = '';

                foreach ($sql as $key => $value) {
                    $tag_link = $this->container['parse_page'];
                    $tag_link->load('blog/tag_link');
                    $tag_link->set('tag_link', HOMEDIR . 'search/' . $value['tag_name'] . '/');
                    $tag_link->set('tag_link_name', str_replace('_', ' ', $value['tag_name']));
                    $tags .= $tag_link->output();
                }

                $post->set('tags', $tags);

                // Page content
                $this->page_data['content'] .= $post->output();

                return $this->page_data;

            default:
                $this_category = isset($params[0]) && isset($params[1]) && $params[0] == 'category' ? $params[1] : '';

                // Page title
                $this->page_data['page_title'] = !empty($this_category) ? '{@localization[category]}}: ' . $this_category : 'Blog';

                // Load index template
                $show_page = $this->container['parse_page'];
                $show_page->load('blog/index');

                // User's localization short code
                // We will use it to get pages with this localization
                $user_localization_short = isset($params[0]) && strlen($params[0]) == 2 ? $params[0] : $this->user->getPreferredLanguage($this->user->user_data['language'], 'short');

                // Update user's localization when page's language is different from current localization
                $this->user->updatePageLocalization($user_localization_short);

                // Count total posts
                $total_posts = empty($this_category) ? $this->db->countRow('pages', "type='post' AND published_status = '2' AND (localization = '{$user_localization_short}' OR localization='')") : $this->db->countRow('tags', "tag_name='{$this_category}'");

                // When there is no posts
                if ($total_posts < 1) {
                    $this->page_data['content'] .= '<p><img src="' . HOMEDIR . 'themes/images/img/reload.gif" alt="Nothing here" /> There is no blog posts yet.</p>';

                    return $this->page_data;
                }

                // start navigation
                $navi = new Navigation($items_per_page, $total_posts);

                // Get blog category names
                foreach ($this->db->query("SELECT * FROM settings WHERE setting_group = 'blog_category' OR setting_group = 'blog_category_{$user_localization_short}' ORDER BY options") as $category) {
                    $page_category = $this->container['parse_page'];
                    $page_category->load('blog/blog_category');
        
                    // Set category name
                    $page_category->set('category_name', $category['setting_name']);
        
                    // Set category link
                    $page_category->set('category_link', HOMEDIR . 'blog/category/' . $category['value'] . '/');
        
                    $all_categories[] = $page_category;
                }

                // Hide <div> with categories if there is no category
                if (empty($all_categories)) {
                    $show_page->set('bc_style_options', ' display-none');
                }

                // Merge categories and output from object
                if (isset($all_categories)) {
                    $show_page->set('category-list', $page_category->merge($all_categories));
                }

                // Get blog posts
                $page_sql_query = empty($this_category) ? "SELECT * FROM pages WHERE type = 'post' AND published_status = '2' AND (localization = '{$user_localization_short}' OR localization = '') ORDER BY date_published DESC LIMIT {$navi->start()['start']}, {$items_per_page}" : 
                "SELECT pages.date_published, pages.date_created, pages.page_title, pages.slug, pages.content, pages.default_img, pages.thumbnail FROM pages INNER JOIN tags ON tags.tag_name = '{$this_category}' AND tags.page_id = pages.id AND pages.published_status = '2' ORDER BY date_published DESC LIMIT {$navi->start()['start']}, {$items_per_page}";
        
                foreach ($this->db->query($page_sql_query) as $key) {
                    // Load template
                    $page_posts = $this->container['parse_page'];
                    $page_posts->load('blog/blog_post');

                    // Linked title
                    $page_posts->set('post_name', '<a href="' . HOMEDIR . 'blog/' . $key['slug'] . '">' . $key['page_title'] . '</a>');

                    // Replace html headings, images and other tags from content
                    $content = !empty($key['content']) ? strip_tags($this->eraseImage(preg_replace('#<h([1-6])>(.*?)<\/h[1-6]>#si', '', $key['content']))) : '';

                    // When there is more then 45 words show only first 45 words
                    if (count(explode(' ', $content)) > 45) {
                        $content = implode(' ', array_slice(explode(' ', str_replace('<br />', ' ', $content)), 0, 45)) . '...';
                    }

                    // Day when article is created
                    $page_posts->set('date-created-day', date('d', $key['date_created']));

                    // Month when article is created
                    $page_posts->set('date-created-month', $this->localization->showAll()['ln_all_month'][date('n', $key['date_created']) - 1]);

                    // Day when article is published
                    $page_posts->set('date-published-day', date('d', $key['date_published']));

                    // Month when article is published
                    $page_posts->set('date-published-month', $this->localization->showAll()['ln_all_month'][date('n', $key['date_published']) - 1]);

                    // Year when article is published
                    $page_posts->set('date-published-year', date('Y', $key['date_published']));

                    // Page URL
                    $page_posts->set('page-link', $this->cleanPageUrl());

                    // Page text
                    $page_posts->set('post_text', $content);

                    // Read more link
                    $page_posts->set('read_more_link', HOMEDIR . 'blog/' . $key['slug']);

                    // Cover image
                    $page_posts->set('page_image', $key['default_img']);

                    // Thumbnail
                    $page_posts->set('thumbnail', $key['thumbnail']);

                    // Blog post objects
                    $all_posts[] = $page_posts;
                }

                // Merge blog posts and output from object
                $show_page->set('posts', $page_posts->merge($all_posts));

                $navigation_link = './?';

                // Handle case when URI doesn't end with '/' sign
                if (!str_ends_with($_SERVER['REQUEST_URI'], '/') && !isset($_GET['page'])) {
                    $navigation_link = $_SERVER['REQUEST_URI'] . '/?';
                }

                // Navigation
                $navigation = new Navigation($items_per_page, $total_posts, $navigation_link);

                $show_page->set('navigation', $navigation->getNavigation());

                $this->page_data['content'] .= $show_page->output();

                return $this->page_data;
        }
    }

    /**
     * Save comment
     */
    public function save_comment()
    {
        // Return page
        $ptl = ltrim($this->check($this->postAndGet('ptl')), '/');

        // In case data is missing
        if ($this->user->userAuthenticated() && (empty($this->postAndGet('comment')) || empty($this->postAndGet('pid')))) {
            $this->redirection(HOMEDIR . $ptl . '?isset=msgshort');
        }

        $comments = new BlogComments($this->container);

        // Insert data to database
        $comments->insert($this->postAndGet('comment'), $this->postAndGet('pid'));

        // Saved, return to page
        $this->redirection(HOMEDIR . $ptl . '?isset=savedok');
    }

    /**
     * Load page
     * 
     * @param array $params
     * @return bool|array
     */
    protected function getBlogPageData($params = []): bool|array
    {
        // Fetch page
        $page_data = $this->db->selectData('pages', 'slug = :param', array(':param' => $params[0]));

        // Return false if there is no data
        if (empty($page_data['page_title']) && empty($page_data['content'])) {
            return false;
        }

        // Localization
        if (!empty($page_data['localization']) && !defined('PAGE_LANGUAGE')) {
            define('PAGE_LANGUAGE', " lang=\"{$page_data['localization']}\"");
        }

        $this->page_title = $page_data['page_title'];
        $this->page_content = $page_data['content'];
        $this->published = $page_data['published_status'];
        $this->page_id = $page_data['id'];
        $this->page_author = $page_data['created_by'];
        $this->page_created_date = $page_data['date_created'];
        $this->head_tags = $page_data['head_tags'];
        $this->page_published_date = $page_data['date_published'];
        $this->page_updated_date = $page_data['date_updated'];
        $this->views = !empty($page_data['views']) ? $page_data['views'] : 0;
        $this->thumbnail = $page_data['thumbnail'];

        return $page_data;
    }
}