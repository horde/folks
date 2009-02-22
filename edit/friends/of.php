<?php
/**
 * $Id: friends.php 976 2008-10-07 21:24:47Z duck $
 *
 * Copyright Obala d.o.o. (www.obala.si)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */

require_once dirname(__FILE__) . '/../../lib/base.php';
require_once FOLKS_BASE . '/edit/tabs.php';

$title = _("We are friends of");

// Load driver
require_once FOLKS_BASE . '/lib/Friends.php';
$friends = Folks_Friends::singleton();

// Get list
$list = $friends->getPossibleFriends();
if ($list instanceof PEAR_Error) {
    $notification->push($list);
    $list = array();
}

// Prepare actions
$actions = array(
    array('url' => Horde::applicationUrl('user.php'),
          'img' => Horde::img('user.png', '', '', $registry->getImageDir('horde')),
          'id' => 'user',
          'name' => _("Profile")));
if ($registry->hasInterface('letter')) {
    $actions[] = array('url' => Horde::applicationUrl('user.php'),
                        'img' => Horde::img('letter.png', '', '', $registry->getImageDir('letter')),
                        'id' => 'user_to',
                        'name' => $registry->get('name', 'letter'));
};

require FOLKS_TEMPLATES . '/common-header.inc';
require FOLKS_TEMPLATES . '/menu.inc';

echo $tabs->render('friendsof');
require FOLKS_TEMPLATES . '/edit/friends.php';

require $registry->get('templates', 'horde') . '/common-footer.inc';