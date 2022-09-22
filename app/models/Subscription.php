<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

class Subscription extends BaseModel {
    public function index()
    {
        // Users data
        $this_page['user'] = $this->user_data;
        $this_page['tname'] = '{@localization[subscriptions]}}';
        $this_page['content'] = '';

        // Authentication
        if (!$this->user->administrator(101)) $this->redirection('../index.php?error');

        // Delete subscription
        if ($this->postAndGet('action') == 'delmail' && $_SESSION['permissions'] == 101) {
            $users_id = $this->check($this->postAndGet('users'));

            if (!empty($users_id)) {
                $fields = array('subscri', 'newscod');
                $values = array('', '');
                $this->user->update_user($fields, $values, $users_id);

                $this->db->delete('subs', "user_id='" . $users_id . "'");

                $this->redirection(HOMEDIR . "adminpanel/subscriptions/?start=mp_delsubmail");
            } else {
                $this->redirection(HOMEDIR . "adminpanel/subscriptions/?start=mp_nodelsubmail");
            }
        }

        // Delete all subscriptions
        if ($this->postAndGet('action') == 'delallsub' && $_SESSION['permissions'] == 101) {
            $sql = "TRUNCATE TABLE subs";
            $this->db->query($sql);
            $this->redirection(HOMEDIR . "adminpanel/subscriptions/?isset=mp_delsuball");
        }

        // Confirm to delete
        if ($this->postAndGet('action') == "poddel") {
            $this_page['content'] .= '<p>' . $this->localization->string('delallsub') . '?</p>';
            $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions/?action=delallsub', $this->localization->string('yessure'), '<p><b>', '</b></p>');
            $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions', $this->localization->string('back'), '<p>', '</p>');
        } 

        // List of subscriptions
        if (empty($this->postAndGet('action'))) {
            $num_items = $this->db->countRow('subs');
            $items_per_page = 10;

            // Navigation
            $navigation = new Navigation($items_per_page, $num_items, $this->postAndGet('page'), HOMEDIR . 'adminpanel/subscriptions/?'); // start navigation
        
            $limit_start = $navigation->start()['start']; // starting point
            $end = $navigation->start()['end']; // ending point
        
            if ($num_items > 0) {
                $sql = "SELECT user_id, user_mail, subscription_name FROM subs ORDER BY id LIMIT $limit_start, $items_per_page";
        
                if ($num_items > 0) {
                    foreach ($this->db->query($sql) as $item) {
                        $this_page['content'] .= $this->sitelink('../pages/user.php?uz=' . $item['user_id'], $this->user->getnickfromid($item['user_id']), '<b>', '</b>');
                        $this_page['content'] .= '<b><font color="#FF0000"> (' . $item['user_mail'] . ')</font></b>';
        
                        if (!empty($item['subscription_name'])) {
                            $this_page['content'] .= ' <strong>' . $item['subscription_name'] . '</strong>';
                        }
        
                        $this_page['content'] .= ' ' . $this->sitelink(HOMEDIR . 'adminpanel/subscriptions/?action=delmail&users=' . $item['user_id'], '[' . $this->localization->string('delete') . ']') . '<hr>';
                    }
                }
                $this_page['content'] .= $navigation->get_navigation();
            } else {
                $this_page['content'] .= '<p><img src="/themes/images/img/reload.gif" alt="" /> No subscriptions</p>'; // update lang
            }
        }

        $this_page['content'] .= '<p>';
        $this_page['content'] .= $this->sitelink('./', $this->localization->string('adminpanel')) . '<br />';
        $this_page['content'] .= $this->homelink();
        $this_page['content'] .= '</p>';

        return $this_page;
    }
}