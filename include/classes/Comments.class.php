<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   29.07.2020. 19:48:23
*/

class Comments {

	// class constructor
	function __construct() {
		global $db, $users;

		$this->db = $db; // database
		$this->user_id = $users->user_id; // user id with active login
		$this->users = $users;
	}

	// Insert new comment
	public function insert($content, $pid) {

		// Values to insert
		$values = array('uid' => $this->user_id, 'comment' => $content, 'pid' => $pid, 'date' => date('Y-m-d H:i:s'));

		// Insert data to database
		$this->db->insert_data(DB_PREFIX . 'comments', $values);

	}

	// count number of comments
	public function count_comments($pid) {

		return $this->db->count_row(DB_PREFIX . 'comments', 'pid=' . $pid);

	}

	// load comments
	public function load_comments($pid, $start, $items_per_page) {

		$all_posts = array();

		foreach ($this->db->query("SELECT * FROM " . DB_PREFIX . "comments WHERE pid = {$pid} ORDER BY id DESC LIMIT {$start}, {$items_per_page}") as $key) {

			// load template
			$page_posts = new PageGen('pages/blog/comment.tpl');

			$page_posts->set('user', '<a href="' . HOMEDIR . 'pages/user.php?uz=' . $key['uid'] . '">' . $this->users->getnickfromid($key['uid']) . '</a>');

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