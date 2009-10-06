<?php

$block_name = _("Last comments on my profile");

/**
 * Horde_Block_folks_my_comments:: Implementation of the Horde_Block API to
 * display last comments on users videos.
 *
 * $Horde: incubator/folks/lib/Block/my_comments.php,v 1.1 2008/03/27 11:02:57 duck Exp $
 *
 * @package Horde_Block
 */
class Horde_Block_folks_my_comments extends Horde_Block {

    var $_app = 'folks';

    function _params()
    {
        return array('limit' => array('name' => _("Number of comments to display"),
                                    'type' => 'int',
                                    'default' => 10));
    }

    /**
     * The title to go in this block.
     *
     * @return string   The title text.
     */
    function _title()
    {
        return _("Last comments on my profile");
    }

    /**
     * The content to go in this block.
     *
     * @return string   The content
     */
    function _content()
    {
        if (!Horde_Auth::isAuthenticated()) {
            return '';
        }

        $GLOBALS['cache'] = Horde_Cache::singleton($GLOBALS['conf']['cache']['driver'],
                                           Horde::getDriverConfig('cache', $GLOBALS['conf']['cache']['driver']));

        $cache_key = 'folks_myscommetns_' . $this->_params['limit'];
        $threads = $GLOBALS['cache']->get($cache_key, $GLOBALS['conf']['cache']['default_lifetime']);
        if ($threads) {
            return $threads;
        }

        Horde::addScriptFile('tables.js', 'horde');
        $html = '<table class="sortable striped" id="my_comment_list" style="width: 100%">'
              . '<thead><tr><th>' . _("Title") . '</th>'
              . '<th>' . _("User") . '</th></tr></thead>';

        try {
            $threads = $GLOBALS['registry']->call('forums/getThreadsByForumOwner',
                                                  array(Horde_Auth::getAuth(), 'message_timestamp', 1, false,
                                                  'folks', 0, $this->_params['limit']));
        } catch (Horde_Exception $e) {
            return $e->getMessage();
        }

        $url = Folks::getUrlFor('user', Horde_Auth::getAuth());
        foreach ($threads as $message) {
            $html .= '<tr><td>'
                  . '<a href="' . $url . '" title="' . $message['message_date']. '">'
                  . $message['message_subject'] . '</a> '
                  . '</td><td>'
                  . $message['message_author'] . '</td></tr>';
        }
        $html .= '</table>';

        $GLOBALS['cache']->set($cache_key, $html);
        return $html;
    }
}
