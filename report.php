<?php

use Horde\Util\Util;

/**
 * Report offensive content
 *
 * Copyright 2007-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Duck <duck@obala.net>
 * @package Folks
 */

require_once __DIR__ . '/lib/base.php';

$user = Util::getFormData('user');
if (empty($user)) {
    $notification->push(_("User is not selected"), 'horde.warning');
    Folks::getUrlFor('list', 'list')->redirect();
}

$title = _("Do you really want to report this user?");

$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title);
$form->setButtons([_("Report"), _("Cancel")]);

$enum = ['advertisement' => _("Advertisement content"),
    'terms' => _("Terms and conditions infringement"),
    'offensive' => _("Offensive content"),
    'copyright' => _("Copyright infringement")];

$form->addVariable($user, 'name', 'description', false);

$form->addHidden('', 'user', 'text', true, true);

$form->addVariable(_("Report type"), 'type', 'radio', true, false, null, [$enum]);
$form->addVariable(_("Report reason"), 'reason', 'longtext', true);

$user_id = Util::getFormData('id');

if ($form->validate()) {
    if (Util::getFormData('submitbutton') == _("Report")) {

        $body =  _("User") . ': ' . $user . "\n"
            . _("Report type") . ': ' . $enum[$vars->get('type')] . "\n"
            . _("Report reason") . ': ' . $vars->get('reason') . "\n"
            . Folks::getUrlFor('user', $user);

        require FOLKS_BASE . '/lib/Notification.php';
        $rn = new Folks_Notification();
        $result = $rn->notifyAdmins($title, $body);
        if ($result instanceof PEAR_Error) {
            $notification->push(_("User was not reported.") . ' '
                                . $result->getMessage(), 'horde.error');
        } else {
            $notification->push(_("User was reported."), 'horde.success');
        }
    } else {
        $notification->push(_("User was not reported."), 'horde.warning');
    }
    Folks::getUrlFor('user', $user)->redirect();
}

$page_output->header([
    'title' => $title,
]);
$notification->notify(['listeners' => 'status']);
$form->renderActive(null, null, null, 'post');
$page_output->footer();
