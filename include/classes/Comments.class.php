<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Updated:   02.09.2020. 22:22:36
 */

class Comments {
	private $vavok;

	// class constructor
	function __construct() {
		global $vavok;

		$this->vavok = $vavok;
		$this->user_id = $this->vavok->go('users')->user_id; // user id with active login
	}

	// Insert new comment
	public function insert($content, $pid) {
		// Values to insert
		$values = array('uid' => $this->user_id, 'comment' => $content, 'pid' => $pid, 'date' => date('Y-m-d H:i:s'));

		// Insert data to database
		$this->vavok->go('db')->insert(DB_PREFIX . 'comments', $values);

	}

	// count number of comments
	public function count_comments($pid)
	{
		return $this->vavok->go('db')->count_row(DB_PREFIX . 'comments', 'pid=' . $pid);
	}

	// load comments
	public function load_comments($pid, $start, $items_per_page) {
		$all_posts = array();

		foreach ($this->vavok->go('db')->query("SELECT * FROM " . DB_PREFIX . "comments WHERE pid = {$pid} ORDER BY id DESC LIMIT {$start}, {$items_per_page}") as $key) {
			// load template
			$page_posts = new PageGen('pages/blog/comment.tpl');

			$page_posts->set('user', '<a href="' . HOMEDIR . 'pages/user.php?uz=' . $key['uid'] . '">' . $this->vavok->go('users')->getnickfromid($key['uid']) . '</a>');

			// User's comment
			$content = $key['comment'];

			$page_posts->set('text', $content); // comment text

			$full_date = explode(' ', $key['date']);
			$date = explode('-', $full_date[0]);
			$day = $date[2]; $month = $date[1]; $year = $date[0];

			$page_posts->set('time_added', $day . '.' . $month . '.' . $year . '.' . ' ' . $full_date[1]);

			// blog post objects
			$all_posts[] = $page_posts;
		}
		return $all_posts;
	}
}


?>