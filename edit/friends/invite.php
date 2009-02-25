<?php
/**
 * $Id: blacklist.php 1234 2009-01-28 18:44:02Z duck $
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
require_once FOLKS_BASE . '/lib/base.php';
require_once FOLKS_BASE . '/edit/tabs.php';

$title = _("Invite friend");

// Load driver
require_once FOLKS_BASE . '/lib/Friends.php';
$friends = Folks_Friends::singleton();

// Manage adding groups
$form = new Horde_Form($vars, $title, 'addgroup');
$translated = Horde::loadConfiguration('groups.php', 'groups', 'folks');
asort($translated);
$form->addVariable(_("Friend's e-mail"), 'email', 'email', true);

$v = &$form->addVariable(_("Subject"), 'subject', 'text', true);
$v->setDefault(sprintf(_("%s Invited to join %s."), ucfirst(Auth::getAuth()), $registry->get('name', 'horde')));

$v = &$form->addVariable(_("Body"), 'body', 'longtext', true);
$body = Horde::loadConfiguration('invite.php', 'body', 'folks');
if ($body instanceof PEAR_Error) {
    $body = $body->getMessage();
} else {
    $body = sprintf($body, $registry->get('name', 'horde'),
                            Folks::getUrlFor('user', Auth::getAuth(), true),
                            Horde::applicationUrl('account/signup.php', true),
                            Auth::getAuth());
}
$v->setDefault($body);

if ($form->validate()) {
    $form->getInfo(null, $info);
    $result = Folks::sendMail($info['email'], $info['subject'], $info['body']);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(sprintf(_("Friend \"%s\" was invited to join %s."), $info['email'], $registry->get('name', 'horde')), 'horde.success');
    }
}

Horde::addScriptFile('popup.js', 'horde', true);

require FOLKS_TEMPLATES . '/common-header.inc';
require FOLKS_TEMPLATES . '/menu.inc';

echo $tabs->render('friends');
require FOLKS_TEMPLATES . '/edit/header.php';
require FOLKS_TEMPLATES . '/edit/invite.php';
require FOLKS_TEMPLATES . '/edit/footer.php';

require $registry->get('templates', 'horde') . '/common-footer.inc';