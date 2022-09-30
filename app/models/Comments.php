<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 */

use App\Classes\Controller;
use App\Classes\Database;

class Comments extends Controller {
    protected object $db;
    protected object $user;

    function __construct()
    {
        $this->db = Database::instance();
    }

    // Insert new comment
    public function insert($content, $pid) {
        // Values to insert
        $values = array('uid' => $_SESSION['uid'], 'comment' => $content, 'pid' => $pid, 'date' => date('Y-m-d H:i:s'));

        // Insert data to database
        $this->db->insert('comments', $values);
    }

    // Count number of comments
    public function count_comments($pid)
    {
        return $this->db->countRow('comments', 'pid=' . $pid);
    }

    // Load comments
    public function load_comments($pid, $start, $items_per_page, $user) {
        $this->user = $user;
        $all_posts = array();

        foreach ($this->db->query("SELECT * FROM comments WHERE pid = {$pid} ORDER BY id DESC LIMIT {$start}, {$items_per_page}") as $key) {
            // load template
            $page_posts = $this->model('ParsePage');
            $page_posts->load('blog/comment');

            $page_posts->set('user', '<a href="' . HOMEDIR . 'users/u/' . $key['uid'] . '">' . $this->user->getnickfromid($key['uid']) . '</a>');

            // User's comment
            $content = $key['comment'];

            $page_posts->set('text', $content); // comment text

            $full_date = explode(' ', $key['date']);
            $date = explode('-', $full_date[0]);
            $day = $date[2]; $month = $date[1]; $year = $date[0];

            $page_posts->set('time_added', $day . '.' . $month . '.' . $year . '.' . ' ' . $full_date[1]);

            // Blog posts
            $all_posts[] = $page_posts;
        }

        return $all_posts;
    }
}