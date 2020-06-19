<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Updated:   19.06.2020. 23:57:30
*/

class Comments {

	// class constructor
	function __construct() {
		global $db, $user_id, $users;

		$this->table_prefix = get_configuration('tablePrefix'); // table prefix
		$this->db = $db; // database
		$this->user_id = $user_id; // user id with active login
		$this->users = $users;
	}

	// Insert new comment
	public function insert($content, $pid) {

		// Values to insert
		$values = array('uid' => $this->user_id, 'comment' => $content, 'pid' => $pid, 'date' => date('Y-m-d H:i:s'));

		// Insert data to database
		$this->db->insert_data($this->table_prefix . 'comments', $values);

	}

	// count number of comments
	public function count_comments($pid) {

		return $this->db->count_row(get_configuration('tablePrefix') . 'comments', 'pid=' . $pid);

	}

	// load comments
	public function load_comments($pid, $start, $items_per_page) {

		$all_posts = array();

		foreach ($this->db->query("SELECT * FROM " . get_configuration('tablePrefix') . "comments WHERE pid = {$pid} ORDER BY id DESC LIMIT {$start}, {$items_per_page}") as $key) {

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