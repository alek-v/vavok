<?php
// (c) Aleksandar Vranešević - vavok.net
// updated 16.04.2020. 1:32:13

class Navigation {
	public $itemsPerPage;
	public $totalItems;
	public $page;

	public function __construct($itemsPerPage, $totalItems, $page) {

		// check page nuber
		if (empty($page) || $page < 1) {
			$page = 1;
		}

		$this->items_per_page = $itemsPerPage; // items per page
		$this->total_items = $totalItems; // total items
		$this->current_page = $page; // number of current page

	}

	// current page
	function current_page($total_pages = 1, $page) {

		if ($page == 'end') $page = intval($total_pages);
		else if (is_numeric($page)) $page = intval($page);

		if ($page < 1) $page = 1;

		if ($page > $total_pages) $page = $total_pages;

		return $page;
	}

	function total_pages($total = 0, $limit = 10) {
		if ($total != 0) {
		$v_pages = ceil($total / $limit);
		return $v_pages;
		}
		else return 1;
	}

	// start counting numbers required for navigation
	function start() {

		$total_pages = $this->total_pages($this->total_items, $this->items_per_page);
		$page = $this->current_page($total_pages, $this->current_page);
		$limit_start = $this->items_per_page * $page - $this->items_per_page;

		return array('total_pages' => $total_pages,
					 'page' => $page,
					 'start' => $limit_start
				);

	}

	// page navigation - prev | next
	public static function pageNavigation($link, $posts, $page, $total) {
	    global $lang_home;

	    $navigation = '<div id="v_pagination">'; 
	    // back link
	    if ($page > 2) {
	        $navigation .= '<a href="' . $link . 'page=' . ($page - 1) . '">' . $lang_home['prev'] . '</a>';
	    } elseif ($page == 2) {
	        $linkx = rtrim($link, '&amp;');
	        $linkx = rtrim($linkx, '?');
	        $navigation .= '<a href="' . $linkx . '">' . $lang_home['prev'] . '</a>';
	    } else {
	        $navigation .= '<span class="prev_v_pagination">' . $lang_home['prev'] . '</span>';
	    } 

    $navigation .= ' | ';
    if ($total > ($posts * $page)) {
        $navigation .= '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['forw'] . '</a>';
    } else {
        $navigation .= '<span class="prev_v_pagination">' . $lang_home['forw'] . '</span>';
    } 

    $navigation .= '</div>';

	return $navigation;
	}

	// numerical navigaton
	public static function numbNavigation($link, $items_per_page, $page, $total, $lnks = 3) {
		global $lang_home;


	    $navigation = '<div id="v_pagination">';

	    // prev link
	    if ($page > 1 && $items_per_page > $total) {
	        $navigation .= '<a href="' . $link . 'page=' . ($page - 1) . '">' . $lang_home['prev'] . '</a>';
	    } else {
	        $navigation .= '<span class="next_v_pagination">' . $lang_home['prev'] . '</span>';
	    } 


	    if ($total > 0) {
	        $ba = ceil($total / $items_per_page);

	        $start = $items_per_page * ($page - 1);
	        $min = $start - $items_per_page * ($lnks - 1);
	        $max = $start + $items_per_page * $lnks;

	        if ($min < $total && $min > 0) {
	            if ($min - $items_per_page > 0) {
	                $linkx = rtrim($link, '&amp;');
	                $linkx = rtrim($linkx, '?');
	                $navigation .= '<a href="' . $linkx . '">1</a> <span class="prev_v_pagination">...</span>';
	            } else {
	                $linkx = rtrim($link, '&amp;');
	                $navigation .= '<a href="' . $linkx . '">1</a> ';
	            } 
	        } 

	        for($i = $min; $i < $max;) {
	            if ($i < $total && $i >= 0) {
	                $ii = floor(1 + $i / $items_per_page);

	                if ($start == $i) {
	                    $navigation .= '<span class="prev_v_pagination">' . $ii . '</span>';
	                } elseif ($ii == 1) {
	                    $linkx = rtrim($link, '&amp;');
	                    $linkx = rtrim($linkx, '?');
	                    $navigation .= '<a href="' . $linkx . '">' . $ii . '</a>';
	                } else {
	                    $navigation .= '<a href="' . $link . 'page=' . $ii . '">' . $ii . '</a>';
	                } 
	            } 

	            $i = $i + $items_per_page;
	        } 

	        if ($max < $total) {
	            if ($max + $items_per_page < $total) {
	                $navigation .= '<span class="prev_v_pagination">...</span> <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } else {
	                $navigation .= '<a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } 
	        } 
	    } 
	    // forward link
	    if ($total > ($items_per_page * $page)) {
	        $navigation .= '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['next'] . '</a>';
	    } else {
	        $navigation .= '<span class="next_v_pagination">' . $lang_home['next'] . '</span>';
	    } 

	    $navigation .= '</div>';
	    
	    return $navigation;
	}

	// page navigation combined - prev, next and page number
	public static function siteNavigation($link, $posts, $page, $total, $lnks = 3) {

	    $navigation = self::pageNavigation($link, $posts, $page, $total);
	    // page number navigation
	    $navigation .= self::numbNavigation($link, $posts, $page, $total);

	    return $navigation;
	} 


}
?>