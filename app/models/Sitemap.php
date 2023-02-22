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
        $this_page['page_title'] = 'Sitemap Generator';
        $this_page['content'] = '';

        if (!$this->user->administrator(101)) $this->redirection('./');
        
        if ($this->postAndGet('action') == 'generate') {
            $total = $this->db->countRow('pages');
        
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
            foreach ($this->db->query("SELECT slug, date_updated, head_tags, type FROM pages WHERE published_status = '2' OR published_status = '0'") as $page) {
                // date updated
                $dateUpdated = $this->correctDate($page['date_updated'], 'Y-m-d');

                // url
                if (!empty($page['head_tags']) && stristr($page['head_tags'], 'og:url')) {
                    preg_match('/<meta property="og:url" content="([^"]*)/i', $page['head_tags'], $matches);
                    $loc = $matches[1];
        
                    if ($this->configuration->getValue('transferProtocol') == 'HTTPS') $loc = str_replace('http://', 'https://', $loc);
                } elseif ($page['slug'] == 'index') {
                    $loc = $this->websiteHomeAddress();
                } else {
                    // check for blog posts
                    if ($page['type'] == 'post') {
                        $loc = $this->websiteHomeAddress() . '/blog/' . $page['slug'];
                    } else { // it is regular page
                        $loc = $this->websiteHomeAddress() . '/page/' . $page['slug'];
                    }
                }

                if ($page['slug'] != 'menu_slider') {
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