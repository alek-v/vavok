<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;

class SearchModel extends BaseModel {
    /**
     * Index page
     */
    public function index($params = [])
    {
        $this_page['tname'] = '{@localization[search]}}';
        $this_page['headt'] = '';
        $this_page['content'] = '';

        // String to search for
        $search_item = isset($params[0]) ? $params[0] : $this->postAndGet('item');

        $itemCyr = $this->latinToCyrillic(htmlspecialchars_decode($search_item)); // translate to cyrillic to search for cyrillic post
        $itemLat = $this->cyrillicToLatin(htmlspecialchars_decode($search_item)); // translate to latin to search for latin post

        // Add meta tags
        $this_page['headt'] .= '<meta name="robots" content="noindex, follow" />';
        
        if (empty($search_item)) {
            $indexPage = $this->container['parse_page'];
            $indexPage->load("search/index");
            $this_page['content'] .= $indexPage->output();

            return $this_page;
        } else {
            if (!empty($ln_loc)) {
                $orderBy = " order by lang='" . $ln_loc . "' desc, pname desc ";
            } else {
                $orderBy = ' ';
            }

            $clean_search_item = str_replace('_', ' ', $search_item);

            $this_page['tname'] = $clean_search_item;

            $resultsPage = $this->container['parse_page'];
            $resultsPage->load('search/results');
            $resultsPage->set('searchItem', $clean_search_item);

            $page_ids = array();

            // Count pages by tags
            $prep = "SELECT id, page_id FROM tags WHERE tag_name = '{$search_item}'";
            foreach ($this->db->query($prep) as $resultItem) {
                if (!in_array($resultItem['page_id'], $page_ids)) array_push($page_ids, $resultItem['page_id']);
            }

            // Count pages by content
            $prep = "SELECT id, pname, tname, content, type FROM pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'";
            foreach($this->db->query($prep) as $resultItem) {
                // Add item if it is not added
                if (!in_array($resultItem['id'], $page_ids)) array_push($page_ids, $resultItem['id']);
            }

            $items = count($page_ids);
            unset($page_ids);

            $thisPageNav = new Navigation(20, $items, $this->postAndGet('page'));

            $searchItems = array();
            $page_ids = array();

            // Search tags
            $prep = "SELECT id, page_id FROM tags WHERE tag_name = '{$search_item}' LIMIT {$thisPageNav->start()['start']}, 20";
            foreach ($this->db->query($prep) as $resultItem) {
                // Page data
                $resultItem = $this->db->selectData('pages', 'id = :id', [':id' => $resultItem['page_id']]);
                $dots = strlen($resultItem['content']) > 50 ? '...' : '';

                $itemText = mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots;

                $setItem = $this->container['parse_page'];
                $setItem->load('search/item');

                if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
                else $type = 'blog';

                $setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '">' . $resultItem['tname'] . '</a>');
                $setItem->set('itemText', $itemText);

                // Add item if it is not added
                if (!in_array($resultItem['id'], $page_ids)) {
                    array_push($page_ids, $resultItem['id']);
                    array_push($searchItems, $setItem);
                }
            }

            // Search pages
            $prep = "SELECT id, pname, tname, content, type FROM pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'" . $orderBy . "LIMIT {$thisPageNav->start()['start']}, 20";
            foreach ($this->db->query($prep) as $resultItem) {
                $dots = isset($resultItem['content']) && strlen($resultItem['content']) > 50 ? '...' : '';

                $itemText = isset($resultItem['content']) ? mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots : '';

                $setItem = $this->container['parse_page'];
                $setItem->load('search/item');

                if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
                else $type = 'blog';

                $setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '">' . $resultItem['tname'] . '</a>');
                $setItem->set('itemText', $itemText);
        
                // Add item if it is not added
                if (!in_array($resultItem['id'], $page_ids)) {
                    array_push($searchItems, $setItem);
                }
            }

            if ($items < 1) {
                $this_page['content'] .= $resultsPage->output();
                $this_page['content'] .= $this->showNotification('No search results, try another phrase');

                return $this_page;
            }

            $resultsPage->set('allResults', $resultsPage->merge($searchItems));
            $this_page['content'] .= $resultsPage->output();

            $navigation = new Navigation(20, $items, "./");
            $this_page['content'] .= $navigation->get_navigation();
        }

        return $this_page;
    }
}