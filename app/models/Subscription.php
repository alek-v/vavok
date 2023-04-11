<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

use App\Classes\BaseModel;
use App\Classes\Navigation;
use App\Classes\Mailer;

class Subscription extends BaseModel {
    public function index()
    {
        $this_page['page_title'] = '{@localization[subscriptions]}}';
        $this_page['content'] = '';

        // Authentication
        if (!$this->user->administrator(101)) $this->redirection('../?error');

        // Delete subscription
        if ($this->postAndGet('action') == 'delmail' && $_SESSION['permissions'] == 101) {
            $users_id = $this->check($this->postAndGet('users'));

            if (!empty($users_id)) {
                $fields = array('subscribed', 'subscription_code');
                $values = array('', '');
                $this->user->updateUser($fields, $values, $users_id);

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
            $this_page['content'] .= '<p>{@localization[delallsub]}?</p>';
            $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions/?action=delallsub', $this->localization->string('yessure'), '<p><b>', '</b></p>');
            $this_page['content'] .= $this->sitelink(HOMEDIR . 'adminpanel/subscriptions', $this->localization->string('back'), '<p>', '</p>');
        } 

        // List of subscriptions
        if (empty($this->postAndGet('action'))) {
            $num_items = $this->db->countRow('subs');
            $items_per_page = 10;

            // Navigation
            $navigation = new Navigation($items_per_page, $num_items, HOMEDIR . 'adminpanel/subscriptions/?'); // start navigation
        
            $limit_start = $navigation->start()['start']; // starting point
            $end = $navigation->start()['end']; // ending point
        
            if ($num_items > 0) {
                $sql = "SELECT user_id, user_mail, subscription_name FROM subs ORDER BY id LIMIT $limit_start, $items_per_page";
        
                foreach ($this->db->query($sql) as $item) {
                    $this_page['content'] .= $this->sitelink(HOMEDIR . 'users/u/' . $item['user_id'], $this->user->getNickFromId($item['user_id']), '<b>', '</b>');
                    $this_page['content'] .= '<b><font color="#FF0000"> (' . $item['user_mail'] . ')</font></b>';
    
                    if (!empty($item['subscription_name'])) {
                        $this_page['content'] .= ' <strong>' . $item['subscription_name'] . '</strong>';
                    }
    
                    $this_page['content'] .= ' ' . $this->sitelink(HOMEDIR . 'adminpanel/subscriptions/?action=delmail&users=' . $item['user_id'], '[{@localization[delete]}}]') . '<hr>';
                }

                $this_page['content'] .= $navigation->getNavigation();
            } else {
                $this_page['content'] .= '<p><img src="/themes/images/img/reload.gif" alt="" /> No subscriptions</p>'; // update lang
            }
        }

        return $this_page;
    }

    public function subscription_options()
    {
        $this_page['page_title'] = '{@localization[subscription_options]}}';
        $this_page['content'] = '';

        // Authentication
        if (!$this->user->administrator(101)) $this->redirection(HOMEDIR . '?error');

        $mailer = new Mailer($this->container);

        // Delete subscription option
        if (!empty($this->postAndGet('delete'))) {
            $mailer->deleteSubscriptionOption($this->postAndGet('delete'));
        }

        // Add new subscription option
        if (!empty($this->postAndGet('subscription_option')) && !empty($this->postAndGet('subscription_description'))) {
            $mailer->addSubscriptionOption($this->postAndGet('subscription_option'), $this->postAndGet('subscription_description'));
        }

        // Array with a current subscription options
        $all_options = $mailer->emailSubscriptionOptions();

        if (empty($all_options)) $this_page['content'] .= '<img src="' . HOMEDIR . 'themes/images/img/reload.gif" /> There is no subscription option defined';

        // Show options
        $this_page['all_options'] = '';
        foreach($all_options as $key => $val) {
            $this_page['all_options'] .= '<div class="row mt-3 bg-light rounded d-flex align-items-center">';
            $this_page['all_options'] .= '<div class="col-2 m-2">{@localization[option]}}: ' . $key . '</div>';
            $this_page['all_options'] .= '<div class="col-3 m-2">{@localization[description]}}: ' . $val . '</div>';
            $this_page['all_options'] .= '<div class="col-2 m-2"><a href="./subscription_options?delete=' . $key . '" class="btn btn-danger">{@localization[delete]}}</a></div>';
            $this_page['all_options'] .= '</div>';
        }

        return $this_page;
    }
}