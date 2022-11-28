<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;
use App\Traits\Core;

class Navigation {
    use Core;

    private int $total_pages;
    private int $current_page;

    public function __construct(private int $items_per_page, private int $total_items, private string $link = '')
    {
        $this->items_per_page = $items_per_page;
        $this->total_items = $total_items;
        $this->total_pages = $this->totalPages($this->total_items, $this->items_per_page);

        // Get page number
        $page = $this->postAndGet('page');
        if (!is_numeric($page)) $page = intval($page);
        if ($page < 2) $page = 1;
        if ($page > $this->total_pages) $page = $this->total_pages;
        $this->current_page = $page;

        // Page where navigation will be
        $this->link = $link;
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
            return ceil($total / $limit);
        }

        return 1;
    }

    /**
     * Start counting numbers required for navigation
     * 
     * @return array
     */
    public function start(): array
    {
        $total_pages = $this->total_pages;
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

    /**
     * Return navigation
     *
     * @return string
     */
    public function getNavigation(): string
    {
        $page = $this->current_page;
        $total_items = $this->total_items;
        $link = $this->link;

        // Number of links in the pagination
        $links = 3;

        // Reduce number of links in pagination for smaller screens
        if ($this->userDevice() == 'phone') $links = 1;

        // Variable for navigation links
        $navigation = '';

        // Previous page link
        if ($page > 1 && $this->total_pages > 1) {
            // Dont show file.php?page=1, only file.php if this is first page
            // We dont want to create duplicated pages with the same content file.php and file.php?page=1
            $navigation .= $page == 2 ? $this->showLink($link, '{@localization[prev]}}') : $this->showLink($link . 'page=' . ($page - 1), '{@localization[prev]}}');
        } else {
            $navigation .= $this->disabledLink('{@localization[prev]}}');
        }

        if ($total_items > 0) {
            $total_pages = ceil($total_items / $this->items_per_page);

            // Where start rows for current page
            $start = $this->items_per_page * ($page - 1);
            // Row number from where to show links to the previous pages
            $min = $start - $this->items_per_page * ($links - 1);
            // Row number from where to show links to the next pages
            $max = $start + $this->items_per_page * $links;

            if ($min < $total_items && $min > 0) {
                // Show dots '...' after page 1 when there is a lot of links after page 1
                if ($min - $this->items_per_page > 0) {
                    $navigation .= $this->showLink($link, '1') . $this->disabledLink('...');
                } else {
                    $navigation .= $this->showLink($link, '1');
                }
            }

            for ($i = $min; $i < $max;) {
                // Don't show more pages than actually exist
                if ($i < $total_items && $i >= 0) {
                    // Page where we at current iteration
                    $ii = floor(1 + $i / $this->items_per_page);

                    // Active page
                    if ($start == $i) $navigation .= $this->activeLink($ii);

                    // Page one without '?page=1' to prevent duplicated pages (page.php and page.php?page=1)
                    elseif ($ii == 1) {
                        $navigation .= $this->showLink($link, $ii);
                    } else {
                        $navigation .= $this->showLink($link . 'page=' . $ii, $ii);
                    }
                }

                // Row from where next iteration will start
                $i = $i + $this->items_per_page;
            }

            // Links at the end of navigation and after possible dots '...'
            if ($max < $total_items) {
                // Show dots and link after dots
                if ($max + $this->items_per_page < $total_items) {
                    $navigation .= $this->disabledLink('...') . $this->showLink($link . 'page=' . $total_pages, $total_pages);
                }
                // Last link, no dots before link
                else {
                    $navigation .= $this->showLink($link . 'page=' . $total_pages, $total_pages);
                }
            }
        }

        // Next page link
        if ($total_items > ($this->items_per_page * $page)) {
            $navigation .= $this->showLink($link . 'page=' . ($page + 1), '{@localization[next]}}');
        }
        // Disabled link when current page is last page
        else {
            $navigation .= $this->disabledLink('{@localization[next]}}');
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
     * @return string
     */
    private function disabledLink(string $name): string
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
     * @return string
     */
    private function showLink(string $link, string $name): string
    {
        // Remove unwanted characters from end of the link
        $link = rtrim(rtrim($link, '&amp;'), '?');

        return '<li class="page-item"><a class="page-link" href="' . $link . '">' . $name . '</a></li>';
    }

    /**
     * Show active page/link
     * 
     * @param string $name
     * @return string
     */
    private function activeLink(string $name): string
    {
        return '<li class="page-item active" aria-current="page">
          <span class="page-link">' . $name . '</span>
        </li>';
    }
}