<?php
// (c) Aleksandar Vranešević - vavok.net
// updated 04.05.2020. 5:09:30

class Navigation {
	
	public $items_per_page;
	public $total_items;
	public $page;
	public $link;

	public function __construct($items_per_page, $total_items, $page, $link = '') {

		$this->items_per_page = $items_per_page; // items per page
		$this->total_items = $total_items; // total items
		$this->total_pages = $this->total_pages($this->total_items, $this->items_per_page); // total pages

		// get page
		if ($page == 'end') $page = intval($this->total_pages);
		else if (is_numeric($page)) $page = intval($page);

		if ($page < 2) $page = 1;

		if ($page > $this->total_pages) $page = $this->total_pages;

		$this->current_page = $page; // number of current page

		$this->link = $link; // page where navigation will be

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
		$page = $this->current_page;
		$limit_start = $this->items_per_page * $page - $this->items_per_page;

		if ($this->total_items < $limit_start + $this->items_per_page) {
		    $end = $this->total_items;
		} else {
		    $end = $limit_start + $this->items_per_page;
		}

		return array('total_pages' => $total_pages,
					 'page' => $page,
					 'start' => $limit_start,
					 'end' => $end
				);

	}

	// site navigaton
	public function get_navigation($link = '', $lnks = 3) {
		global $lang_home;

		$page = $this->current_page;
		$total = $this->total_items;
		$link = $this->link;

	    $navigation = '<div id="v_pagination">';

	    // prev link
	    if ($page > 1 && $this->total_pages > 1 && $page != 2) {
	        $navigation .= '<a href="' . $link . 'page=' . ($page - 1) . '">' . $lang_home['prev'] . '</a>';
	    } elseif ($page == 2) {
	    	$linkx = rtrim($link, '&amp;');
	        $linkx = rtrim($linkx, '?');
	    	$navigation .= '<a href="' . $linkx . '">' . $lang_home['prev'] . '</a>';
	    } else {
	        $navigation .= '<span class="next_v_pagination">' . $lang_home['prev'] . '</span>';
	    } 


	    if ($total > 0) {
	        $ba = ceil($total / $this->items_per_page);

	        $start = $this->items_per_page * ($page - 1);
	        $min = $start - $this->items_per_page * ($lnks - 1);
	        $max = $start + $this->items_per_page * $lnks;

	        if ($min < $total && $min > 0) {
	            if ($min - $this->items_per_page > 0) {
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
	                $ii = floor(1 + $i / $this->items_per_page);

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

	            $i = $i + $this->items_per_page;
	        } 

	        if ($max < $total) {
	            if ($max + $this->items_per_page < $total) {
	                $navigation .= '<span class="prev_v_pagination">...</span> <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } else {
	                $navigation .= '<a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } 
	        } 
	    } 
	    // forward link
	    if ($total > ($this->items_per_page * $page)) {
	        $navigation .= '<a href="' . $link . 'page=' . ($page + 1) . '">' . $lang_home['next'] . '</a>';
	    } else {
	        $navigation .= '<span class="next_v_pagination">' . $lang_home['next'] . '</span>';
	    } 

	    $navigation .= '</div>';
	    
	    return $navigation;
	}

	// numerical navigaton - deprecated 26.04.2020. 22:07:47
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

	// page navigation combined - prev, next and page number - deprecated 26.04.2020. 22:17:03
	public static function siteNavigation($link, $posts, $page, $total, $lnks = 3) {

	    // page number navigation
	    $navigation .= self::numbNavigation($link, $posts, $page, $total);

	    return $navigation;
	} 


}
?>