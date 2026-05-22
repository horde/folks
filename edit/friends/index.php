<?php

/**
 * Copyright Obala d.o.o. (www.obala.si)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */

require_once __DIR__ . '/../../lib/base.php';
require_once FOLKS_BASE . '/edit/tabs.php';

$title = _("All");

// Load driver
require_once FOLKS_BASE . '/lib/Friends.php';
$friends = Folks_Friends::singleton();

// Get friends
$list = $friends->getFriends();
if ($list instanceof PEAR_Error) {
    $notification->push($list);
    $list = [];
}

/**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
// Prepare actions
$actions = [
    ['url' => Horde::url('edit/friends/add.php'),
        'img' => Horde::img('delete.png'),
        'id' => 'user',
        'name' => _("Remove")],
    ['url' => Horde::url('user.php'),
        'img' => Horde::img('user.png'),
        'id' => 'user',
        'name' => _("View profile")]];
if ($registry->hasInterface('letter')) {
    /**
     * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
     * @deprecated Use Horde_Themes_Image::tag() instead
     * @see Horde_Deprecated::img()
     */
    $actions[] = ['url' => $registry->callByPackage('letter', 'compose', ''),
        'img' => Horde::img('letter.png'),
        'id' => 'user_to',
        'name' => _("Send message")];
}

$page_output->header([
    'title' => $title,
]);
require FOLKS_TEMPLATES . '/menu.inc';

echo $tabs->render('friends');
require FOLKS_TEMPLATES . '/edit/header.php';
require FOLKS_TEMPLATES . '/edit/friends.php';
require FOLKS_TEMPLATES . '/edit/footer.php';

$page_output->footer();
