<?php

/**
 * @author  Duck <duck@obala.net>
 * @package Folks
 */
class Folks_Block_Know extends Horde_Core_Block
{
    /**
     */
    public function __construct($app, $params = [])
    {
        parent::__construct($app, $params);

        $this->_name = _("People you might know");
    }

    /**
     */
    protected function _content()
    {
        require_once __DIR__ . '/../base.php';

        $friends_driver = Folks_Friends::singleton();
        $list = $friends_driver->getPossibleFriends(20);
        if ($list instanceof PEAR_Error) {
            return $list;
        }

        // Prepare actions
        $actions = [
            ['url' => Horde::url('edit/friends/add.php'),
                'id' => 'user',
                'name' => _("Add friend")],
            ['url' => Horde::url('user.php'),
                'id' => 'user',
                'name' => _("View profile")]];
        if ($GLOBALS['registry']->hasInterface('letter')) {
            $actions[] = ['url' => $GLOBALS['registry']->callByPackage('letter', 'compose', ''),
                'id' => 'user_to',
                'name' => _("Send message")];
        }

        $GLOBALS['page_output']->addScriptFile('stripe.js', 'horde');

        ob_start();
        require FOLKS_TEMPLATES . '/block/users.php';
        return ob_get_clean();
    }
}
