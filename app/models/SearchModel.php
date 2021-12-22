<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class SearchModel extends BaseModel {
    /**
     * Index page
     */
    public function index($params = [])
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = '{@localization[search]}}';
        $this_page['headt'] = '';
        $this_page['content'] = '';

        if (!function_exists('cyr_to_lat')) {
            function cyr_to_lat($str) {
                $latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
                $cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");
                $str = str_replace($cyrillic, $latin, $str);
                return $str;
            }
        }
        if (!function_exists('lat_to_cyr')) {
            function lat_to_cyr($str, $correct = '') {
                $latin = array("Đ", "Lj", "LJ", "Nj", "NJ", "DŽ", "Dž", "đ", "lj", "nj", "dž", "dz", "a", "b", "v", "g", "d", "e", "ž", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "ć", "u", "f", "h", "c", "č", "š", "A", "B", "V", "G", "D", "E", "Ž", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "Ć", "U", "F", "H", "C", "Č", "Š");
                $cyrillic = array("Ђ", "Љ", "Љ", "Њ", "Њ", "Џ", "Џ", "ђ", "љ", "њ", "џ", "џ", "а", "б", "в", "г", "д", "е", "ж", "з", "и", "ј", "к", "л", "м", "н", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "ш", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Ј", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Ш");
        
                $str = str_replace($latin, $cyrillic, $str);
        
                if ($correct == 'full') {
                    $str = skip($str);
                }
        
                return $str;
            }
        }

        // String for search
        $search_item = isset($params[0]) ? $params[0] : $this->post_and_get('item');

        $itemCyr = lat_to_cyr(htmlspecialchars_decode($search_item)); // translate to cyrillic to search for cyrillic post
        $itemLat = cyr_to_lat(htmlspecialchars_decode($search_item)); // translate to latin to search for latin post

        // Add meta tags
        $this_page['headt'] .= '<meta name="robots" content="noindex, follow" />';
        
        if (empty($search_item)) {
            $indexPage = $this->model('ParsePage');
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

            $resultsPage = $this->model('ParsePage');
            $resultsPage->load('search/results');
            $resultsPage->set('searchItem', $clean_search_item);

            $page_ids = array();

            /**
             * Count pages by tags
             */
            $prep = "SELECT id, page_id FROM tags WHERE tag_name = '{$search_item}'";
            foreach ($this->db->query($prep) as $resultItem) {
                if (!in_array($resultItem['page_id'], $page_ids)) array_push($page_ids, $resultItem['page_id']);
            }

            /**
             * Count pages by content
             */
            $prep = "SELECT id, pname, tname, content, type FROM pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'";
            foreach($this->db->query($prep) as $resultItem) {
                /**
                 * Add item if it is not added
                 */
                if (!in_array($resultItem['id'], $page_ids)) array_push($page_ids, $resultItem['id']);
            }

            $items = count($page_ids);
            unset($page_ids);

            /**
             * Count forum posts
             */
            if ($this->db->table_exists('ftopics')) {
                $items += $this->db->count_row("ftopics", "name LIKE '%" . $itemLat . "%' OR name LIKE '%" . $itemCyr . "%' AND (closed = '' OR closed = 0)");
            }

            $thisPageNav = new Navigation(20, $items, $this->post_and_get('page'));

            $searchItems = array();
            $page_ids = array();
        
            /**
             * Search tags
             */
            $prep = "SELECT id, page_id FROM tags WHERE tag_name = '{$search_item}' LIMIT {$thisPageNav->start()['start']}, 20";
            foreach ($this->db->query($prep) as $resultItem) {
                /**
                 * Page data
                 */
                $resultItem = $this->db->get_data('pages', "id = '" . $resultItem['page_id'] . "'");
                $dots = strlen($resultItem['content']) > 50 ? '...' : '';
        
                $itemText = mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots;
        
                $setItem = $this->model('ParsePage');
                $setItem->load('search/item');
        
                if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
                else $type = 'blog';
        
                $setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '/">' . $resultItem['tname'] . '</a>');
                $setItem->set('itemText', $itemText);
        
                /**
                 * Add item if it is not added
                 */
                if (!in_array($resultItem['id'], $page_ids)) {
                    array_push($page_ids, $resultItem['id']);
        
                    array_push($searchItems, $setItem);
                }
            }
        
            /**
             * Search pages
             */
            $prep = "SELECT id, pname, tname, content, type FROM pages WHERE pname LIKE '%" . $itemLat . "%' OR pname LIKE '%" . $itemCyr . "%' AND published = '2'" . $orderBy . "LIMIT {$thisPageNav->start()['start']}, 20";
            foreach ($this->db->query($prep) as $resultItem) {
                $dots = strlen($resultItem['content']) > 50 ? '...' : '';
        
                $itemText = mb_substr(strip_tags($resultItem['content']), 0, 50, 'UTF-8') . $dots;
        
                $setItem = $this->model('ParsePage');
                $setItem->load('search/item');
        
                if (empty($resultItem['type']) || $resultItem['type'] == 'page') $type = 'page';
                else $type = 'blog';
        
                $setItem->set('itemLink', '<a href="' . HOMEDIR . $type . '/' . $resultItem['pname'] . '/">' . $resultItem['tname'] . '</a>');
                $setItem->set('itemText', $itemText);
        
                /**
                 * Add item if it is not added
                 */
                if (!in_array($resultItem['id'], $page_ids)) {
                    array_push($searchItems, $setItem);
                }
            }
        
            /**
             * Search forum posts
             */
            if ($this->db->table_exists('ftopics')) {
                $preps = "SELECT `name`, `text`, `id` FROM ftopics WHERE name LIKE '%" . $itemLat . "%' OR name LIKE '%" . $itemCyr . "%' AND (closed = '' OR closed = 0) LIMIT {$thisPageNav->start()['start']}, 20";
        
                foreach($this->db->query($preps) as $resultItems) {
                    $dots = strlen($resultItems['text']) > 50 ? '...' : '';
        
                    $itemText = mb_substr(strip_tags($resultItems['text']), 0, 50, 'UTF-8') . $dots;
        
                    $setItem = $this->model('ParsePage');
                    $setItem->load('search/item');
        
                    $setItem->set('itemLink', '<a href="' . HOMEDIR . 'forum/viewtpc/' . $resultItems['id'] . '-' . $this->trans($resultItems['name']) . '/">' . $resultItems['name'] . '</a>');
                    $setItem->set('itemText', $itemText);
        
                    $searchItems[] = $setItem;
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