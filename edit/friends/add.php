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
require_once FOLKS_BASE . '/lib/Forms/AddFriend.php';
require_once FOLKS_BASE . '/edit/tabs.php';

$title = _("Add friend");

// Load driver
require_once FOLKS_BASE . '/lib/Friends.php';
$friends = Folks_Friends::singleton();

// Perform action
$user = Util::getFormData('user');
if ($user) {
    if ($friends->isFriend($user)) {
        $result = $friends->removeFriend($user);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } else {
            $notification->push(sprintf(_("User \"%s\" was removed from your friend list."), $user), 'horde.success');
            header('Location: ' . Horde::applicationUrl('edit/friends/index.php'));
            exit;
        }
    } else {
        $result = $friends->addFriend($user);
        if ($result instanceof PEAR_Error) {
            $notification->push($result);
        } elseif ($friends->needsApproval($user)) {
            $notification->push(sprintf(_("A confirmation was send to \"%s\"."), $user), 'horde.warning');
            $title = sprintf(_("%s added you as a friend on %s"),
                                        Auth::getAuth(),
                                        $GLOBALS['registry']->get('name', 'horde'));
            $body = sprintf(_("User %s added you to his firends list on %s. \nTo approve, go to: %s \nTo reject, go to: %s \nTo see to his profile, go to: %s \n"),
                            Auth::getAuth(),
                            $registry->get('name', 'horde'),
                            Util::addParameter(Horde::applicationUrl('edit/friends/approve.php', true, -1), 'user', Auth::getAuth()),
                            Util::addParameter(Horde::applicationUrl('edit/friends/reject.php', true, -1), 'user', Auth::getAuth()),
                            Folks::getUrlFor('user', Auth::getAuth(), true, -1));
            $friends->sendNotification($user, $title, $body);
            header('Location: ' . Horde::applicationUrl('edit/friends/index.php'));
            exit;
        } else {
            $notification->push(sprintf(_("User \"%s\" was added as your friend."), $user), 'horde.success');
            header('Location: ' . Horde::applicationUrl('edit/friends/index.php'));
            exit;
        }
    }
}

$friend_form = new Folks_AddFriend_Form($vars, _("Add or remove user"), 'blacklist');

Horde::addScriptFile('tables.js', 'horde', true);

require FOLKS_TEMPLATES . '/common-header.inc';
require FOLKS_TEMPLATES . '/menu.inc';

echo $tabs->render('add');
require FOLKS_TEMPLATES . '/edit/header.php';
require FOLKS_TEMPLATES . '/edit/add.php';
require FOLKS_TEMPLATES . '/edit/footer.php';

require $registry->get('templates', 'horde') . '/common-footer.inc';