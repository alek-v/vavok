<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

namespace App\Classes;
use Pimple\Container;

class BlogComments {
    protected object $container;
    protected object $db;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->db = $container['db'];
    }

    /**
     * Insert a new comment
     * 
     * @param string $content
     * @param int $pid
     * @return void
     */
    public function insert(string $content, int $pid): void
    {
        // Values to insert
        $values = array('uid' => $_SESSION['uid'], 'comment' => $content, 'pid' => $pid, 'date' => date('Y-m-d H:i:s'));

        // Insert data into the database
        $this->db->insert('comments', $values);
    }

    /**
     * Count number of comments
     * 
     * @param int $pid
     * @return int
     */
    public function countComments(int $pid): int
    {
        return $this->db->countRow('comments', 'pid=' . $pid);
    }

    /**
     * Load comments
     * 
     * @param int $pid
     * @param int $start
     * @param int $items_per_page
     * @return array
     */
    public function loadComments(int $pid, int $start, int $items_per_page): array
    {
        $this->user = $this->container['user'];
        $all_posts = array();

        $sql = "SELECT * FROM comments WHERE pid = {$pid} ORDER BY id DESC LIMIT {$start}, {$items_per_page}";
        foreach ($this->db->query($sql) as $key) {
            // Load template
            $page_posts = $this->container['parse_page'];
            $page_posts->load('blog/comment');

            // Link to the user's profile
            $page_posts->set('user', '<a href="' . HOMEDIR . 'users/u/' . $key['uid'] . '">' . $this->user->getNickFromId($key['uid']) . '</a>');

            // User's comment
            $content = $key['comment'];

            // Comment text
            $page_posts->set('text', $content);

            $full_date = explode(' ', $key['date']);
            $date = explode('-', $full_date[0]);
            $day = $date[2]; $month = $date[1]; $year = $date[0];

            $page_posts->set('time_added', $day . '.' . $month . '.' . $year . '.' . ' ' . $full_date[1]);

            // Array with a comments
            $all_posts[] = $page_posts;
        }

        return $all_posts;
    }
}