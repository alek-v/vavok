<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;

class Sitemap extends BaseModel {
    /**
     * Index page
     */
    public function index()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = 'Sitemap Generator';
        $this_page['content'] = '';

        if (!$this->user->administrator(101)) $this->redirection('./');
        
        if ($this->postAndGet('action') == 'generate') {
            $total = $this->db->countRow('pages');
        
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
            foreach ($this->db->query("SELECT pname, lastupd, headt, type FROM pages WHERE published = '2' OR published = '0'") as $page) {
                // date updated
                $dateUpdated = $this->correctDate($page['lastupd'], 'Y-m-d');

                // url
                if (!empty($page['headt']) && stristr($page['headt'], 'og:url')) {
                    preg_match('/<meta property="og:url" content="([^"]*)/i', $page['headt'], $matches);
                    $loc = $matches[1];
        
                    if ($this->configuration('transferProtocol') == 'HTTPS') $loc = str_replace('http://', 'https://', $loc);
                } elseif ($page['pname'] == 'index') {
                    $loc = $this->websiteHomeAddress();
                } else {
                    // check for blog posts
                    if ($page['type'] == 'post') {
                        $loc = $this->websiteHomeAddress() . '/blog/' . $page['pname'];
                    } else { // it is regular page
                        $loc = $this->websiteHomeAddress() . '/page/' . $page['pname'];
                    }
                }

                if ($page['pname'] != 'menu_slider') {
                   $sitemap .= '
        <url>
            <loc>' . $loc . '</loc>
            <lastmod>' . $dateUpdated . '</lastmod>
        </url>';
                    }
                }

            $sitemap .= '
    </urlset>';

            // Send sitemap to user
            $length = strlen($sitemap);

            header('Content-Description: File Transfer');
            header('Content-Type: text/xml');
            header('Content-Disposition: attachment; filename=sitemap.xml');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $length);
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
        
            echo $sitemap;
            exit;
        }

        $this_page['homelink'] = $this->homelink('<p>', '</p>');

        return $this_page;
    }
}