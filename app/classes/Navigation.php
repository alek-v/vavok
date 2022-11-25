<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;
use App\Traits\Core;

class Navigation {
    use Core;

    public $items_per_page;
    public $total_items;
    public $page;
    public $link;

    public function __construct($items_per_page, $total_items, $link = '')
    {
        $this->items_per_page = $items_per_page; // items per page
        $this->total_items = $total_items; // total items
        $this->total_pages = $this->totalPages($this->total_items, $this->items_per_page); // total pages

        // Get page number
        $page = $this->postAndGet('page');
        if ($page == 'end') $page = intval($this->total_pages);
        else if (is_numeric($page)) $page = intval($page);
        if ($page < 2) $page = 1;
        if ($page > $this->total_pages) $page = $this->total_pages;

        // Set page number
        $this->current_page = $page; // number of current page

        $this->link = $link; // page where navigation will be
    }

    /**
     * Return number of pages
     * 
     * @param int $total
     * @param int $limit
     * @return int
     */
    private function totalPages(int $total = 0, int $limit = 10): int
    {
        if ($total != 0) {
            $v_pages = ceil($total / $limit);
            return $v_pages;
        }
        else return 1;
    }

    // start counting numbers required for navigation
    function start()
    {
        $total_pages = $this->totalPages($this->total_items, $this->items_per_page);
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
    public function get_navigation($link = '', $lnks = 3)
    {
        $page = $this->current_page;
        $total = $this->total_items;
        $link = $this->link;
        // Reduce number of links in pagination for smaller screens
        if ($this->userDevice() == 'phone') $lnks = 1;
        //$lnks = 1;

        // Variable for navigation links
        $navigation = '';

        // Previous page link
        if ($page > 1 && $this->total_pages > 1) {
            // Dont show file.php?page=1, only file.php if this is first page
            // We dont want to create duplicated pages with the same content file.php and file.php?page=1
            $navigation .= $page == 2 ? $this->show_link($link, '{@localization[prev]}}') : $this->show_link($link . 'page=' . ($page - 1), '{@localization[prev]}}');
        } else {
            $navigation .= $this->disabled_link('{@localization[prev]}}');
        }

        if ($total > 0) {
            $ba = ceil($total / $this->items_per_page);

            $start = $this->items_per_page * ($page - 1);
            $min = $start - $this->items_per_page * ($lnks - 1);
            $max = $start + $this->items_per_page * $lnks;

            if ($min < $total && $min > 0) {
                // Show dots '...' after page 1 when there is a lot of links after page 1
                if ($min - $this->items_per_page > 0) {
                    $navigation .= $this->show_link($link, '1') . $this->disabled_link('...');
                } else {
                    $navigation .= $this->show_link($link, '1');
                }
            }

            for($i = $min; $i < $max;) {
                if ($i < $total && $i >= 0) {
                    $ii = floor(1 + $i / $this->items_per_page);

                    // Active page
                    if ($start == $i) {
                        $navigation .= $this->active_link($ii);
                    }
                    // Page one without '?page=1' to prevent duplicated pages page.php and page.php?page=1
                    elseif ($ii == 1) {
                        $navigation .= $this->show_link($link, $ii);
                    } else {
                        $navigation .= $this->show_link($link . 'page=' . $ii, $ii);
                    } 
                } 

                $i = $i + $this->items_per_page;
            }

            // Links at the end of navigation and after possible dots '...'
            if ($max < $total) {
                // Show dots and link after dots
                if ($max + $this->items_per_page < $total) {
                    $navigation .= $this->disabled_link('...') . $this->show_link($link . 'page=' . $ba, $ba);
                }
                // Last link, no dots before link
                else {
                    $navigation .= $this->show_link($link . 'page=' . $ba, $ba);
                }
            }
        }

        // Next page link
        if ($total > ($this->items_per_page * $page)) {
            $navigation .= $this->show_link($link . 'page=' . ($page + 1), '{@localization[next]}}');
        }
        // Disabled link when current page is last page
        else {
            $navigation .= $this->disabled_link('{@localization[next]}}');
        }

        // HTML before links, using Bootstrap
        $before = '<nav aria-label="page-navigation" class="page_navigation">';
        $before .= '<ul class="pagination">';

        // HTML after links
        $after = '</ul></nav>';

        return $before . $navigation . $after;
    }

    /**
     * Show disabled page/link
     * 
     * @param string $name
     * @return str
     */
    protected function disabled_link($name)
    {
        return '<li class="page-item disabled">
          <span class="page-link">' . $name . '</span>
        </li>';
    }

    /**
     * Show  page/link
     * 
     * @param string $link
     * @param string $name
     * @return str
     */
    protected function show_link($link, $name)
    {
        // Remove unwanted characters from end of the link
        $link = rtrim(rtrim($link, '&amp;'), '?');

        return '<li class="page-item"><a class="page-link" href="' . $link . '">' . $name . '</a></li>';
    }

    /**
     * Show active page/link
     * 
     * @param string $name
     * @return str
     */
    protected function active_link($name)
    {
        return '<li class="page-item active" aria-current="page">
          <span class="page-link">' . $name . '</span>
        </li>';
    }
}