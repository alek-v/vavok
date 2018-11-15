<?php
// (c) vavok.net
class Navigation {
	public $itemsPerPage;
	public $totalItems;

	public function __construct($itemsPerPage, $totalItems) {
		global $_GET, $limit_start, $page;

		if (isset($_GET['page'])) {
		    $page = check($_GET['page']);
		} 
		if ($page == "" || $page <= 0)$page = 1;

		$num_pages = ceil($totalItems / $itemsPerPage);

		if (($page > $num_pages) && $page != 1)$page = $num_pages;

	    $limit_start = ($page-1) * $itemsPerPage;
	    if ($limit_start < 0) {
	        $limit_start = 0;
	    } 
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
	public static function numbNavigation($link, $posts, $page, $total, $lnks = 3) {
	    global $lang_home;
	    $navigation = '<div id="v_pagination">'; 

	    if ($total > 0) {
	        $ba = ceil($total / $posts);

	        $start = $posts * ($page - 1);
	        $min = $start - $posts * ($lnks - 1);
	        $max = $start + $posts * $lnks;

	        if ($min < $total && $min > 0) {
	            if ($min - $posts > 0) {
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
	                $ii = floor(1 + $i / $posts);

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

	            $i = $i + $posts;
	        } 

	        if ($max < $total) {
	            if ($max + $posts < $total) {
	                $navigation .= '<span class="prev_v_pagination">...</span> <a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } else {
	                $navigation .= '<a href="' . $link . 'page=' . $ba . '">' . $ba . '</a>';
	            } 
	        } 
	    } 
	    // forward link
	    if ($total > ($posts * $page)) {
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